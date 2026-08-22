<!DOCTYPE html>

<style>

.overflow-y-auto::-webkit-scrollbar{
    width:8px;
}

.overflow-y-auto::-webkit-scrollbar-track{
    background:#f1f5f9;
    border-radius:999px;
}

.overflow-y-auto::-webkit-scrollbar-thumb{
    background:#94a3b8;
    border-radius:999px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover{
    background:#64748b;
}


    .manual-dropdown-option {
        width: 100%;
        display: flex;
        align-items: center;
        padding: 10px 12px;
        border-radius: 9px;
        text-align: left;
        font-size: 14px;
        cursor: pointer;
        transition: all .15s ease;
    }

    .manual-dropdown-option:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .manual-dropdown-option.active {
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 600;
    }

    .manual-dropdown-option.no-result {
        cursor: default;
        color: #64748b;
        justify-content: center;
    }

    .manual-dropdown-option.no-result:hover {
        background: transparent;
        color: #64748b;
    }
</style>

<dialog id="modal_upload_member" class="modal">

    <div class="modal-box max-w-3xl rounded-3xl p-0 overflow-hidden">

        <div class="bg-gradient-to-r from-blue-700 to-cyan-600 text-white p-6">

            <div class="flex justify-between items-center">

                <div>

                    <h2 class="text-2xl font-bold">

                        Tambah Peserta Ekstrakurikuler

                    </h2>

                    <p class="text-blue-100 text-sm mt-1">

                        Tambahkan peserta secara manual atau upload melalui template Excel.

                    </p>

                </div>

                <form method="dialog">

                    <button class="btn btn-circle btn-sm btn-ghost text-white">

                        ✕

                    </button>

                </form>

            </div>

        </div>

        <form

            id="form_upload_member"

            method="POST"

            enctype="multipart/form-data"

        >

            @csrf

            <div class="p-8 space-y-8 overflow-y-auto max-h-[70vh]">

              {{-- =========================
     MANUAL INPUT
========================== --}}

{{-- =========================
     TAMBAH MANUAL
========================== --}}
<div class="rounded-2xl border bg-slate-50 p-6">

    <h3 class="font-bold text-lg mb-5">
        Tambah Manual
    </h3>

    <div class="grid md:grid-cols-3 gap-5">

        {{-- =====================================================
             KELAS
        ====================================================== --}}
        <div class="relative">

            <label class="label">
                <span class="label-text">
                    Kelas
                </span>
            </label>

            <div class="relative">

                <button
                    type="button"
                    id="kelas_dropdown_button"
                    class="w-full select select-bordered text-left bg-white pr-10"
                >
                    <span id="kelas_dropdown_text">
                        Pilih Kelas
                    </span>
                </button>

                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400"></i>

            </div>

            <div
                id="kelas_dropdown"
                class="hidden absolute z-[100] mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
            >

                <div class="p-2 border-b bg-white sticky top-0">

                    <div class="relative">

                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                        <input
                            type="text"
                            id="kelas_search"
                            class="input input-bordered w-full pl-9"
                            placeholder="Cari kelas..."
                            autocomplete="off"
                        >

                    </div>

                </div>

                <div
                    id="kelas_options"
                    class="max-h-[240px] overflow-y-auto p-1"
                ></div>

                <div
                    id="kelas_empty"
                    class="hidden px-4 py-3 text-sm text-slate-500 text-center"
                >
                    Kelas tidak ditemukan
                </div>

            </div>

            {{-- VALUE SEBENARNYA YANG DIKIRIM --}}
            <select
                id="kelas"
                name="kelas"
                class="hidden"
            >
                <option value="">Pilih Kelas</option>

                @php
                    $kelasAktif = $students
                        ->map(function ($student) {

                            $classData =
                                $student->UserAccount
                                    ->StudentSchoolClass
                                    ->first()
                                    ?->SchoolClass;

                            return is_object(
                                $classData->kelas ?? null
                            )
                                ? $classData->kelas->kelas
                                : ($classData->kelas ?? '');
                        })
                        ->filter()
                        ->unique()
                        ->sort();
                @endphp

                @foreach($kelasAktif as $kelas)
                    <option value="{{ $kelas }}">
                        {{ $kelas }}
                    </option>
                @endforeach

            </select>

        </div>


        {{-- =====================================================
             TIPE KELAS
        ====================================================== --}}
        <div class="relative">

            <label class="label">
                <span class="label-text">
                    Tipe Kelas
                </span>
            </label>

            <div class="relative">

                <button
                    type="button"
                    id="tipe_kelas_dropdown_button"
                    class="w-full select select-bordered text-left bg-white pr-10 disabled:bg-slate-100"
                    disabled
                >
                    <span id="tipe_kelas_dropdown_text">
                        Pilih Tipe Kelas
                    </span>
                </button>

                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400"></i>

            </div>

            <div
                id="tipe_kelas_dropdown"
                class="hidden absolute z-[90] mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
            >

                <div class="p-2 border-b bg-white sticky top-0">

                    <div class="relative">

                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                        <input
                            type="text"
                            id="tipe_kelas_search"
                            class="input input-bordered w-full pl-9"
                            placeholder="Cari tipe kelas..."
                            autocomplete="off"
                        >

                    </div>

                </div>

                <div
                    id="tipe_kelas_options"
                    class="max-h-[240px] overflow-y-auto p-1"
                ></div>

                <div
                    id="tipe_kelas_empty"
                    class="hidden px-4 py-3 text-sm text-slate-500 text-center"
                >
                    Tipe kelas tidak ditemukan
                </div>

            </div>

            <select
                id="nama_kelas"
                name="nama_kelas"
                class="hidden"
                disabled
            >
                <option value="">
                    Pilih Tipe Kelas
                </option>
            </select>

        </div>


        {{-- =====================================================
             NAMA SISWA
        ====================================================== --}}
        <div class="relative md:col-span-3">

            <label class="label">
                <span class="label-text">
                    Nama Peserta
                </span>
            </label>

            <div class="relative">

                <button
                    type="button"
                    id="student_dropdown_button"
                    class="w-full select select-bordered text-left bg-white pr-10 disabled:bg-slate-100"
                    disabled
                >
                    <span id="student_dropdown_text">
                        Pilih Kelas dan Tipe Kelas terlebih dahulu
                    </span>
                </button>

                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400"></i>

            </div>

            <div
                id="student_dropdown"
                class="hidden absolute z-[80] mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
            >

                <div class="p-2 border-b bg-white sticky top-0">

                    <div class="relative">

                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>

                        <input
                            type="text"
                            id="student_search"
                            class="input input-bordered w-full pl-9"
                            placeholder="Cari nama siswa..."
                            autocomplete="off"
                        >

                    </div>

                </div>

                <div
                    id="student_options"
                    class="max-h-[240px] overflow-y-auto p-1"
                ></div>

                <div
                    id="student_empty"
                    class="hidden px-4 py-3 text-sm text-slate-500 text-center"
                >
                    Siswa tidak ditemukan
                </div>

            </div>


            {{-- VALUE YANG DIKIRIM KE CONTROLLER --}}
            <select
                id="student_profile_id"
                name="student_profile_id"
                class="hidden member-required"
                required
                disabled
            >
                <option value="">
                    Pilih Kelas dan Tipe Kelas terlebih dahulu
                </option>

                @foreach($students as $student)

                    @php

                        $classData =
                            $student->UserAccount
                                ->StudentSchoolClass
                                ->first()
                                ?->SchoolClass;

                        $kelasStudent =
                            is_object(
                                $classData->kelas ?? null
                            )
                                ? $classData->kelas->kelas
                                : ($classData->kelas ?? '');

                        $tipeKelasStudent =
                            $classData->class_name ?? '';

                    @endphp

                    <option
                        value="{{ $student->id }}"
                        data-class="{{ $kelasStudent }}"
                        data-type="{{ $tipeKelasStudent }}"
                    >
                        {{ $student->nama_lengkap }}
                    </option>

                @endforeach

            </select>

        </div>

    </div>


    <div class="mt-6">

        <button
            type="button"
            id="btn_manual_member"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-user-plus mr-2"></i>
            Tambah Peserta
        </button>

    </div>

</div>

                {{-- =========================
                     PEMISAH
                ========================== --}}

                <div class="divider">

                    ATAU

                </div>

                {{-- =========================
                     BULK EXCEL
                ========================== --}}

                <div>

                    <a

                        href="{{ route(
                            'lms.student-vice-principal.extracurricular-management.member.download-template',
                            [
                                'role'=>request()->route('role'),
                                'schoolName'=>request()->route('schoolName'),
                                'schoolId'=>request()->route('schoolId'),
                                'extracurricularId'=>request()->route('extracurricularId')
                            ]
                        ) }}"

                        class="btn btn-success"

                    >

                        <i class="fa-solid fa-download mr-2"></i>

                        Download Template

                    </a>

                    <label

                        for="upload_member_excel"

                        class="mt-6 border-2 border-dashed border-slate-300 rounded-3xl p-10 flex flex-col items-center cursor-pointer hover:border-blue-500 transition"

                    >

                        <i class="fa-solid fa-file-excel text-6xl text-green-600"></i>

                        <p class="font-semibold mt-4">

                            Klik atau Drag File Excel

                        </p>

                        <p class="text-sm text-slate-500">

                            *.xlsx / *.xls

                        </p>

                        <p

                            id="upload_member_filename"

                            class="mt-3 text-blue-600 font-semibold"

                        ></p>

                    </label>

                    <input

                        type="file"

                        id="upload_member_excel"

                        name="excel_file"

                        accept=".xlsx,.xls"

                        class="hidden"

                    >

                </div>

            </div>

            <div class="border-t p-5 flex justify-end gap-3">

                <button

                    type="button"

                    class="btn"

                    onclick="modal_upload_member.close()"

                >

                    Batal

                </button>

                <button

                    type="submit"

                    class="btn btn-primary"

                >

                    <i class="fa-solid fa-upload mr-2"></i>

                    Upload Excel

                </button>

            </div>

        </form>

    </div>

</dialog>

<script>

const excelInput=document.getElementById('upload_member_excel');
const uploadBox=document.querySelector('label[for="upload_member_excel"]');
const fileName=document.getElementById('upload_member_filename');

excelInput.addEventListener('change',function(){

    fileName.innerHTML=this.files.length
        ?this.files[0].name
        :'';

    document.querySelectorAll('.member-required').forEach(function(item){

        item.required=!excelInput.files.length;

    });

});

uploadBox.addEventListener('dragover',function(e){

    e.preventDefault();

    uploadBox.classList.add('border-blue-500','bg-blue-50');

});

uploadBox.addEventListener('dragleave',function(){

    uploadBox.classList.remove('border-blue-500','bg-blue-50');

});

uploadBox.addEventListener('drop',function(e){

    e.preventDefault();

    uploadBox.classList.remove('border-blue-500','bg-blue-50');

    excelInput.files=e.dataTransfer.files;

    excelInput.dispatchEvent(new Event('change'));

});



$('#btn_manual_member').click(function(){

    let formData = new FormData($('#form_upload_member')[0]);

    $.ajax({

        url:"{{ route(
            'lms.student-vice-principal.extracurricular-management.member.store',
            [
                'role'=>request()->route('role'),
                'schoolName'=>request()->route('schoolName'),
                'schoolId'=>request()->route('schoolId'),
                'extracurricularId'=>request()->route('extracurricularId')
            ]
        ) }}",

        type:'POST',

        data:formData,

        processData:false,

        contentType:false,

        success:function(res){

            Swal.fire({
                icon:'success',
                title:'Berhasil',
                text:res.message
            }).then(()=>{

                modal_upload_member.close();

                $('#form_upload_member')[0].reset();

                $('#kelas').val('');
                $('#nama_kelas').val('');

                location.reload();

            });

        },

        error:function(xhr){

            Swal.fire({
                icon:'warning',
                title:'Peringatan',
                text:xhr.responseJSON?.message ?? 'Gagal menambahkan peserta.'
            });

        }

    });

});

$('#form_upload_member').submit(function(e){

    e.preventDefault();

    if(!excelInput.files.length){

        return;

    }

    let formData=new FormData(this);

    $.ajax({

        url:"{{ route(
            'lms.student-vice-principal.extracurricular-management.upload-member',
            [
                'role'=>request()->route('role'),
                'schoolName'=>request()->route('schoolName'),
                'schoolId'=>request()->route('schoolId'),
                'extracurricularId'=>request()->route('extracurricularId')
            ]
        ) }}",

        type:'POST',

        data:formData,

        processData:false,

        contentType:false,

        beforeSend:function(){

            Swal.fire({

                title:'Mengupload...',

                allowOutsideClick:false,

                didOpen(){

                    Swal.showLoading();

                }

            });

        },

        success:function(res){

            modal_upload_member.close();

            $('#form_upload_member')[0].reset();

            $('#upload_member_filename').text('');

            Swal.fire({

                icon:'success',

                title:'Berhasil',

                text:res.message

            }).then(()=>location.reload());

        },

        error:function(xhr){

            Swal.fire({

                icon:'error',

                title:'Gagal',

                text:xhr.responseJSON?.message ?? 'Upload gagal.'

            });

        }

    });

});

</script>

<script>

(function () {

    'use strict';


    /* =========================================================
       ELEMENT
    ========================================================= */

    const modal =
        document.getElementById('modal_upload_member');

    const form =
        document.getElementById('form_upload_member');

    const excelInput =
        document.getElementById('upload_member_excel');

    const uploadBox =
        document.querySelector(
            'label[for="upload_member_excel"]'
        );

    const fileName =
        document.getElementById(
            'upload_member_filename'
        );

    const kelasSelect =
        document.getElementById('kelas');

    const tipeKelasSelect =
        document.getElementById('nama_kelas');

    const studentSelect =
        document.getElementById(
            'student_profile_id'
        );

    const manualButton =
        document.getElementById(
            'btn_manual_member'
        );


    /* =========================================================
       ELEMENT SEARCHABLE DROPDOWN
    ========================================================= */

    const kelasButton =
        document.getElementById(
            'kelas_dropdown_button'
        );

    const kelasText =
        document.getElementById(
            'kelas_dropdown_text'
        );

    const kelasDropdown =
        document.getElementById(
            'kelas_dropdown'
        );

    const kelasSearch =
        document.getElementById(
            'kelas_search'
        );

    const kelasOptions =
        document.getElementById(
            'kelas_options'
        );

    const kelasEmpty =
        document.getElementById(
            'kelas_empty'
        );


    const tipeButton =
        document.getElementById(
            'tipe_kelas_dropdown_button'
        );

    const tipeText =
        document.getElementById(
            'tipe_kelas_dropdown_text'
        );

    const tipeDropdown =
        document.getElementById(
            'tipe_kelas_dropdown'
        );

    const tipeSearch =
        document.getElementById(
            'tipe_kelas_search'
        );

    const tipeOptions =
        document.getElementById(
            'tipe_kelas_options'
        );

    const tipeEmpty =
        document.getElementById(
            'tipe_kelas_empty'
        );


    const studentButton =
        document.getElementById(
            'student_dropdown_button'
        );

    const studentText =
        document.getElementById(
            'student_dropdown_text'
        );

    const studentDropdown =
        document.getElementById(
            'student_dropdown'
        );

    const studentSearch =
        document.getElementById(
            'student_search'
        );

    const studentOptionsContainer =
        document.getElementById(
            'student_options'
        );

    const studentEmpty =
        document.getElementById(
            'student_empty'
        );


    /* =========================================================
       CEK ELEMENT
    ========================================================= */

    if (
        !modal ||
        !form ||
        !excelInput ||
        !uploadBox ||
        !fileName ||
        !kelasSelect ||
        !tipeKelasSelect ||
        !studentSelect ||
        !manualButton
    ) {

        console.warn(
            'Element modal peserta tidak lengkap.'
        );

        return;
    }


    /* =========================================================
       KONFIGURASI
    ========================================================= */

    const MAX_VISIBLE = 4;


    /* =========================================================
       DATA SISWA
    ========================================================= */

    const studentOptions =
        Array.from(
            studentSelect.querySelectorAll(
                'option[data-class]'
            )
        ).map(function (option) {

            return {

                value:
                    option.value,

                text:
                    option.textContent.trim(),

                kelas:
                    option.dataset.class || '',

                tipe:
                    option.dataset.type || ''

            };

        });


    /* =========================================================
       DATA KELAS
    ========================================================= */

    const kelasOptionsData =
        Array.from(
            kelasSelect.querySelectorAll(
                'option'
            )
        )
        .filter(function (option) {

            return option.value !== '';

        })
        .map(function (option) {

            return {

                value:
                    option.value,

                text:
                    option.textContent.trim()

            };

        });


    /* =========================================================
       STATE
    ========================================================= */

    let selectedKelas = '';

    let selectedTipe = '';

    let selectedStudent = '';


    /* =========================================================
       NORMALIZE
    ========================================================= */

    function normalize(value) {

        return String(value || '')
            .toLowerCase()
            .trim();

    }


    /* =========================================================
       ESCAPE HTML
    ========================================================= */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value;

        return div.innerHTML;

    }


    /* =========================================================
       CLOSE SEMUA DROPDOWN
    ========================================================= */

    function closeAllDropdowns() {

        if (kelasDropdown) {

            kelasDropdown.classList.add(
                'hidden'
            );

        }

        if (tipeDropdown) {

            tipeDropdown.classList.add(
                'hidden'
            );

        }

        if (studentDropdown) {

            studentDropdown.classList.add(
                'hidden'
            );

        }

    }


    /* =========================================================
       OPEN DROPDOWN
    ========================================================= */

    function openDropdown(dropdown) {

        closeAllDropdowns();

        dropdown.classList.remove(
            'hidden'
        );

    }


    /* =========================================================
       RENDER KELAS
       MAX 4 DATA
    ========================================================= */

    function renderKelas(searchValue) {

        if (!kelasOptions) {
            return;
        }

        kelasOptions.innerHTML = '';

        kelasEmpty.classList.add(
            'hidden'
        );


        const keyword =
            normalize(searchValue);


        const filtered =
            kelasOptionsData.filter(
                function (item) {

                    return normalize(
                        item.text
                    ).includes(keyword);

                }
            );


        if (!filtered.length) {

            kelasEmpty.classList.remove(
                'hidden'
            );

            return;

        }


        filtered
            .slice(0, MAX_VISIBLE)
            .forEach(function (item) {

                const button =
                    document.createElement(
                        'button'
                    );

                button.type =
                    'button';

                button.className =
                    'manual-dropdown-option';


                if (
                    item.value ===
                    selectedKelas
                ) {

                    button.classList.add(
                        'active'
                    );

                }


                button.textContent =
                    item.text;


                button.addEventListener(
                    'click',
                    function () {

                        selectKelas(item);

                    }
                );


                kelasOptions.appendChild(
                    button
                );

            });

    }


    /* =========================================================
       SELECT KELAS
    ========================================================= */

function selectKelas(item) {

    selectedKelas =
        item.value;

    kelasSelect.value =
        item.value;

    kelasText.textContent =
        item.text;

    kelasSearch.value =
        '';

    closeAllDropdowns();


    /* =========================================
       RESET TIPE KELAS
    ========================================= */

    selectedTipe = '';

    selectedStudent = '';


    tipeKelasSelect.innerHTML = `
        <option value="">
            Pilih Tipe Kelas
        </option>
    `;

    tipeKelasSelect.value =
        '';


    tipeText.textContent =
        'Pilih Tipe Kelas';

    tipeSearch.value =
        '';


    /* =========================================
       RESET SISWA
    ========================================= */

    studentSelect.innerHTML = `
        <option value="">
            Pilih Kelas dan Tipe Kelas terlebih dahulu
        </option>
    `;

    studentSelect.value =
        '';

    studentSelect.disabled =
        true;


    studentButton.disabled =
        true;


    studentText.textContent =
        'Pilih Kelas dan Tipe Kelas terlebih dahulu';


    studentSearch.value =
        '';


    /* =========================================
       CEK TIPE KELAS
    ========================================= */

    const availableTipe =
        getTipeKelas();


    if (availableTipe.length > 0) {

        tipeButton.disabled =
            false;

        tipeKelasSelect.disabled =
            false;

    } else {

        tipeButton.disabled =
            true;

        tipeKelasSelect.disabled =
            true;

    }


    /* =========================================
       TAMPILKAN TIPE
    ========================================= */

    renderTipeKelas('');

}


    /* =========================================================
       GET TIPE KELAS
    ========================================================= */

    function getTipeKelas() {

        if (!selectedKelas) {

            return [];

        }


        const tipeList =
            studentOptions

                .filter(function (student) {

                    return (
                        student.kelas ===
                        selectedKelas
                    );

                })

                .map(function (student) {

                    return student.tipe;

                })

                .filter(function (tipe) {

                    return tipe !== '';

                });


        return [
            ...new Set(tipeList)
        ]
        .sort()
        .map(function (tipe) {

            return {

                value: tipe,

                text: tipe

            };

        });

    }


    /* =========================================================
       RENDER TIPE KELAS
       MAX 4 DATA
    ========================================================= */

    function renderTipeKelas(searchValue) {

        if (!tipeOptions) {
            return;
        }


        tipeOptions.innerHTML = '';

        tipeEmpty.classList.add(
            'hidden'
        );


        const tipeList =
            getTipeKelas();


        const keyword =
            normalize(searchValue);


        const filtered =
            tipeList.filter(
                function (item) {

                    return normalize(
                        item.text
                    ).includes(keyword);

                }
            );


        if (!filtered.length) {

            tipeEmpty.classList.remove(
                'hidden'
            );

            return;

        }


        filtered
            .slice(0, MAX_VISIBLE)
            .forEach(function (item) {

                const button =
                    document.createElement(
                        'button'
                    );

                button.type =
                    'button';

                button.className =
                    'manual-dropdown-option';


                if (
                    item.value ===
                    selectedTipe
                ) {

                    button.classList.add(
                        'active'
                    );

                }


                button.textContent =
                    item.text;


                button.addEventListener(
                    'click',
                    function () {

                        selectTipeKelas(item);

                    }
                );


                tipeOptions.appendChild(
                    button
                );

            });

    }


    /* =========================================================
       SELECT TIPE KELAS
    ========================================================= */

    function selectTipeKelas(item) {

        selectedTipe =
            item.value;


        tipeKelasSelect.innerHTML = `
            <option value="${escapeHtml(item.value)}">
                ${escapeHtml(item.text)}
            </option>
        `;


        tipeKelasSelect.value =
            item.value;


        tipeKelasSelect.disabled =
            false;


        tipeText.textContent =
            item.text;


        tipeSearch.value =
            '';


        closeAllDropdowns();


        /* =========================================
           AKTIFKAN SISWA
        ========================================= */

        selectedStudent =
            '';


        studentSelect.innerHTML = `
            <option value="">
                Pilih Nama Siswa
            </option>
        `;


        studentSelect.value =
            '';


        studentSelect.disabled =
            false;


        studentButton.disabled =
            false;


        studentText.textContent =
            'Pilih Nama Siswa';


        studentSearch.value =
            '';


        renderStudents('');

    }


    /* =========================================================
       RENDER SISWA
       MAX 4 DATA
    ========================================================= */

    function renderStudents(searchValue) {

        if (!studentOptionsContainer) {
            return;
        }


        studentOptionsContainer.innerHTML =
            '';


        studentEmpty.classList.add(
            'hidden'
        );


        if (
            !selectedKelas ||
            !selectedTipe
        ) {

            studentEmpty.classList.remove(
                'hidden'
            );

            return;

        }


        const keyword =
            normalize(searchValue);


        const filteredStudents =
            studentOptions.filter(
                function (student) {

                    const sameKelas =
                        student.kelas ===
                        selectedKelas;


                    const sameTipe =
                        student.tipe ===
                        selectedTipe;


                    const sameSearch =
                        normalize(
                            student.text
                        ).includes(
                            keyword
                        );


                    return (
                        sameKelas &&
                        sameTipe &&
                        sameSearch
                    );

                }
            );


        if (!filteredStudents.length) {

            studentEmpty.classList.remove(
                'hidden'
            );

            return;

        }


        filteredStudents
            .slice(0, MAX_VISIBLE)
            .forEach(function (student) {

                const button =
                    document.createElement(
                        'button'
                    );

                button.type =
                    'button';

                button.className =
                    'manual-dropdown-option';


                if (
                    student.value ===
                    selectedStudent
                ) {

                    button.classList.add(
                        'active'
                    );

                }


                button.textContent =
                    student.text;


                button.addEventListener(
                    'click',
                    function () {

                        selectStudent(
                            student
                        );

                    }
                );


                studentOptionsContainer
                    .appendChild(button);

            });

    }


    /* =========================================================
       SELECT SISWA
    ========================================================= */

    function selectStudent(student) {

        selectedStudent =
            student.value;


        studentSelect.value =
            student.value;


        studentText.textContent =
            student.text;


        studentSearch.value =
            '';


        closeAllDropdowns();

    }


    /* =========================================================
       KELAS DROPDOWN
    ========================================================= */

    if (kelasButton) {

        kelasButton.addEventListener(
            'click',
            function (e) {

                e.preventDefault();

                e.stopPropagation();


                openDropdown(
                    kelasDropdown
                );


                renderKelas(
                    kelasSearch.value
                );


                setTimeout(
                    function () {

                        kelasSearch.focus();

                    },
                    50
                );

            }
        );

    }


    /* =========================================================
       SEARCH KELAS
    ========================================================= */

    if (kelasSearch) {

        kelasSearch.addEventListener(
            'input',
            function () {

                renderKelas(
                    this.value
                );

            }
        );

    }


    /* =========================================================
       TIPE KELAS DROPDOWN
    ========================================================= */

    if (tipeButton) {

        tipeButton.addEventListener(
            'click',
            function (e) {

                e.preventDefault();

                e.stopPropagation();


                if (this.disabled) {

                    return;

                }


                openDropdown(
                    tipeDropdown
                );


                renderTipeKelas(
                    tipeSearch.value
                );


                setTimeout(
                    function () {

                        tipeSearch.focus();

                    },
                    50
                );

            }
        );

    }


    /* =========================================================
       SEARCH TIPE KELAS
    ========================================================= */

    if (tipeSearch) {

        tipeSearch.addEventListener(
            'input',
            function () {

                renderTipeKelas(
                    this.value
                );

            }
        );

    }


    /* =========================================================
       SISWA DROPDOWN
    ========================================================= */

    if (studentButton) {

        studentButton.addEventListener(
            'click',
            function (e) {

                e.preventDefault();

                e.stopPropagation();


                if (this.disabled) {

                    return;

                }


                openDropdown(
                    studentDropdown
                );


                renderStudents(
                    studentSearch.value
                );


                setTimeout(
                    function () {

                        studentSearch.focus();

                    },
                    50
                );

            }
        );

    }


    /* =========================================================
       SEARCH SISWA
    ========================================================= */

    if (studentSearch) {

        studentSearch.addEventListener(
            'input',
            function () {

                renderStudents(
                    this.value
                );

            }
        );

    }


    /* =========================================================
       STOP PROPAGATION DROPDOWN
    ========================================================= */

    [
        kelasDropdown,
        tipeDropdown,
        studentDropdown
    ].forEach(function (dropdown) {

        if (!dropdown) {
            return;
        }


        dropdown.addEventListener(
            'click',
            function (e) {

                e.stopPropagation();

            }
        );

    });


    /* =========================================================
       CLICK DI LUAR DROPDOWN
    ========================================================= */

    document.addEventListener(
        'click',
        function () {

            closeAllDropdowns();

        }
    );


    /* =========================================================
       RESET FORM
    ========================================================= */

    function resetForm() {

        form.reset();

        fileName.textContent =
            '';


        selectedKelas =
            '';

        selectedTipe =
            '';

        selectedStudent =
            '';


        /* =========================================
           RESET SELECT VALUE
        ========================================= */

        kelasSelect.value =
            '';


        tipeKelasSelect.innerHTML = `
            <option value="">
                Pilih Tipe Kelas
            </option>
        `;

        tipeKelasSelect.value =
            '';

        tipeKelasSelect.disabled =
            true;


        studentSelect.innerHTML = `
            <option value="">
                Pilih Kelas dan Tipe Kelas terlebih dahulu
            </option>
        `;

        studentSelect.value =
            '';

        studentSelect.disabled =
            true;


        /* =========================================
           RESET TEXT DROPDOWN
        ========================================= */

        if (kelasText) {

            kelasText.textContent =
                'Pilih Kelas';

        }


        if (tipeText) {

            tipeText.textContent =
                'Pilih Tipe Kelas';

        }


        if (studentText) {

            studentText.textContent =
                'Pilih Kelas dan Tipe Kelas terlebih dahulu';

        }


        /* =========================================
           RESET SEARCH
        ========================================= */

        if (kelasSearch) {

            kelasSearch.value =
                '';

        }


        if (tipeSearch) {

            tipeSearch.value =
                '';

        }


        if (studentSearch) {

            studentSearch.value =
                '';

        }


        /* =========================================
           DISABLE BUTTON
        ========================================= */

        if (tipeButton) {

            tipeButton.disabled =
                true;

        }


        if (studentButton) {

            studentButton.disabled =
                true;

        }


        /* =========================================
           TUTUP DROPDOWN
        ========================================= */

        closeAllDropdowns();


        /* =========================================
           REQUIRED
        ========================================= */

        document
            .querySelectorAll(
                '.member-required'
            )
            .forEach(function (item) {

                item.required =
                    true;

            });

    }


    /* =========================================================
       TUTUP MODAL
    ========================================================= */

    function closeModal() {

        resetForm();


        if (
            typeof modal.close ===
            'function'
        ) {

            modal.close();

        }

    }


    /* =========================================================
       FILE EXCEL
    ========================================================= */

    excelInput.addEventListener(
        'change',
        function () {

            fileName.textContent =
                this.files.length
                    ? this.files[0].name
                    : '';


            document
                .querySelectorAll(
                    '.member-required'
                )
                .forEach(function (item) {

                    item.required =
                        !excelInput.files.length;

                });

        }
    );


    /* =========================================================
       DRAG OVER
    ========================================================= */

    uploadBox.addEventListener(
        'dragover',
        function (e) {

            e.preventDefault();


            uploadBox.classList.add(
                'border-blue-500',
                'bg-blue-50'
            );

        }
    );


    /* =========================================================
       DRAG LEAVE
    ========================================================= */

    uploadBox.addEventListener(
        'dragleave',
        function () {

            uploadBox.classList.remove(
                'border-blue-500',
                'bg-blue-50'
            );

        }
    );


    /* =========================================================
       DROP
    ========================================================= */

    uploadBox.addEventListener(
        'drop',
        function (e) {

            e.preventDefault();


            uploadBox.classList.remove(
                'border-blue-500',
                'bg-blue-50'
            );


            excelInput.files =
                e.dataTransfer.files;


            excelInput.dispatchEvent(
                new Event('change')
            );

        }
    );


    /* =========================================================
       TAMBAH MANUAL
    ========================================================= */

    $(manualButton)

        .off('click.addMember')

        .on(
            'click.addMember',
            function () {

                const studentId =
                    studentSelect.value;


                if (!studentId) {

                    Swal.fire({

                        icon:
                            'warning',

                        title:
                            'Peringatan',

                        text:
                            'Silakan pilih nama siswa terlebih dahulu.'

                    });

                    return;

                }


                const formData =
                    new FormData(form);


                $.ajax({

                    url: "{{ route(
                        'lms.student-vice-principal.extracurricular-management.member.store',
                        [
                            'role'=>request()->route('role'),
                            'schoolName'=>request()->route('schoolName'),
                            'schoolId'=>request()->route('schoolId'),
                            'extracurricularId'=>request()->route('extracurricularId')
                        ]
                    ) }}",

                    type:
                        'POST',

                    data:
                        formData,

                    processData:
                        false,

                    contentType:
                        false,


                    /* =====================================
                       BEFORE SEND
                    ===================================== */

                    beforeSend:
                        function () {

                            manualButton.disabled =
                                true;


                            manualButton.innerHTML = `
                                <span class="loading loading-spinner loading-sm"></span>
                                Menambahkan...
                            `;

                        },


                    /* =====================================
                       SUCCESS
                    ===================================== */

                    success:
                        function (res) {

                            closeModal();


                            Swal.fire({

                                icon:
                                    'success',

                                title:
                                    'Berhasil',

                                text:
                                    res.message ||
                                    'Peserta berhasil ditambahkan.',

                                timer:
                                    1200,

                                showConfirmButton:
                                    false

                            }).then(
                                function () {

                                    location.reload();

                                }
                            );

                        },


                    /* =====================================
                       ERROR
                    ===================================== */

                    error:
                        function (xhr) {

                            manualButton.disabled =
                                false;


                            manualButton.innerHTML = `
                                <i class="fa-solid fa-user-plus mr-2"></i>
                                Tambah Peserta
                            `;


                            Swal.fire({

                                icon:
                                    'warning',

                                title:
                                    'Peringatan',

                                text:
                                    xhr.responseJSON?.message ||
                                    'Gagal menambahkan peserta.'

                            });

                        }

                });

            }
        );


    /* =========================================================
       UPLOAD EXCEL
    ========================================================= */

    $(form)

        .off('submit.uploadMember')

        .on(
            'submit.uploadMember',
            function (e) {

                e.preventDefault();


                if (!excelInput.files.length) {

                    Swal.fire({

                        icon:
                            'warning',

                        title:
                            'Peringatan',

                        text:
                            'Silakan pilih file Excel terlebih dahulu.'

                    });

                    return;

                }


                const formData =
                    new FormData(form);


                $.ajax({

                    url: "{{ route(
                        'lms.student-vice-principal.extracurricular-management.upload-member',
                        [
                            'role'=>request()->route('role'),
                            'schoolName'=>request()->route('schoolName'),
                            'schoolId'=>request()->route('schoolId'),
                            'extracurricularId'=>request()->route('extracurricularId')
                        ]
                    ) }}",

                    type:
                        'POST',

                    data:
                        formData,

                    processData:
                        false,

                    contentType:
                        false,


                    /* =====================================
                       BEFORE SEND
                    ===================================== */

                    beforeSend:
                        function () {

                            Swal.fire({

                                title:
                                    'Mengupload...',

                                text:
                                    'Mohon tunggu.',

                                allowOutsideClick:
                                    false,

                                allowEscapeKey:
                                    false,

                                didOpen:
                                    function () {

                                        Swal.showLoading();

                                    }

                            });

                        },


                    /* =====================================
                       SUCCESS
                    ===================================== */

                    success:
                        function (res) {

                            Swal.close();


                            closeModal();


                            Swal.fire({

                                icon:
                                    'success',

                                title:
                                    'Berhasil',

                                text:
                                    res.message ||
                                    'Peserta berhasil diupload.',

                                timer:
                                    1200,

                                showConfirmButton:
                                    false

                            }).then(
                                function () {

                                    location.reload();

                                }
                            );

                        },


                    /* =====================================
                       ERROR
                    ===================================== */

                    error:
                        function (xhr) {

                            Swal.close();


                            Swal.fire({

                                icon:
                                    'error',

                                title:
                                    'Gagal',

                                text:
                                    xhr.responseJSON?.message ||
                                    'Upload gagal.'

                            });

                        }

                });

            }
        );


    /* =========================================================
       RESET SAAT MODAL DITUTUP
    ========================================================= */

    modal.addEventListener(
        'close',
        function () {

            resetForm();

        }
    );


    /* =========================================================
       INITIAL STATE
    ========================================================= */

    resetForm();


})();

</script>