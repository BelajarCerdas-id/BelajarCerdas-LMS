```blade
{{-- ========================================================= --}}
{{-- MODAL TAMBAHKAN NILAI --}}
{{-- ========================================================= --}}

<dialog id="modalTambahNilai" class="modal">

    <div class="modal-box max-w-3xl rounded-3xl p-0 overflow-hidden">

        {{-- ================================================= --}}
        {{-- HEADER --}}
        {{-- ================================================= --}}

        <div class="bg-gradient-to-r from-blue-700 to-cyan-600 text-white p-6">

            <div class="flex justify-between items-center">

                <div>

                    <h2 class="text-2xl font-bold">
                        Tambahkan Nilai
                    </h2>

                    <p class="text-blue-100 text-sm mt-1">
                        Download template, isi nilai peserta, kemudian upload kembali.
                    </p>

                </div>

                <button
                    type="button"
                    id="btnCloseNilai"
                    class="btn btn-circle btn-sm btn-ghost text-white"
                >
                    ✕
                </button>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- FORM --}}
        {{-- ================================================= --}}

        <form
            id="formUploadNilai"
            action="{{ route(
                'lms.student-vice-principal.extracurricular.nilai.upload',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                    'extracurricularId' => $extracurricular->id
                ]
            ) }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf


            <div class="p-8 space-y-8 overflow-y-auto max-h-[70vh]">


                {{-- ================================================= --}}
                {{-- DOWNLOAD TEMPLATE --}}
                {{-- ================================================= --}}

                <div class="rounded-2xl border bg-slate-50 p-6">

                    <h3 class="font-bold text-lg mb-2">
                        Template Nilai
                    </h3>

                    <p class="text-sm text-slate-500 mb-5">
                        Download template Excel terlebih dahulu, isi nilai peserta,
                        lalu upload kembali file tersebut.
                    </p>


                    <a
                        href="{{ route(
                            'lms.student-vice-principal.extracurricular.nilai.template',
                            [
                                'role' => $role,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId,
                                'extracurricularId' => $extracurricular->id
                            ]
                        ) }}"
                        class="btn btn-success"
                    >

                        <i class="fa-solid fa-download mr-2"></i>

                        Download Template

                    </a>

                </div>


                {{-- ================================================= --}}
                {{-- PEMISAH --}}
                {{-- ================================================= --}}

                <div class="divider">
                    ATAU
                </div>


                {{-- ================================================= --}}
                {{-- UPLOAD EXCEL --}}
                {{-- ================================================= --}}

                <div>

                    <label
                        for="inputExcelNilai"
                        id="uploadNilaiBox"
                        class="
                            border-2
                            border-dashed
                            border-slate-300
                            rounded-3xl
                            p-10
                            flex
                            flex-col
                            items-center
                            cursor-pointer
                            hover:border-blue-500
                            hover:bg-blue-50
                            transition
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-file-excel
                                text-6xl
                                text-green-600
                            "
                        ></i>


                        <p class="font-semibold mt-4">
                            Klik atau Drag File Excel
                        </p>


                        <p class="text-sm text-slate-500">
                            *.xlsx / *.xls
                        </p>


                        <p
                            id="uploadNilaiFilename"
                            class="
                                mt-3
                                text-blue-600
                                font-semibold
                                text-center
                                break-all
                            "
                        ></p>

                    </label>


                    <input
                        type="file"
                        id="inputExcelNilai"
                        name="file"
                        accept=".xlsx,.xls"
                        class="hidden"
                    >

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- FOOTER --}}
            {{-- ================================================= --}}

            <div class="border-t p-5 flex justify-end gap-3">

                <button
                    type="button"
                    id="btnBatalNilai"
                    class="btn"
                >
                    Batal
                </button>


                <button
                    type="submit"
                    id="btnUploadNilai"
                    class="btn btn-primary"
                >

                    <i class="fa-solid fa-upload mr-2"></i>

                    Upload Excel

                </button>

            </div>

        </form>

    </div>


    {{-- ================================================= --}}
    {{-- BACKDROP --}}
    {{-- ================================================= --}}

    <form
        method="dialog"
        class="modal-backdrop"
    >
        <button>close</button>
    </form>

</dialog>


<script>

(function () {

    'use strict';


    /* =========================================================
       ELEMENT
    ========================================================= */

    const modal =
        document.getElementById('modalTambahNilai');


    const btnTambahNilai =
        document.getElementById('btnTambahNilai');


    const btnCloseNilai =
        document.getElementById('btnCloseNilai');


    const btnBatalNilai =
        document.getElementById('btnBatalNilai');


    const btnUploadNilai =
        document.getElementById('btnUploadNilai');


    const formUploadNilai =
        document.getElementById('formUploadNilai');


    const inputExcelNilai =
        document.getElementById('inputExcelNilai');


    const uploadNilaiBox =
        document.getElementById('uploadNilaiBox');


    const uploadNilaiFilename =
        document.getElementById('uploadNilaiFilename');


    if (!modal) {
        console.warn('modalTambahNilai tidak ditemukan.');
        return;
    }


    if (!inputExcelNilai) {
        console.warn('inputExcelNilai tidak ditemukan.');
        return;
    }


    /* =========================================================
       BUKA MODAL
    ========================================================= */

    if (btnTambahNilai) {

        btnTambahNilai.addEventListener(
            'click',
            function () {

                inputExcelNilai.value = '';

                uploadNilaiFilename.textContent = '';

                modal.showModal();

            }
        );

    }


    /* =========================================================
       RESET MODAL
    ========================================================= */

    function resetModal() {

        inputExcelNilai.value = '';

        uploadNilaiFilename.textContent = '';

        uploadNilaiBox.classList.remove(
            'border-blue-500',
            'bg-blue-50'
        );

    }


    /* =========================================================
       CLOSE
    ========================================================= */

    function closeModal() {

        resetModal();

        modal.close();

    }


    if (btnCloseNilai) {

        btnCloseNilai.addEventListener(
            'click',
            closeModal
        );

    }


    if (btnBatalNilai) {

        btnBatalNilai.addEventListener(
            'click',
            closeModal
        );

    }


    /* =========================================================
       PILIH FILE
    ========================================================= */

    inputExcelNilai.addEventListener(
        'change',
        function () {

            if (!this.files.length) {

                uploadNilaiFilename.textContent = '';

                return;

            }


            const file =
                this.files[0];


            uploadNilaiFilename.textContent =
                file.name;

        }
    );


    /* =========================================================
       DRAG OVER
    ========================================================= */

    uploadNilaiBox.addEventListener(
        'dragover',
        function (e) {

            e.preventDefault();

            uploadNilaiBox.classList.add(
                'border-blue-500',
                'bg-blue-50'
            );

        }
    );


    /* =========================================================
       DRAG LEAVE
    ========================================================= */

    uploadNilaiBox.addEventListener(
        'dragleave',
        function () {

            uploadNilaiBox.classList.remove(
                'border-blue-500',
                'bg-blue-50'
            );

        }
    );


    /* =========================================================
       DROP FILE
    ========================================================= */

    uploadNilaiBox.addEventListener(
        'drop',
        function (e) {

            e.preventDefault();

            uploadNilaiBox.classList.remove(
                'border-blue-500',
                'bg-blue-50'
            );


            if (!e.dataTransfer.files.length) {
                return;
            }


            inputExcelNilai.files =
                e.dataTransfer.files;


            inputExcelNilai.dispatchEvent(
                new Event('change')
            );

        }
    );


    /* =========================================================
       UPLOAD
    ========================================================= */

    if (formUploadNilai) {

        formUploadNilai.addEventListener(
            'submit',
            function (e) {

                e.preventDefault();


                const file =
                    inputExcelNilai.files[0];


                /* =============================================
                   CEK FILE
                ============================================= */

                if (!file) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'File belum dipilih',

                        text:
                            'Silakan pilih file Excel terlebih dahulu.'

                    });

                    return;

                }


                /* =============================================
                   CEK EXTENSION
                ============================================= */

                const extension =
                    file.name
                        .split('.')
                        .pop()
                        .toLowerCase();


                if (
                    !['xlsx', 'xls']
                        .includes(extension)
                ) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Format file tidak valid',

                        text:
                            'File harus berupa Excel (.xlsx atau .xls).'

                    });

                    return;

                }


                /* =============================================
                   FORMDATA
                ============================================= */

                const formData =
                    new FormData(formUploadNilai);


                /* =============================================
                   LOADING
                ============================================= */

                btnUploadNilai.disabled =
                    true;


                btnUploadNilai.innerHTML = `
                    <span class="loading loading-spinner loading-sm"></span>
                    Mengupload...
                `;


                Swal.fire({

                    title: 'Mengupload...',

                    text:
                        'Sedang memproses file nilai.',

                    allowOutsideClick: false,

                    allowEscapeKey: false,

                    didOpen: function () {

                        Swal.showLoading();

                    }

                });


                /* =============================================
                   AJAX
                ============================================= */

                $.ajax({

                    url:
                        formUploadNilai.action,

                    type:
                        'POST',

                    data:
                        formData,

                    processData:
                        false,

                    contentType:
                        false,


                    /* =========================================
                       SUCCESS
                    ========================================= */

                    success:
                        function (response) {

                            console.log(
                                'UPLOAD NILAI SUCCESS:',
                                response
                            );


                            resetModal();

                            modal.close();


                            Swal.fire({

                                icon:
                                    'success',

                                title:
                                    'Berhasil!',

                                text:
                                    response.message ??
                                    'Data nilai peserta berhasil disimpan.',

                                timer:
                                    1800,

                                showConfirmButton:
                                    false

                            });


                            if (
                                typeof refreshKPI ===
                                'function'
                            ) {

                                refreshKPI();

                            }


                            setTimeout(
                                function () {

                                    location.reload();

                                },
                                1800
                            );

                        },


                    /* =========================================
                       ERROR
                    ========================================= */

                    error:
                        function (xhr) {

                            console.error(
                                'UPLOAD NILAI ERROR:',
                                xhr.responseText
                            );


                            let message =
                                'Terjadi kesalahan saat mengupload Excel.';


                            if (
                                xhr.responseJSON &&
                                xhr.responseJSON.message
                            ) {

                                message =
                                    xhr.responseJSON.message;

                            }


                            if (
                                xhr.responseJSON &&
                                xhr.responseJSON.errors
                            ) {

                                const errors =
                                    xhr.responseJSON.errors;


                                const firstError =
                                    Object.values(errors)[0];


                                if (
                                    firstError &&
                                    firstError[0]
                                ) {

                                    message =
                                        firstError[0];

                                }

                            }


                            Swal.fire({

                                icon:
                                    'error',

                                title:
                                    'Upload gagal',

                                text:
                                    message

                            });

                        },


                    /* =========================================
                       COMPLETE
                    ========================================= */

                    complete:
                        function () {

                            btnUploadNilai.disabled =
                                false;


                            btnUploadNilai.innerHTML = `
                                <i class="fa-solid fa-upload mr-2"></i>
                                Upload Excel
                            `;

                        }

                });

            }
        );

    }

})();

</script>
