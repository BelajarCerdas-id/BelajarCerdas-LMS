function formSubmitReflectionManagement(search_year = null) {
    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role || !schoolName || !schoolId) return;

    // LOADING TARGET JENJANG
    document.getElementById('target-jenjang-kelas').innerHTML = `
        <div class="col-span-full grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">

            ${Array.from({ length: 6 }).map(() => `
                <div class="rounded-3xl border border-gray-200 p-5 animate-pulse bg-white">

                    <div class="flex items-start justify-between">

                        <div class="flex items-start gap-4">

                            <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>

                            <div>
                                <div class="h-4 w-24 bg-slate-200 rounded mb-2"></div>
                                <div class="h-3 w-20 bg-slate-100 rounded"></div>
                            </div>
                        </div>

                        <div class="w-5 h-5 rounded-full bg-slate-200"></div>
                    </div>

                    <div class="mt-5 flex items-center justify-between">
                        <div class="h-3 w-20 bg-slate-100 rounded"></div>
                        <div class="h-5 w-10 bg-slate-200 rounded"></div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    // LOADING PREVIEW
    const previewContainer = document.getElementById('preview-target-reflection-container');

    previewContainer.classList.remove('hidden');

    previewContainer.innerHTML = `
        <div class="rounded-4xl border border-blue-100 bg-white p-6 animate-pulse">

            <div class="flex items-start justify-between mb-6">

                <div class="flex items-start gap-4">

                    <div class="w-14 h-14 rounded-2xl bg-slate-200"></div>

                    <div>
                        <div class="h-5 w-48 bg-slate-200 rounded mb-2"></div>
                        <div class="h-3 w-32 bg-slate-100 rounded"></div>
                    </div>
                </div>

                <div class="h-8 w-28 rounded-2xl bg-slate-200"></div>
            </div>

            <div class="space-y-4">

                ${Array.from({ length: 3 }).map(() => `
                    <div class="rounded-2xl border border-slate-100 p-4">

                        <div class="flex justify-between">

                            <div>
                                <div class="h-4 w-28 bg-slate-200 rounded mb-2"></div>
                                <div class="h-3 w-20 bg-slate-100 rounded"></div>
                            </div>

                            <div class="h-5 w-10 bg-slate-200 rounded"></div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/form-submit`,
        method: 'GET',
        data: { search_year },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function (response) {

            // DROPDOWN TAHUN AJARAN
            const dropdownTahunAjaran = document.getElementById('dropdown-tahun-ajaran');

            dropdownTahunAjaran.innerHTML = `
                ${response.tahunAjaran.map(item => `
                    <option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>
                        Tahun Ajaran ${item}
                    </option>
                `).join('')}
            `;

            const targetJenjangKelas = document.getElementById('target-jenjang-kelas');

            // EMPTY STATE
            if (response.kelas.length === 0) {

                targetJenjangKelas.innerHTML = `
                    <div class="col-span-full">

                        <div class="rounded-4xl border border-dashed border-gray-300 bg-slate-50 px-6 py-14 text-center">

                            <div class="w-18 h-18 mx-auto rounded-3xl bg-white border border-gray-200 
                                flex items-center justify-center text-3xl text-slate-400 shadow-sm mb-5">

                                <i class="fas fa-school"></i>
                            </div>

                            <h4 class="text-lg font-black text-slate-700">
                                Belum Ada Jenjang Kelas
                            </h4>

                            <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto leading-relaxed">
                                Belum ada data jenjang kelas aktif pada tahun ajaran ini.
                                Silakan tambahkan rombel atau pilih tahun ajaran lain.
                            </p>
                        </div>
                    </div>
                `;

                previewContainer.innerHTML = '';

                return;
            }

            // RENDER CLASS LIST
            targetJenjangKelas.innerHTML = `
                ${response.kelas.map(item => `
                    <label class="jenjang-kelas-card cursor-pointer border border-gray-300 rounded-xl p-4 flex flex-col transition hover:border-blue-400">

                        <div class="flex items-start justify-between gap-4">

                            <div class="flex items-start gap-4">

                                <div id="icon-graduate-${item.id}"
                                    class="w-13 h-13 rounded-2xl bg-white text-slate-500 flex items-center justify-center shadow-sm text-lg transition-all">

                                    <i class="fas fa-graduation-cap"></i>
                                </div>

                                <div>
                                    <h4 class="font-black text-slate-800 text-lg">
                                        ${item.kelas}
                                    </h4>

                                    <p class="text-xs text-slate-500 mt-1">
                                        ${item.total_rombel} Rombel Aktif
                                    </p>
                                </div>

                                <input type="checkbox" name="target_class_id[]" value="${item.id ?? ''}" data-kelas="${item.kelas}" data-rombel="${item.total_rombel}"
                                    data-siswa="${item.total_siswa}" class="rombel-checkbox hidden">
                            </div>

                            <div id="icon-check-${item.id}"
                                class="w-6 h-6 rounded-full border-2 border-gray-300 text-transparent flex items-center justify-center text-[10px] shrink-0 transition-all">

                                <i class="fas fa-check"></i>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between">

                            <div class="text-xs text-slate-500 font-semibold">
                                Total Siswa
                            </div>

                            <div class="text-lg font-black text-[#2563EB]">
                                ${item.total_siswa}
                            </div>
                        </div>
                    </label>
                `).join('')}
            `;

            // RENDER DEFAULT STATE
            updateTargetJenjangKelas();
            renderPreviewTargetReflection();
        }
    });
}

$(document).ready(function () {
    formSubmitReflectionManagement();
});

$(document).on('change', '#dropdown-tahun-ajaran', function () {
    formSubmitReflectionManagement($(this).val());
});

document.addEventListener('change', function (e) {

    if (e.target.classList.contains('rombel-checkbox')) {

        updateTargetJenjangKelas();
        renderPreviewTargetReflection();
    }
});

// SELECT & UNSELECT ALL
document.addEventListener('click', function (e) {

    if (e.target.closest('#toggle-select-rombel')) {

        const checkboxes = document.querySelectorAll('.rombel-checkbox');

        const checked = document.querySelectorAll('.rombel-checkbox:checked');

        const allSelected = checkboxes.length === checked.length;

        checkboxes.forEach(item => {
            item.checked = !allSelected;
        });

        updateTargetJenjangKelas();
        renderPreviewTargetReflection();
    }
});

function updateTargetJenjangKelas() {

    const checkboxes = document.querySelectorAll('.rombel-checkbox');

    const checked = document.querySelectorAll('.rombel-checkbox:checked');

    const total = checked.length;

    const allSelected = checkboxes.length > 0 && checkboxes.length === checked.length;

    // UPDATE BUTTON
    document.querySelectorAll('#toggle-select-rombel').forEach(button => {

        button.innerHTML = allSelected
            ? `<i class="fas fa-times-circle mr-1"></i> Unselect All`
            : `<i class="fas fa-check-double mr-1"></i> Select All`;
    });

    // UPDATE COUNTER
    document.querySelectorAll('#total-jenjang-kelas-selected').forEach(item => {

        item.innerHTML = `
            <i class="fas fa-users"></i>
            <span>${total} Jenjang Dipilih</span>
        `;
    });

    $('#error-target_class_id').text(''); // remove text error

    // UPDATE CARD UI
    checkboxes.forEach(item => {

        const label = item.closest('.jenjang-kelas-card');

        const graduateIcon = document.getElementById(`icon-graduate-${item.value}`);

        const checkIcon = document.getElementById(`icon-check-${item.value}`);

        if (item.checked) {

            // CARD
            label.classList.add(
                'border-blue-500',
                'bg-blue-50',
                'shadow-lg',
                'shadow-blue-100'
            );

            label.classList.remove('border-gray-300');

            // ICON
            graduateIcon.classList.add('text-[#2563EB]');

            graduateIcon.classList.remove('bg-white', 'text-slate-500');

            // CHECK
            checkIcon.classList.add(
                'bg-[#2563EB]',
                'border-[#2563EB]',
                'text-white'
            );

            checkIcon.classList.remove(
                'border-gray-300',
                'text-transparent'
            );

        } else {

            // CARD
            label.classList.remove(
                'border-blue-500',
                'bg-blue-50',
                'shadow-lg',
                'shadow-blue-100'
            );

            label.classList.add('border-gray-300');

            // ICON
            graduateIcon.classList.remove(
                'bg-[#2563EB]',
                'text-white'
            );

            graduateIcon.classList.add(
                'bg-white',
                'text-slate-500'
            );

            // CHECK
            checkIcon.classList.remove(
                'bg-[#2563EB]',
                'border-[#2563EB]'
            );

            checkIcon.classList.add(
                'border-gray-300',
                'text-transparent'
            );
        }
    });
}

function renderPreviewTargetReflection() {

    const checked = document.querySelectorAll('.rombel-checkbox:checked');

    const container = document.getElementById('preview-target-reflection-container');

    container.classList.remove('hidden');

    const tahunAjaran = document.getElementById('dropdown-tahun-ajaran').value;

    let totalSiswa = 0;

    let content = `
        <div class="rounded-4xl border border-blue-100 bg-linear-to-br from-blue-50 to-white p-6">

            <div class="flex items-start justify-between gap-5 flex-wrap mb-6">

                <div class="flex items-start gap-4">

                    <div
                        class="w-14 h-14 rounded-2xl bg-white text-[#2563EB] flex items-center justify-center shadow-sm text-xl">

                        <i class="fas fa-bullseye"></i>
                    </div>

                    <div>
                        <h4 class="font-black text-slate-800 text-lg">
                            Preview Target Refleksi
                        </h4>

                        <p class="text-sm text-slate-500 mt-1">
                            Refleksi akan dikirim ke jenjang berikut.
                        </p>
                    </div>
                </div>

                <div class="px-4 py-2 rounded-2xl bg-[#2563EB] text-white text-xs font-black">
                    ${tahunAjaran}
                </div>
            </div>
    `;

    // EMPTY SELECT
    if (checked.length === 0) {

        content += `
            <div class="rounded-2xl border border-dashed border-blue-200 bg-white/80 px-6 py-10 text-center">

                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-[#2563EB] 
                    flex items-center justify-center text-2xl mb-4">

                    <i class="fas fa-users-slash"></i>
                </div>

                <h5 class="text-base font-black text-slate-700">
                    Belum Ada Jenjang Dipilih
                </h5>

                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Pilih minimal satu jenjang kelas untuk melihat preview target refleksi siswa.
                </p>
            </div>
        `;

        content += `</div>`;

        container.innerHTML = content;

        return;
    }

    // TARGET LIST
    const targetList = Array.from(checked).map(item => {

        const kelas = item.dataset.kelas;

        const rombel = item.dataset.rombel;

        const siswa = parseInt(item.dataset.siswa);

        totalSiswa += siswa;

        return `
            <div class="flex items-center justify-between rounded-2xl bg-white border border-blue-100 px-5 py-4">

                <div class="flex items-center gap-3">

                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2563EB] 
                        flex items-center justify-center">

                        <i class="fas fa-layer-group"></i>
                    </div>

                    <div>
                        <h5 class="font-black text-slate-800">
                            ${kelas}
                        </h5>

                        <p class="text-xs text-slate-500">
                            ${rombel} rombel aktif
                        </p>
                    </div>
                </div>

                <div class="text-right">

                    <p class="text-[11px] text-slate-400 font-bold uppercase">
                        Siswa
                    </p>

                    <p class="text-base font-black text-[#2563EB]">
                        ${siswa}
                    </p>
                </div>
            </div>
        `;
    }).join('');

    content += `
        <div class="space-y-4 max-h-62.5 overflow-y-auto pr-1">
            ${targetList}
        </div>

        <div class="mt-4 rounded-2xl border border-blue-200 bg-white px-4 py-3 flex items-center justify-between">

            <div class="flex items-center gap-3">

                <div class="w-10 h-10 rounded-xl bg-[#2563EB] text-white 
                    flex items-center justify-center text-sm">

                    <i class="fas fa-users"></i>
                </div>

                <div>

                    <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                        Total Target
                    </p>

                    <h4 class="text-sm font-black text-slate-800">
                        ${totalSiswa} Siswa
                    </h4>
                </div>
            </div>

            <div class="px-3 py-1.5 rounded-xl bg-blue-50 text-[#2563EB] text-xs font-black">
                ${checked.length} Jenjang
            </div>
        </div>
    `;

    content += `</div>`;

    container.innerHTML = content;
}

let isProcessing = false;

// Form action reflection management
$('#submit-btn-create-reflection').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role || !schoolName || !schoolId) return;

    const form = $('#create-reflection-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-create-reflection').html(`
                    <div class=" w-full flex justify-center">
                        <div class="fixed z-9999">
                            <div id="alertSuccess"
                                class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-600 text-sm">${response.message}</span>
                                <i class="fas fa-times cursor-pointer text-green-600" id="btnClose"></i>
                        </div>
                    </div>
                </div>
            `);

            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function () {
                $('#alertSuccess').remove();
            });

            // RESET SEMUA
            $('#create-reflection-form')[0].reset();

            isProcessing = false;
            btn.prop('disabled', false);

            formSubmitReflectionManagement();
            paginateReflectionManagementHistoryRecent();
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors;

                // reset error
                $('.border-red-400').removeClass('border-red-400 border');
                $('.error-meeting-date').text('');
                $('.text-error').text('');

                $.each(errors, function (field, messages) {

                    // Tampilkan pesan error
                    $('#create-reflection-form').find(`#error-${field}`).text(messages[0]).removeClass('hidden');

                    // Tambahkan style error ke input (jika ada)
                    $('#create-reflection-form').find(`[name="${field}"]`).addClass('border-red-400 border');

                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});