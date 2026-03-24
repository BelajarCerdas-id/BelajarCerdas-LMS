function paginateMapelManagement(page = 1) {
    const container = document.getElementById('container-mapel-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const isSchoolMode = !!schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;

    fetchDataMapel(schoolName, schoolId, curriculumName, curriculumId, faseId, kelasId);

    function fetchDataMapel() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/mapel/paginate`,
            method: 'GET',
            data: { page: page },
            success: function (response) {
                $('#tbody-mapel-management').empty();
                $('.pagination-container-mapel-management').empty();

                if (response.data.length > 0) {
                    const schoolDetailCard = document.getElementById('school-detail-card');
                    const schoolIdentity = response.schoolIdentity;

                    schoolDetailCard.innerHTML = `
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

                            <!-- KIRI : ICON + NAMA SEKOLAH -->
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#EEF6FF] flex items-center justify-center text-[#0071BC] text-2xl shadow-sm">
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-gray-800 leading-tight">
                                        ${schoolIdentity.nama_sekolah}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Detail langganan LMS sekolah
                                    </p>
                                </div>
                            </div>

                            <!-- KANAN : INFO SEKOLAH -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full lg:w-auto">

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NPSN</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.npsn}</p>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NIK Kepala Sekolah</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.user_account?.school_staff_profile?.nik}</p>
                                </div>

                                <div class="bg-[#EEF6FF] rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-[#0071BC] mb-1">Total Pengguna</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${response.countUsers}</p>
                                </div>

                            </div>
                        </div>
                    `;

                    // Render rows
                    $.each(response.data, function (index, item) {

                        const formatDate = (dateString) => {
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                            const date = new Date(dateString);
                            const day = date.getDate();
                            const monthName = months[date.getMonth()];
                            const year = date.getFullYear();

                            return `${day}-${monthName}-${year}`;
                        };

                        const updatedAt = item.updated_at ? `${formatDate(item.updated_at)}` : '-';

                        const babDetail = response.babDetail.replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumName', curriculumName)
                            .replace(':curriculumId', curriculumId).replace(':faseId', faseId).replace(':kelasId', kelasId).replace(':mapelId', item.id);
                        
                        let buttonEditMapel = '';

                        // jika pada mapel ada school partner ini maka tampilkan tombol edit
                        if (item.school_partner_id) {
                            buttonEditMapel = `
                                <li class="text-xs">
                                    <a href="#" class="btn-edit-mapel" data-school-name="${schoolName}" data-school-id="${schoolId}" data-curriculum-id="${curriculumId}" 
                                        data-fase-id="${faseId}" data-kelas-id="${kelasId}" data-mapel-id="${item.id}" data-mapel-name="${item.mata_pelajaran}">
                                        <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                        Edit mata pelajaran
                                    </a>
                                </li>
                            `;
                        }

                        const isGlobalActive = item.status_mata_pelajaran === 'active';
                        const hasSchoolOverride = item.school_mapel.length > 0;

                        // EFFECTIVE STATUS (dipakai / tidak)
                        const isChecked = isSchoolMode
                            ? (
                                hasSchoolOverride
                                    ? !!item.school_mapel[0].is_active
                                    : isGlobalActive
                            )
                            : isGlobalActive; // ADMIN MODE → PURE GLOBAL

                        $('#tbody-mapel-management').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">${item.mata_pelajaran ?? '-'}</td>
                                <td class="border text-center border-gray-300">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-mapel"
                                            data-id="${item.id}"
                                            data-global-active="${isGlobalActive ? 1 : 0}"
                                            ${isChecked ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                            <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${babDetail}" class="btn-kelas-detail">
                                        <div class="text-[#0071BC]">
                                            <span>Detail</span>
                                            <i class="fas fa-chevron-right text-xs"></i>
                                        </div>
                                    </a>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <div class="dropdown dropdown-left">
                                        <div tabindex="0" role="button">
                                            <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                        </div>
                                        <ul tabindex="0"
                                            class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">

                                            ${buttonEditMapel}

                                            <li onclick="historyMapel(this)"
                                                class="cursor-pointer"
                                                data-nama_lengkap="${item.user_account?.office_profile?.nama_lengkap || item.user_account?.school_staff_profile?.nama_lengkap}"
                                                data-role="${item.user_account?.role ?? '-'}"
                                                data-updated_at="${updatedAt}"
                                                data-global_status="${item.status_mata_pelajaran}"
                                                data-school_status="${item.school_mapel?.[0]?.is_active ? true : false}"
                                                data-has-school-override="${item.school_mapel?.length ? 'true' : 'false'}"
                                                data-school_name="${item.school_partner?.nama_sekolah ?? ''}"
                                                data-is_default="${item.school_partner_id ? 'false' : 'true'}">
                                                <span>
                                                    <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                                                    Histori Mapel
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });

                    // Insert pagination HTML
                    $('.pagination-container-mapel-management').html(response.links);

                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();

                    $('#empty-message-mapel-management').hide();
                    $('.thead-table-mapel-management').show();
                    $('#school-detail-card').show();
                } else {
                    $('#empty-message-mapel-management').show();
                    $('.thead-table-mapel-management').hide();
                    $('#school-detail-card').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-mapel-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateMapelManagement(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateMapelManagement();
});

// open modal history mapel
function historyMapel(element) {
    const container = document.getElementById('container-mapel-management');
    const schoolId = container.dataset.schoolId;

    const namaLengkap = element.dataset.nama_lengkap;
    const role = element.dataset.role;
    const updatedAt = element.dataset.updated_at;

    const globalStatus = element.dataset.global_status;
    const hasSchoolOverride = element.dataset.hasSchoolOverride === 'true';
    const schoolStatusRaw = element.dataset.school_status === 'true';

    const schoolName = element.dataset.school_name;
    const isDefault = element.dataset.is_default === "true";

    // BASIC INFO
    document.getElementById('text-nama_lengkap').innerText = namaLengkap;
    document.getElementById('text-role').innerText = role;
    document.getElementById('text-updated_at').innerText =
        updatedAt ? `Terakhir diperbarui: ${updatedAt}` : '';

    // PUBLISHER
    const publisherEl = document.getElementById('text-publisher');
    if (isDefault) {
        publisherEl.innerHTML = '<i class="fa-solid fa-building-columns mr-1"></i> belajarcerdas.id';
        publisherEl.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-yellow-100 text-yellow-700';
    } else {
        publisherEl.innerHTML = `<i class="fa-solid fa-school mr-1"></i> ${schoolName}`;
        publisherEl.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700';
    }

    // BADGE GLOBAL
    const badgeGlobal = document.getElementById('badge-global');
    if (globalStatus === 'active') {
        badgeGlobal.innerText = 'AKTIF';
        badgeGlobal.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700';
    } else {
        badgeGlobal.innerText = 'NONAKTIF';
        badgeGlobal.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700';
    }

    if (schoolId) {
        // BADGE SCHOOL
        const badgeSchool = document.getElementById('badge-school');

        if (!hasSchoolOverride) {
            badgeSchool.innerText = '-';
            badgeSchool.className = '';
        } else if (schoolStatusRaw) {
            badgeSchool.innerText = 'AKTIF';
            badgeSchool.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700';
        } else {
            badgeSchool.innerText = 'NONAKTIF';
            badgeSchool.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-gray-200 text-gray-600';
        }

        // INFO MESSAGE
        const infoEl = document.getElementById('text-info');
        if (!globalStatus) {
            infoEl.innerHTML =
                '<i class="fa-solid fa-triangle-exclamation text-red-500"></i> Mata pelajaran ini dinonaktifkan oleh platform dan tidak dapat digunakan oleh sekolah.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-red-50 text-red-700';

        } else if (!hasSchoolOverride) {
            infoEl.innerHTML =
                '<i class="fa-solid fa-circle-check text-green-500"></i> Mata pelajaran mengikuti status global dan dapat digunakan.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-green-50 text-green-700';

        } else if (schoolStatusRaw) {
            infoEl.innerHTML =
                '<i class="fa-solid fa-circle-check text-green-500"></i> Mata pelajaran aktif dan dapat digunakan oleh guru dan siswa.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-green-50 text-green-700';

        } else {
            infoEl.innerHTML =
                '<i class="fa-solid fa-triangle-exclamation text-yellow-500"></i> Mata pelajaran ini dinonaktifkan oleh sekolah.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-yellow-50 text-yellow-700';
        }
    }

    document.getElementById('my_modal_2').showModal();
}

let isProcessing = false;

// Form Action create mapel
$('#submit-button-create-mapel').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-mapel').text('');

    const form = $('#create-mapel-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container-mapel-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/mapel/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-mapel').html(
                `
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
                `
            );

            setTimeout(function () {
                document.getElementById('alertSuccess').remove();
            }, 3000);

            document.getElementById('btnClose').addEventListener('click', function () {
                document.getElementById('alertSuccess').remove();
            });

            $('#create-mapel-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateMapelManagement();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-mapel-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-mapel-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit mapel" (open modal)
$(document).off('click', '.btn-edit-mapel').on('click', '.btn-edit-mapel', function (e) {
    e.preventDefault();

    const schoolPartnerId = $(this).data('school-id');
    const curriculumId = $(this).data('curriculum-id');
    const faseId = $(this).data('fase-id');
    const kelasId = $(this).data('kelas-id');
    const mapelId = $(this).data('mapel-id');
    const mapelName = $(this).data('mapel-name');

    // set value ke form
    $('#edit-school-partner-id').val(schoolPartnerId);
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-fase-id').val(faseId);
    $('#edit-kelas-id').val(kelasId);
    $('#edit-mapel-id').val(mapelId);
    $('#edit-mapel').val(mapelName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit mapel
$('#submit-button-edit-mapel').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-mapel-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container-mapel-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = $('#edit-mapel-id').val();

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/mapel/${mapelId}/edit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Menutup modal
            const modal = document.getElementById('my_modal_1');
            if (modal) {
                modal.close();

                $('#alert-success-edit-data-mapel').html(
                    `
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
                    `
                );

                setTimeout(function () {
                    document.getElementById('alertSuccess').remove();
                }, 3000);

                document.getElementById('btnClose').addEventListener('click', function () {
                    document.getElementById('alertSuccess').remove();
                });

                $('#edit-mapel-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateMapelManagement();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-mapel-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-mapel-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// function activate mapel
$(document).ready(function () {
    $(document).on('change', '.toggle-mapel', function () {
        const checkbox = $(this);
        let mapelId = $(this).data('id'); // Ambil ID mapel dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 1 : 0; // Jika toggle ON maka 1, kalau OFF maka 0

        const container = document.getElementById('container-mapel-management');
        const schoolName = container.dataset.schoolName;
        const schoolId = container.dataset.schoolId;
        const curriculumName = container.dataset.curriculumName;
        const curriculumId = container.dataset.curriculumId;
        const faseId = container.dataset.faseId;
        const kelasId = container.dataset.kelasId;

        const action = checkbox.is(':checked') ? 'enable' : 'disable';

        if (!container) return;
        if (!schoolName) return;
        if (!schoolId) return;
        if (!curriculumName) return;
        if (!curriculumId) return;
        if (!faseId) return;
        if (!kelasId) return;

        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/mapel/${mapelId}/activate`, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { action },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                paginateMapelManagement();
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});
