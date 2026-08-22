@include('components/sidebar-beranda', [
    'headerSideNav' => 'Detail Ekstrakurikuler'
])

@if(Auth::user()->role == 'Wakil Kesiswaan' || Auth::user()->role == 'Admin')

{{-- =========================================================
    MAIN CONTENT
========================================================= --}}

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

    <div class="mx-6 my-8 space-y-8">

        {{-- =========================================================
            HEADER
        ========================================================= --}}

        <section>

            <div
                class="bg-white rounded-3xl border border-slate-100
                shadow-sm p-8">

                <div
                    class="flex flex-col lg:flex-row
                    lg:items-center lg:justify-between gap-6">

                    {{-- LEFT --}}

                    <div>

                        <a
                            href="{{ url()->previous() }}"
                            class="inline-flex items-center gap-2
                            text-sm text-blue-600
                            hover:text-blue-700 transition">

                            <i class="fa-solid fa-arrow-left"></i>

                            Kembali

                        </a>


                        <div class="flex items-center gap-4 mt-5">

                            {{-- ICON --}}

                            <div
                                class="w-16 h-16 rounded-2xl
                                bg-blue-100
                                flex items-center justify-center">

                                <i
                                    class="fa-solid fa-basketball
                                    text-3xl text-blue-600">
                                </i>

                            </div>


                            {{-- TITLE --}}

                            <div>

                                <h1
                                    class="text-3xl font-bold
                                    text-slate-800">

                                    {{ $extracurricular->name }}

                                </h1>


                                <p
                                    class="text-slate-500 mt-1">

                                    Administrasi Ekstrakurikuler
                                    {{ $extracurricular->name }}

                                </p>

                            </div>

                        </div>

                    </div>
  

                </div>

            </div>

        </section>



        {{-- =========================================================
            KPI
        ========================================================= --}}

        <section>

            <div
                class="bg-white rounded-3xl
                shadow-sm border border-slate-100
                p-8">

                <div
                    class="flex flex-col md:flex-row
                    md:items-center md:justify-between gap-6">

                    {{-- LEFT --}}

                    <div>

                        <p class="text-slate-500">

                            Jumlah Berkas

                        </p>


                        <h2
                            id="total-document"
                            class="text-5xl font-bold
                            text-blue-600 mt-2">

                            {{ $totalDocument }} / 4

                        </h2>


                        <p
                            class="text-sm text-slate-400 mt-2">

                            {{ $totalDocument }}
                            dokumen administrasi telah diunggah.

                        </p>

                    </div>


                    {{-- STATUS --}}

                    <div>

                        @if($isComplete)

                            <div
                                class="inline-flex items-center
                                gap-2 px-5 py-3
                                rounded-2xl
                                bg-green-50
                                text-green-700">

                                <i class="fa-solid fa-circle-check"></i>

                                <span class="font-semibold">
                                    Lengkap
                                </span>

                            </div>

                        @elseif($totalDocument > 0)

                            <div
                                class="inline-flex items-center
                                gap-2 px-5 py-3
                                rounded-2xl
                                bg-yellow-50
                                text-yellow-700">

                                <i class="fa-solid fa-circle-half-stroke"></i>

                                <span class="font-semibold">
                                    Hampir Lengkap
                                </span>

                            </div>

                        @else

                            <div
                                class="inline-flex items-center
                                gap-2 px-5 py-3
                                rounded-2xl
                                bg-red-50
                                text-red-700">

                                <i class="fa-solid fa-circle-xmark"></i>

                                <span class="font-semibold">
                                    Belum Lengkap
                                </span>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </section>



        {{-- =========================================================
            DAFTAR BERKAS
        ========================================================= --}}

        <section>

            <div
                class="bg-white rounded-3xl
                shadow-sm border border-slate-100
                overflow-hidden">

                {{-- HEADER --}}

                <div
                    class="px-8 py-6
                    border-b border-slate-100">

                    <h2
                        class="text-xl font-bold
                        text-slate-800">

                        Berkas Administrasi

                    </h2>


                    <p
                        class="text-sm text-slate-500 mt-1">

                        Seluruh dokumen administrasi yang wajib
                        diunggah untuk ekstrakurikuler

                        <span class="font-semibold text-slate-700">

                            {{ $extracurricular->name }}

                        </span>.

                    </p>

                </div>



                {{-- DOCUMENT LIST --}}

                <div class="p-6 space-y-5">


                    {{-- =================================================
                        SILABUS
                    ================================================= --}}

                    <div
                        class="flex flex-col lg:flex-row
                        lg:items-center lg:justify-between
                        gap-5 rounded-2xl p-5
                        {{ $kelengkapan->silabus
                            ? 'bg-green-50'
                            : 'bg-red-50' }}">

                        <div class="flex items-center gap-4">

                            <div
                                class="w-14 h-14 shrink-0
                                rounded-2xl bg-white
                                flex items-center justify-center
                                shadow-sm">

                                <i
                                    class="fa-solid fa-file-pdf
                                    text-3xl text-red-500">
                                </i>

                            </div>


                            <div>

                                <h3
                                    class="font-bold
                                    text-slate-800">

                                    Silabus

                                </h3>


                                @if($kelengkapan->silabus)

                                    <p
                                        class="text-sm
                                        text-green-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-check mr-1">
                                        </i>

                                        Berkas sudah diunggah

                                    </p>

                                @else

                                    <p
                                        class="text-sm
                                        text-red-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-xmark mr-1">
                                        </i>

                                        Berkas belum diunggah

                                    </p>

                                @endif

                            </div>

                        </div>


                        {{-- ACTION --}}

                        <div class="flex gap-3">

                            @if($kelengkapan->silabus)

                                <a
                                    href="{{ route(
                                        'lms.student-vice-principal.extracurricular-management.kelengkapan.file',
                                        [
                                            'role' => $role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId,
                                            'extracurricularId' => $extracurricularId,
                                            'document' => 'silabus'
                                        ]
                                    ) }}"
                                    target="_blank"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-white
                                    border border-slate-200
                                    hover:bg-slate-100
                                    text-slate-700
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-eye"></i>

                                    Lihat

                                </a>


                                <button
                                    type="button"
                                    onclick="replaceDocument('silabus')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-blue-600
                                    hover:bg-blue-700
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-pen"></i>

                                    Ganti

                                </button>

                            @else

                                <button
                                    type="button"
                                    onclick="uploadDocument('silabus')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-red-500
                                    hover:bg-red-600
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-upload"></i>

                                    Upload

                                </button>

                            @endif

                        </div>

                    </div>



                    {{-- =================================================
                        PROTA
                    ================================================= --}}

                    <div
                        class="flex flex-col lg:flex-row
                        lg:items-center lg:justify-between
                        gap-5 rounded-2xl p-5
                        {{ $kelengkapan->prota
                            ? 'bg-green-50'
                            : 'bg-red-50' }}">

                        <div class="flex items-center gap-4">

                            <div
                                class="w-14 h-14 shrink-0
                                rounded-2xl bg-white
                                flex items-center justify-center
                                shadow-sm">

                                <i
                                    class="fa-solid fa-file-pdf
                                    text-3xl text-red-500">
                                </i>

                            </div>


                            <div>

                                <h3
                                    class="font-bold
                                    text-slate-800">

                                    PROTA

                                </h3>


                                @if($kelengkapan->prota)

                                    <p
                                        class="text-sm
                                        text-green-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-check mr-1">
                                        </i>

                                        Berkas sudah diunggah

                                    </p>

                                @else

                                    <p
                                        class="text-sm
                                        text-red-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-xmark mr-1">
                                        </i>

                                        Berkas belum diunggah

                                    </p>

                                @endif

                            </div>

                        </div>


                        <div class="flex gap-3">

                            @if($kelengkapan->prota)

                                <a
                                    href="{{ route(
                                        'lms.student-vice-principal.extracurricular-management.kelengkapan.file',
                                        [
                                            'role' => $role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId,
                                            'extracurricularId' => $extracurricularId,
                                            'document' => 'prota'
                                        ]
                                    ) }}"
                                    target="_blank"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-white
                                    border border-slate-200
                                    hover:bg-slate-100
                                    text-slate-700
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-eye"></i>

                                    Lihat

                                </a>


                                <button
                                    type="button"
                                    onclick="replaceDocument('prota')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-blue-600
                                    hover:bg-blue-700
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-pen"></i>

                                    Ganti

                                </button>

                            @else

                                <button
                                    type="button"
                                    onclick="uploadDocument('prota')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-red-500
                                    hover:bg-red-600
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-upload"></i>

                                    Upload

                                </button>

                            @endif

                        </div>

                    </div>



                    {{-- =================================================
                        PROSEM
                    ================================================= --}}

                    <div
                        class="flex flex-col lg:flex-row
                        lg:items-center lg:justify-between
                        gap-5 rounded-2xl p-5
                        {{ $kelengkapan->prosem
                            ? 'bg-green-50'
                            : 'bg-red-50' }}">

                        <div class="flex items-center gap-4">

                            <div
                                class="w-14 h-14 shrink-0
                                rounded-2xl bg-white
                                flex items-center justify-center
                                shadow-sm">

                                <i
                                    class="fa-solid fa-file-pdf
                                    text-3xl text-red-500">
                                </i>

                            </div>


                            <div>

                                <h3
                                    class="font-bold
                                    text-slate-800">

                                    PROSEM

                                </h3>


                                @if($kelengkapan->prosem)

                                    <p
                                        class="text-sm
                                        text-green-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-check mr-1">
                                        </i>

                                        Berkas sudah diunggah

                                    </p>

                                @else

                                    <p
                                        class="text-sm
                                        text-red-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-xmark mr-1">
                                        </i>

                                        Berkas belum diunggah

                                    </p>

                                @endif

                            </div>

                        </div>


                        <div class="flex gap-3">

                            @if($kelengkapan->prosem)

                                <a
                                    href="{{ route(
                                        'lms.student-vice-principal.extracurricular-management.kelengkapan.file',
                                        [
                                            'role' => $role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId,
                                            'extracurricularId' => $extracurricularId,
                                            'document' => 'prosem'
                                        ]
                                    ) }}"
                                    target="_blank"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-white
                                    border border-slate-200
                                    hover:bg-slate-100
                                    text-slate-700
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-eye"></i>

                                    Lihat

                                </a>


                                <button
                                    type="button"
                                    onclick="replaceDocument('prosem')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-blue-600
                                    hover:bg-blue-700
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-pen"></i>

                                    Ganti

                                </button>

                            @else

                                <button
                                    type="button"
                                    onclick="uploadDocument('prosem')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-red-500
                                    hover:bg-red-600
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-upload"></i>

                                    Upload

                                </button>

                            @endif

                        </div>

                    </div>



                    {{-- =================================================
                        RPP
                    ================================================= --}}

                    <div
                        class="flex flex-col lg:flex-row
                        lg:items-center lg:justify-between
                        gap-5 rounded-2xl p-5
                        {{ $kelengkapan->rpp
                            ? 'bg-green-50'
                            : 'bg-red-50' }}">

                        <div class="flex items-center gap-4">

                            <div
                                class="w-14 h-14 shrink-0
                                rounded-2xl bg-white
                                flex items-center justify-center
                                shadow-sm">

                                <i
                                    class="fa-solid fa-file-pdf
                                    text-3xl text-red-500">
                                </i>

                            </div>


                            <div>

                                <h3
                                    class="font-bold
                                    text-slate-800">

                                    RPP

                                </h3>


                                @if($kelengkapan->rpp)

                                    <p
                                        class="text-sm
                                        text-green-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-check mr-1">
                                        </i>

                                        Berkas sudah diunggah

                                    </p>

                                @else

                                    <p
                                        class="text-sm
                                        text-red-600 mt-1">

                                        <i
                                            class="fa-solid
                                            fa-circle-xmark mr-1">
                                        </i>

                                        Berkas belum diunggah

                                    </p>

                                @endif

                            </div>

                        </div>


                        <div class="flex gap-3">

                            @if($kelengkapan->rpp)

                                <a
                                    href="{{ route(
                                        'lms.student-vice-principal.extracurricular-management.kelengkapan.file',
                                        [
                                            'role' => $role,
                                            'schoolName' => $schoolName,
                                            'schoolId' => $schoolId,
                                            'extracurricularId' => $extracurricularId,
                                            'document' => 'rpp'
                                        ]
                                    ) }}"
                                    target="_blank"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-white
                                    border border-slate-200
                                    hover:bg-slate-100
                                    text-slate-700
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-eye"></i>

                                    Lihat

                                </a>


                                <button
                                    type="button"
                                    onclick="replaceDocument('rpp')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-blue-600
                                    hover:bg-blue-700
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-pen"></i>

                                    Ganti

                                </button>

                            @else

                                <button
                                    type="button"
                                    onclick="uploadDocument('rpp')"
                                    class="inline-flex items-center
                                    gap-2 px-5 py-2.5
                                    rounded-xl
                                    bg-red-500
                                    hover:bg-red-600
                                    text-white
                                    font-semibold
                                    transition">

                                    <i class="fa-solid fa-upload"></i>

                                    Upload

                                </button>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- =========================================================
            KOMENTAR
        ========================================================= --}}

        @if(isset($kelengkapan->comment))

            <section>

                <div
                    class="bg-white rounded-3xl
                    shadow-sm border border-slate-100
                    p-8">

                    <div class="flex items-start gap-4">

                        <div
                            class="w-12 h-12 rounded-2xl
                            bg-blue-100
                            flex items-center justify-center
                            shrink-0">

                            <i
                                class="fa-solid fa-comment-dots
                                text-blue-600 text-xl">
                            </i>

                        </div>

                        <div class="flex-1">

                            <h3
                                class="font-bold text-slate-800">

                                Komentar Wakil Kesiswaan

                            </h3>


                            <p
                                class="text-sm text-slate-500 mt-1">

                                Catatan administrasi ekstrakurikuler.

                            </p>


                            <div
                                class="mt-4 p-4
                                bg-slate-50
                                rounded-2xl
                                text-sm text-slate-600">

                                {{ $kelengkapan->comment ?: 'Belum ada komentar.' }}

                            </div>

                        </div>

                    </div>

                </div>

            </section>

        @endif

    </div>

</div>



{{-- =========================================================
    HIDDEN FILE INPUT
========================================================= --}}

<input
    type="file"
    id="upload-document-input"
    class="hidden"
    accept=".pdf,application/pdf">



{{-- =========================================================
    JAVASCRIPT
========================================================= --}}

{{-- =========================================================
JAVASCRIPT
========================================================= --}}

<script>
(function () {

    let selectedDocument = null;
    let uploadMode = 'upload';


    /*
    |--------------------------------------------------------------------------
    | GET INPUT
    |--------------------------------------------------------------------------
    */

    function getUploadInput()
    {
        return window.document.getElementById(
            'upload-document-input'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPLOAD DOCUMENT
    |--------------------------------------------------------------------------
    */

    window.uploadDocument = function (documentName = null)
    {
        selectedDocument = documentName;
        uploadMode = 'upload';

        const input = getUploadInput();

        if (!input) {

            console.error(
                'Element #upload-document-input tidak ditemukan.'
            );

            if (typeof Swal !== 'undefined') {

                Swal.fire({
                    icon: 'error',
                    title: 'Input tidak ditemukan',
                    text: 'Input file upload tidak ditemukan pada halaman.'
                });

            }

            return;
        }

        input.value = '';

        input.click();
    };


    /*
    |--------------------------------------------------------------------------
    | REPLACE DOCUMENT
    |--------------------------------------------------------------------------
    */

    window.replaceDocument = function (documentName)
    {
        selectedDocument = documentName;
        uploadMode = 'replace';

        const input = getUploadInput();

        if (!input) {

            console.error(
                'Element #upload-document-input tidak ditemukan.'
            );

            if (typeof Swal !== 'undefined') {

                Swal.fire({
                    icon: 'error',
                    title: 'Input tidak ditemukan',
                    text: 'Input file upload tidak ditemukan pada halaman.'
                });

            }

            return;
        }

        input.value = '';

        input.click();
    };


    /*
    |--------------------------------------------------------------------------
    | FILE INPUT CHANGE
    |--------------------------------------------------------------------------
    */

    function initUploadInput()
    {
        const input = getUploadInput();

        if (!input) {

            console.error(
                'upload-document-input belum ditemukan.'
            );

            return;
        }


        /*
        | Hindari event listener terpasang dua kali
        */

        if (input.dataset.uploadInitialized === 'true') {
            return;
        }

        input.dataset.uploadInitialized = 'true';


        input.addEventListener('change', function ()
        {
            if (!this.files || !this.files.length) {
                return;
            }


            const file = this.files[0];


            /*
            |--------------------------------------------------------------------------
            | VALIDASI PDF
            |--------------------------------------------------------------------------
            */

            const isPdf =
                file.type === 'application/pdf' ||
                file.name.toLowerCase().endsWith('.pdf');


            if (!isPdf) {

                if (typeof Swal !== 'undefined') {

                    Swal.fire({
                        icon: 'error',
                        title: 'File tidak valid',
                        text: 'Dokumen harus berupa file PDF.'
                    });

                } else {

                    alert(
                        'Dokumen harus berupa file PDF.'
                    );

                }

                this.value = '';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | VALIDASI JENIS DOKUMEN
            |--------------------------------------------------------------------------
            */

            if (!selectedDocument) {

                if (typeof Swal !== 'undefined') {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Dokumen',
                        text:
                            'Silakan pilih jenis dokumen terlebih dahulu.'
                    });

                }

                this.value = '';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | UPLOAD
            |--------------------------------------------------------------------------
            */

            uploadKelengkapanFile(
                selectedDocument,
                file,
                uploadMode
            );

        });
    }


    /*
    |--------------------------------------------------------------------------
    | UPLOAD / REPLACE
    |--------------------------------------------------------------------------
    */

    window.uploadKelengkapanFile = function (
        documentName,
        file,
        mode
    )
    {

        let uploadUrl = '';


        /*
        |--------------------------------------------------------------------------
        | REPLACE
        |--------------------------------------------------------------------------
        */

        if (mode === 'replace') {

            uploadUrl = "{{ route(
                'lms.student-vice-principal.extracurricular-management.kelengkapan.replace',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'extracurricularId' => $extracurricularId
                ]
            ) }}";


        /*
        |--------------------------------------------------------------------------
        | UPLOAD
        |--------------------------------------------------------------------------
        */

        } else {

            uploadUrl = "{{ route(
                'lms.student-vice-principal.extracurricular-management.kelengkapan.upload',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'extracurricularId' => $extracurricularId
                ]
            ) }}";

        }


        console.log(
            'Kelengkapan upload:',
            {
                url: uploadUrl,
                document: documentName,
                mode: mode,
                file: file
            }
        );


        /*
        |--------------------------------------------------------------------------
        | FORM DATA
        |--------------------------------------------------------------------------
        */

        const formData = new FormData();

        formData.append(
            '_token',
            "{{ csrf_token() }}"
        );

        formData.append(
            'document',
            documentName
        );

        formData.append(
            'file',
            file
        );


        /*
        |--------------------------------------------------------------------------
        | SWEETALERT LOADING
        |--------------------------------------------------------------------------
        */

        if (typeof Swal !== 'undefined') {

            Swal.fire({
                title:
                    mode === 'replace'
                        ? 'Mengganti berkas...'
                        : 'Mengunggah berkas...',

                text: 'Mohon tunggu.',

                allowOutsideClick: false,

                allowEscapeKey: false,

                didOpen: function () {
                    Swal.showLoading();
                }
            });

        }


        /*
        |--------------------------------------------------------------------------
        | FETCH
        |--------------------------------------------------------------------------
        */

        fetch(uploadUrl, {

            method: 'POST',

            body: formData,

            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }

        })

        .then(async function (response)
        {

            const contentType =
                response.headers.get('content-type') || '';


            let data;


            /*
            | JSON
            */

            if (contentType.includes('application/json')) {

                data = await response.json();

            }


            /*
            | BUKAN JSON
            */

            else {

                const text =
                    await response.text();

                console.error(
                    'Response server:',
                    text
                );

                throw new Error(
                    'Server mengembalikan response yang bukan JSON.'
                );

            }


            /*
            | HTTP ERROR
            */

            if (!response.ok) {

                throw new Error(
                    data.message ||
                    'Gagal mengunggah berkas.'
                );

            }


            return data;

        })


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        .then(function (data)
        {

            console.log(
                'Upload berhasil:',
                data
            );


            if (typeof Swal !== 'undefined') {

                Swal.fire({

                    icon: 'success',

                    title: 'Berhasil',

                    text:
                        data.message ||
                        'Berkas berhasil disimpan.',

                    confirmButtonColor: '#2563eb'

                })

                .then(function () {

                    window.location.reload();

                });

            } else {

                alert(
                    data.message ||
                    'Berkas berhasil disimpan.'
                );

                window.location.reload();

            }

        })


        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        .catch(function (error)
        {

            console.error(
                'Upload error:',
                error
            );


            if (typeof Swal !== 'undefined') {

                Swal.fire({

                    icon: 'error',

                    title: 'Gagal',

                    text:
                        error.message ||
                        'Terjadi kesalahan saat mengunggah berkas.'

                });

            } else {

                alert(
                    error.message ||
                    'Terjadi kesalahan saat mengunggah berkas.'
                );

            }

        })


        /*
        |--------------------------------------------------------------------------
        | FINALLY
        |--------------------------------------------------------------------------
        */

        .finally(function ()
        {

            const input = getUploadInput();

            if (input) {
                input.value = '';
            }

        });

    };


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    if (
        window.document.readyState === 'loading'
    ) {

        window.document.addEventListener(
            'DOMContentLoaded',
            initUploadInput
        );

    } else {

        initUploadInput();

    }

})();
</script>
@endif