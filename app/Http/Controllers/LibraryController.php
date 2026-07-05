<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LibraryBook;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TopikMateri;
use App\Models\Bab;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\StudentSchoolClass;
use App\Models\StudentTkaAnswer;
use App\Models\StudentTkaAttempt;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LibraryController extends Controller
{

    /* ================= ADMIN LIBRARY ================= */

    public function administrator()
    {
        Log::debug('Administrator library page accessed.');

        $books = LibraryBook::with([
            'kelas',
            'mapel',
            'bab',
            'topik' 
        ])->get();

        $getCurriculum = Kurikulum::orderBy('nama_kurikulum')->get();

        $topiks = TopikMateri::with(['kelas','mapel'])
            ->orderBy('nama_topik')
            ->get();

        $mapels = Mapel::selectRaw('MIN(id) as id, mata_pelajaran')
            ->groupBy('mata_pelajaran')
            ->orderBy('mata_pelajaran')
            ->get();

        $babs = Bab::orderBy('nama_bab')->get();

        $kelas = Kelas::select('id','kelas')
            ->distinct()
            ->get();

        return view(
            'features.lms.administrator.library',
            compact(
                'books',
                'mapels',
                'babs',
                'kelas',
                'topiks',
                'getCurriculum'
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

        $request->validate([
        'title' => 'required',
        'description' => 'nullable|string',
        'kelas_id' => 'nullable|exists:kelas,id',
        'mapel_id' => 'required',
        'bab_id' => 'nullable',
        'topik_materi_id' => 'nullable',
        'tipe' => 'required|in:buku,ppt,lks,video',
        'file' => 'nullable|mimes:pdf,ppt,pptx|max:20480',
        'video_url' => 'nullable|string',
        'auto_cover' => 'nullable|string'
    ]);

        $exists = LibraryBook::where('mapel_id', $request->mapel_id)
        ->where('bab_id', $request->bab_id)
        ->where('topik_materi_id', $request->topik_materi_id) // <-- ADD THIS LINE
        ->where('tipe', $request->tipe)
        ->when($request->kelas_id, function ($q) use ($request) {
            $q->where('kelas_id', $request->kelas_id);
        })
        ->when(in_array($request->tipe, ['buku', 'ppt']), function ($q) use ($request) {
            $q->where('title', $request->title);
        })
        ->exists();
        if ($exists) {
            return back()->with('error','File untuk kelas, mapel, bab dan tipe ini sudah ada');
        }

        $fileName = null;
        $coverName = null;

        /* ================= COVER ================= */
        if ($request->hasFile('cover')) {

            $cover = $request->file('cover');
            $coverName = time().'_cover.'.$cover->getClientOriginalExtension();
            $cover->move(public_path('library/sampul'), $coverName);

        } elseif ($request->auto_cover) {

            $image = str_replace('data:image/jpeg;base64,', '', $request->auto_cover);
            $image = base64_decode($image);

            $coverName = time().'_cover.jpg';

            file_put_contents(
                public_path('library/sampul/'.$coverName),
                $image
            );
        }

        /* ================= DEFAULT COVER (SAFE) ================= */
        if (!$coverName) {
            $coverName = asset('images/default-video.jpg');
        }

        /* =====================================================
            FILE HANDLING (BUKU / PPT / LKS)
        ===================================================== */
        if ($request->tipe !== 'video') {

            if ($request->hasFile('file')) {

                $file = $request->file('file');

                $fileName = time().'_'.$file->getClientOriginalName();

                $file->move(public_path('library/file'), $fileName);
            }
        }

        /* =====================================================
            VIDEO HANDLING (YOUTUBE + DRIVE + FILE VIDEO)
        ===================================================== */
        if ($request->tipe === 'video') {

            $url = $request->video_url;
            $videoId = null;

            // ================= YOUTUBE =================
            if ($url && str_contains($url, 'youtube.com')) {

                parse_str(parse_url($url, PHP_URL_QUERY), $query);
                $videoId = $query['v'] ?? null;

                $fileName = $url;
            }

            elseif ($url && str_contains($url, 'youtu.be/')) {

                $videoId = last(explode('/', $url));

                $fileName = $url;
            }

            // ================= GOOGLE DRIVE =================
            elseif ($url && str_contains($url, 'drive.google.com')) {

                preg_match('/\/d\/(.*?)\//', $url, $match);
                $videoId = $match[1] ?? null;

                $fileName = $url;

                $coverName = 'https://drive.google.com/thumbnail?id='.$videoId.'&sz=w1000';
            }

            // ================= YOUTUBE COVER =================
            if ($videoId && str_contains($url, 'youtube')) {

                $coverName = 'https://img.youtube.com/vi/'.$videoId.'/hqdefault.jpg';
            }

            // ================= UPLOAD VIDEO FILE =================
            if ($request->hasFile('video_file')) {

                $video = $request->file('video_file');

                $fileName = time().'_'.$video->getClientOriginalName();

                $video->move(public_path('library/video'), $fileName);

                $coverName = asset('images/default-video.jpg');
            }
        }

        $seriesNo = 0;

        if (
            in_array(
                $request->tipe,
                ['buku','ppt']
            )
            &&
            $request->topik_materi_id
        ) {

            $seriesNo =
                LibraryBook::where(
                    'topik_materi_id',
                    $request->topik_materi_id
                )->count() + 1;

            
        }

        $finalDescription = $request->description;

    // kalau buku / ppt ambil dari topik
    if (
        in_array($request->tipe, ['buku', 'ppt']) &&
        $request->topik_materi_id
    ) {
        $topik = TopikMateri::find($request->topik_materi_id);

        if ($topik) {
            $finalDescription = $topik->deskripsi;
        }
    }

        /* ================= SAVE DATABASE ================= */
        LibraryBook::create([
        'title' => $request->title,
        'description' => $finalDescription ?? $request->description ?? '',
        'kelas_id' => $request->kelas_id,
        'mapel_id' => $request->mapel_id,
        'bab_id' => $request->bab_id,
        'topik_materi_id' => $request->topik_materi_id,
        'series_no' => $seriesNo ?? 0,
        'file' => $fileName,
        'cover' => $coverName,
        'tipe' => $request->tipe
    ]);

        return back()->with('success','File berhasil diupload');
    }

    /* ================= GET BAB ================= */

    public function getBab(Request $request)
    {
        $kelas_id = $request->query('kelas_id');
        $mapel_id = $request->query('mapel_id');

        $babs = Bab::where('kelas_id', $kelas_id)
                ->where('mapel_id', $mapel_id)
                ->get();

        return response()->json($babs);
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
