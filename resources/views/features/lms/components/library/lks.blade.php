{{-- SIDEBAR --}}
@include('components/sidebar-beranda', [
'headerSideNav' => 'Library',
])

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

/* FILTER STYLE */
.filter-section{
background: linear-gradient(90deg,#eff6ff,#eef2ff);
border-radius:16px;
padding:25px;
margin-bottom:40px;
border:1px solid #dbeafe;
box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.filter-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:15px;
}

.filter-title{
font-size:18px;
font-weight:600;
color:#374151;
display:flex;
align-items:center;
gap:8px;
}

.filter-form{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:20px;
align-items:end;
}

.filter-group{
display:flex;
flex-direction:column;
}

.filter-group label{
font-size:12px;
font-weight:600;
color:#6b7280;
margin-bottom:5px;
}

.filter-select{
position:relative;
}

.filter-select i{
position:absolute;
left:12px;
top:50%;
transform:translateY(-50%);
color:#9ca3af;
font-size:13px;
}

.filter-select select{
width:100%;
padding:10px 12px 10px 35px;
border-radius:10px;
border:1px solid #d1d5db;
font-size:14px;
background:#fff;
transition:0.2s;
}

.filter-select select:focus{
outline:none;
border-color:#3b82f6;
box-shadow:0 0 0 2px rgba(59,130,246,.2);
}

.filter-button button{
background:linear-gradient(90deg,#2563eb,#4f46e5);
color:#fff;
border:none;
padding:10px 22px;
border-radius:10px;
font-size:14px;
font-weight:600;
cursor:pointer;
display:flex;
align-items:center;
justify-content:center;
gap:8px;
transition:0.25s;
box-shadow:0 3px 8px rgba(0,0,0,.1);
width:100%;
}

.filter-button button:hover{
transform:translateY(-1px);
box-shadow:0 6px 12px rgba(0,0,0,.15);
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
<section class="filter-section">

<div class="filter-header">

    <div class="filter-title">
        <i class="fa-solid fa-filter"></i>
        Filter LKPD
    </div>

</div>

<form method="GET" class="filter-form">

    <!-- KELAS -->
    <div class="filter-group">

        <label>Kelas</label>

        <div class="filter-select">
            <i class="fa-solid fa-school"></i>

            <select name="kelas_id">

                <option value="">Semua Kelas</option>

                @foreach($kelas as $k)
                <option value="{{ $k->id }}"
                    {{ request('kelas_id') == $k->id ? 'selected' : '' }}>

                    Kelas {{ $k->kelas }}

                </option>
                @endforeach

            </select>

        </div>

    </div>


    <!-- BAB -->
    <div class="filter-group">

        <label>Bab</label>

        <div class="filter-select">

            <i class="fa-solid fa-book-open"></i>

            <select name="bab_id">

                <option value="">Semua Bab</option>

                @foreach($babs as $bab)

                <option value="{{ $bab->id }}"
                    {{ request('bab_id') == $bab->id ? 'selected' : '' }}>

                    {{ $bab->nama_bab }}

                </option>

                @endforeach

            </select>

        </div>

    </div>


    <!-- BUTTON -->
    <div class="filter-button">

        <button type="submit">

            <i class="fa-solid fa-magnifying-glass"></i>
            Filter

        </button>

    </div>


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

