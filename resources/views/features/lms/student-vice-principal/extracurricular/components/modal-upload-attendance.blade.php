<dialog id="modal_upload_attendance" class="modal">

    <div class="modal-box max-w-2xl rounded-3xl">

        <h3 class="text-2xl font-bold mb-2">
            Bulk Upload Absensi
        </h3>

        <p class="text-slate-500">
    Download template terlebih dahulu, isi status kehadiran siswa,
    kemudian upload kembali file Excel.
</p>

<div class="alert alert-info mt-5 border border-blue-200 bg-blue-50 text-slate-700">

    <div>

        <h4 class="font-semibold mb-2">
            Informasi Pengisian Template
        </h4>

        <ul class="list-disc ml-5 space-y-2 text-sm">

            <li>
                File yang diunduh merupakan <b>template resmi sistem</b> dan sudah berisi
                seluruh data peserta ekstrakurikuler yang terdaftar saat template dibuat.
            </li>

            <li>
                Kolom <b>"DD/MM/YY"</b> hanya merupakan contoh format tanggal.
                Ganti dengan <b>tanggal setiap pertemuan</b> sesuai jadwal ekstrakurikuler.
            </li>

            <li>
                Untuk menandai siswa <b>hadir</b>, isi kolom absensi dengan huruf
                <span class="font-bold text-green-700">H</span>
                atau
                <span class="font-bold text-green-700">h</span>.
            </li>

            <li>
                Selain huruf <b>H</b> atau <b>h</b> (misalnya A, Absen, ✓, 1, Y, atau teks lainnya),
                <b>tidak akan dihitung sebagai hadir</b>.
            </li>

            <li>
                Jika kolom absensi <b>dibiarkan kosong</b>, maka sistem akan menganggap
                siswa tersebut <b>tidak hadir</b>.
            </li>

            <li>
                Apabila setelah template diunduh terdapat <b>penambahan peserta</b>,
                disarankan untuk <b>mengunduh template terbaru</b> agar seluruh data peserta
                ikut tercantum pada file Excel.
            </li>

            <li>
                Jangan mengubah urutan kolom, menghapus kolom, atau mengubah data identitas
                siswa (Nama, NISN, Kelas, dan Tipe Kelas) agar proses import berjalan dengan benar.
            </li>

        </ul>

    </div>

</div>
<br>

        <div class="space-y-5">

            {{-- Download Template --}}
            <div class="rounded-2xl border border-slate-200 p-5">

                <div class="flex items-center justify-between">

                    <div>

                        <h4 class="font-semibold">
                            Template Excel
                        </h4>

                        <p class="text-sm text-slate-500 mt-1">
                            Berisi seluruh peserta dan seluruh pertemuan.
                        </p>

                    </div>

                    <a href="{{ route(
                    'lms.student-vice-principal.extracurricular-management.attendance.template',
                    [
                        'role' => $role,
                        'schoolName' => $schoolName,
                        'schoolId' => $schoolId,
                        'extracurricularId' => $extracurricular->id
                    ]
                ) }}">
                    Download
                </a>

                </div>

            </div>

            {{-- Upload --}}
            <form
                id="form_import_attendance"
                enctype="multipart/form-data"
                method="POST">

                @csrf

               <div>

    <label class="font-semibold mb-2 block">
        Upload File Excel
    </label>

    <label
        id="dropZoneAttendance"
        class="flex flex-col items-center justify-center w-full h-52 border-2 border-dashed border-slate-300 rounded-2xl cursor-pointer transition hover:border-blue-500 hover:bg-blue-50">

        <input
            id="attendanceFile"
            type="file"
            name="file"
            accept=".xlsx,.xls"
            class="hidden">

        <i class="fa-solid fa-file-excel text-6xl text-green-600 mb-4"></i>

        <h3 class="font-semibold text-lg">
            Drag & Drop File Excel
        </h3>

        <p class="text-sm text-slate-500 mt-1">
            atau klik untuk memilih file
        </p>

        <p
            id="attendanceFileName"
            class="mt-4 text-sm font-semibold text-blue-600 hidden">
        </p>

    </label>

</div>

                <div class="modal-action">

                    <button
                        type="button"
                        class="btn"
                        onclick="modal_upload_attendance.close()">

                        Batal

                    </button>

                    <button
                        type="submit"
                        id="btnImportAttendance"
                        class="btn bg-blue-600 hover:bg-blue-700 text-white border-0">

                        <i class="fa-solid fa-file-import mr-2"></i>
                        Upload

                    </button>

                </div>

            </form>

        </div>

    </div>

</dialog>

<script>
/*
|--------------------------------------------------------------------------
| URL Upload
|--------------------------------------------------------------------------
*/
const ATTENDANCE_IMPORT_URL =
"{{ route(
'lms.student-vice-principal.extracurricular-management.attendance.import',
[
    'role'=>$role,
    'schoolName'=>$schoolName,
    'schoolId'=>$schoolId,
    'extracurricularId'=>$extracurricular->id
]) }}";

/*
|--------------------------------------------------------------------------
| Element
|--------------------------------------------------------------------------
*/
const attendanceForm = document.getElementById('form_import_attendance');
const attendanceDropZone = document.getElementById('dropZoneAttendance');
const attendanceFileInput = document.getElementById('attendanceFile');
const attendanceFileName = document.getElementById('attendanceFileName');
const attendanceBtnImport = document.getElementById('btnImportAttendance');

/*
|--------------------------------------------------------------------------
| Tampilkan nama file
|--------------------------------------------------------------------------
*/
function showAttendanceFile(file){

    attendanceFileName.classList.remove('hidden');

    attendanceFileName.innerHTML = `
        <i class="fa-solid fa-file-excel text-green-600 mr-2"></i>
        ${file.name}
    `;

}

/*
|--------------------------------------------------------------------------
| Klik area upload
|--------------------------------------------------------------------------
*/
attendanceDropZone.addEventListener('click', function () {

    attendanceFileInput.click();

});

/*
|--------------------------------------------------------------------------
| Pilih file
|--------------------------------------------------------------------------
*/
attendanceFileInput.addEventListener('change', function () {

    if(this.files.length){

        showAttendanceFile(this.files[0]);

    }

});

/*
|--------------------------------------------------------------------------
| Drag Over
|--------------------------------------------------------------------------
*/
attendanceDropZone.addEventListener('dragover', function(e){

    e.preventDefault();

    attendanceDropZone.classList.add(
        'border-blue-600',
        'bg-blue-50',
        'scale-[1.02]'
    );

});

/*
|--------------------------------------------------------------------------
| Drag Leave
|--------------------------------------------------------------------------
*/
attendanceDropZone.addEventListener('dragleave', function(){

    attendanceDropZone.classList.remove(
        'border-blue-600',
        'bg-blue-50',
        'scale-[1.02]'
    );

});

/*
|--------------------------------------------------------------------------
| Drop
|--------------------------------------------------------------------------
*/
attendanceDropZone.addEventListener('drop', function(e){

    e.preventDefault();

    attendanceDropZone.classList.remove(
        'border-blue-600',
        'bg-blue-50',
        'scale-[1.02]'
    );

    const files = e.dataTransfer.files;

    if(files.length){

        attendanceFileInput.files = files;

        showAttendanceFile(files[0]);

    }

});

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/
attendanceForm.addEventListener('submit', function(e){

    e.preventDefault();

    if(attendanceFileInput.files.length === 0){

        Swal.fire({
            icon:'warning',
            title:'Pilih File',
            text:'Silakan pilih file Excel terlebih dahulu.'
        });

        return;

    }

    const formData = new FormData(attendanceForm);
     console.log(ATTENDANCE_IMPORT_URL);
     console.log(attendanceFileInput.files[0]);
    $.ajax({

        url: ATTENDANCE_IMPORT_URL,

        type:'POST',

        data:formData,

        processData:false,

        contentType:false,

        cache:false,

        beforeSend:function(){

            attendanceBtnImport.disabled = true;

            attendanceBtnImport.innerHTML = `
                <span class="loading loading-spinner loading-sm"></span>
                Mengupload...
            `;

        },

        success:function(res){

            Swal.fire({

                icon:'success',

                title:'Berhasil',

                text:res.message,

                timer:1500,

                showConfirmButton:false

            }).then(function(){

                location.reload();

            });

        },

       error:function(xhr){

    console.log(xhr);

    console.log(xhr.responseText);

    Swal.fire({

        icon:'error',
        title:'Gagal',

        text:
            xhr.responseJSON?.message ??
            xhr.responseText ??
            'Upload gagal.'

    });

},

        complete:function(){

            attendanceBtnImport.disabled = false;

            attendanceBtnImport.innerHTML = `
                <i class="fa-solid fa-file-import mr-2"></i>
                Upload
            `;

        }

    });

});
</script>