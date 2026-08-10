let currentSchoolFoundationId = null;
let selectedSchool = [];
let isProcessing;
function paginateSchoolFoundation(search_school_foundation = null, loadSummary = false, page = 1) {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/school-foundation/paginate`,
        method: 'GET',
        data: {
            search_school_foundation,
            page: page
        },
        beforeSend: function () {

            if (loadSummary) {
                $('#kpi-loading').removeClass('hidden');
                $('#kpi-content').addClass('hidden');

                $('#container-school-foundation-list-loading').removeClass('hidden');
                $('#container-school-foundation-list-content').addClass('hidden');
            }

            $('#container-school-foundation-list-loading').removeClass('hidden');
            $('#container-school-foundation-list-content').addClass('hidden');
        },
        success: function (response) {
            if (loadSummary) {
                renderSchoolFoundationKPI(response.summary);
            }

            $('#container-school-foundation-list-loading').addClass('hidden');
            $('#container-school-foundation-list-content').removeClass('hidden');

            const grid = $('#grid-school-foundation-list');
            grid.empty();

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {

                    const schoolFoundationEditForm = response.schoolFoundationEditForm.replace(':role', role).replace(':schoolFoundationId', item.id);
                    const schoolFoundationAccessControl = response.schoolFoundationAccessControl.replace(':role', role).replace(':schoolFoundationId', item.id);
                    const schoolFoundationFinanceAccess = response.schoolFoundationFinanceAccess.replace(':role', role).replace(':schoolFoundationId', item.id);

                    const card = `
                        <div class="rounded-2xl border border-gray-300 bg-base-100 p-5 hover:border-primary hover:shadow-md transition-all">

                            <!-- Header -->
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="h-14 w-14 rounded-xl border border-gray-300 bg-slate-50 flex items-center justify-center shrink-0">
                                        ${item.logo ? `
                                            <img src="/${item.logo}" class="w-full h-full object-contain rounded-xl">
                                        ` : `
                                            <div class="flex flex-col items-center justify-center text-slate-400">

                                                <div class="w-9 h-9 rounded-lg bg-slate-200 flex items-center justify-center">
                                                    <i class="fa-solid fa-school text-sm"></i>
                                                </div>

                                            </div>
                                        `}
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-base truncate">
                                            ${item.nama_yayasan}
                                        </h3>

                                        <p class="text-xs text-base-content/60">
                                            ${item.school_count} Sekolah Terdaftar
                                        </p>
                                    </div>
                            </div>

                                <a href="${schoolFoundationEditForm}">
                                    <button type="button" class="btn btn-sm bg-[#0071BC] text-white border-none w-full sm:w-auto">
                                        <i class="fa-solid fa-pen"></i>
                                        Edit Yayasan
                                    </button>
                                </a>
                            </div>

                            <!-- Statistik -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-5">

                                <!-- Sekolah -->
                                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Sekolah
                                            </p>

                                            <h3 class="mt-1 text-xl font-bold text-slate-800">
                                                ${item.school_count}
                                            </h3>
                                        </div>

                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                                            <i class="fa-solid fa-school text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guru -->
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Guru
                                            </p>

                                            <h3 class="mt-1 text-xl font-bold text-slate-800">
                                                ${item.teacher_count}
                                            </h3>
                                        </div>

                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                                            <i class="fa-solid fa-user-tie text-emerald-600"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Siswa -->
                                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Siswa
                                            </p>

                                            <h3 class="mt-1 text-xl font-bold text-slate-800">
                                                ${item.student_count}
                                            </h3>
                                        </div>

                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                                            <i class="fa-solid fa-user-graduate text-amber-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- List Sekolah -->
                            <div class="mt-6">

                                <!-- Header -->
                                <div class="flex items-center justify-between mb-4">    
                                    <div>
                                        <h4 class="font-semibold text-sm">
                                            Sekolah Dibawah Yayasan
                                        </h4>

                                        <p class="text-xs text-base-content/60">
                                            Kelola sekolah yang berada di bawah yayasan ini.
                                        </p>
                                    </div>
                                </div>

                                ${item.schools.length > 0 ? `
                                    <div class="h-60 overflow-y-auto pr-2 space-y-3">

                                        ${item.schools.map(school => `
                                            <div class="rounded-xl border border-gray-300 p-4">

                                                <!-- Header -->
                                                <div class="flex items-start gap-3">

                                                    <!-- Logo -->
                                                    <div class="w-14 h-14 shrink-0 rounded-xl border border-gray-300 overflow-hidden bg-base-100">
                                                        ${school.logo
                                                            ? `<img src="/${school.logo}" class="w-full h-full object-contain">`
                                                            : `<div class="flex h-full items-center justify-center">
                                                                    <i class="fa-solid fa-school text-slate-500"></i>
                                                                </div>`
                                                        }
                                                    </div>

                                                    <!-- Informasi -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <h5 class="font-semibold text-sm wrap-break-word">
                                                                ${school.nama_sekolah ?? '-'}
                                                            </h5>
                                                        </div>

                                                        <p class="text-xs text-base-content/60 mt-1">
                                                            NPSN : ${school.npsn ?? '-'}
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Statistik -->
                                                <div class="grid grid-cols-2 gap-2 mt-4">

                                                    <div class="rounded-lg bg-base-200 px-3 py-2">

                                                        <div class="flex items-center gap-2 text-xs text-base-content/60">
                                                            <i class="fa-solid fa-user-tie text-primary"></i>
                                                            <span>Guru</span>
                                                        </div>

                                                        <div class="font-semibold mt-1">
                                                            ${school.teacher_count ?? 0}
                                                        </div>
                                                    </div>

                                                    <div class="rounded-lg bg-base-200 px-3 py-2">

                                                        <div class="flex items-center gap-2 text-xs text-base-content/60">
                                                            <i class="fa-solid fa-user-graduate text-primary"></i>
                                                            <span>Siswa</span>
                                                        </div>

                                                        <div class="font-semibold mt-1">
                                                            ${school.student_count ?? 0}
                                                        </div>

                                                    </div>
                                                </div>

                                                <!-- Action -->
                                                <div class="mt-4 pt-3 border-t border-gray-200 flex justify-end">
                                                    <button type="button"
                                                        class="text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg px-3 py-2 text-sm font-medium transition
                                                        btn-remove-school-from-foundation cursor-pointer"
                                                        data-school-id="${school.id}">

                                                        <i class="fa-solid fa-building-circle-xmark mr-2"></i>
                                                        Keluarkan
                                                    </button>
                                                </div>
                                            </div>
                                        `).join('')}

                                    </div>
                                ` : `
                                    <div class="rounded-xl border-2 border-dashed border-gray-300 py-12 text-center">
                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                                            <i class="fa-solid fa-school text-2xl text-slate-400"></i>
                                        </div>

                                        <h4 class="mt-4 font-semibold text-sm">
                                            Belum Ada Sekolah
                                        </h4>

                                        <p class="mt-2 text-sm text-base-content/60">
                                            Yayasan ini belum memiliki sekolah.
                                        </p>
                                    </div>
                                `}
                            </div>

                            <!-- Footer -->
                            <div class="w-full mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 border-t border-gray-300 pt-4">

                                <!-- Kelola Akses -->
                                <div class="w-full">
                                    <a href="${schoolFoundationAccessControl}">
                                        <button type="button" class="btn btn-sm btn-outline border-primary bg-primary text-white w-full">
                                            <i class="fa-solid fa-users-gear"></i>
                                            Kelola Akses
                                        </button>
                                    </a>
                                </div>

                                <!-- Kelola Link Keuangan -->
                                <div class="w-full">
                                    <a href="${schoolFoundationFinanceAccess}">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline border-emerald-600 bg-emerald-600 text-white w-full">
                                            <i class="fa-solid fa-folder-open"></i>
                                            Kelola Link Keuangan
                                        </button>
                                    </a>
                                </div>

                                <!-- Tambah Sekolah -->
                                <div class="w-full">
                                    <button type="button" class="btn-add-school btn btn-sm bg-[#0071BC] text-white border-none hover:bg-[#005f9f] w-full"
                                        data-foundation-id="${item.id}">

                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Sekolah
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    grid.append(card);
                });

                $('.pagination-container-school-foundation-list').html(response.links);
                bindPaginationLinks();
                $('#empty-message-school-foundation-list').hide();
            } else {
                $('#empty-message-school-foundation-list').show();
                $('.pagination-container-school-foundation-list').html('');
            }
        },
        error: function (err) {
            if (loadSummary) {
                $('#kpi-loading').addClass('hidden');
                $('#kpi-content').removeClass('hidden');

                $('#container-school-foundation-list-loading').addClass('hidden');
                $('#container-school-foundation-list-content').removeClass('hidden');
            }

            $('#container-school-foundation-list-loading').addClass('hidden');
            $('#container-school-foundation-list-content').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateSchoolFoundation(null, true, 1);
});

function bindPaginationLinks() {
    $('.pagination-container-school-foundation-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_school_foundation = $('#search_teacher').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSchoolFoundation(search_school_foundation, false, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$('#search_school').on('input', function () {
    loadAvailableSchool(currentSchoolFoundationId, $(this).val());
});

function loadAvailableSchool(foundationId, search = '') {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    $('#loading-school-foundation').removeClass('hidden');
    $('#school-foundation-list').addClass('hidden');
    $('#empty-school-foundation').addClass('hidden');

    $.ajax({

        url: `/lms/${role}/school-foundation/${foundationId}/manage/form/paginate-school-list`,
        type: 'GET',
        data: {
            search_school: search
        },
        success: function (res) {

            $('#loading-school-foundation').addClass('hidden');
            $('#school-foundation-list').removeClass('hidden');

            let html = '';

            if (res.data.length > 0) {
                if (search.trim() !== '') {
                    $('#available-school-count').text(`${res.data.length} dari ${res.total} Sekolah`);
                } else {
                    $('#available-school-count').text(`${res.data.length} Sekolah Tersedia`);
                }
                
                res.data.forEach(item => {
                    const checked = selectedSchool.includes(String(item.id));

                    html += `

                        <label class="block">

                            <input type="checkbox" name="school_partner_id[]" value="${item.id}" class="hidden school-checkbox" ${checked ? 'checked' : ''}>

                            <div class="school-card cursor-pointer rounded-xl border border-gray-300 p-4 hover:border-primary transition
                                ${checked ? 'border-primary bg-blue-50' : 'border-gray-300'}"">

                                <div class="flex gap-4">

                                    <div class="w-14 h-14 rounded-xl border border-gray-300 overflow-hidden bg-white shrink-0">
                                        ${item.logo
                                        ?
                                            `<img src="/${item.logo}" class="w-full h-full object-contain">`
                                        :
                                            `<div class="flex h-full items-center justify-center">
                                                <i class="fa-solid fa-school text-slate-400"></i>
                                            </div>`
                                        }
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-semibold text-sm">
                                                ${item.nama_sekolah}
                                            </h4>
                                        </div>

                                        <div class="text-xs text-base-content/60 mt-1">
                                            NPSN : ${item.npsn}
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 mt-3">
                                            <div class="rounded-lg bg-base-200 px-3 py-2">

                                                <div class="text-xs">
                                                    <i class="fa-solid fa-user-tie text-primary"></i>
                                                    Guru
                                                </div>

                                                <div class="font-semibold">
                                                    ${item.teacher_count}
                                                </div>
                                            </div>

                                            <div class="rounded-lg bg-base-200 px-3 py-2">

                                                <div class="text-xs">
                                                    <i class="fa-solid fa-user-graduate text-primary"></i>
                                                    Siswa
                                                </div>

                                                <div class="font-semibold">
                                                    ${item.student_count}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    `;
                });

                $('#empty-school-foundation').addClass('hidden');
                $('#school-foundation-list').html(html);
            } else {
                if (search.trim() !== '') {
                    $('#available-school-count')
                        .text(`0 dari ${res.total} Sekolah`);
                } else {
                    $('#available-school-count')
                        .text(`0 Sekolah Tersedia`);
                }

                $('#empty-school-foundation').removeClass('hidden');
                $('#school-foundation-list').html('');
            }
        }
    });
}

$(document).on('change', '.school-checkbox', function () {
    const id = $(this).val();
    const card = $(this).siblings('.school-card');

    if ($(this).is(':checked')) {

        if (!selectedSchool.includes(id)) {
            selectedSchool.push(id);
        }

        card.addClass('border-primary bg-blue-50');

    } else {
        selectedSchool = selectedSchool.filter(item => item != id);
        card.removeClass('border-primary bg-blue-50');
    }

    $('#selected-school-count').text(`${selectedSchool.length} Sekolah`);
});

// btn remove school to foundation
$(document).on('click', '.btn-remove-school-from-foundation', function () {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    const schoolId = $(this).data('school-id');

    Swal.fire({
        icon: 'warning',
        title: 'Keluarkan Sekolah',
        text: 'Apakah anda yakin ingin mengeluarkan sekolah ini dari yayasan?',
        showDenyButton: true,
        denyButtonText: 'Tidak',
        confirmButtonText: 'Ya',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/lms/${role}/school-foundation/${currentSchoolFoundationId}/manage/remove-school-to-foundation/${schoolId}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#alert-success-remove-school-to-foundation').html(`
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

                    paginateSchoolFoundation(null, false, 1);
                }
            });
        }
    });
});

// btn add school to foundation
$(document).on('click', '.btn-add-school', function () {
    currentSchoolFoundationId = $(this).data('foundation-id');
    loadAvailableSchool(currentSchoolFoundationId);
    document.getElementById('modal-add-school-foundation').showModal();
});

// Form Action Crate Add School To Foundation
$('#submit-button-add-school-to-foundation').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    const form = $('#add-school-to-foundation-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    // hapus jika ada
    formData.delete('school_partner_id[]');

    // tambahkan dari map
    selectedSchool.forEach(function (schoolId) {
        formData.append('school_partner_id[]', schoolId);
    });

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/school-foundation/${currentSchoolFoundationId}/manage/school-to-foundation/submit-form`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('modal-add-school-foundation');
            if (modal) {
                modal.close();
            }

            $('#alert-success-add-school-to-foundation').html(`
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

            resetCreateSchoolFoundationForm();

            isProcessing = false;
            btn.prop('disabled', false);

            paginateSchoolList();
        },
        error: function (xhr) {

            isProcessing = false;
            btn.prop('disabled', false);

            if (xhr.status === 422) {

                const response = xhr.responseJSON;

                // Sekolah sudah memiliki yayasan
                if (response.flag === 'school_already_has_foundation') {

                    const schoolList = response.schools.map(item => `
                        <div class="rounded-xl border border-base-300 bg-base-100 p-3 text-left mb-2">
                            <div class="font-semibold text-base-content">
                                ${item.nama_sekolah}
                            </div>

                            <div class="text-sm text-base-content/70 mt-1">
                                Sudah berada di bawah
                                <span class="font-semibold text-primary">
                                    ${item.nama_yayasan}
                                </span>
                            </div>
                        </div>
                    `).join('');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Sekolah Sudah Terhubung',
                        html: `
                            <p class="mb-4">
                                sekolah yang dipilih sudah berada di bawah yayasan lain.
                            </p>

                            <div class="max-h-72 overflow-y-auto">
                                ${schoolList}
                            </div>
                        `,
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#0071BC'
                    });

                    return;
                }

                $.each(response.errors, function (field, messages) {

                    const errorText = $(`#error-${field}`);

                    errorText.removeClass('hidden').text(messages[0]);

                    $(`[name="${field}"]`).addClass('border-red-400 border');

                });

                return;
            }

            alert('Terjadi kesalahan saat mengirim data.');
        }
    });
});

$('#btn-close-add-school-foundation').on('click', function () {
    document.getElementById('modal-add-school-foundation').close();

    resetCreateSchoolFoundationForm();
});

function resetCreateSchoolFoundationForm() {

    // Reset form
    $('#add-school-to-foundation-form')[0].reset();

    // Kosongkan sekolah yang dipilih
    selectedSchool = [];

    // Reset search
    $('#search_school').val('');

    // Hapus semua error
    $('.text-red-500').text('').addClass('hidden');

    $('.border-red-400').removeClass('border-red-400 border');

    // Reload list sekolah supaya semua checkbox kembali kosong
    paginateSchoolFoundation(null, false, 1);
}