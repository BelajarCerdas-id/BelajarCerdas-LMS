<!DOCTYPE html>
<style>

    .overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 999px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 999px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

</style>

<dialog id="modal_add_extracurricular" class="modal">

    <div class="modal-box max-w-3xl h-[90vh] rounded-3xl p-0 overflow-hidden flex flex-col">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-700 to-cyan-600 text-white p-6 shrink-0">

            <div class="flex justify-between items-center">

                <div>

                    <h2 class="text-2xl font-bold">
                        Tambah Ekstrakurikuler
                    </h2>

                    <p class="text-sm text-blue-100 mt-1">
                        Tambah satu ekstrakurikuler atau import banyak data menggunakan Excel.
                    </p>

                </div>

                <form method="dialog">

                    <button class="btn btn-circle btn-sm btn-ghost text-white">

                        ✕

                    </button>

                </form>

            </div>

        </div>

        {{-- FORM --}}
<form
    id="form_add_extracurricular"
    method="POST"
    enctype="multipart/form-data"
    class="flex flex-col flex-1 min-h-0"
    action="{{ route(
        'lms.student-vice-principal.extracurricular-management.store',
        [
            'role' => request()->route('role'),
            'schoolName' => request()->route('schoolName'),
            'schoolId' => request()->route('schoolId'),
        ]
    ) }}"
    data-import-url="{{ route(
        'lms.student-vice-principal.extracurricular-management.import',
        [
            'role' => request()->route('role'),
            'schoolName' => request()->route('schoolName'),
            'schoolId' => request()->route('schoolId'),
        ]
    ) }}"
>
    @csrf

    <div class="flex-1 min-h-0 overflow-y-auto p-6 space-y-6">

                {{-- Nama --}}
                <div>

                    <label class="font-semibold">
                        Nama Ekstrakurikuler
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="input input-bordered w-full mt-2"
                        placeholder="Contoh : Robotik">

                </div>

                {{-- Deskripsi --}}
                <div>

                    <label class="font-semibold">

                        Deskripsi

                    </label>

                    <textarea
                        name="description"
                        rows="3"
                        class="textarea textarea-bordered w-full mt-2"
                        placeholder="Deskripsi singkat..."></textarea>

                </div>

                {{-- Coach --}}
                <div>

                    <label class="font-semibold">

                        Pembina

                    </label>

                    <input
                        type="text"
                        name="coach"
                        class="input input-bordered w-full mt-2"
                        placeholder="Nama Pembina">

                </div>

                {{-- TYPE --}}
                <div>

                    <label class="font-semibold block mb-3">

                        Jenis Ekstrakurikuler

                    </label>

                    <div class="grid grid-cols-2 gap-4">

                        <label
                            class="border rounded-2xl p-4 cursor-pointer hover:border-red-500 transition">

                            <input
                                type="radio"
                                name="type"
                                value="wajib"
                                class="radio radio-error"
                                required>

                            <span class="ml-2 font-semibold">

                                Wajib

                            </span>

                            <p class="text-xs text-slate-500 mt-2">

                                Contoh: Pramuka

                            </p>

                        </label>

                        <label
                            class="border rounded-2xl p-4 cursor-pointer hover:border-blue-500 transition">

                            <input
                                type="radio"
                                name="type"
                                value="pilihan"
                                class="radio radio-info"
                                required>

                            <span class="ml-2 font-semibold">

                                Pilihan

                            </span>

                            <p class="text-xs text-slate-500 mt-2">

                                Basket, Robotik, PMR dll

                            </p>

                        </label>

                    </div>

                </div>

                <hr>

                {{-- BULK UPLOAD --}}
                <div>

                    <div class="flex justify-between items-center mb-4">

                        <div>

                            <h3 class="font-bold">

                                Bulk Upload Excel

                            </h3>

                            <p class="text-sm text-slate-500">

                                Upload beberapa ekstrakurikuler sekaligus.

                            </p>

                        </div>

                        <a
href="{{ route(
'lms.student-vice-principal.extracurricular-management.download-template',
[
'role'=>request()->route('role'),
'schoolName'=>request()->route('schoolName'),
'schoolId'=>request()->route('schoolId')
]
) }}"
class="btn btn-success btn-sm">

    <i class="fa-solid fa-download"></i>

    Download Template

</a>

                    </div>

                   <label
    for="excel_file"
    class="border-2 border-dashed border-slate-300 rounded-3xl p-10 flex flex-col items-center justify-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">

    <i class="fa-solid fa-file-excel text-6xl text-green-600"></i>

    <p class="font-semibold mt-4">
        Klik atau Drag Excel
    </p>

    <p class="text-sm text-slate-500">
        *.xlsx / *.xls
    </p>

    <p
        id="excel_file_name"
        class="mt-2 text-sm text-blue-600 font-semibold">
    </p>

</label>

<input
    type="file"
    id="excel_file"
    name="excel_file"
    accept=".xlsx,.xls"
    class="hidden">

                </div>

            </div>

            {{-- FOOTER --}}
           <div class="bg-white border-t px-6 py-5 flex justify-end gap-3 shrink-0">

    <button
        type="button"
        onclick="modal_add_extracurricular.close()"
        class="btn">

        Batal

    </button>

    <button
        type="submit"
        class="btn btn-primary">

        <i class="fa-solid fa-plus"></i>

        Simpan

    </button>

</div>

        </form>

    </div>

</dialog>


<script>

    document
.getElementById('excel_file')
.addEventListener('change', function () {

    const text = document.getElementById('excel_file_name');

    if (this.files.length) {

        text.innerHTML = this.files[0].name;

    } else {

        text.innerHTML = '';

    }

});

const excelInput = document.getElementById('excel_file');
const excelName = document.getElementById('excel_file_name');

const nameInput = document.querySelector('input[name="name"]');
const descriptionInput = document.querySelector('textarea[name="description"]');
const coachInput = document.querySelector('input[name="coach"]');
const typeInputs = document.querySelectorAll('input[name="type"]');

function toggleFormByExcel() {

    const hasFile = excelInput.files.length > 0;

    excelName.textContent = hasFile
        ? excelInput.files[0].name
        : '';

    // Name
    nameInput.required = !hasFile;
    nameInput.disabled = hasFile;

    // Description
    descriptionInput.required = false;
    descriptionInput.disabled = hasFile;

    // Coach
    coachInput.required = false;
    coachInput.disabled = hasFile;

    // Type
    typeInputs.forEach(radio => {

        radio.required = !hasFile;
        radio.disabled = hasFile;

        if (hasFile) {
            radio.checked = false;
        }

    });

}

excelInput.addEventListener('change', toggleFormByExcel);

document
    .getElementById('modal_add_extracurricular')
    .addEventListener('close', function () {

        document
            .getElementById('form_add_extracurricular')
            .reset();

        toggleFormByExcel();

    });

toggleFormByExcel();
</script>

