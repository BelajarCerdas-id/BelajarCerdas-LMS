{{-- SIDEBAR --}}
@include('components/sidebar-beranda', [
    'headerSideNav' => 'PPT Library',
])

@if (Auth::check() && Auth::user()->role === 'Siswa')

<style>
main{
background:#f8fafc;
padding:20px;
border-radius:12px;
} 
/* CARD */
/* CARD FLOATING */
.ppt-card{
background:#ffffff;
border-radius:16px;
overflow:hidden;
border:1px solid #e5e7eb;
cursor:pointer;

/* efek timbul */
box-shadow:
0 4px 12px rgba(0,0,0,0.06),
0 10px 25px rgba(0,0,0,0.08);

transition:all .35s ease;
}

/* hover naik */
.ppt-card:hover{
transform:translateY(-8px) scale(1.01);

box-shadow:
0 10px 25px rgba(0,0,0,0.10),
0 20px 45px rgba(0,0,0,0.15);
}

.ppt-card:hover{
transform:translateY(-6px);
box-shadow:0 15px 30px rgba(0,0,0,.15);
}

/* COVER LANDSCAPE */
.ppt-cover{
position:relative;
width:100%;
aspect-ratio:16/9;
background:#f3f4f6;
overflow:hidden;
border-bottom:1px solid #f1f5f9;
}

.ppt-cover img{
width:100%;
height:100%;
object-fit:cover;
transition:transform .5s ease;
}

.ppt-card:hover .ppt-cover img{
transform:scale(1.08);
}

.ppt-card::after{
content:"";
position:absolute;
inset:0;
border-radius:16px;
opacity:0;
transition:.3s;
box-shadow:0 0 0 2px #2563eb inset;
}

.ppt-card:hover::after{
opacity:.08;
}

.ppt-card{
position:relative;
}
/* BADGE PPT */
.ppt-badge{
position:absolute;
top:8px;
left:8px;
background:rgba(139,92,246,.9);
color:white;
font-size:11px;
padding:4px 8px;
border-radius:6px;
font-weight:600;
letter-spacing:.3px;
}

/* CONTENT */
.ppt-content{
padding:14px;
}

.ppt-title{
font-weight:600;
font-size:15px;
color:#1f2937;
line-height:1.35;
transition:.25s;
}

.ppt-card:hover .ppt-title{
color:#2563eb;
}

.ppt-meta{
font-size:12px;
color:#6b7280;
margin-top:4px;
}

.ppt-desc{
font-size:12px;
color:#4b5563;
margin-top:6px;
display:-webkit-box;
-webkit-line-clamp:2;
-webkit-box-orient:vertical;
overflow:hidden;
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


<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)]">

    <div class="py-8 px-6 md:px-10">

        <main>

            {{-- HEADER --}}
            <section class="mb-8">
                <br>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                    PPT Library
                </h1>

                <p class="text-gray-500 mt-1 text-sm md:text-base">
                    Kumpulan file presentasi pembelajaran
                </p>

                <div class="mt-3 h-[2px] w-32 bg-blue-500 rounded-full"></div>
            </section>


           {{-- ================= FILTER SECTION ================= --}}
<section class="filter-section">

    {{-- HEADER --}}
    <div class="filter-header">

        <div class="filter-title">
            <i class="fa-solid fa-filter"></i>
            Filter Materi
        </div>

        <a href="{{ url()->current() }}" class="filter-reset">
            Reset
        </a>

    </div>


    {{-- FORM --}}
    <form method="GET" class="filter-form">

        {{-- KELAS --}}
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


        {{-- MAPEL --}}
        <div class="filter-group">

            <label>Mapel</label>

            <div class="filter-select">

                <i class="fa-solid fa-book"></i>

                <select name="mapel_id">

                    <option value="">Semua Mapel</option>

                    @foreach($mapels as $m)
                        <option value="{{ $m->id }}"
                        {{ request('mapel_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->mata_pelajaran }}
                        </option>
                    @endforeach

                </select>

            </div>

        </div>


        {{-- BAB --}}
        <div class="filter-group">

            <label>Bab</label>

            <div class="filter-select">

                <i class="fa-solid fa-book-open"></i>

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


        {{-- BUTTON --}}
        <div class="filter-button">

            <button type="submit">

                <i class="fa-solid fa-magnifying-glass"></i>
                Filter

            </button>

        </div>

    </form>

</section>

            {{-- GRID --}}
            <section class="library-grid">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

                    @forelse($books as $book)

                        <a href="{{ route('student.library.read', $book->id) }}" class="group">

                            <div class="ppt-card">

                                {{-- COVER --}}
                                <div class="ppt-cover">

                                    <span class="ppt-badge">
                                        PPT
                                    </span>

                                    @if($book->cover)
                                        <img src="{{ asset('library/sampul/'.$book->cover) }}">
                                    @else
                                        <div class="flex items-center justify-center h-full text-gray-400 text-sm">
                                            No Cover
                                        </div>
                                    @endif

                                </div>

                                {{-- CONTENT --}}
                                <div class="ppt-content">

                                    <div class="ppt-title">
                                        {{ $book->title }}
                                    </div>

                                    <div class="ppt-meta">
                                        {{ $book->kelas->kelas ?? '-' }} • {{ $book->mapel->mata_pelajaran ?? '-' }}
                                    </div>

                                    <div class="ppt-desc">
                                        {{ $book->description }}
                                    </div>

                                </div>

                            </div>

                        </a>

                    @empty

                        <div class="col-span-full text-left text-gray-400 py-12">
                            Tidak ada file PPT
                        </div>

                    @endforelse

                </div>

            </section>

        </main>

    </div>
</div>

@endif