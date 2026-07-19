{{-- ========================================================= --}}
{{-- Upload Manager --}}
{{-- ========================================================= --}}
<style>

/*=========================================================
=                  Upload Manager                         =
=========================================================*/

#uploadManager{

    position:fixed;

    right:20px;

    bottom:20px;

    width:420px;

    max-height:80vh;

    background:#fff;

    border-radius:14px;

    overflow:hidden;

    border:1px solid #e5e7eb;

    box-shadow:
        0 15px 40px rgba(0,0,0,.18);

    z-index:999999;

    animation:uploadShow .25s ease;

}

#uploadManager.hidden{

    display:none;

}

/*=========================================================
=                      Header                             =
=========================================================*/

#uploadManagerHeader{

    background:#4f46e5;

    color:#fff;

    padding:14px 18px;

    display:flex;

    justify-content:space-between;

    align-items:center;

}

#uploadManagerTitle{

    font-size:16px;

    font-weight:600;

}

#uploadCounter{

    margin-top:2px;

    font-size:12px;

    opacity:.85;

}

#uploadManagerHeader button{

    width:34px;

    height:34px;

    border:none;

    border-radius:8px;

    cursor:pointer;

    background:rgba(255,255,255,.15);

    color:#fff;

    transition:.2s;

}

#uploadManagerHeader button:hover{

    background:rgba(255,255,255,.3);

}

/*=========================================================
=                         BODY                            =
=========================================================*/

#uploadManagerBody{

    background:#f9fafb;

    max-height:500px;

    overflow-y:auto;

}

#uploadManagerBody.hidden{

    display:none;

}

/*=========================================================
=                    Upload Card                          =
=========================================================*/

.upload-item{

    margin:12px;

    padding:15px;

    background:#fff;

    border-radius:12px;

    border:1px solid #ececec;

    transition:.25s;

}

.upload-item:hover{

    transform:translateY(-2px);

    box-shadow:0 8px 18px rgba(0,0,0,.08);

}

.upload-name{

    font-size:14px;

    font-weight:600;

    color:#222;

}

.upload-status{

    margin-top:3px;

    font-size:12px;

    color:#6b7280;

}

/*=========================================================
=                     Progress                            =
=========================================================*/

.progress{

    width:100%;

    height:8px;

    margin-top:12px;

    border-radius:999px;

    background:#ececec;

    overflow:hidden;

}

.upload-progress{

    width:0;

    height:100%;

    border-radius:999px;

    background:linear-gradient(
        90deg,
        #4f46e5,
        #2563eb
    );

    transition:width .25s;

}

/*=========================================================
=                    Footer Info                          =
=========================================================*/

.upload-info{

    display:flex;

    justify-content:space-between;

    margin-top:10px;

    font-size:12px;

}

.upload-percent{

    font-weight:600;

}

.upload-speed{

    color:#2563eb;

}

.upload-eta{

    color:#16a34a;

}

/*=========================================================
=                     Buttons                             =
=========================================================*/

.upload-buttons{

    display:flex;

    gap:8px;

    margin-top:12px;

}

.upload-buttons button{

    flex:1;

    border:none;

    cursor:pointer;

    border-radius:8px;

    padding:8px;

    color:#fff;

    transition:.25s;

}

.retryUpload{

    background:#f59e0b;

}

.retryUpload:hover{

    background:#d97706;

}

.cancelUpload{

    background:#ef4444;

}

.cancelUpload:hover{

    background:#dc2626;

}

/*=========================================================
=                     Status                              =
=========================================================*/

.upload-success{

    color:#16a34a;

    font-weight:bold;

}

.upload-error{

    color:#dc2626;

    font-weight:bold;

}

.upload-uploading{

    color:#2563eb;

    font-weight:bold;

}

/*=========================================================
=                     Scrollbar                           =
=========================================================*/

#uploadManagerBody::-webkit-scrollbar{

    width:8px;

}

#uploadManagerBody::-webkit-scrollbar-thumb{

    background:#cbd5e1;

    border-radius:999px;

}

#uploadManagerBody::-webkit-scrollbar-track{

    background:transparent;

}

/*=========================================================
=                    Animation                            =
=========================================================*/

@keyframes uploadShow{

    from{

        opacity:0;

        transform:translateY(35px);

    }

    to{

        opacity:1;

        transform:translateY(0);

    }

}

/*=========================================================
=                    Responsive                           =
=========================================================*/

@media(max-width:768px){

    #uploadManager{

        width:95%;

        left:10px;

        right:10px;

        bottom:10px;

    }

}

</style>
<div id="uploadManager"

class="fixed bottom-5 right-5 w-[420px] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-[999999] hidden">

    {{-- HEADER --}}
    <div
        class="bg-indigo-600 text-white flex items-center justify-between px-4 py-3">

        <div class="flex items-center gap-2">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M7 9l5-5m0 0l5 5m-5-5v12"/>

            </svg>

            <div>

                <div class="font-semibold">

                    Upload Manager

                </div>

                <div
                    id="uploadCounter"
                    class="text-xs text-indigo-100">

                    0 Upload

                </div>

            </div>

        </div>

        <div class="flex gap-2">

            <button
                id="btnMinimizeUpload"
                class="hover:bg-indigo-700 rounded px-2">

                —

            </button>

            <button
                id="btnCloseUpload"
                class="hover:bg-red-600 rounded px-2">

                ✕

            </button>

        </div>

    </div>

    {{-- BODY --}}
    <div
        id="uploadManagerBody"
        class="max-h-[450px] overflow-y-auto">

        {{-- Queue akan ditambahkan lewat JS --}}

    </div>

</div>
{{-- TEMPLATE ITEM (disalin JS) --}}

<template id="uploadItemTemplate">

<div
class="upload-item border-b p-4">

    <div class="flex justify-between">

        <div>

            <div
            class="font-semibold text-sm upload-name">

                Video.mp4

            </div>

            <div
            class="text-xs text-gray-500 upload-status">

                Waiting...

            </div>

        </div>

        <div>

            <button
            class="retryUpload hidden text-yellow-600 text-xs">

                Retry

            </button>

            <button
            class="cancelUpload text-red-500 text-xs">

                Cancel

            </button>

        </div>

    </div>

    <div
    class="mt-3">

        <div
        class="w-full h-2 bg-gray-200 rounded">

            <div
            class="upload-progress h-2 bg-indigo-600 rounded"
            style="width:0%">

            </div>

        </div>

    </div>

    <div
    class="flex justify-between text-xs mt-2 text-gray-500">

        <span class="upload-percent">

            0%

        </span>

        <span class="upload-speed">

            0 MB/s

        </span>

        <span class="upload-eta">

            ETA --:--

        </span>

    </div>

</div>

</template>