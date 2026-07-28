{{-- SIDEBAR --}}
@include('components/sidebar-beranda', [
'headerSideNav' => 'LKPD Library',
])
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)]">
    <div class="mx-7.5 mt-6 mb-4">
        <a href="{{ url()->previous() }}"
            class="inline-flex items-center gap-2 text-lg font-semibold hover:text-blue-600 transition">
            <i class="fa-solid fa-chevron-left text-sm"></i>
            <span>kembali</span>
        </a>
    </div>
</div>
@if (Auth::check() && in_array(Auth::user()->role, ['Siswa', 'Guru']))

<style>

body{
background:linear-gradient(to bottom,#f8fafc,#eef2f7);
}

/* SECTION CARD */
.section-card{
background:white;
border-radius:16px;
padding:28px;
margin-bottom:40px;
border:1px solid #e5e7eb;
box-shadow:0 8px 20px rgba(0,0,0,0.05);
}

/* SCROLL ROW */
.book-row{
display:flex;
gap:20px;
overflow-x:auto;
padding-bottom:10px;
scroll-behavior:smooth;
}

.book-row::-webkit-scrollbar{
display:none;
}

/* BOOK CARD */
.book-card{
min-width:190px;
max-width:190px;
background:white;
border-radius:14px;
overflow:hidden;
border:1px solid #e5e7eb;
transition:all .35s ease;
cursor:pointer;
box-shadow:0 6px 14px rgba(0,0,0,.08);
}

.book-card:hover{
transform:translateY(-6px) scale(1.08);
box-shadow:0 20px 40px rgba(0,0,0,.2);
}

.book-cover{
height:220px;
background:#f3f4f6;
overflow:hidden;
}

.book-cover img{
width:100%;
height:100%;
object-fit:cover;
}

.book-info{
padding:12px;
}

.book-tag{
font-size:11px;
background:#dbeafe;
color:#2563eb;
padding:3px 6px;
border-radius:5px;
}

.book-title{
font-weight:600;
font-size:14px;
margin-top:6px;
line-height:1.3;
}

.book-class{
font-size:12px;
color:#6b7280;
}

</style>


<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

<div class="my-15 mx-7.5">

<main>

<!-- HEADER -->
<section class="section-card">

<h1 class="text-2xl font-bold opacity-80">
{{ $mapel->mata_pelajaran }}
</h1>

<p class="text-gray-500 mt-1">
Semua LKPD {{ $mapel->mata_pelajaran }}
</p>

</section>


<!-- LIST LKS -->
<!-- FILTER -->
<section class="section-card">

<form method="GET" class="flex gap-3 flex-wrap">

<select name="kelas_id" class="border rounded px-3 py-2">

<option value="">Semua Kelas</option>

@foreach($kelas as $k)
<option value="{{ $k->id }}" 
@if(request('kelas_id')==$k->id) selected @endif>
Kelas {{ $k->kelas }}
</option>
@endforeach

</select>

<select name="bab_id" class="border rounded px-3 py-2">

<option value="">Semua Bab</option>

@foreach($babs as $bab)
<option value="{{ $bab->id }}"
@if(request('bab_id')==$bab->id) selected @endif>
{{ $bab->nama_bab }}
</option>
@endforeach

</select>

<button class="bg-blue-600 text-white px-4 rounded">
Filter
</button>

</form>

</section>



<!-- LIST LKS PER BAB -->
@forelse($chapters as $chapterName => $books)

<section class="section-card">

<h2 class="text-xl font-bold mb-5">
{{ $chapterName }}
</h2>

<div class="book-row">

@foreach($books as $book)

<a href="{{ route('student.library.read', $book->id) }}">

<div class="book-card">

<div class="book-cover">

@if($book->cover)
<img src="{{ asset('library/sampul/'.$book->cover) }}">
@else
<div class="flex items-center justify-center h-full text-gray-400">
No Cover
</div>
@endif

</div>

<div class="book-info">

<span class="book-tag">
LKPD
</span>

<div class="book-title">
{{ $book->title }}
</div>

<div class="book-class">
Kelas {{ $book->kelas->kelas ?? '-' }}
</div>

</div>

</div>

</a>

@endforeach

</div>

</section>

@empty

<section class="section-card text-center text-gray-400">

Tidak ada LKPD tersedia

</section>

@endforelse

</main>

</div>
</div>

@else

<div class="flex flex-col min-h-screen items-center justify-center">

<p class="text-xl font-bold">ALERT</p>

<p class="text-gray-500">
You do not have access to this page
</p>

</div>

@endif

<script>

function openModal(id){
document.getElementById("modal-"+id).style.display="flex"
}

function closeModal(id){
document.getElementById("modal-"+id).style.display="none"
}

window.onclick = function(e){

document.querySelectorAll('.modal-bg').forEach(modal=>{
if(e.target === modal){
modal.style.display="none"
}
})

}

</script>

