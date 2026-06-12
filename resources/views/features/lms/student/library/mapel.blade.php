{{-- SIDEBAR --}}
@include('components/sidebar-beranda', [
'headerSideNav' => 'Library',
])

@if (Auth::check() && Auth::user()->role === 'Siswa')

<style>

/* BACKGROUND */
body{
background:linear-gradient(to bottom,#f8fafc,#eef2f7);
}

/* SECTION CARD */
.section-card{
background:white;
border-radius:14px;
border:1px solid #e5e7eb;
padding:22px;
box-shadow:0 6px 18px rgba(0,0,0,.06);
transition:.3s;
}

.section-card:hover{
box-shadow:0 12px 30px rgba(0,0,0,.08);
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
position:relative;
box-shadow:0 6px 14px rgba(0,0,0,.08);
}

.book-card:hover{
transform:translateY(-6px) scale(1.08);
z-index:10;
box-shadow:0 20px 40px rgba(0,0,0,.2);
}

/* COVER */
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

/* INFO */
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

/* MORE CARD */
.more-card{
display:flex;
align-items:center;
justify-content:center;
background:#111827;
color:white;
font-weight:600;
}

.more-card:hover{
background:#000;
transform:translateY(-6px) scale(1.08);
}

.more-text{
text-align:center;
}

.more-number{
font-size:28px;
font-weight:bold;
}

/* MODAL */

.modal-bg{
position:fixed;
inset:0;
background:rgba(0,0,0,.6);
display:none;
align-items:center;
justify-content:center;
z-index:999;
}

.modal-content{
background:white;
width:90%;
max-width:1100px;
max-height:80vh;
overflow-y:auto;
border-radius:16px;
padding:25px;
box-shadow:0 30px 60px rgba(0,0,0,.25);
}

.modal-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.modal-close{
cursor:pointer;
font-size:20px;
font-weight:bold;
}

.modal-books{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
gap:20px;
}

.section-card{
background:white;
border-radius:16px;
padding:28px;
margin-bottom:60px;

border:1px solid #e5e7eb;

box-shadow:
0 8px 20px rgba(0,0,0,0.05),
0 2px 6px rgba(0,0,0,0.03);

transition:all .25s ease;
}

.section-card:hover{
transform:translateY(-2px);
box-shadow:
0 14px 30px rgba(0,0,0,0.08),
0 4px 10px rgba(0,0,0,0.04);
}

/* ================= FILTER SECTION ================= */

.filter-section{
background: linear-gradient(90deg,#eff6ff,#eef2ff);
border-radius:16px;
padding:25px;
margin-bottom:40px;
border:1px solid #dbeafe;
box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

/* HEADER */

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

.filter-title i{
color:#2563eb;
}

.filter-reset{
font-size:13px;
color:#6b7280;
text-decoration:none;
transition:0.2s;
}

.filter-reset:hover{
color:#ef4444;
}

/* FORM */

.filter-form{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:20px;
align-items:end;
}

/* GROUP */

.filter-group{
display:flex;
flex-direction:column;
}

.filter-group label{
font-size:12px;
font-weight:600;
color:#6b7280;
margin-bottom:4px;
}

/* SELECT */

.filter-select{
position:relative;
}

.filter-select i{
position:absolute;
left:10px;
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
box-shadow:0 0 0 2px rgba(59,130,246,0.2);
}

/* BUTTON */

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
gap:8px;
transition:0.25s;
box-shadow:0 3px 8px rgba(0,0,0,0.1);
}

.filter-button button:hover{
transform:translateY(-1px);
box-shadow:0 6px 12px rgba(0,0,0,0.15);
background:linear-gradient(90deg,#1d4ed8,#4338ca);
}
</style>


<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

<div class="my-15 mx-7.5">

<main>

<!-- HEADER MAPEL -->
<section class="section-card mb-10">

<h1 class="text-2xl font-bold opacity-80">
{{ $mapel->mata_pelajaran }}
</h1>

<p class="text-gray-500 mt-1">
Materi dan tugas mata pelajaran {{ $mapel->mata_pelajaran }}
</p>

<hr class="border-gray-300 mt-4 w-60">

</section>


{{-- ================= FILTER SECTION ================= --}}
<section class="filter-section">

<div class="filter-header">

<h2 class="filter-title">
<i class="fa-solid fa-filter"></i>
Filter Materi
</h2>

<a href="{{ url()->current() }}" class="filter-reset">
Reset
</a>

</div>

<form method="GET" class="filter-form">

{{-- ================= KELAS ================= --}}
<div class="filter-group">

<label>Kelas</label>

<div class="filter-select">

<i class="fa-solid fa-school"></i>

<select name="kelas_id">

<option value="">Semua Kelas</option>

@foreach($kelas as $k)
<option value="{{ $k->id }}"
{{ request('kelas_id') == $k->id ? 'selected' : '' }}>
{{ $k->kelas }}
</option>
@endforeach

</select>

</div>

</div>


{{-- ================= BAB ================= --}}
<div class="filter-group">

<label>Bab</label>

<div class="filter-select">

<i class="fa-solid fa-book"></i>

<select name="bab_id">

<option value="">Semua Bab</option>

@foreach($babs as $b)
<option value="{{ $b->id }}"
{{ request('bab_id') == $b->id ? 'selected' : '' }}>
{{ $b->nama_bab }}
</option>
@endforeach

</select>

</div>

</div>


{{-- ================= BUTTON ================= --}}
<div class="filter-button">

<button type="submit">
<i class="fa-solid fa-magnifying-glass"></i>
Filter
</button>

</div>

</form>

</section>

<!-- ================= LOOP BAB ================= -->
@forelse($chapters as $chapterName => $books)

<section class="section-card">

    <!-- HEADER BAB -->
    <div class="mb-8">

        <h2 class="text-xl font-bold text-gray-800">
            {{ $chapterName }}
        </h2>

        <div class="w-24 h-[2px] bg-gray-300 mt-2"></div>

    </div>


    <!-- ================= SCROLL ROW ================= -->
    <div class="book-row">

        @foreach($books as $index => $book)

        @if($index < 5)

        <a href="{{ route('student.library.read', $book->id) }}">

            <div class="book-card">

                <!-- COVER -->
                <div class="book-cover">

                    @if($book->cover)
                        <img src="{{ asset('library/sampul/'.$book->cover) }}">
                    @else
                        <div class="flex items-center justify-center h-full text-gray-400">
                            No Cover
                        </div>
                    @endif

                </div>


                <!-- INFO -->
                <div class="book-info">

                    <span class="book-tag">
                        BUKU
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

        @endif

        @endforeach


        <!-- ================= BUTTON + MATERI ================= -->
        @if($books->count() > 5)

        <div
            class="book-card more-card"
            onclick="openModal('{{ Str::slug($chapterName) }}')"
        >

            <div class="more-text">

                <div class="more-number">
                    +
                </div>

                <div>
                    {{ $books->count() - 5 }} Materi
                </div>

            </div>

        </div>

        @endif

    </div>



    <!-- ================= MODAL ================= -->
    <div id="modal-{{ Str::slug($chapterName) }}" class="modal-bg">

        <div class="modal-content">

            <div class="modal-header">

                <h2 class="text-xl font-bold">
                    {{ $chapterName }}
                </h2>

                <div
                    class="modal-close"
                    onclick="closeModal('{{ Str::slug($chapterName) }}')"
                >
                    ✕
                </div>

            </div>


            <div class="modal-books">

                @foreach($books->slice(5) as $book)

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
                                BUKU
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

        </div>

    </div>

</section>

@empty

<div class="text-center text-gray-400 py-20">
    Tidak ada buku pada mapel ini
</div>

@endforelse

</main>

</div>
</div>


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

@else

<div class="flex flex-col min-h-screen items-center justify-center">

<p class="text-xl font-bold">ALERT</p>

<p class="text-gray-500">
You do not have access to this page
</p>

</div>

@endif