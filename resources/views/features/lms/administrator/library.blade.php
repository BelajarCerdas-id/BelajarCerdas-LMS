@include('components/sidebar-beranda', ['headerSideNav' => 'LMS Library'])

@if (Auth::user()->role === 'Administrator')

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)]">

    <div class="my-6 mx-4">

        <main>

            <!-- Header: Search + Tambah Buku -->
            <div class="flex justify-between items-center mb-4">
                <input type="search" placeholder="Cari buku..." class="input input-bordered w-40 sm:w-64">
                <button type="button" onclick="modal_add_book.showModal()"
                        class="bg-green-500 text-white px-4 py-2 rounded">
                    Tambah Buku
                </button>
            </div>

            <!-- Tabel Buku -->
            <div class="overflow-x-auto bg-white rounded shadow">
                <table class="min-w-full text-sm">
                    <thead class="text-gray-500 text-xs border-b">
                    <tr>
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Cover</th>
                        <th class="px-4 py-2">Judul</th>
                        <th class="px-4 py-2">Kelas</th>
                        <th class="px-4 py-2">Mapel</th>
                        <th class="px-4 py-2">Bab</th>
                        <th class="px-4 py-2">File</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @foreach($books as $book)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">
                                @if($book->cover)
                                    <img src="{{ asset('library/sampul/'.$book->cover) }}" class="w-16 h-20 object-cover">
                                @endif
                            </td>
                            <td class="px-4 py-2 max-w-[200px] truncate">
                                {{ $book->title ?? '-' }}
                            </td>
                            <td class="px-4 py-2">{{ $book->kelas->kelas ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $book->mapel->mata_pelajaran ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $book->bab->nama_bab ?? '-' }}</td>
                            <td class="px-4 py-2">
                                @if($book->file)
                                    <a href="{{ asset('library/file/'.$book->file) }}" target="_blank"
                                       class="text-blue-500 text-xs">Lihat</a>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                <button onclick="openEditModal(
                                    '{{ $book->id }}',
                                    '{{ $book->title }}',
                                    '{{ $book->description }}',
                                    '{{ $book->kelas_id }}',
                                    '{{ $book->mapel_id }}',
                                    '{{ $book->bab_id }}'
                                )"
                                        class="px-2 py-1 bg-yellow-400 text-white rounded text-xs">
                                    Edit
                                </button>
                                <form action="{{ route('library.delete',$book->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="px-2 py-1 bg-red-500 text-white rounded text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </main>

    </div>

</div>

@else
<div class="flex items-center justify-center h-screen">
    <p>You do not have access</p>
</div>
@endif

{{-- Modal Tambah Buku --}}
<dialog id="modal_add_book" class="modal">
    <div class="modal-box w-96">
        <h3 class="font-bold text-center mb-4">Tambah Buku</h3>
        <form action="{{ route('library.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-2">
                <input type="text" name="title" placeholder="Judul Buku" class="input input-bordered w-full">
                <textarea name="description" placeholder="Deskripsi" class="textarea input-bordered w-full"></textarea>
                 Masukan cover
                <input type="file" name="cover" accept="image/*" class="file-input w-full">
                Masukan buku
                <input type="file" id="file_pdf" name="file" accept=".pdf,.ppt,.doc,.docx" class="file-input w-full">
                <input type="hidden" name="auto_cover" id="auto_cover">
                <select name="kelas_id" class="select select-bordered w-full">
                    <option disabled selected>Pilih Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->kelas }}</option>
                    @endforeach
                </select>
                <select name="mapel_id" id="mapel_add" class="select select-bordered w-full">
                    <option disabled selected>Pilih Mapel</option>
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}">{{ $mapel->mata_pelajaran }}</option>
                    @endforeach
                </select>
                <select name="bab_id" id="bab_add" class="select select-bordered w-full">
                    <option value="">Pilih Bab</option>
                    @foreach($babs as $bab)
                        <option value="{{ $bab->id }}" data-mapel="{{ $bab->mapel_id }}">{{ $bab->nama_bab }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

{{-- Modal Edit Buku --}}
<dialog id="modal_edit_book" class="modal">
    <div class="modal-box w-96">
        <h3 class="font-bold text-center mb-4">Edit Buku</h3>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-2">
                <input type="text" name="title" id="edit_title" class="input input-bordered w-full">
                <textarea name="description" id="edit_description" class="textarea input-bordered w-full"></textarea>
                <input type="file" name="cover" class="file-input w-full">
                <input type="file" name="file" class="file-input w-full">
                <select name="kelas_id" id="edit_kelas" class="select select-bordered w-full">
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->kelas }}</option>
                    @endforeach
                </select>
                <select name="mapel_id" id="edit_mapel" class="select select-bordered w-full" onchange="filterBab(this.value,babEdit)">
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}">{{ $mapel->mata_pelajaran }}</option>
                    @endforeach
                </select>
                <select name="bab_id" id="bab_edit" class="select select-bordered w-full">
                    <option disabled selected>Pilih Bab</option>
                    @foreach($babs as $bab)
                        <option value="{{ $bab->id }}" data-mapel="{{ $bab->mapel_id }}">{{ $bab->nama_bab }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/turn.js/4.1.0/turn.min.js"></script>

<script>
const modal_edit_book = document.getElementById('modal_edit_book');
const mapelAdd = document.getElementById('mapel_add');
const babAdd = document.getElementById('bab_add');
const mapelEdit = document.getElementById('edit_mapel');
const babEdit = document.getElementById('bab_edit');

function filterBab(mapelId,babSelect){
    babSelect.querySelectorAll('option').forEach(opt=>{
        if(!opt.dataset.mapel) return;
        opt.style.display = opt.dataset.mapel == mapelId ? 'block':'none';
    });
    babSelect.value="";
}

mapelAdd?.addEventListener('change',()=>filterBab(mapelAdd.value,babAdd));
mapelEdit?.addEventListener('change',()=>filterBab(mapelEdit.value,babEdit));

function openEditModal(id,title,description,kelas,mapel,bab){
    document.getElementById('edit_title').value=title;
    document.getElementById('edit_description').value=description;
    document.getElementById('edit_kelas').value=kelas;
    document.getElementById('edit_mapel').value=mapel;
    filterBab(mapel,babEdit);
    babEdit.value=bab;
    document.getElementById('editForm').action="/administrator/library/update/"+id;
    modal_edit_book.showModal();
}

// ================= Auto-generate cover dari PDF =================
document.getElementById("file_pdf").addEventListener("change", function(e){
    const file = e.target.files[0];
    if(!file) return;

    const coverInput = document.querySelector('input[name="cover"]');
    if(coverInput.files.length > 0) return;

    const fileReader = new FileReader();
    fileReader.onload = function(){
        const typedarray = new Uint8Array(this.result);
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.getDocument(typedarray).promise.then(function(pdf){
            pdf.getPage(1).then(function(page){
                const viewport = page.getViewport({scale:1.5});
                const canvas = document.createElement("canvas");
                const context = canvas.getContext("2d");
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                page.render({canvasContext:context, viewport:viewport}).promise.then(function(){
                    const image = canvas.toDataURL("image/jpeg");
                    document.getElementById("auto_cover").value = image;
                });
            });
        });
    };
    fileReader.readAsArrayBuffer(file);
});
</script>