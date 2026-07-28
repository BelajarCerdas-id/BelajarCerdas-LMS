<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\LibraryBook;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TopikMateri;
use App\Models\Bab;
use App\Models\LmsQuestionBank;
use App\Models\StudentSchoolClass;
use App\Models\StudentTkaAnswer;
use App\Models\StudentTkaAttempt;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Validator;
use App\Models\UploadSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LibraryController extends Controller
{

    /* ================= ADMIN LIBRARY ================= */

    public function administrator(Request $request)
    {
       Log::debug('Administrator library page accessed.');

    $books = LibraryBook::with([
        'kelas',
        'mapel',
        'bab',
        'topik'
    ]);

    if ($request->filled('search')) {

        $search = $request->search;

        $books->where(function ($q) use ($search) {

            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('tipe', 'like', "%{$search}%")

              ->orWhereHas('mapel', function ($m) use ($search) {
                    $m->where('mata_pelajaran', 'like', "%{$search}%");
              })

              ->orWhereHas('bab', function ($b) use ($search) {
                    $b->where('nama_bab', 'like', "%{$search}%");
              })

              ->orWhereHas('topik', function ($t) use ($search) {
                    $t->where('nama_topik', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
              })

              ->orWhereHas('kelas', function ($k) use ($search) {
                    $k->where('kelas', 'like', "%{$search}%");
              });

        });

    }

    $books = $books->get();

    $uploadingVideos = UploadSession::where('status', 'uploading')
        ->latest()
        ->get();

    $uploadingVideos = UploadSession::where('status', 'uploading')
        ->orderByDesc('created_at')
        ->get();

        $topiks = TopikMateri::with(['kelas','mapel'])
            ->orderBy('nama_topik')
            ->get();

        $mapels = Mapel::selectRaw('MIN(id) as id, mata_pelajaran')
            ->groupBy('mata_pelajaran')
            ->orderBy('mata_pelajaran')
            ->where('school_partner_id', null)
            ->get();

        $babs = Bab::orderBy('nama_bab')->get();

        $kelas = Kelas::select('id','kelas')
            ->distinct()
            ->get();

        return view(
            'features.lms.administrator.library',
            compact(
                'books',
                'uploadingVideos',
                'mapels',
                'babs',
                'kelas',
                'topiks'
            )
        );
    }

    public function getMapelByKelas(Request $request)
    {
        $kelasId = $request->kelas_id;

        $mapels = Mapel::whereHas('libraryBooks', function ($q) use ($kelasId) {
            $q->where('tipe', 'lks');

            if ($kelasId) {
                $q->where('kelas_id', $kelasId);
            }
        })
        ->orderBy('mata_pelajaran')
        ->get();

        return response()->json($mapels);
    }

    /*--------ajax------ */

    public function getTopikByMapel(Request $request)
    {
        $query = TopikMateri::query();

        if ($request->mapel_id) {
            $query->where('mapel_id', $request->mapel_id);
        }

        $topik = $query->select('id', 'nama_topik', 'deskripsi', 'mapel_id', 'kelas_id')
            ->orderBy('nama_topik')
            ->get();

        return response()->json($topik);
    }

    /*======================add topik==============*/

    public function storeTopik(Request $request)
    {

        $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'topik'    => 'required|array|min:1',
        ]);

        foreach ($request->topik as $item) {

            if (empty($item['nama_topik']) && empty($item['deskripsi'])) {
                continue;
            }

            TopikMateri::create([
                'mapel_id'   => $request->mapel_id,
                'nama_topik' => $item['nama_topik'] ?? '',
                'deskripsi'  => $item['deskripsi'] ?? null,
            ]);
        }

        return back()->with('success', 'Topik materi berhasil ditambahkan');
    }

    public function getSeries($topikId, Request $request)
    {
        $tipe = $request->tipe ?? 'buku';

        $next = LibraryBook::where('topik_materi_id', $topikId)
            ->where('tipe', $tipe)
            ->count() + 1;

        return response()->json([
            'next' => $next
        ]);
    }

    /* ================= STUDENT LIBRARY ================= */

    public function studentLibrary()
    {
        $mapels = Mapel::whereHas('libraryBooks', function ($q) {
            $q->where('tipe', 'buku');
        })
        ->orderBy('mata_pelajaran')
        ->get();

        return view(
            'features.lms.components.library.library-siswa',
            compact('mapels')
        );
    }

    public function teacherLibrary()
    {
        $mapels = Mapel::whereHas('libraryBooks', function ($q) {
            $q->where('tipe', 'buku');
        })
        ->orderBy('mata_pelajaran')
        ->get();

        return view(
            'features.lms.components.library.library-siswa',
            compact('mapels')
        );
    }

    /* ================= LKS ================= */
    public function lksLibrary(Request $request)
    {
        $kelasId = $request->kelas_id;

        $mapels = Mapel::whereHas('libraryBooks', function ($q) use ($kelasId) {
            $q->where('tipe', 'lks');

            if ($kelasId) {
                $q->where('kelas_id', $kelasId);
            }
        })
        ->distinct()
        ->orderBy('mata_pelajaran')
        ->where('school_partner_id', null)
        ->get();

        $kelas = Kelas::all();

        return view('features.lms.components.library.library-lks', compact(
            'mapels',
            'kelas',
            'kelasId'
        ));
    }

    /* ================= LKS DETAIL ================= */
    public function lksDetail(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);

        $query = LibraryBook::with(['kelas','mapel','bab'])
            ->where('mapel_id', $id)
            ->where('tipe', 'lks');

        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->bab_id) {
            $query->where('bab_id', $request->bab_id);
        }

        $books = $query->get();

        $chapters = $books->groupBy(function ($book) {
            return $book->bab->nama_bab ?? 'Bab Tidak Diketahui';
        });

        return view(
            'features.lms.components.library.lks',
            [
                'chapters' => $chapters,
                'mapel' => $mapel,
                'kelas' => Kelas::all(),
                'babs' => Bab::where('mapel_id',$id)
                            ->orderBy('nama_bab')
                            ->get()
            ]
        );
    }

    /* ================= MAPEL DETAIL ================= */

    public function mapelDetail(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);

        $query = LibraryBook::with([
            'topik',
            'kelas',
            'mapel'
        ])
        ->where('mapel_id', $id)
        ->where('tipe', 'buku');

        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->topik_materi_id) {
            $query->where(
                'topik_materi_id',
                $request->topik_materi_id
            );
        }

        $books = $query->get();

        $chapters = $books->groupBy('topik_materi_id');

        return view(
            'features.lms.components.library.mapel',
            [
                'chapters' => $chapters,
                'mapel' => $mapel,
                'kelas' => Kelas::all(),
                'topiks' => TopikMateri::where('mapel_id', $id)
                            ->orderBy('nama_topik')
                            ->get()
                            ->keyBy('id')
            ]
        );
    }

    /* ================= READ BOOK ================= */

    public function readBook($id)
    {
        $book = LibraryBook::with([
            'kelas',
            'mapel',
            'bab',
            'topik'
        ])->findOrFail($id);

        $extension = strtolower(pathinfo($book->file, PATHINFO_EXTENSION));

        $isPdf = $extension === 'pdf';
        $isPpt = in_array($extension, ['ppt','pptx']);

        /* ================= RELATED BOOKS ================= */

        $relatedBooks = collect();

        if ($book->tipe == 'buku') {

            $relatedBooks = LibraryBook::where(
                'topik_materi_id',
                $book->topik_materi_id
            )
            ->where('id','!=',$book->id)
            ->latest()
            ->limit(8)
            ->get();
        }
        elseif ($book->tipe == 'lks') {

            $relatedBooks = LibraryBook::where('bab_id', $book->bab_id)
                ->where('tipe', 'lks') 
                ->where('id','!=',$book->id)
                ->latest()
                ->limit(8)
                ->get();
        }


        /* ================= RELATED PPT ================= */

        $relatedPpts = [];

        if($book->tipe == 'ppt'){

            $relatedPpts = LibraryBook::where('tipe','ppt')
                ->where('id','!=',$book->id)
                ->where('mapel_id',$book->mapel_id)
                ->latest()
                ->limit(8)
                ->get();
        }

        return view(
            'features.lms.components.library.book-view',
            compact(
                'book',
                'relatedBooks',
                'relatedPpts',
                'isPdf',
                'isPpt'
            )
        );
    }


    /* ================= STORE ================= */

    public function store(Request $request)
{

    Log::debug('LibraryController@store initiated', $request->all());

    $validator = Validator::make($request->all(), [
    'title' => 'required',
    'description' => 'nullable|string',
    'kelas_id' => 'nullable|exists:kelas,id',
    'mapel_id' => 'required',
    'bab_id' => 'nullable',
    'topik_materi_id' => 'nullable',

    'tipe' => 'required|in:buku,ppt,lks,video',

    'file' => 'nullable|mimes:pdf,ppt,pptx|max:20480',

    'video_url' => 'nullable|string',

    'video_file' => 'nullable|file|mimes:mp4,mov,avi,webm|max:512000',

    'uploaded_video_path' => 'nullable|string',

    'uploaded_video_cover' => 'nullable|string',

    'auto_cover' => 'nullable|string'
]);

if ($validator->fails()) {

    if ($request->expectsJson()) {

        return response()->json([
            'success' => false,
            'finished' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422);

    }

    return back()
        ->withErrors($validator)
        ->withInput();
}

    /* ================= FOLDER SAFETY ================= */
    $videoPath = public_path('library/video');
    $filePath  = public_path('library/file');
    $coverPath = public_path('library/sampul');

    if (!file_exists($videoPath)) mkdir($videoPath, 0777, true);
    if (!file_exists($filePath)) mkdir($filePath, 0777, true);
    if (!file_exists($coverPath)) mkdir($coverPath, 0777, true);

    /* ================= INIT ================= */
    $fileName  = null;
    $coverName = null;

    /* =====================================================
        COVER HANDLING (PRIORITY: upload > auto > default)
    ===================================================== */

    if ($request->hasFile('cover')) {

        $cover = $request->file('cover');
        $coverName = time().'_cover.'.$cover->getClientOriginalExtension();
        $cover->move($coverPath, $coverName);

    } elseif ($request->auto_cover) {

        $image = str_replace('data:image/jpeg;base64,', '', $request->auto_cover);
        $image = base64_decode($image);

        $coverName = time().'_cover.jpg';

        file_put_contents(
            $coverPath.'/'.$coverName,
            $image
        );
    }

    /* =====================================================
        FILE HANDLING (BUKU / PPT / LKS)
    ===================================================== */
    if ($request->tipe !== 'video' && $request->hasFile('file')) {

        $file = $request->file('file');

        $fileName = time().'_'.$file->getClientOriginalName();

        $file->move($filePath, $fileName);
    }

    /* =====================================================
        VIDEO HANDLING (UPLOAD / URL)
    ===================================================== */

    if ($request->tipe === 'video') {

    $url = $request->video_url ?? null;

    /*
    |--------------------------------------------
    | VIDEO SUDAH DIUPLOAD VIA AJAX
    |--------------------------------------------
    */
    if ($request->uploaded_video_path) {

        $fileName = basename($request->uploaded_video_path);

        if ($request->uploaded_video_cover) {
            $coverName = $request->uploaded_video_cover;
        }

        if (!$coverName) {
            $coverName = 'images/default-video.jpg';
        }
    }

    /*
    |--------------------------------------------
    | FALLBACK UPLOAD BIASA
    |--------------------------------------------
    */
    elseif ($request->hasFile('video_file')) {

        $video = $request->file('video_file');

        $fileName = time().'_'.$video->getClientOriginalName();

        $video->move($videoPath,$fileName);

        if (!$coverName) {
            $coverName = 'images/default-video.jpg';
        }
    }

    /*
    |--------------------------------------------
    | VIDEO URL
    |--------------------------------------------
    */
    elseif ($url) {

        $videoId = null;

        if (str_contains($url,'youtube.com')) {

            parse_str(parse_url($url,PHP_URL_QUERY),$query);

            $videoId = $query['v'] ?? null;

            $fileName = $url;

            if (!$coverName && $videoId) {
                $coverName = 'https://img.youtube.com/vi/'.$videoId.'/hqdefault.jpg';
            }

        }
        elseif (str_contains($url,'youtu.be/')) {

            $videoId = last(explode('/',$url));

            $fileName = $url;

            if (!$coverName) {
                $coverName = 'https://img.youtube.com/vi/'.$videoId.'/hqdefault.jpg';
            }

        }
        elseif (str_contains($url,'drive.google.com')) {

            preg_match('/\/d\/(.*?)\//',$url,$match);

            $videoId = $match[1] ?? null;

            $fileName = $url;

            if (!$coverName && $videoId) {
                $coverName = 'https://drive.google.com/thumbnail?id='.$videoId.'&sz=w1000';
            }

        }
        elseif (filter_var($url,FILTER_VALIDATE_URL)) {

            $fileName = $url;

        }

        if (!$coverName) {
            $coverName = 'images/default-video.jpg';
        }

    }

}

    /* ================= SERIES ================= */
    $seriesNo = 0;

    if (
        in_array($request->tipe, ['buku','ppt']) &&
        $request->topik_materi_id
    ) {
        $seriesNo = LibraryBook::where('topik_materi_id', $request->topik_materi_id)
            ->where('tipe', $request->tipe)
            ->count() + 1;
    }

    /* ================= DESCRIPTION ================= */
    $finalDescription = $request->description;

    if (
        in_array($request->tipe, ['buku','ppt']) &&
        $request->topik_materi_id
    ) {
        $topik = TopikMateri::find($request->topik_materi_id);

        if ($topik) {
            $finalDescription = $topik->deskripsi;
        }
    }

    /* ================= SAVE ================= */
    $book = LibraryBook::create([
    'title' => $request->title,
    'description' => $finalDescription ?? '',
    'kelas_id' => $request->kelas_id,
    'mapel_id' => $request->mapel_id,
    'bab_id' => $request->bab_id,
    'topik_materi_id' => $request->topik_materi_id,
    'series_no' => $seriesNo,
    'file' => $fileName,
    'cover' => $coverName,
    'tipe' => $request->tipe
    ]);

    if ($request->expectsJson()) {

    $book->load([
        'kelas',
        'mapel',
        'bab',
        'topik'
    ]);

    $row = view(
    'features.lms.administrator.video-row',
    compact('book')
)->render();

return response()->json([
    'success'  => true,
    'finished' => true,
    'message'  => 'Upload selesai',

    // HTML row yang akan menggantikan row sementara
    'row' => $row,

    // (opsional) tetap kirim data book kalau nanti diperlukan JS lain
    'book' => [
        'id'     => $book->id,
        'title'  => $book->title,
        'cover'  => $book->cover,
        'file'   => $book->file,
        'kelas'  => optional($book->kelas)->kelas,
        'mapel'  => optional($book->mapel)->mata_pelajaran,
        'bab'    => optional($book->bab)->nama_bab,
        'tipe'   => $book->tipe,
    ]
]);
}

    Log::debug('LibraryBook created', ['id' => $book->id]);

    return back()->with('success', 'File berhasil diupload');
}

    /* ================= GET BAB ================= */

    public function getBab($mapel_id)
    {
        return response()->json(
            Bab::where('mapel_id',$mapel_id)->get()
        );
    }


    /* ================= UPDATE ================= */

    public function update(Request $request, $id)
    {
        $book = LibraryBook::findOrFail($id);

        // Capture old classification to detect movement
        $oldTopikId = $book->topik_materi_id;
        $oldTipe = $book->tipe;

        $data = [
            'title' => $request->filled('title') ? $request->title : $book->title,
            'kelas_id' => $request->kelas_id ?? null,
            'mapel_id' => $request->mapel_id,
            'bab_id' => $request->bab_id,
            'topik_materi_id' => $request->topik_materi_id,
            'tipe' => $request->tipe ?? $book->tipe,
        ];

        if ($request->filled('description')) {
            $data['description'] = $request->description;
        } else {
            $data['description'] = $book->description;
        }

        if ($request->auto_cover) {

            $image = str_replace(
                'data:image/jpeg;base64,',
                '',
                $request->auto_cover
            );

            $image = base64_decode($image);

            $coverName = time().'_cover.jpg';

            file_put_contents(
                public_path('library/sampul/'.$coverName),
                $image
            );

            $data['cover'] = $coverName;
        }

        if ($request->hasFile('file')) {

            $file = $request->file('file');

            $fileName = time().'_'.$file->getClientOriginalName();

            $file->move(
                public_path('library/file'),
                $fileName
            );

            $data['file'] = $fileName;
        }

        $book->update($data);

        // Reorder the old topic to fill any gaps left by the moved book
        if ($oldTopikId && ($oldTopikId != $book->topik_materi_id || $oldTipe != $book->tipe)) {
            $this->reorderSeries($oldTopikId, $oldTipe);
        }

        // Reorder the new topic destination to ensure flawless sequencing
        if ($book->topik_materi_id) {
            $this->reorderSeries($book->topik_materi_id, $book->tipe);
        }

        return back()->with(
            'success',
            'Buku berhasil diupdate'
        );
    }


    /* ================= DELETE ================= */

    public function delete($id)
    {
        $book = LibraryBook::findOrFail($id);
        
        $topikId = $book->topik_materi_id;
        $tipe = $book->tipe;
        
        $book->delete();

        // Fill the gap left by the deleted book
        $this->reorderSeries($topikId, $tipe);

        return back()->with(
            'success',
            'Buku berhasil dihapus'
        );
    }


    /* ================= EDIT ================= */

    public function edit($id)
    {
        $book = LibraryBook::findOrFail($id);

        $kelas = Kelas::all();

        $mapels = Mapel::where('kelas_id', $book->kelas_id)->get();

        $babs = Bab::where('mapel_id', $book->mapel_id)->get();

        $topiks = TopikMateri::where('mapel_id', $book->mapel_id)
        ->orderBy('nama_topik')
        ->get();

        return view(
            'features.lms.administrator.library_edit',
            compact('book','kelas','mapels','babs','topiks')
        );
    }


    /* ================= PPT LIBRARY ================= */

    public function pptLibrary(Request $request)
    {
        $query = LibraryBook::with([
        'kelas',
        'mapel',
        'topik'
        ])
        ->where('tipe','ppt');

        if ($request->kelas_id) {
            $query->where('kelas_id',$request->kelas_id);
        }

        if ($request->mapel_id) {
            $query->where('mapel_id',$request->mapel_id);
        }

        if ($request->topik_materi_id) {
            $query->where(
                'topik_materi_id',
                $request->topik_materi_id
            );
        }

        $books = $query->get();

        return view(
            'features.lms.components.library.library-ppt',
            [
                'books'  => $books,
                'kelas'  => Kelas::all(),
                'mapels' => Mapel::all(),

                'babs'   => Bab::orderBy('nama_bab')->get(),

                'topiks' => TopikMateri::with([
                    'kelas',
                    'mapel'
                ])->get(),
            ]
        );
    }

    public function videoLibrary(Request $request)
    {
        $query = LibraryBook::with(['kelas','mapel'])
            ->where('tipe','video');

        if ($request->kelas_id) {
            $query->where('kelas_id',$request->kelas_id);
        }

        if ($request->mapel_id) {
            $query->where('mapel_id',$request->mapel_id);
        }

        $videos = $query->latest()->get();

        $mapels = Mapel::whereHas('libraryBooks', function ($q) {
            $q->where('tipe', 'video');
        })
        ->orderBy('mata_pelajaran')
        ->where('school_partner_id', null)
        ->get();

        return view(
            'features.lms.components.library.library-video',
            compact('videos', 'mapels')
        );
    }

    /* ================= HELPER: REORDER SERIES ================= */

    private function reorderSeries($topik_materi_id, $tipe)
    {
        if (!$topik_materi_id || !in_array($tipe, ['buku', 'ppt'])) {
            return;
        }

        $books = LibraryBook::where('topik_materi_id', $topik_materi_id)
            ->where('tipe', $tipe)
            ->orderBy('series_no', 'asc') // Maintain current sequential order
            ->orderBy('id', 'asc')        // Fallback for duplicates
            ->get();

        $currentSeriesNo = 1;

        foreach ($books as $book) {
            // Find "Series Materi X" and replace the number, preserving custom text if any
            $newTitle = preg_replace('/Series Materi \d+/i', 'Series Materi ' . $currentSeriesNo, $book->title);

            $book->update([
                'series_no' => $currentSeriesNo,
                'title'     => $newTitle
            ]);

            $currentSeriesNo++;
        }
    }

    public function topikManagement(Request $request)
    {
        $query = \App\Models\TopikMateri::with(['mapel', 'kelas'])
            ->orderBy('nama_topik');

        if ($request->mapel_id) {
            $query->where('mapel_id', $request->mapel_id);
        }

        $topiks = $query->get();

        $mapels = \App\Models\Mapel::query()
            ->selectRaw('MIN(id) as id, mata_pelajaran')
            ->groupBy('mata_pelajaran')
            ->orderBy('mata_pelajaran')
            ->where('school_partner_id', null)
            ->get();

        return view('features.lms.administrator.topik-management', compact('topiks', 'mapels'));
    }

    public function updateTopik(Request $request, $id)
    {
        $request->validate([
            'mapel_id' => 'required',
            'nama_topik' => 'required',
            'deskripsi' => 'nullable'
        ]);

        $topik = TopikMateri::findOrFail($id);

        $topik->update([
            'mapel_id' => $request->mapel_id,
            'nama_topik' => $request->nama_topik,
            'deskripsi' => $request->deskripsi
        ]);

        return back()
            ->with('success', 'Topik berhasil diperbarui');
    }

public function deleteTopik($id)
{
    $topik = TopikMateri::findOrFail($id);

    $jumlahDipakai = LibraryBook::where('topik_materi_id', $id)->count();

    if ($jumlahDipakai > 0) {
        return redirect()->back()->with([
            'error' => "Topik \"{$topik->nama_topik}\" sedang digunakan oleh {$jumlahDipakai} materi dan tidak dapat dihapus."
        ]);
    }

    $topik->delete();

    return redirect()->back()->with([
        'success' => 'Topik berhasil dihapus.'
    ]);
}   

    public function initVideoUpload(Request $request)
        {
            $uploadId = Str::uuid()->toString();

            $session = UploadSession::create([
                'upload_id' => $uploadId,
                'file_name' => $request->file_name,
                'total_chunks' => $request->total_chunks,
                'uploaded_chunks' => 0,
                'status' => 'uploading'
            ]);

            return response()->json($session);
        }

    public function uploadVideoChunk(Request $request)
    {
    $request->validate([
        'upload_id' => 'required',
        'chunk_index' => 'required',
        'file' => 'required|file'
    ]);

    $session = UploadSession::where('upload_id', $request->upload_id)->firstOrFail();

    $dir = storage_path("app/video_chunks/{$request->upload_id}");

    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    $request->file('file')->move($dir, $request->chunk_index);

    $session->increment('uploaded_chunks');

    $session->refresh();

    $progress = round(
        ($session->uploaded_chunks / $session->total_chunks) * 100
    );

    return response()->json([
        'progress' => $progress,
        'uploaded' => $session->uploaded_chunks,
        'upload_id' => $session->upload_id
    ]);

    }

    public function completeVideoUpload(Request $request)
    {
    $session = UploadSession::where('upload_id', $request->upload_id)->firstOrFail();

    $dir = storage_path("app/video_chunks/{$session->upload_id}");

    $fileName = time() . "_" . $session->file_name;
    $finalPath = public_path("library/video/" . $fileName);

    if (!file_exists(public_path("library/video"))) {
        mkdir(public_path("library/video"), 0777, true);
    }

    $out = fopen($finalPath, "ab");

    for ($i = 0; $i < $session->total_chunks; $i++) {

        $chunkPath = $dir . "/" . $i;

        if (!file_exists($chunkPath)) continue;

        $in = fopen($chunkPath, "rb");
        stream_copy_to_stream($in, $out);
        fclose($in);
    }

    fclose($out);

    // cleanup
    array_map('unlink', glob("$dir/*"));
    @rmdir($dir);

    $session->update([
        'path' => "library/video/" . $fileName,
        'status' => 'done'
    ]);

    return response()->json([
    'status' => true,
    'finished' => true,
    'upload_id' => $session->upload_id,
    'path' => "library/video/".$fileName
    ]);
    }

    public function row($id)
{
    $video = LibraryBook::with([
        'kelas',
        'mapel',
        'bab',
        'topik'
    ])->findOrFail($id);

    return view(
        'features.lms.administrator.partials.video_row',
        compact('video')
    );
}

public function startUpload(Request $request)
{
    $request->validate([
        'file_name'     => 'required|string',
        'file_size'     => 'required|integer',
        'chunk_size'    => 'required|integer',
        'total_chunks'  => 'required|integer',
        'title'         => 'required|string',
        'kelas_id'      => 'nullable',
        'mapel_id'      => 'required',
        'bab_id'        => 'nullable',
        'description'   => 'nullable',
        'cover'         => 'nullable|string',
    ]);

    $upload = UploadSession::create([
        'upload_id'        => (string) Str::uuid(),
        'file_name'        => $request->file_name,
        'file_size'        => $request->file_size,
        'chunk_size'       => $request->chunk_size,
        'total_chunks'     => $request->total_chunks,
        'uploaded_chunks'  => 0,
        'status'           => 'uploading',

        'title'            => $request->title,
        'description'      => $request->description,
        'kelas_id'         => $request->kelas_id,
        'mapel_id'         => $request->mapel_id,
        'bab_id'           => $request->bab_id,
        'cover'            => $request->cover,
    ]);

    Storage::makeDirectory("uploads/{$upload->upload_id}");

    return response()->json([
        'success'   => true,
        'upload_id' => $upload->upload_id
    ]);
}

public function uploadChunk(Request $request)
{
    $request->validate([
        'upload_id' => 'required',
        'chunk' => 'required|file',
        'index' => 'required|integer'
    ]);

    $upload = UploadSession::where(
        'upload_id',
        $request->upload_id
    )->firstOrFail();

    $dir = storage_path(
        'app/uploads/'.$upload->upload_id
    );

    if (!file_exists($dir)) {
        mkdir($dir,0777,true);
    }

    $chunkName = "chunk_".$request->index;

    move_uploaded_file(
        $request->file('chunk')->getPathname(),
        $dir.'/'.$chunkName
    );

    $upload->uploaded_chunks = max(
        $upload->uploaded_chunks,
        $request->index + 1
    );

    $upload->save();

    return response()->json([
        'success'=>true,
        'uploaded'=>$upload->uploaded_chunks,
        'total'=>$upload->total_chunks
    ]);
}

public function finishUpload(Request $request)
{
    $request->validate([
        'upload_id'=>'required'
    ]);

    $upload = UploadSession::where(
        'upload_id',
        $request->upload_id
    )->firstOrFail();

    if($upload->uploaded_chunks < $upload->total_chunks){

        return response()->json([
            'success'=>false,
            'message'=>'Chunk belum lengkap.'
        ],422);

    }

    $fileName = $this->mergeChunks($upload);

    $book = LibraryBook::create([
        'title'=>$upload->title,
        'description'=>$upload->description,
        'kelas_id'=>$upload->kelas_id,
        'mapel_id'=>$upload->mapel_id,
        'bab_id'=>$upload->bab_id,
        'cover'=>$upload->cover,
        'file'=>$fileName,
        'tipe'=>'video'
    ]);

    $upload->status='finished';
    $upload->save();

    return response()->json([
        'success'=>true,
        'book'=>$book
    ]);
}

public function uploadStatus($uploadId)
{
    $upload = UploadSession::where(
        'upload_id',
        $uploadId
    )->first();

    if(!$upload){

        return response()->json([
            'success'=>false
        ],404);

    }

    return response()->json([

        'success'=>true,

        'uploaded_chunks'=>$upload->uploaded_chunks,

        'total_chunks'=>$upload->total_chunks,

        'status'=>$upload->status,

        'progress'=>round(
            ($upload->uploaded_chunks/$upload->total_chunks)*100
        )

    ]);
}

private function mergeChunks(UploadSession $upload)
{
    $folder = storage_path(
        'app/uploads/'.$upload->upload_id
    );

    $extension = pathinfo(
        $upload->file_name,
        PATHINFO_EXTENSION
    );

    $finalName =
        time().'_'.$upload->file_name;

    $output =
        public_path('library/video/'.$finalName);

    $dest = fopen($output,'ab');

    for($i=0;$i<$upload->total_chunks;$i++){

        $chunk = $folder."/chunk_".$i;

        $source = fopen($chunk,'rb');

        stream_copy_to_stream(
            $source,
            $dest
        );

        fclose($source);

        unlink($chunk);

    }

    fclose($dest);

    @rmdir($folder);

    return $finalName;
}

    // STUDENT TKA PRACTICE TEST
    public function studentTKASubjectList($role)
    {
        $subjects = Mapel::whereNull('school_partner_id')->count();

        return view('features.lms.components.library.tka.student.student-tka-subject-list', compact('role', 'subjects'));
    }

    public function paginateTKASubject($role)
    {
        $user = Auth::user();
        
        $getClass = StudentSchoolClass::with(['SchoolClass.Kelas'])->where('student_class_status', 'active')->where('student_id', $user->id)->where(function ($q) {
            $q->whereNull('academic_action')->orWhere('academic_action', '');
        })->first();

        $currentClass = (int) preg_replace('/\D/', '', $getClass->SchoolClass->Kelas->kelas);

        if ($currentClass <= 6) {
            $targetClass = 6;
        } elseif ($currentClass <= 9) {
            $targetClass = 9;
        } else {
            $targetClass = 12;
        }

        $subjects = Mapel::whereHas('Kelas', function ($query) use ($targetClass) {
            $query->where('kelas', 'Kelas ' . $targetClass);
        })->whereNull('school_partner_id')->where('status_mata_pelajaran', 'active')->whereHas('LmsQuestionBank', function ($query) {
            $query->where('question_category', 'TKA');
        })
        ->withCount(['LmsQuestionBank as total_question'])->get();

        $subjectCount = $subjects->count();

        return response()->json([
            'data' => $subjects,
            'subjectCount' => $subjectCount,
            'studentTkaPracticeTest' => '/lms/:role/tka-simulation/class/:kelasId/subject/:mapelId/practice-test',
        ]);
    }

    public function studentTKAPracticeTest($role, $kelasId, $mapelId)
    {
        return view('features.lms.components.library.tka.student.student-tka-practice-test', compact('role', 'kelasId', 'mapelId'));
    }

    public function studentTKAPracticeTestForm(Request $request, $role, $kelasId, $mapelId)
    {
        $user = UserAccount::with('StudentProfile')->find(Auth::id());

        $attempt = StudentTkaAttempt::where('student_id', $user->id)->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('status', 'active')->latest()->first();

        if (!$attempt) {
            return response()->json([
                'has_attempt' => false,
            ]);
        }

        $questionIds = $attempt->question_order ?? [];

        if (empty($questionIds)) {

            return response()->json([
                'message' => 'Question order tidak ditemukan.'
            ], 500);

        }

        // Ambil soal berdasarkan ID yang sudah dipilih
        $questions = LmsQuestionBank::with([
            'LmsQuestionOption',
            'Mapel'
        ])
        ->whereIn('id', $questionIds)
        ->get()
        ->sortBy(function ($question) use ($questionIds) {
            return array_search($question->id, $questionIds);
        })
        ->values();

        $optionOrder = $attempt->option_order ?? [];
        $isOptionOrderChanged = false;

        // SHUFFLE OPTIONS
        $questions->transform(function ($question) use ($attempt, &$optionOrder, &$isOptionOrderChanged) {

            $type = strtoupper($question->tipe_soal ?? '');

            $options = $question->LmsQuestionOption;

            if (!$options) {
                return $question;
            }

            $publishedOptionIds = $options->sortBy('id')->pluck('id')->implode(',');

            // $optionOrder = $attempt->option_order ?? [];

            $cacheName = "mcq_{$question->id}_{$publishedOptionIds}";

            // MCQ / MCMA
            if (in_array($type, ['MCQ','MCMA'])) {

                if (isset($optionOrder[$cacheName])) {

                    $cachedIds = $optionOrder[$cacheName];

                    $sorted = $options
                        ->whereIn('id', $cachedIds)
                        ->sortBy(function ($opt) use ($cachedIds) {
                            return array_search($opt->id, $cachedIds);
                        })
                        ->values();

                } else {

                    $sorted = $options->shuffle()->values();

                    $optionOrder[$cacheName] = $sorted
                        ->pluck('id')
                        ->toArray();

                    // $attempt->option_order = $optionOrder;
                    // $attempt->save();
                    $isOptionOrderChanged = true;
                }

                $question->setRelation('LmsQuestionOption', $sorted);
            }

            // MATCHING
            if ($type === 'MATCHING') {

                $left = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'left';
                })->values();

                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'right';
                })->values();

                $publishedRightIds = $right->sortBy('id')->pluck('id')->implode(',');

                $cacheName = "matching_{$question->id}_{$publishedRightIds}";

                if (isset($optionOrder[$cacheName])) {

                    $cachedIds = $optionOrder[$cacheName];

                    $right = $right
                        ->whereIn('id', $cachedIds)
                        ->sortBy(function ($opt) use ($cachedIds) {
                            return array_search($opt->id, $cachedIds);
                        })
                        ->values();

                } else {

                    $right = $right->shuffle()->values();

                    $optionOrder[$cacheName] = $right
                        ->pluck('id')
                        ->toArray();

                    // $attempt->option_order = $optionOrder;
                    // $attempt->save();
                    $isOptionOrderChanged = true;
                }

                $shuffled = collect();

                foreach ($left as $l) {
                    $shuffled->push($l);
                }

                foreach ($right as $r) {
                    $shuffled->push($r);
                }

                $question->setRelation('LmsQuestionOption', $shuffled);
            }

            if ($type === 'PG_KOMPLEKS') {

                // AMBIL ITEMS (ROW)
                $items = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'item';
                })->values();

                $publishedItemIds = $items->sortBy('id')->pluck('id')->implode(',');
                
                $cacheName = "pgk_item_{$question->id}_{$publishedItemIds}";

                if (isset($optionOrder[$cacheName])) {

                    $cachedIds = $optionOrder[$cacheName];

                    $items = $items
                        ->whereIn('id', $cachedIds)
                        ->sortBy(function ($opt) use ($cachedIds) {
                            return array_search($opt->id, $cachedIds);
                        })
                        ->values();

                } else {

                    $items = $items->shuffle()->values();

                    $optionOrder[$cacheName] = $items
                        ->pluck('id')
                        ->toArray();

                    // $attempt->option_order = $optionOrder;
                    // $attempt->save();
                    $isOptionOrderChanged = true;
                }

                // AMBIL CATEGORY (COLUMN)
                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'category';
                })->values();

                $publishedRightIds = $right->sortBy('id')->pluck('id')->implode(',');

                $cacheName = "pgk_category_{$question->id}_{$publishedRightIds}";

                if (isset($optionOrder[$cacheName])) {

                    $cachedIds = $optionOrder[$cacheName];

                    $right = $right
                        ->whereIn('id', $cachedIds)
                        ->sortBy(function ($opt) use ($cachedIds) {
                            return array_search($opt->id, $cachedIds);
                        })
                        ->values();

                } else {

                    $right = $right->shuffle()->values();

                    $optionOrder[$cacheName] = $right
                        ->pluck('id')
                        ->toArray();

                    // $attempt->option_order = $optionOrder;
                    // $attempt->save();
                    $isOptionOrderChanged = true;
                }

                // GABUNGKAN ITEMS + CATEGORY
                $shuffled = collect();

                foreach ($items as $item) {
                    $shuffled->push($item);
                }

                foreach ($right as $cat) {
                    $shuffled->push($cat);
                }

                $question->setRelation('LmsQuestionOption', $shuffled);

                $shuffled = collect();

                foreach ($items as $l) {
                    $shuffled->push($l);
                }

                foreach ($right as $r) {
                    $shuffled->push($r);
                }

                $question->setRelation('LmsQuestionOption', $shuffled);
            }

            return $question;
        });

        if ($isOptionOrderChanged) {
            $attempt->update([
                'option_order' => $optionOrder
            ]);
        }
        
        $questionsAnswer = collect();

        if ($attempt) {
            // STUDENT ANSWER
            $questionsAnswer = StudentTkaAnswer::with(['StudentTkaAttempt'])->where('attempt_id', $attempt->id)->get()->mapWithKeys(function ($item) {
    
                    $data = $item->attributesToArray();
    
                    if (is_string($data['answer_value'])) {
                        $decoded = json_decode($data['answer_value'], true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $data['answer_value'] = $decoded;
                        }
                    }
    
                    $question = LmsQuestionBank::with('LmsQuestionOption')->find($item->question_id);
    
                    $isCorrect = false;
    
                    if ($question) {
    
                        $type = $question->tipe_soal;
    
                        $correctOptions = $question->LmsQuestionOption
                            ->where('is_correct', 1)
                            ->pluck('options_key')
                            ->values()
                            ->toArray();
    
                        $studentAnswer = $data['answer_value'];
    
                        if ($type === 'MCQ') {
                            $isCorrect = $studentAnswer === ($correctOptions[0] ?? null);
                        }
    
                        if ($type === 'MCMA') {
    
                            if (!is_array($studentAnswer)) {
                                $isCorrect = false;
                            } else {
    
                                sort($correctOptions);
                                sort($studentAnswer);
    
                                $isCorrect = $studentAnswer === $correctOptions;
                            }
                        }
    
                        if ($type === 'MATCHING') {
    
                            if (is_string($studentAnswer)) {
                                $studentAnswer = json_decode($studentAnswer, true);
                            }
    
                            if (!is_array($studentAnswer)) {
                                $isCorrect = false;
                            } else {
    
                                $correctPairs = $question->LmsQuestionOption
                                    ->filter(function ($opt) {
                                        return isset($opt->extra_data['side']) 
                                            && $opt->extra_data['side'] === 'left';
                                    })
                                    ->mapWithKeys(function ($opt) {
                                        return [
                                            trim($opt->options_key) =>
                                            trim($opt->extra_data['pair_with'] ?? '')
                                        ];
                                    })
                                    ->toArray();
    
                                $normalizedStudentAnswer = collect($studentAnswer)
                                    ->mapWithKeys(function ($value, $key) {
                                        return [trim($key) => trim($value)];
                                    })
                                    ->toArray();
    
                                ksort($correctPairs);
                                ksort($normalizedStudentAnswer);
    
                                $isCorrect = $correctPairs === $normalizedStudentAnswer;
                            }
                        }
    
                        if ($type === 'PG_KOMPLEKS') {
    
                            if (is_string($studentAnswer)) {
                                $studentAnswer = json_decode($studentAnswer, true);
                            }
    
                            if (!is_array($studentAnswer)) {
                                $isCorrect = false;
                            } else {
    
                                $correctPairs = $question->LmsQuestionOption
                                    ->filter(function ($opt) {
                                        return isset($opt->extra_data['side']) 
                                            && $opt->extra_data['side'] === 'item';
                                    })
                                    ->mapWithKeys(function ($opt) {
                                        return [
                                            trim($opt->options_key) =>
                                            trim($opt->extra_data['answer'] ?? '')
                                        ];
                                    })
                                    ->toArray();
    
                                $normalizedStudentAnswer = collect($studentAnswer)
                                    ->mapWithKeys(function ($value, $key) {
                                        return [trim($key) => trim($value)];
                                    })
                                    ->toArray();
    
                                ksort($correctPairs);
                                ksort($normalizedStudentAnswer);
    
                                $isCorrect = $correctPairs === $normalizedStudentAnswer;
                            }
                        }
                    }
    
                    $data['is_correct'] = $isCorrect;
    
                    return [
                        $item->question_id => $data
                    ];
                });
        }

        return response()->json([
            'has_attempt' => true,
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'kelas_id' => $attempt->kelas_id,
                'mapel_id' => $attempt->mapel_id,
                'total_question' => $attempt->total_question,
            ],

            'data' => $questions,
            'user' => $user,
            'questionsAnswer' => $questionsAnswer,
        ]);
    }

    private function generateQuestionOrder($kelasId, $mapelId, $totalQuestion = 15)
    {
        // Base query (exclude ESSAY)
        $baseQuery = LmsQuestionBank::where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('status_bank_soal', 'Publish')
        ->where('question_category', 'TKA')->where('tipe_soal', '!=', 'ESSAY');

        // Ambil seluruh tipe soal yang tersedia
        $availableTypes = (clone $baseQuery)->select('tipe_soal')->distinct()->pluck('tipe_soal');

        $selectedQuestionIds = collect();

        // Ambil minimal 1 soal dari setiap tipe
        foreach ($availableTypes as $type) {

            $question = (clone $baseQuery)->where('tipe_soal', $type)->inRandomOrder()->first();

            if ($question) {
                $selectedQuestionIds->push($question->id);
            }
        }

        // Hitung sisa soal
        $remaining = max($totalQuestion - $selectedQuestionIds->count(), 0);

        // Ambil soal random selain yang sudah dipilih
        if ($remaining > 0) {

            $randomQuestionIds = (clone $baseQuery)->whereNotIn('id', $selectedQuestionIds)->inRandomOrder()->limit($remaining)->pluck('id');

            $selectedQuestionIds = $selectedQuestionIds->merge($randomQuestionIds);
        }

        // Acak kembali agar soal wajib tidak selalu muncul di awal
        return $selectedQuestionIds->shuffle()->values()->toArray();
    }

    public function studentTkaStartPractice(Request $request, $role, $kelasId, $mapelId)
    {
        $userId = Auth::id();

        StudentTkaAttempt::where('student_id', $userId)->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('status', 'active')->update([
            'status' => 'inactive'
        ]);

        $questionOrder = $this->generateQuestionOrder($kelasId, $mapelId);

        $attempt = StudentTkaAttempt::create([
            'student_id'     => $userId,
            'kelas_id'       => $kelasId,
            'mapel_id'       => $mapelId,
            'total_question' => count($questionOrder),
            'question_order' => $questionOrder,
            'status'         => 'active',
        ]);

        return response()->json([
            'attempt_id' => $attempt->id,
        ]);
    }

    public function studentTkaResstartPractice(Request $request, $role, $kelasId, $mapelId)
    {
        $userId = Auth::id();

        StudentTkaAttempt::where('student_id', $userId)->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('status', 'active')->update([
            'status' => 'inactive'
        ]);

        $questionOrder = $this->generateQuestionOrder($kelasId, $mapelId);

        $attempt = StudentTkaAttempt::create([
            'student_id'     => $userId,
            'kelas_id'       => $kelasId,
            'mapel_id'       => $mapelId,
            'total_question' => count($questionOrder),
            'question_order' => $questionOrder,
            'status'         => 'active',
        ]);

        return response()->json([
            'attempt_id' => $attempt->id,
        ]);
    }

    public function studentTkaSubmitAnswer(Request $request, $role, $kelasId, $mapelId, $attemptId)
    {
        $userId = Auth::id();

        $attempt = StudentTkaAttempt::where('id', $attemptId)
            ->where('status', 'active')
            ->where('student_id', $userId)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:lms_question_banks,id',
            'answer_value' => [
                Rule::requiredIf(!$request->auto_submit)
            ],
            'status_answer' => 'required|in:draft,submitted',
        ], [
            'answer_value.required' => 'Jawaban tidak boleh kosong.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ambil soal
        $question = LmsQuestionBank::findOrFail($request->question_id);

        // Ambil jawaban siswa
        $answer = StudentTkaAnswer::where('attempt_id', $attemptId)
            ->where('question_id', $request->question_id)
            ->first();

        // NORMALISASI JAWABAN
        $answerData = $request->answer_value;

        if (is_string($answerData)) {
            $decoded = json_decode($answerData, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $answerData = $decoded;
            }
        }

        if ($answerData === '' || $answerData === [] || $answerData === null) {
            $answerData = null;
        }

        // HITUNG SCORE
        $scorePerQuestion = 100 / $attempt->total_question;

        $isCorrect = false;
        $score = 0;

        if ($answerData !== null) {

            switch ($question->tipe_soal) {

                case 'MCQ':

                    $correctOption = $question->lmsQuestionOption()
                        ->where('is_correct', 1)
                        ->first();

                    $isCorrect = $correctOption &&
                        $correctOption->options_key == $answerData;

                break;

                case 'MCMA':

                    if (is_array($answerData)) {

                        $correctOptions = $question->lmsQuestionOption()
                            ->where('is_correct', 1)
                            ->pluck('options_key')
                            ->toArray();

                        sort($correctOptions);
                        sort($answerData);

                        $isCorrect = ($correctOptions == $answerData);
                    }

                break;

                case 'MATCHING':

                    if (is_array($answerData)) {

                        $correctPairs = $question->lmsQuestionOption()
                            ->get()
                            ->filter(function ($opt) {
                                return isset($opt->extra_data['side'])
                                    && $opt->extra_data['side'] === 'left';
                            })
                            ->mapWithKeys(function ($opt) {
                                return [
                                    $opt->options_key => $opt->extra_data['pair_with'] ?? null
                                ];
                            })
                            ->toArray();

                        ksort($correctPairs);
                        ksort($answerData);

                        $isCorrect = ($correctPairs == $answerData);
                    }

                break;

                case 'PG_KOMPLEKS':

                    if (is_array($answerData)) {

                        $correctAnswers = $question->lmsQuestionOption()
                            ->get()
                            ->filter(function ($opt) {
                                return isset($opt->extra_data['side'])
                                    && $opt->extra_data['side'] === 'item';
                            })
                            ->mapWithKeys(function ($opt) {
                                return [
                                    $opt->options_key => $opt->extra_data['answer']
                                ];
                            })
                            ->toArray();

                        ksort($correctAnswers);
                        ksort($answerData);

                        $isCorrect = ($correctAnswers == $answerData);
                    }

                break;

                case 'ESSAY':

                    // Essay menunggu penilaian guru
                    $score = 0;

                break;
            }

            if ($question->tipe_soal !== 'ESSAY') {
                $score = $isCorrect ? $scorePerQuestion : 0;
            }
        }

        // SIMPAN JAWABAN
        $answer = StudentTkaAnswer::where('question_id', $request->question_id)
            ->whereHas('StudentTkaAttempt', function ($query) use ($userId, $kelasId, $mapelId) {
                $query->where('student_id', $userId)
                    ->where('kelas_id', $kelasId)
                    ->where('mapel_id', $mapelId);
            })
            ->first();

        if ($answer) {

            $updateData = [
                'attempt_id'     => $attemptId,
                'status_answer'  => $request->status_answer,
                'answer_value'   => $answerData,
                'question_score' => $score,
            ];

            $answer->update($updateData);

        } else {

            StudentTkaAnswer::create([
                'attempt_id'     => $attemptId,
                'question_id'    => $request->question_id,
                'answer_value'   => $answerData,
                'question_score' => $score,
                'status_answer'  => $request->status_answer,
            ]);

        }
        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil disimpan',
        ]);
    }
}