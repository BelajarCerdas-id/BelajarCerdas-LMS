@include('components/sidebar-beranda', ['headerSideNav' => 'LMS Library'])

@php
use Illuminate\Support\Str;
@endphp

@if (Auth::user()->role === 'Administrator')

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)]">

<div class="my-6 mx-4">

<main>

<!-- HEADER -->

<div class="flex justify-between items-center mb-6">

<input
type="search"
placeholder="Cari buku..."
class="border rounded px-3 py-2 w-48 sm:w-64 text-sm"
/>

<button
onclick="modal_add_book.showModal()"
class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
Tambah Buku
</button>

</div>


<!-- TAB -->

<div class="flex gap-4 mb-4 border-b">

<button onclick="showTab('buku')" id="tab_buku"
class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">
Buku
</button>

<button onclick="showTab('ppt')" id="tab_ppt"
class="px-4 py-2 text-gray-500">
PPT
</button>

</div>


<!-- TABLE BUKU -->

<div id="table_buku">

<div class="overflow-x-auto bg-white rounded shadow">

<table class="min-w-full text-sm">

<thead class="text-gray-500 text-xs border-b bg-gray-50">

<tr>
<th class="px-4 py-3">No</th>
<th class="px-4 py-3">Cover</th>
<th class="px-4 py-3">Judul</th>
<th class="px-4 py-3">Kelas</th>
<th class="px-4 py-3">Mapel</th>
<th class="px-4 py-3">Bab</th>
<th class="px-4 py-3">File</th>
<th class="px-4 py-3">Action</th>
</tr>

</thead>

<tbody class="divide-y">

@foreach($books as $book)

@if($book->tipe === 'buku')

<tr class="hover:bg-gray-50">

<td class="px-4 py-2">{{ $loop->iteration }}</td>

<td class="px-4 py-2">
@if($book->cover)
<img src="{{ asset('library/sampul/'.$book->cover) }}" class="w-16 h-20 object-cover rounded">
@endif
</td>

<td class="px-4 py-2 max-w-[200px] truncate">
{{ $book->title }}
</td>

<td class="px-4 py-2">
{{ $book->kelas->kelas ?? '-' }}
</td>

<td class="px-4 py-2">
{{ $book->mapel->mata_pelajaran ?? '-' }}
</td>

<td class="px-4 py-2">
{{ $book->bab->nama_bab ?? '-' }}
</td>

<td class="px-4 py-2">

@if($book->file)

<a
href="{{ asset('library/file/'.$book->file) }}"
target="_blank"
class="text-blue-500 text-xs">

Lihat

</a>

@endif

</td>

<td class="px-4 py-2">

<div class="flex gap-2">

<button
onclick="openEditModal(
'{{ $book->id }}',
`{{ $book->title }}`,
`{{ $book->description }}`,
'{{ $book->kelas_id }}',
'{{ $book->mapel_id }}',
'{{ $book->bab_id }}',
'{{ $book->tipe }}'
)"
class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs">

Edit

</button>

<form action="{{ route('library.delete',$book->id) }}" method="POST">

@csrf
@method('DELETE')

<button
class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">

Delete

</button>

</form>

</div>

</td>

</tr>

@endif

@endforeach

</tbody>

</table>

</div>

</div>


<!-- TABLE PPT -->

<div id="table_ppt" class="hidden">

<div class="overflow-x-auto bg-white rounded shadow">

<table class="min-w-full text-sm">

<thead class="text-gray-500 text-xs border-b bg-gray-50">

<tr>
<th class="px-4 py-3">No</th>
<th class="px-4 py-3">Cover</th>
<th class="px-4 py-3">Judul</th>
<th class="px-4 py-3">Kelas</th>
<th class="px-4 py-3">Mapel</th>
<th class="px-4 py-3">Bab</th>
<th class="px-4 py-3">File</th>
<th class="px-4 py-3">Action</th>
</tr>

</thead>

<tbody class="divide-y">

@foreach($books as $book)

@if($book->tipe === 'ppt')

<tr class="hover:bg-gray-50">

<td class="px-4 py-2">{{ $loop->iteration }}</td>

<td class="px-4 py-2">
@if($book->cover)
<img src="{{ asset('library/sampul/'.$book->cover) }}" class="w-16 h-20 object-cover rounded">
@endif
</td>

<td class="px-4 py-2">
{{ $book->title }}
</td>

<td class="px-4 py-2">
{{ $book->kelas->kelas ?? '-' }}
</td>

<td class="px-4 py-2">
{{ $book->mapel->mata_pelajaran ?? '-' }}
</td>

<td class="px-4 py-2">
{{ $book->bab->nama_bab ?? '-' }}
</td>

<td class="px-4 py-2">

@if($book->file)

<a
href="{{ asset('library/file/'.$book->file) }}"
target="_blank"
class="text-blue-500 text-xs">

Lihat

</a>

@endif

</td>

<td class="px-4 py-2">

<div class="flex gap-2">

<button
onclick="openEditModal(
'{{ $book->id }}',
`{{ $book->title }}`,
`{{ $book->description }}`,
'{{ $book->kelas_id }}',
'{{ $book->mapel_id }}',
'{{ $book->bab_id }}',
'{{ $book->tipe }}'
)"
class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs">

Edit

</button>

<form action="{{ route('library.delete',$book->id) }}" method="POST">

@csrf
@method('DELETE')

<button
class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">

Delete

</button>

</form>

</div>

</td>

</tr>

@endif

@endforeach

</tbody>

</table>

</div>

</div>

</main>

</div>

</div>

@endif


<!-- MODAL EDIT -->

<dialog id="modal_edit_book" class="modal">

<div class="modal-box w-96">

<h3 class="font-bold text-center mb-4">
Edit Buku
</h3>

<form id="editForm" method="POST" enctype="multipart/form-data">

@csrf
@method('PUT')

<div class="space-y-3">

<input id="edit_title"
type="text"
name="title"
class="border rounded w-full px-3 py-2">

<textarea id="edit_description"
name="description"
class="border rounded w-full px-3 py-2"></textarea>

<select id="edit_kelas"
name="kelas_id"
class="border rounded w-full px-3 py-2">

@foreach($kelas as $k)

<option value="{{ $k->id }}">
{{ $k->kelas }}
</option>

@endforeach

</select>


<select id="edit_mapel"
name="mapel_id"
class="border rounded w-full px-3 py-2">

@foreach($mapels as $mapel)

<option value="{{ $mapel->id }}">
{{ $mapel->mata_pelajaran }}
</option>

@endforeach

</select>


<select id="edit_bab"
name="bab_id"
class="border rounded w-full px-3 py-2">

@foreach($babs as $bab)

<option value="{{ $bab->id }}">
{{ $bab->nama_bab }}
</option>

@endforeach

</select>

<input
type="file"
name="file"
class="border rounded w-full px-2 py-1">

</div>

<div class="flex justify-end mt-5">

<button
type="submit"
class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">

Update

</button>

</div>

</form>

</div>

<form method="dialog" class="modal-backdrop">
<button>close</button>
</form>

</dialog>


<!-- ================= SCRIPT ================= -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>

<script>


function showTab(tab){

const tabs = ['buku','ppt'];

tabs.forEach(function(t){

document.getElementById('table_'+t).classList.add('hidden');

const btn = document.getElementById('tab_'+t);

btn.classList.remove('border-blue-500','text-blue-600','font-semibold');

btn.classList.add('text-gray-500');

});

document.getElementById('table_'+tab).classList.remove('hidden');

const activeTab = document.getElementById('tab_'+tab);

activeTab.classList.remove('text-gray-500');

activeTab.classList.add('border-blue-500','text-blue-600','font-semibold');

}

function openEditModal(id,title,desc,kelas,mapel,bab,tipe){

document.getElementById("edit_title").value = title;
document.getElementById("edit_description").value = desc;

document.getElementById("edit_kelas").value = kelas;
document.getElementById("edit_mapel").value = mapel;
document.getElementById("edit_bab").value = bab;

document.getElementById("editForm").action = "/library/update/"+id;

modal_edit_book.showModal();

}

function switchForm(tab){

const formBuku = document.getElementById('form_buku');
const formPPT = document.getElementById('form_ppt');

const tabBuku = document.getElementById('tabFormBuku');
const tabPPT = document.getElementById('tabFormPPT');

if(tab === 'buku'){

formBuku.classList.remove('hidden');
formPPT.classList.add('hidden');

tabBuku.classList.add('border-blue-500','text-blue-600','font-semibold');
tabBuku.classList.remove('text-gray-500');

tabPPT.classList.remove('border-blue-500','text-blue-600','font-semibold');
tabPPT.classList.add('text-gray-500');

}

if(tab === 'ppt'){

formPPT.classList.remove('hidden');
formBuku.classList.add('hidden');

tabPPT.classList.add('border-blue-500','text-blue-600','font-semibold');
tabPPT.classList.remove('text-gray-500');

tabBuku.classList.remove('border-blue-500','text-blue-600','font-semibold');
tabBuku.classList.add('text-gray-500');

}

}



function filterBab(mapelId,babSelect){

babSelect.querySelectorAll('option').forEach(opt=>{

if(!opt.dataset.mapel) return;

opt.style.display =
opt.dataset.mapel == mapelId ? 'block' : 'none';

});

babSelect.value="";

}



document.getElementById('mapel_add')
?.addEventListener('change',function(){

filterBab(this.value,document.getElementById('bab_add'));

});



const fileInput = document.getElementById("file_pdf");
const autoCoverInput = document.getElementById("auto_cover");


fileInput?.addEventListener("change", function(e){

const file = e.target.files[0];

if(!file) return;

const ext = file.name.split('.').pop().toLowerCase();


if(ext === "pdf"){

const reader = new FileReader();

reader.onload = function(){

const typedarray = new Uint8Array(this.result);

const pdfjsLib = window['pdfjs-dist/build/pdf'];

pdfjsLib.getDocument(typedarray).promise.then(function(pdf){

pdf.getPage(1).then(function(page){

const viewport = page.getViewport({scale:1.5});

const canvas = document.createElement("canvas");

const context = canvas.getContext("2d");

canvas.height = viewport.height;
canvas.width = viewport.width;

page.render({
canvasContext:context,
viewport:viewport
}).promise.then(function(){

autoCoverInput.value = canvas.toDataURL("image/jpeg");

});

});

});

};

reader.readAsArrayBuffer(file);

}



if(ext === "ppt" || ext === "pptx" || ext === "doc" || ext === "docx"){

const canvas = document.createElement("canvas");

const ctx = canvas.getContext("2d");

canvas.width = 600;
canvas.height = 800;

ctx.fillStyle = "#2563EB";
ctx.fillRect(0,0,canvas.width,canvas.height);

ctx.fillStyle = "#FFFFFF";
ctx.font = "bold 42px Arial";
ctx.textAlign = "center";

ctx.fillText(ext.toUpperCase()+" FILE", canvas.width/2, canvas.height/2);

autoCoverInput.value = canvas.toDataURL("image/jpeg");

}

});

</script>