/*=====================================================
=            Upload Manager v1                        =
=====================================================*/

const UploadManager = {

    queue: [],

    activeUploads: {},

    maxParallel: 2,

    currentRunning: 0,

    init() {

        this.popup = document.getElementById("uploadManager");
        this.body = document.getElementById("uploadManagerBody");
        this.counter = document.getElementById("uploadCounter");

        this.bind();

    },

    bind() {

        document
        .getElementById("btnMinimizeUpload")
        .onclick = () => {

            this.body.classList.toggle("hidden");

        };

        document
        .getElementById("btnCloseUpload")
        .onclick = () => {

            this.popup.classList.add("hidden");

        };

    },

    show() {

        this.popup.classList.remove("hidden");

    },

    hide() {

        this.popup.classList.add("hidden");

    },

    updateCounter() {

        this.counter.innerHTML =
            this.queue.length+" Upload";

    },

    createItem(file) {

        const tpl =
            document
            .getElementById("uploadItemTemplate")
            .content
            .cloneNode(true);

        const item =
            tpl.querySelector(".upload-item");

        item.dataset.id = Date.now();

        item.querySelector(".upload-name")
            .innerHTML = file.name;

        item.querySelector(".upload-status")
            .innerHTML = "Waiting...";

        item.querySelector(".retryUpload")
            .onclick = () => {

                this.retry(item.dataset.id);

            };

        item.querySelector(".cancelUpload")
            .onclick = () => {

                this.cancel(item.dataset.id);

            };

        this.body.appendChild(item);

        return item;

    },

    add(file,form){

    this.show();

    const row =
        this.createItem(file);

    row.id =
        "upload-row-"+Date.now();

    this.queue.push({

        id:row.dataset.id,

        file:file,

        form:form,

        row:row,

        status:"waiting",

        progress:0,

        xhr:null

    });

    this.updateCounter();

    this.process();

},

    process(){

        if(
            this.currentRunning>=this.maxParallel
        ){

            return;

        }

        const next =
            this.queue.find(q=>q.status==="waiting");

        if(!next){

            return;

        }

        next.status="uploading";

        this.currentRunning++;

        startUpload(next);

    },

    finish(id){

        let q =
            this.queue.find(x=>x.id==id);

        if(!q)return;

        q.status="finished";

        q.row.querySelector(".upload-status")
            .innerHTML="✔ Upload selesai";

        this.currentRunning--;

        this.process();

        setTimeout(()=>{

    q.row.remove();

    this.queue =
        this.queue.filter(
            x=>x.id!=id
        );

    this.updateCounter();

},3000);

    },

    fail(id){

        let q =
            this.queue.find(x=>x.id==id);

        if(!q)return;

        q.status="error";

        q.row.querySelector(".upload-status")
            .innerHTML="Upload gagal";

        q.row.querySelector(".retryUpload")
            .classList.remove("hidden");

        this.currentRunning--;

        this.process();

    },

    retry(id){

    let upload =

        this.queue.find(

            x=>x.id==id

        );

    if(!upload) return;

    upload.status="waiting";

    upload.progress=0;

    upload.row

        .querySelector(".retryUpload")

        .classList.add("hidden");

    upload.row

        .querySelector(".upload-status")

        .innerHTML="Waiting...";

    this.process();

},

    cancel(id){

    let upload =

        this.queue.find(

            x=>x.id==id

        );

    if(!upload) return;

    if(upload.xhr){

        upload.xhr.abort();

    }

    upload.row.remove();

    this.queue =

        this.queue.filter(

            x=>x.id!=id

        );

    if(this.currentRunning>0){

        this.currentRunning--;

    }

    this.updateCounter();

    this.process();

},

    updateProgress(id,percent){

        let q =
            this.queue.find(x=>x.id==id);

        if(!q)return;

        q.progress=percent;

        q.row
        .querySelector(".upload-progress")
        .style.width=
        percent+"%";

        q.row
        .querySelector(".upload-percent")
        .innerHTML=
        percent+"%";

    },

    updateSpeed(id,speed){

        let q =
            this.queue.find(x=>x.id==id);

        if(!q)return;

        q.row
        .querySelector(".upload-speed")
        .innerHTML=speed;

    },

    updateETA(id,eta){

        let q =
            this.queue.find(x=>x.id==id);

        if(!q)return;

        q.row
        .querySelector(".upload-eta")
        .innerHTML=eta;

    }

};

/*=====================================================
=            Upload Engine                            =
=====================================================*/

function startUpload(upload) {

    const xhr = new XMLHttpRequest();

    upload.xhr = xhr;

    const formData = new FormData(upload.form);

const input = upload.form.querySelector(
    'input[type=file][name=video_file]'
);

if(input){

    input.value="";
}

formData.set(
    "video_file",
    upload.file
);

    const startTime = Date.now();

    upload.row.querySelector(".upload-status").innerHTML = "Uploading...";

    xhr.upload.onprogress = function (e) {

        if (!e.lengthComputable) return;

        const percent = Math.round((e.loaded / e.total) * 100);

        UploadManager.updateProgress(upload.id, percent);

        updateTransferInfo(
            upload,
            e.loaded,
            e.total,
            startTime
        );

    };

    xhr.onreadystatechange = function () {

    if (xhr.readyState !== 4) return;

    if (xhr.status >= 200 && xhr.status < 300) {

        UploadManager.finish(upload.id);

        try {

            const response = JSON.parse(xhr.responseText);

            console.log("Response Upload :", response);

            if (response.success && response.row) {

                addVideoRow(response.row);

            }

            notifyUpload(upload.file.name);

        } catch (err) {

            console.error("Response bukan JSON", xhr.responseText);

        }

    } else {

        UploadManager.fail(upload.id);

        console.error(xhr.responseText);

    }

};

    xhr.onerror = function () {

        UploadManager.fail(upload.id);

    };

    xhr.onabort = function () {

        console.log("Upload dibatalkan");

    };

    xhr.open("POST", upload.form.action);

    xhr.setRequestHeader(
        "X-CSRF-TOKEN",
        document.querySelector('meta[name="csrf-token"]').content
    );

    xhr.setRequestHeader(
        "Accept",
        "application/json"
    );

    xhr.send(formData);

}

/*=====================================================
=            Speed & ETA                              =
=====================================================*/

function updateTransferInfo(upload, loaded, total, startTime) {

    const elapsed = (Date.now() - startTime) / 1000;

    const speed = loaded / elapsed;

    const remain = total - loaded;

    const eta = remain / speed;

    UploadManager.updateSpeed(

        upload.id,

        formatBytes(speed) + "/s"

    );

    UploadManager.updateETA(

        upload.id,

        formatTime(eta)

    );

}

function formatBytes(bytes) {

    if (bytes <= 0) return "0 B";

    const sizes = [

        "B",

        "KB",

        "MB",

        "GB"

    ];

    const i = Math.floor(

        Math.log(bytes) /

        Math.log(1024)

    );

    return (

        bytes /

        Math.pow(1024, i)

    ).toFixed(2)

        + " "

        + sizes[i];

}

function formatTime(sec) {

    sec = Math.max(

        0,

        Math.floor(sec)

    );

    const h =

        Math.floor(sec / 3600);

    const m =

        Math.floor(

            (sec % 3600) / 60

        );

    const s =

        sec % 60;

    if (h > 0)

        return `${h}j ${m}m`;

    if (m > 0)

        return `${m}m ${s}d`;

    return `${s}d`;

}

/*=====================================================
=            Bind Upload Form                         =
=====================================================*/

function bindLibraryUploadForm() {

    const form = document.getElementById("libraryForm");

    if (!form) return;

    form.addEventListener("submit", function (e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        const videoInput = document.getElementById("video_file");

        const tipe = document.getElementById("tipe_library")?.value;

        if (tipe !== "video") {
            form.submit();
            return;
        }

        if (!videoInput.files.length) {
            Swal.fire("Pilih video terlebih dahulu", "", "warning");
            return;
        }

        Array.from(videoInput.files).forEach(file => {
            UploadManager.add(file, form);
        });

        Array.from(videoInput.files).forEach(file => {
    UploadManager.add(file, form);
});

// Tutup modal
const modal = document.getElementById("modal_add_book");
if (modal) {
    modal.close();
}

// Reset form
form.reset();

document.getElementById("video_file_box")?.classList.add("hidden");

    }, true);


}



/*=====================================================
=            Auto Add Video Row                       =
=====================================================*/

function addVideoRow(html){

    const table = document.getElementById("table_video_body");

    if(!table){
        console.error("table_video_body tidak ditemukan");
        return;
    }

    table.insertAdjacentHTML("afterbegin", html);

}

function notifyUpload(title){

    if(window.Swal){

        Swal.fire({

            icon:"success",

            title:"Upload selesai",

            text:title,

            toast:true,

            timer:3000,

            position:"bottom-end",

            showConfirmButton:false

        });

    }

}

document.addEventListener(

"DOMContentLoaded",

()=>{

    UploadManager.init();

    bindLibraryUploadForm();

});