{{-- VIDEO LIBRARY --}}

@include('components/sidebar-beranda', [
'headerSideNav' => 'Video Library',
])

@if (Auth::check() && in_array(Auth::user()->role, ['Siswa', 'Guru']))

<style>
.video-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
    gap:18px;
}

.video-card{
    background:white;
    border-radius:12px;
    overflow:hidden;
    cursor:pointer;
    transition:.25s;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.video-card:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 24px rgba(0,0,0,.15);
}

.video-thumb{
    width:100%;
    height:120px;
    background:#000;
    overflow:hidden;
}

.video-thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:.3s;
}

.video-card:hover .video-thumb img{
    transform:scale(1.05);
}

.video-title{
    font-size:14px;
    font-weight:600;
    color:#111827;
    line-height:1.4;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}

.video-meta{
    font-size:12px;
    color:#6b7280;
    margin-top:4px;
}

.top-bar{
    position: sticky;
    top: 0;
    background: white;
    z-index: 50;
    padding: 12px 0;
}
</style>

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)]">

<div class="py-6 px-6">

<div class="flex gap-6">

{{-- MAIN --}}
<div class="flex-1">

{{-- ================= TOP BAR ================= --}}
<div class="top-bar mb-6">

    {{-- SEARCH --}}
    <input type="text"
           id="videoSearch"
           onkeyup="filterVideos()"
           placeholder="Cari video, mapel, atau judul..."
           class="w-full px-4 py-3 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 mb-4">

    {{-- MAPEL FILTER --}}
    <div class="flex gap-3 overflow-x-auto pb-2">

        <button onclick="filterByMapel('all')"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-full text-sm whitespace-nowrap">
            Semua
        </button>

        @foreach($mapels as $mapel)
        <button onclick="filterByMapel({{ $mapel->id }})"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-full text-sm whitespace-nowrap">
            {{ $mapel->mata_pelajaran }}
        </button>
        @endforeach

    </div>
</div>


{{-- ================= WATCH MODE ================= --}}
<div id="watchMode" class="hidden flex gap-4 mb-6 items-start w-full">

    <div class="flex-[0.75]">

        <div class="bg-black rounded-xl overflow-hidden shadow-lg sticky top-20 w-full aspect-video">
            <div id="player" class="w-full h-full"></div>
        </div>

        <div class="mt-4">
            <h1 id="videoTitle" class="text-2xl font-semibold text-gray-900"></h1>
            <p class="text-sm text-gray-500">Video Pembelajaran</p>
        </div>

    </div>

    <div class="flex-[0.25] hidden md:block">

        <h2 class="text-sm font-semibold mb-3">Rekomendasi</h2>

        <div class="flex flex-col gap-3 max-h-[88vh] overflow-y-auto">

            @foreach($videos as $v)
            <div class="flex gap-3 cursor-pointer hover:bg-gray-100 p-2 rounded-lg transition"
                 onclick="playVideo('{{ $v->id }}','{{ $v->file }}','{{ $v->title }}')">

                <div class="w-48 h-28 bg-gray-200 rounded-md overflow-hidden">

                    @if($v->cover)
                        @if(Str::startsWith($v->cover, 'http'))
                            <img src="{{ $v->cover }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('library/sampul/'.$v->cover) }}" class="w-full h-full object-cover">
                        @endif
                    @endif

                </div>

                <div>
                    <div class="text-sm font-semibold line-clamp-2">
                        {{ $v->title }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $v->mapel->mata_pelajaran ?? '-' }}
                    </div>
                </div>

            </div>
            @endforeach

        </div>

    </div>
</div>


{{-- ================= HOME MODE ================= --}}
<div id="homeMode">

    <h1 class="text-3xl font-bold mb-6">Video Library</h1>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">

        @foreach($videos as $video)

        <div class="video-card"
             data-title="{{ strtolower($video->title) }}"
             data-bab="{{ $video->bab_id }}"
             data-mapel="{{ $video->mapel_id }}"
             onclick="playVideo('{{ $video->id }}','{{ $video->file }}','{{ $video->title }}')">

            <div class="video-thumb">

                @if($video->cover)
                    @if(Str::startsWith($video->cover, 'http'))
                        <img src="{{ $video->cover }}">
                    @else
                        <img src="{{ asset('library/sampul/'.$video->cover) }}">
                    @endif
                @endif

            </div>

            <div class="p-3">

                <div class="video-title">
                    {{ $video->title }}
                </div>

                <div class="video-meta">
                    {{ $video->mapel->mata_pelajaran ?? '-' }}
                </div>

            </div>

        </div>

        @endforeach

    </div>

</div>

</div>
</div>
</div>

@endif


{{-- ================= SCRIPT ================= --}}
<script>

let activeMapel = 'all';

function playVideo(id, file, title){

    document.getElementById("homeMode").style.display = "none";
    document.getElementById("watchMode").style.display = "flex";

    document.getElementById("videoTitle").innerText = title;

    let html = "";

    if(file.includes("drive.google.com")){
        let match = file.match(/\/d\/(.*?)\//);
        let idFile = match ? match[1] : null;

        html = `<iframe class="w-full h-full"
            src="https://drive.google.com/file/d/${idFile}/preview"
            allow="autoplay"></iframe>`;
    }
    else if(file.includes("youtube.com") || file.includes("youtu.be")){
        let embed = file.replace("watch?v=", "embed/");
        html = `<iframe class="w-full h-full"
            src="${embed}"
            allowfullscreen></iframe>`;
    }
    else {
        html = `<video controls autoplay class="w-full h-full">
            <source src="/library/file/${file}" type="video/mp4">
        </video>`;
    }

    document.getElementById("player").innerHTML = html;
    window.scrollTo({top:0, behavior:"smooth"});
}


// SEARCH + MAPEL FILTER COMBINE
function filterVideos() {
    let input = document.getElementById("videoSearch").value.toLowerCase();
    let cards = document.querySelectorAll(".video-card");

    cards.forEach(card => {

        let title = card.getAttribute("data-title");
        let mapel = card.getAttribute("data-mapel");

        let matchSearch = title.includes(input);
        let matchMapel = (activeMapel === 'all') || (mapel == activeMapel);

        card.style.display = (matchSearch && matchMapel) ? "block" : "none";
    });
}


// MAPEL FILTER
function filterByMapel(mapelId) {

    activeMapel = mapelId;

    let cards = document.querySelectorAll(".video-card");

    cards.forEach(card => {

        let title = card.getAttribute("data-title");
        let mapel = card.getAttribute("data-mapel");

        let search = document.getElementById("videoSearch").value.toLowerCase();

        let matchSearch = title.includes(search);
        let matchMapel = (mapelId === 'all') || (mapel == mapelId);

        card.style.display = (matchSearch && matchMapel) ? "block" : "none";
    });
}

</script>