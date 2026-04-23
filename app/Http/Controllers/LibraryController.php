<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LibraryBook;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Bab;

class LibraryController extends Controller
{

/* ================= ADMIN LIBRARY ================= */

public function administrator()
{
    $books = LibraryBook::with(['kelas','mapel','bab'])->get();

    $mapels = Mapel::select('id','mata_pelajaran')
        ->distinct()
        ->orderBy('mata_pelajaran')
        ->get();

    $babs = Bab::orderBy('nama_bab')->get();

    $kelas = Kelas::select('id','kelas')
        ->distinct()
        ->get();

    return view(
        'features.lms.administrator.library',
        compact('books','mapels','babs','kelas')
    );
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
        'features.lms.student.library.library-siswa',
        compact('mapels')
    );
}


/* ================= MAPEL DETAIL ================= */

public function mapelDetail(Request $request, $id)
{
    $mapel = Mapel::findOrFail($id);

    $query = LibraryBook::with(['bab','kelas','mapel'])
        ->where('mapel_id', $id)
        ->where('tipe', 'buku');

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
        'features.lms.student.library.mapel',
        [
            'chapters' => $chapters,
            'mapel' => $mapel,
            'kelas' => Kelas::all(),

            // BAB hanya untuk mapel ini
            'babs' => Bab::where('mapel_id', $id)
                        ->orderBy('nama_bab')
                        ->get()
        ]
    );
}

/* ================= READ BOOK ================= */

public function readBook($id)
{
    $book = LibraryBook::with(['kelas','mapel','bab'])
        ->findOrFail($id);

    $extension = strtolower(pathinfo($book->file, PATHINFO_EXTENSION));

    $isPdf = $extension === 'pdf';
    $isPpt = in_array($extension, ['ppt','pptx']);

    /* ================= RELATED BOOKS ================= */

$relatedBooks = [];

if($book->tipe == 'buku'){

    $relatedBooks = LibraryBook::where('tipe','buku')
        ->where('id','!=',$book->id)
        ->where('mapel_id',$book->mapel_id)
        ->where('bab_id',$book->bab_id)
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
        'features.lms.student.library.book-view',
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
        'kelas_id' => 'required',
        'mapel_id' => 'required',
        'bab_id' => 'required',
        'tipe' => 'required|in:buku,ppt',
        'file' => 'required|mimes:pdf,ppt,pptx|max:20480',
        'auto_cover' => 'nullable|string'
    ]);

    $exists = LibraryBook::where('kelas_id',$request->kelas_id)
        ->where('mapel_id',$request->mapel_id)
        ->where('bab_id',$request->bab_id)
        ->where('tipe',$request->tipe)
        ->exists();

    if ($exists) {
        return back()->with(
            'error',
            'File untuk kelas, mapel, bab dan tipe ini sudah ada'
        );
    }

    $file = $request->file('file');
    $fileName = time().'_'.$file->getClientOriginalName();
    $file->move(public_path('library/file'),$fileName);

    $coverName = null;

    if ($request->hasFile('cover')) {

        $cover = $request->file('cover');

        $coverName = time().'_cover.'.$cover->getClientOriginalExtension();

        $cover->move(
            public_path('library/sampul'),
            $coverName
        );
    }

    else if ($request->auto_cover) {

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
    }

    LibraryBook::create([
        'title' => $request->title,
        'kelas_id' => $request->kelas_id,
        'mapel_id' => $request->mapel_id,
        'bab_id' => $request->bab_id,
        'file' => $fileName,
        'cover' => $coverName,
        'tipe' => $request->tipe
    ]);

    return back()->with(
        'success',
        'File berhasil diupload'
    );
}


/* ================= GET BAB ================= */

public function getBab($mapel_id)
{
    return response()->json(
        Bab::where('mapel_id',$mapel_id)->get()
    );
}


/* ================= UPDATE ================= */

public function update(Request $request,$id)
{
    $book = LibraryBook::findOrFail($id);

    $data = [
        'title' => $request->title,
        'description' => $request->description,
        'kelas_id' => $request->kelas_id,
        'mapel_id' => $request->mapel_id,
        'bab_id' => $request->bab_id,
        'tipe' => $request->tipe
    ];

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

    return back()->with(
        'success',
        'Buku berhasil diupdate'
    );
}


/* ================= DELETE ================= */

public function delete($id)
{
    LibraryBook::findOrFail($id)->delete();

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
    $mapels = Mapel::all();
    $babs = Bab::all();

    return view(
        'features.lms.administrator.library_edit',
        compact('book','kelas','mapels','babs')
    );
}


/* ================= PPT LIBRARY ================= */

public function pptLibrary(Request $request)
{
    $query = LibraryBook::with(['kelas','mapel','bab'])
        ->where('tipe','ppt');

    if ($request->kelas_id) {
        $query->where('kelas_id',$request->kelas_id);
    }

    if ($request->mapel_id) {
        $query->where('mapel_id',$request->mapel_id);
    }

    if ($request->bab_id) {
        $query->where('bab_id',$request->bab_id);
    }

    $books = $query->get();

    return view(
        'features.lms.student.library.library-ppt',
        [
            'books'=>$books,
            'kelas'=>Kelas::all(),
            'mapels'=>Mapel::all(),
            'babs'=>Bab::all(),
        ]
    );
}

}