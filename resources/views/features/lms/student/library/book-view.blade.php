@include('components/sidebar-beranda', [

'headerSideNav' => 'Library',

])



@if (Auth::user()->role === 'Siswa')



<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)]">



<div class="my-10 mx-6">



<div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-start">



<!-- COVER -->

<div class="max-w-[260px]">



<canvas id="coverCanvas"

class="w-full rounded-xl shadow-lg cursor-pointer"></canvas>



<button onclick="openViewer()"

class="mt-3 w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-semibold">

Baca Buku

</button>



</div>





<!-- DETAIL -->

<div>



<h2 class="text-3xl font-bold text-gray-700 mb-3">

{{ $book->title }}

</h2>



<p class="text-gray-400 mb-6">

Kelas {{ $book->kelas_id }}

</p>
<p class="text-gray-400 mb-6">

Mata Pelajaran {{ $book->mapel->mata_pelajaran ?? '-' }}

</p>



<h3 class="font-semibold text-lg mb-2">

Ringkasan Buku

</h3>



<p class="text-gray-600 leading-relaxed">

{{ $book->description }}

</p>



</div>



</div>



</div>



</div>





<!-- PDF VIEWER -->

<div id="pdfModal"

class="fixed inset-0 bg-black hidden z-50">



<!-- CLOSE BUTTON -->

<button onclick="closeViewer()"

class="fixed top-6 right-8 bg-white w-12 h-12 rounded-full shadow-lg text-xl flex items-center justify-center hover:bg-gray-200 z-[999]">

✕

</button>



<div class="w-full h-full flex items-center justify-center gap-10 px-20">



<!-- PREV -->

<button onclick="prevPage()"

class="bg-white w-14 h-14 rounded-full shadow text-2xl flex items-center justify-center hover:bg-gray-200">

❮

</button>



<!-- PAGE -->

<div class="bg-white p-4 rounded shadow">



<canvas id="pdfCanvas"

class="max-w-[650px] w-full"></canvas>



<p class="text-center text-sm mt-2">

Page <span id="page_num"></span> /

<span id="page_count"></span>

</p>



</div>



<!-- NEXT -->

<button onclick="nextPage()"

class="bg-white w-14 h-14 rounded-full shadow text-2xl flex items-center justify-center hover:bg-gray-200">

❯

</button>



<!-- THUMBNAIL -->

<div class="bg-black pl-4 pr-6 py-4 h-[80vh] overflow-y-auto rounded">



<div id="thumbnailContainer"

class="flex flex-col gap-4"></div>



</div>



</div>



</div>



@endif







<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>



<script>



const url = "{{ asset('library/file/'.$book->file) }}";



const pdfjsLib = window['pdfjs-dist/build/pdf'];



pdfjsLib.GlobalWorkerOptions.workerSrc =

'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';





let pdfDoc = null;

let pageNum = 1;



const canvas = document.getElementById('pdfCanvas');

const ctx = canvas.getContext('2d');



const coverCanvas = document.getElementById('coverCanvas');

const coverCtx = coverCanvas.getContext('2d');





function renderPage(num){



pdfDoc.getPage(num).then(function(page){



let viewport = page.getViewport({scale:1});



let containerWidth = 800;

let scale = containerWidth / viewport.width;



viewport = page.getViewport({scale:scale});



canvas.height = viewport.height;

canvas.width = viewport.width;



page.render({

canvasContext:ctx,

viewport:viewport

});



document.getElementById('page_num').textContent = num;



});



}





function prevPage(){



if(pageNum <= 1) return;



pageNum--;



renderPage(pageNum);



}





function nextPage(){



if(pageNum >= pdfDoc.numPages) return;



pageNum++;



renderPage(pageNum);



}





function openViewer(){



const modal = document.getElementById("pdfModal")



modal.classList.remove("hidden")

modal.classList.add("flex")



renderPage(pageNum)



}





function closeViewer(){



document.getElementById("pdfModal").classList.add("hidden");



}







pdfjsLib.getDocument(url).promise.then(function(pdf){



pdfDoc = pdf;



document.getElementById('page_count').textContent = pdf.numPages;





// COVER

pdf.getPage(1).then(function(page){



const viewport = page.getViewport({scale:0.8});



coverCanvas.height = viewport.height;

coverCanvas.width = viewport.width;



page.render({

canvasContext:coverCtx,

viewport:viewport

});



});





// THUMBNAIL

const container = document.getElementById("thumbnailContainer");



for(let i=1;i<=pdf.numPages;i++){



pdf.getPage(i).then(function(page){



const viewport = page.getViewport({scale:0.2});



const thumbCanvas = document.createElement("canvas");

const context = thumbCanvas.getContext("2d");



thumbCanvas.height = viewport.height;

thumbCanvas.width = viewport.width;



thumbCanvas.style.cursor = "pointer";

thumbCanvas.style.width = "90px";



page.render({

canvasContext:context,

viewport:viewport

});



thumbCanvas.onclick = function(){



pageNum = i;

renderPage(pageNum);



}



container.appendChild(thumbCanvas);



});



}



});





coverCanvas.onclick = function(){



openViewer();



}



</script>