@include('components/sidebar-beranda', [
'headerSideNav' => 'Library',
])

@if (Auth::user()->role === 'Siswa')

<style>

/* ================= BACKGROUND ================= */

body{
background:#f6f8fc;
}

/* ================= COVER ================= */

.book-cover{
width:210px;
flex-shrink:0;
}

.book-card{
border-radius:16px;
overflow:hidden;
background:#fff;
border:1px solid rgba(0,0,0,0.04);
transition:all .35s ease;
position:relative;

box-shadow:
0 4px 10px rgba(0,0,0,0.05),
0 10px 25px rgba(0,0,0,0.08);
}

.book-card:hover{
transform:translateY(-8px) scale(1.02);

box-shadow:
0 20px 45px rgba(0,0,0,0.18),
0 6px 18px rgba(0,0,0,0.12);
}

.book-image{
height:260px;
background:#f3f4f6;
overflow:hidden;
}

.book-image img{
width:100%;
height:100%;
object-fit:cover;
}

/* ================= RELATED ================= */

.related-scroll{
display:flex;
gap:20px;
overflow-x:auto;
padding-bottom:10px;
scroll-behavior:smooth;
}

.related-scroll::-webkit-scrollbar{
height:6px;
}

.related-scroll::-webkit-scrollbar-thumb{
background:#ddd;
border-radius:10px;
}

/* RELATED CARD */

.related-book,
.related-ppt{
flex-shrink:0;
border-radius:14px;
overflow:hidden;
background:#fff;
border:1px solid rgba(0,0,0,0.05);
cursor:pointer;
transition:.35s;

box-shadow:
0 4px 12px rgba(0,0,0,0.06),
0 8px 20px rgba(0,0,0,0.08);
}

.related-book{
width:180px;
}

.related-ppt{
width:260px;
}

.related-book:hover,
.related-ppt:hover{
transform:translateY(-8px);

box-shadow:
0 20px 45px rgba(0,0,0,0.18),
0 6px 15px rgba(0,0,0,0.1);
}

.book-cover-related{
height:230px;
background:#f3f4f6;
}

.book-cover-related img{
width:100%;
height:100%;
object-fit:cover;
}

.ppt-cover{
height:150px;
background:#f3f4f6;
}

.ppt-cover img{
width:100%;
height:100%;
object-fit:cover;
}

/* ================= PDF VIEWER ================= */

.pdf-viewer{
position:fixed;
inset:0;
background:#111827;
display:none;
flex-direction:column;
z-index:200;
}

.pdf-header{
background:#1f2937;
color:#fff;
padding:14px 20px;
display:flex;
justify-content:space-between;
}

.pdf-body{
flex:1;
padding:20px;
}

.pdf-body iframe{
width:100%;
height:100%;
border:none;
background:white;
border-radius:8px;
}

/* ================= PPT VIEWER ================= */

.ppt-viewer{
position:fixed;
inset:0;
background:#0f172a;
display:none;
flex-direction:column;
z-index:300;
}

.ppt-header{
background:#111827;
color:white;
padding:12px 20px;
display:flex;
justify-content:space-between;
align-items:center;
}

.ppt-controls{
display:flex;
gap:10px;
}

.ppt-btn{
background:#1f2937;
color:white;
border:none;
padding:6px 12px;
border-radius:6px;
cursor:pointer;
}

.ppt-btn:hover{
background:#374151;
}

.ppt-body{
flex:1;
display:flex;
overflow:hidden;
}

.ppt-thumbnails{
width:160px;
background:#111827;
overflow-y:auto;
padding:10px;
}

.ppt-thumb{
width:100%;
margin-bottom:10px;
border-radius:6px;
cursor:pointer;
border:2px solid transparent;
}

.ppt-thumb.active{
border:2px solid #3b82f6;
}

.ppt-stage{
flex:1;
display:flex;
justify-content:center;
align-items:center;
background:#020617;
}

#ppt-canvas{
max-width:90%;
max-height:90%;
box-shadow:0 25px 60px rgba(0,0,0,.4);
border-radius:8px;
transition:transform .25s ease;
}

/* ================= MORE CARD ================= */

.more-card{
width:180px;
height:230px;
display:flex;
align-items:center;
justify-content:center;
border-radius:14px;
border:2px dashed #d1d5db;
font-weight:600;
color:#555;
cursor:pointer;
transition:.3s;
flex-shrink:0;

background:#fff;

box-shadow:
0 4px 10px rgba(0,0,0,0.04);
}

.more-card:hover{
background:#f3f4f6;
transform:translateY(-6px);
box-shadow:
0 15px 30px rgba(0,0,0,0.12);
}

/* ================= MODAL ================= */

.modal-bg{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,.65);
display:none;
align-items:center;
justify-content:center;
z-index:400;
}

.modal-content{
background:#fff;
width:90%;
max-width:950px;
border-radius:18px;
padding:30px;
max-height:80vh;
overflow:auto;

box-shadow:
0 30px 70px rgba(0,0,0,0.35);
}

.close-modal{
position:absolute;
top:18px;
right:22px;
font-size:22px;
cursor:pointer;
}

/* ================= PPT ANIMATION ================= */

.slide-enter{
animation: slideEnter .35s ease forwards;
}

.slide-exit{
animation: slideExit .25s ease forwards;
}

@keyframes slideEnter{
0%{
opacity:0;
transform:translateX(60px) scale(.96);
}
100%{
opacity:1;
transform:translateX(0) scale(1);
}
}

@keyframes slideExit{
0%{
opacity:1;
transform:translateX(0) scale(1);
}
100%{
opacity:0;
transform:translateX(-60px) scale(.96);
}
}

</style>


<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)]">

<div class="py-8 px-6">

<div class="flex gap-8 items-start">

{{-- COVER --}}
<div class="book-cover mt-10">

<div class="book-card">

<div class="book-image cursor-pointer" onclick="openFile()">

@if($book->cover)
<img src="{{ asset('library/sampul/'.$book->cover) }}">
@endif

</div>

<div class="p-3 border-t">

<button onclick="openFile()"
class="w-full bg-black text-white py-2 rounded-lg text-sm">

Buka Materi

</button>

</div>

</div>

</div>

{{-- DETAIL --}}
<div class="space-y-3 max-w-xl mt-10">
    
<span class="text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-600">
{{ strtoupper($book->tipe ?? 'library') }}
</span>

<h1 class="text-2xl font-bold text-gray-800">
{{ $book->title }}
</h1>

<p class="text-gray-600 text-sm">
{{ $book->description }}
</p>

</div>

</div>

{{-- relate buku- --}}

@if($book->tipe == 'buku' && $relatedBooks->count())

<div class="mt-14">

<h2 class="text-lg font-semibold mb-4">
Related Book
</h2>

<div class="related-scroll">

@foreach($relatedBooks->slice(0,5) as $item)

<a href="{{ route('student.library.read', $item->id) }}" class="related-book">

<div class="book-cover-related">

@if($item->cover)
<img src="{{ asset('library/sampul/'.$item->cover) }}">
@endif

</div>

<div class="p-3 text-sm font-medium text-gray-700">
{{ Str::limit($item->title,50) }}
</div>

</a>

@endforeach


@if($relatedBooks->count() > 5)

<div class="more-card"
onclick="openModal('bookModal')">

+{{ $relatedBooks->count()-5 }} Materi

</div>

@endif

</div>

</div>

@endif

<div id="bookModal" class="modal-bg">

<div class="modal-content relative">

<span class="close-modal"
onclick="closeModal('bookModal')">✕</span>

<h3 class="text-lg font-semibold mb-6">
Semua Buku Lainnya
</h3>

<div class="modal-scroll">

@foreach(collect($relatedBooks)->take(5) as $item)

<a href="{{ route('student.library.read', $item->id) }}" class="related-book">

<div class="book-cover-related">

@if($item->cover)
<img src="{{ asset('library/sampul/'.$item->cover) }}">
@endif

</div>

<div class="p-3 text-sm font-medium text-gray-700">
{{ Str::limit($item->title,50) }}
</div>

</a>

@endforeach

</div>

</div>

</div>

{{-- -relate ppt --}}
@if($book->tipe == 'ppt' && $relatedPpts->count())

<div class="mt-14">

<h2 class="text-lg font-semibold mb-4">
Related PowerPoint
</h2>

<div class="related-scroll">

@foreach($relatedPpts->slice(0,5) as $item)

<a href="{{ route('student.library.read', $item->id) }}" class="related-ppt">

<div class="ppt-cover">

@if($item->cover)
<img src="{{ asset('library/sampul/'.$item->cover) }}">
@endif

</div>

<div class="p-3 text-sm font-medium text-gray-700">
{{ Str::limit($item->title,60) }}
</div>

</a>

@endforeach


@if($relatedPpts->count() > 5)

<div class="more-card"
onclick="openModal('pptModal')">

+{{ $relatedPpts->count()-5 }} Materi

</div>

@endif

</div>

</div>

@endif

<div id="pptModal" class="modal-bg">

<div class="modal-content relative">

<span class="close-modal"
onclick="closeModal('pptModal')">✕</span>

<h3 class="text-lg font-semibold mb-6">
Semua PPT Lainnya
</h3>

<div class="modal-scroll">

@foreach(collect($relatedPpts)->slice(5) as $item)

<a href="{{ route('student.library.read', $item->id) }}" class="related-ppt">

<div class="ppt-cover">

@if($item->cover)
<img src="{{ asset('library/sampul/'.$item->cover) }}">
@endif

</div>

<div class="p-3 text-sm font-medium text-gray-700">
{{ Str::limit($item->title,60) }}
</div>

</a>

@endforeach

</div>

</div>

</div>  

{{-- PDF VIEWER --}}
@if($book->tipe == 'buku')

<div id="pdfViewer" class="pdf-viewer">

<div class="pdf-header">

<div>{{ $book->title }}</div>

<button onclick="closePdf()">Tutup</button>

</div>

<div class="pdf-body">

<iframe src="{{ asset('library/file/'.$book->file) }}"></iframe>

</div>

</div>

@endif

@endif

@if($book->tipe == 'ppt')

<div id="pptViewer" class="ppt-viewer">

<div class="ppt-header">

<div class="font-semibold">
{{ $book->title }}
</div>

<div class="ppt-controls">

<button class="ppt-btn" onclick="prevSlide()">◀</button>

<button class="ppt-btn" onclick="nextSlide()">▶</button>

<button class="ppt-btn" onclick="closePpt()">Tutup</button>

</div>

</div>

<div class="ppt-body">

<div id="pptThumbs" class="ppt-thumbnails">
</div>

<div class="ppt-stage">

<canvas id="ppt-canvas"></canvas>

</div>

</div>

</div>

@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<script>

const fileType = "{{ $book->tipe }}";
const url = "{{ asset('library/file/'.$book->file) }}";

let pdfDoc = null;
let pageNum = 1;
let scale = 1.4;

function openFile(){

if(fileType==="ppt"){
document.getElementById("pptViewer").style.display="flex";
loadSlides();
}else{
document.getElementById("pdfViewer").style.display="flex";
}

}

function closePdf(){
document.getElementById("pdfViewer").style.display="none";
}

function closePpt(){
document.getElementById("pptViewer").style.display="none";
}

async function loadSlides(){

pdfDoc = await pdfjsLib.getDocument(url).promise;

renderSlide(pageNum);

loadThumbs();

}

/* ===================== */
/* RENDER SLIDE ANIMATION */
/* ===================== */

async function renderSlide(num){

const canvas = document.getElementById("ppt-canvas");

canvas.classList.remove("slide-enter");
canvas.classList.add("slide-exit");

setTimeout(async()=>{

const page = await pdfDoc.getPage(num);

const viewport = page.getViewport({scale:scale});

const ctx = canvas.getContext("2d");

canvas.width = viewport.width;
canvas.height = viewport.height;

await page.render({
canvasContext: ctx,
viewport: viewport
});

canvas.classList.remove("slide-exit");
canvas.classList.add("slide-enter");

updateActiveThumb();

},150);

}

function nextSlide(){

if(pageNum >= pdfDoc.numPages) return;

pageNum++;

renderSlide(pageNum);

}

function prevSlide(){

if(pageNum <= 1) return;

pageNum--;

renderSlide(pageNum);

}

function zoomIn(){

if(scale >= 3) return; // batas maksimal zoom

scale += 0.2;

renderSlide(pageNum);

}

function zoomOut(){

if(scale <= 0.6) return; // batas minimal zoom

scale -= 0.2;

renderSlide(pageNum);

}

function resetZoom(){

scale = 1.4;

renderSlide(pageNum);

}

async function loadThumbs(){

const container = document.getElementById("pptThumbs");

container.innerHTML = "";

for(let i=1;i<=pdfDoc.numPages;i++){

const page = await pdfDoc.getPage(i);

const viewport = page.getViewport({scale:0.25});

const canvas = document.createElement("canvas");

const ctx = canvas.getContext("2d");

canvas.width = viewport.width;
canvas.height = viewport.height;

await page.render({
canvasContext:ctx,
viewport:viewport
});

canvas.classList.add("ppt-thumb");

canvas.dataset.page = i;

canvas.onclick = function(){

pageNum = i;

renderSlide(pageNum);

};

container.appendChild(canvas);

}

}

function updateActiveThumb(){

document.querySelectorAll(".ppt-thumb").forEach(t=>{

t.classList.remove("active");

if(parseInt(t.dataset.page) === pageNum){

t.classList.add("active");

}

});

}

document.addEventListener("keydown",function(e){

if(document.getElementById("pptViewer").style.display === "flex"){

if(e.key === "ArrowRight") nextSlide();

if(e.key === "ArrowLeft") prevSlide();

}

});

function openModal(id){

const modal = document.getElementById(id);

if(modal){
modal.style.display = "flex";
}

}

function closeModal(id){

const modal = document.getElementById(id);

if(modal){
modal.style.display = "none";
}

}

</script>