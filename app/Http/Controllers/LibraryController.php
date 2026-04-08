<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LibraryBook;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Bab;

class LibraryController extends Controller
{

    // =============================
    // ADMIN PAGE
    // =============================

public function administrator()
{
   $books = LibraryBook::with(['kelas','mapel','bab'])->get();
    $mapels = Mapel::selectRaw('MIN(id) as id, mata_pelajaran')
            ->groupBy('mata_pelajaran')
            ->orderBy('mata_pelajaran')
            ->get();
    $babs = Bab::all();
    $kelas = Kelas::select('id','kelas')->distinct()->get();

    return view('features.lms.administrator.library',compact(
        'books',
        'mapels',
        'babs',
        'kelas'
    )); 
}


    // =============================
    // STUDENT PAGE (MAPEL LIST)
    // =============================

    public function studentLibrary()
    {
        $mapels = Mapel::orderBy('mata_pelajaran')->get();

        return view(
            'features.lms.student.library.library-siswa',
            compact('mapels')
        );
    }


    // =============================
    // MAPEL DETAIL
    // =============================

    public function mapelDetail($id)
    {

        $mapel = Mapel::findOrFail($id);

        $books = LibraryBook::with(['bab','kelas','mapel'])
                    ->where('mapel_id', $id)
                    ->get();

        $chapters = $books->groupBy(function($book){
            return $book->bab->nama_bab ?? 'Bab Tidak Diketahui';
        });

        return view(
            'features.lms.student.library.mapel',
            compact('chapters','mapel')
        );
    }


    // =============================
    // READ BOOK
    // =============================

    public function readBook($id)
    {
        $book = LibraryBook::with(['kelas','mapel','bab'])->findOrFail($id);

        return view(
            'features.lms.student.library.book-view',
            compact('book')
        );
    }


    // =============================
    // TAMBAH BUKU
    // =============================

public function store(Request $request)
{
    $data = $request->validate([
        'title' => 'required',
        'description' => 'nullable',
        'kelas_id' => 'nullable',
        'mapel_id' => 'nullable',
        'bab_id' => 'nullable',
        'cover' => 'nullable|image',
        'file' => 'nullable|file',
        'auto_cover' => 'nullable|string'
    ]);

   // Jika ada auto_cover dari halaman pertama PDF
if($request->auto_cover){
    $image = $request->auto_cover;
    $image = str_replace('data:image/jpeg;base64,', '', $image);
    $image = base64_decode($image);
    $coverName = time().'_cover.jpg';
    file_put_contents(public_path('library/sampul/'.$coverName), $image);
    $data['cover'] = $coverName;
}

    // Upload file buku
    if($request->hasFile('file')){
        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('library/file'), $fileName);
        $data['file'] = $fileName;
    }

    LibraryBook::create($data);

    return redirect()->back()->with('success','Buku berhasil ditambahkan');
}

public function getBab($mapel_id)
{
    $babs = Bab::where('mapel_id', $mapel_id)->get();

    return response()->json($babs);
}


    // =============================
    // UPDATE BUKU
    // =============================

    // =============================
// UPDATE BUKU
// =============================
public function update(Request $request, $id)
{
    $book = LibraryBook::findOrFail($id);

    $data = [
        'title' => $request->title,
        'description' => $request->description,
        'kelas_id' => $request->kelas_id,
        'mapel_id' => $request->mapel_id,
        'bab_id' => $request->bab_id
    ];

   // Jika ada auto_cover dari halaman pertama PDF
if($request->auto_cover){
    $image = $request->auto_cover;
    $image = str_replace('data:image/jpeg;base64,', '', $image);
    $image = base64_decode($image);
    $coverName = time().'_cover.jpg';
    file_put_contents(public_path('library/sampul/'.$coverName), $image);
    $data['cover'] = $coverName;
}

    // Upload file buku
    if($request->hasFile('file')){
        $fileName = time().'_'.$request->file->getClientOriginalName();
        $request->file->move(public_path('library/file'), $fileName);
        $data['file'] = $fileName;
    }

    $book->update($data);

    return redirect()->back()->with('success','Buku berhasil diupdate');
}


    // =============================
    // DELETE BUKU
    // =============================

    public function delete($id)
    {

        $book = LibraryBook::findOrFail($id);

        $book->delete();

        return redirect()->back()
            ->with('success','Buku berhasil dihapus');
    }


    // =============================
    // EDIT PAGE
    // =============================

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

}