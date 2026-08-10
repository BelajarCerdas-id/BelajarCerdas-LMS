let selectedSchools = new Map();
function paginateSchoolList(search_school = '') {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/form/paginate-school-list`,
        method: 'GET',
        data: {
            search_school: search_school
        },
        beforeSend: function () {
            $('#school-list-skeleton').removeClass('hidden');
            $('#container-grid-school-list').addClass('hidden');
            $('#empty-message-school-list').addClass('hidden');
        },
        success: function (res) {
            $('#school-list-skeleton').addClass('hidden');
            const grid = $('#grid-school-list');
            grid.empty();

            if (res.data.length > 0) {
                $.each(res.data, function (index, item) {
                    const isChecked = selectedSchools.has(item.id);

                    const card = `
                        <label class="cursor-pointer border border-gray-300 rounded-xl">
                            <input type="checkbox" name="school_partner_id[]" class="peer hidden school-checkbox" value="${item.id}" data-name="${item.nama_sekolah}" 
                                data-school-logo="${item.logo}" data-foundation-id="${item.school_foundation_id ?? ''}" 
                                data-foundation-name="${item.school_foundation?.nama_yayasan ?? ''}" ${isChecked ? 'checked' : ''}>

                            <div class="relative rounded-2xl border border-base-300 bg-base-100 p-5 transition-all duration-300 hover:border-primary hover:shadow-md
                                peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:ring-2 peer-checked:ring-primary/20">

                                <!-- Status -->
                                <div class="absolute right-4 top-4 hidden peer-checked:flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white shadow">
                                    <i class="fa-solid fa-check text-sm"></i>
                                </div>

                                <div class="flex gap-4">

                                    <!-- Logo -->
                                    ${item.logo ? `
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden">
                                            <img src="/${item.logo}" alt="Logo Sekolah" class="h-full w-full object-contain">
                                        </div>
                                    ` : `
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-base-300 bg-white shadow-sm">
                                            <i class="fa-solid fa-school text-slate-500 text-sm"></i>
                                        </div>
                                    `} 

                                    <!-- Content -->
                                    <div class="flex-1">
                                        <h3 class="text-md font-bold leading-tight">
                                            ${item.nama_sekolah ?? '-'}
                                        </h3>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="text-xs text-base-content/60">
                                                NPSN : ${item.npsn ?? '-'}
                                            </span>
                                        </div>

                                        <div class="mt-2 flex flex-wrap gap-4 text-xs text-base-content/70">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-user-tie text-primary"></i>
                                                ${item.teacher_count ?? 0} Guru
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-user-graduate text-primary"></i>
                                                ${item.student_count ?? 0} Siswa
                                            </div>
                                        </div>

                                        <div class="mt-2 flex flex-wrap gap-4 text-xs text-base-content/70">
                                            ${item.school_foundation_id ? `
                                                <span class="badge badge-warning badge-sm">
                                                    <i class="fa-solid fa-building-columns"></i>
                                                    ${item.school_foundation?.nama_yayasan ?? '-'}
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    `;

                    grid.append(card);
                });

                $('#container-grid-school-list').removeClass('hidden');
                $('#empty-message-school-list').addClass('hidden');
            } else {
                $('#container-grid-school-list').addClass('hidden');
                $('#empty-message-school-list').removeClass('hidden');
            }
        },
        error: function (err) {
            $('#school-list-skeleton').addClass('hidden');
            $('#container-grid-school-list').addClass('hidden');
            
            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateSchoolList();
});

$(document).on('change', '.school-checkbox', function () {
    const id = Number($(this).val());

    if ($(this).is(':checked')) {
        selectedSchools.set(id, {
            id: id,
            name: $(this).data('name'),
            logo: $(this).data('school-logo')
        });
    } else {
        selectedSchools.delete(id);
    }

    updateSchoolSummary();
});

$('#nama_yayasan').on('input', function () {
    const value = $(this).val().trim();

    $('#summary-foundation-name').text(value === '' ? 'Belum diisi' : value);
});

$('#search_school').on('input', function () {
    paginateSchoolList($(this).val());
}); 

$(document).on('click', '.school-checkbox', function (e) {

    const foundationId = $(this).data('foundation-id');

    if (foundationId) {

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Sekolah Sudah Memiliki Yayasan',
            html: `
                <div class="text-left">
                    <p>
                        Sekolah
                        <b>${$(this).data('name')}</b>
                        sudah berada di bawah:
                    </p>

                    <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 p-3">
                        <i class="fa-solid fa-building-columns text-amber-500"></i>
                        <b>${$(this).data('foundation-name')}</b>
                    </div>

                    <p class="mt-3 text-sm text-gray-500">
                        Satu sekolah hanya dapat berada di bawah satu yayasan.
                        Apabila ingin memindahkan sekolah ini, silakan ubah yayasan
                        pada data sekolah atau edit yayasan yang sudah ada.
                    </p>
                </div>
            `,
            confirmButtonText: 'Mengerti'
        });

        return false;
    }
});

function updateSchoolSummary() {

    $('#summary-school-count').text(`${selectedSchools.size} Sekolah`);

    const container = $('#summary-school-list');

    container.empty();

    if (selectedSchools.size === 0) {

        container.html(`
            <div class="text-base-content/50 text-sm">
                Belum ada sekolah dipilih.
            </div>
        `);

        return;
    } else {
        $('#error-school_partner_id').text('');
    }

    selectedSchools.forEach(function (school) {
        
        container.append(`
            <div class="flex items-center gap-3 rounded-lg bg-base-200 px-3 py-2 text-sm">

                <div class="flex h-6 w-6 shrink-0 items-center justify-center">

                    ${school.logo ? `
                        <img src="/${school.logo}" class="h-full w-full rounded-full object-contain">
                    ` : `
                        <i class="fa-solid fa-school text-primary"></i>
                    `}

                </div>

                <span class="truncate">${school.name}</span>

            </div>
        `);

    });
}  

let isProcessing = false;

// Form Action Create School Foundation
$('#submit-button-create-school-foundation').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    const form = $('#create-school-foundation-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    // hapus jika ada
    formData.delete('school_partner_id[]');

    // tambahkan dari Map
    selectedSchools.forEach(function (school) {
        formData.append('school_partner_id[]', school.id);
    });

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/submit-form`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-create-school-foundation').html(`
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

function resetCreateSchoolFoundationForm() {

    // Reset form HTML
    $('#create-school-foundation-form')[0].reset();

    // Kosongkan sekolah yang dipilih
    selectedSchools.clear();

    // Reset ringkasan
    $('#summary-foundation-name').text('Belum diisi');
    $('#summary-school-count').text('0 Sekolah');

    $('#summary-school-list').html(`
        <div class="text-base-content/50 text-sm">
            Belum ada sekolah dipilih.
        </div>
    `);

    // Reset preview logo
    $('#logoPreview').attr('src', '').addClass('hidden');

    $('#logoPlaceholder').removeClass('hidden');

    // Reset search
    $('#search_school').val('');

    // Hapus semua error
    $('.text-red-500').text('').addClass('hidden');

    $('.border-red-400').removeClass('border-red-400 border');

    // Reload list sekolah supaya semua checkbox kembali kosong
    paginateSchoolList();
}