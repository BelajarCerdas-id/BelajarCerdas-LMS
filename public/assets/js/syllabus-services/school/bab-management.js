function paginateBabManagement(page = 1) {
    const container = document.getElementById('container-bab-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;

    fetchDataBab(schoolName, schoolId, curriculumName, curriculumId, faseId, kelasId, mapelId);

    function fetchDataBab() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/bab/paginate`,
            method: 'GET',
            data: { page: page },
            success: function (response) {
                $('#tbody-bab-management').empty();
                $('.pagination-container-bab-management').empty();

                // ====== HAK AKSES BERDASARKAN MAPEL ======
                if (response.mapel && response.mapel.school_partner_id !== null) {
                    $('#container-create-bab').show();
                    $('.thead-action').removeClass('hidden');
                    $('.tbody-action').removeClass('hidden');
                } else {
                    $('#container-create-bab').hide();
                    $('.thead-action').addClass('hidden');
                    $('.tbody-action').addClass('hidden');
                }

                const hiddenClass = response.mapel && response.mapel.school_partner_id !== null ? '' : 'hidden';

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
                
                $('#school-detail-card').show();

                if (response.data.length > 0) {

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

                        const subBabDetail = response.subBabDetail.replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumName', curriculumName)
                            .replace(':curriculumId', curriculumId).replace(':faseId', faseId).replace(':kelasId', kelasId).replace(':mapelId', mapelId).replace(':babId', item.id);

                        $('#tbody-bab-management').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">
                                    ${item.nama_bab ?? '-'}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    ${item.semester ?? '-'}
                                </td>
                                <td class="tbody-action border border-gray-300 px-3 py-2 text-center ${hiddenClass}">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-bab"
                                            data-id="${item.id}"
                                            ${item.status_bab === 'active' ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                            <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${subBabDetail}" class="btn-kelas-detail">
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
                                            <li class="${hiddenClass}">
                                                <a href="#" class="btn-edit-bab" data-school-name="${schoolName}" data-school-id="${schoolId}" data-curriculum-id="${curriculumId}" 
                                                    data-fase-id="${faseId}" data-kelas-id="${kelasId}" data-mapel-id="${mapelId}" data-bab-id="${item.id}" data-bab-name="${item.nama_bab}"
                                                    data-semester="${item.semester}">
                                                    <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                                    Edit bab
                                                </a>
                                            </li>
                                            <li onclick="historyBab(this)"
                                                class="cursor-pointer"
                                                data-nama_lengkap="${item.user_account?.office_profile?.nama_lengkap || item.user_account?.school_staff_profile?.nama_lengkap}"
                                                data-role="${item.user_account?.role ?? '-'}"
                                                data-updated_at="${updatedAt}"
                                                data-global_status="${item.status_bab}"
                                                data-school_status="${item.school_mapel?.[0]?.is_active ? true : false}"
                                                data-school_name="${item.school_partner?.nama_sekolah ?? ''}"
                                                data-is_default="${item.school_partner_id ? 'false' : 'true'}">
                                                <span>
                                                    <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                                                    Histori Bab
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });

                    // Insert pagination HTML
                    $('.pagination-container-bab-management').html(response.links);

                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();

                    $('#empty-message-bab-management').hide();
                    $('.thead-table-bab-management').show();
                } else {
                    $('#empty-message-bab-management').show();
                    $('.thead-table-bab-management').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-bab-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateBabManagement(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateBabManagement();
});

// open modal history bab
function historyBab(element) {
    const namaLengkap = element.dataset.nama_lengkap;
    const role = element.dataset.role;
    const updatedAt = element.dataset.updated_at;

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

    document.getElementById('my_modal_2').showModal();
}

let isProcessing = false;

// Form Action create bab
$('#submit-button-create-bab').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-bab').text('');

    const form = $('#create-bab-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container-bab-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/bab/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-bab').html(
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

            $('#create-bab-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateBabManagement();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-bab-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-bab-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit bab" (open modal)
$(document).off('click', '.btn-edit-bab').on('click', '.btn-edit-bab', function (e) {
    e.preventDefault();

    const schoolPartnerId = $(this).data('school-id');
    const curriculumId = $(this).data('curriculum-id');
    const faseId = $(this).data('fase-id');
    const kelasId = $(this).data('kelas-id');
    const mapelId = $(this).data('mapel-id');
    const babId = $(this).data('bab-id');
    const babName = $(this).data('bab-name');
    const semester = $(this).data('semester');

    // set value ke form
    $('#edit-school-partner-id').val(schoolPartnerId);
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-fase-id').val(faseId);
    $('#edit-kelas-id').val(kelasId);
    $('#edit-mapel-id').val(mapelId);
    $('#edit-bab-id').val(babId);
    $('#edit-nama_bab').val(babName);
    $('#edit-semester').val(semester);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit bab
$('#submit-button-edit-bab').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-bab-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container-bab-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;
    const babId = $('#edit-bab-id').val();

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;
    if (!babId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/bab/${babId}/edit`,
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

                $('#alert-success-edit-data-bab').html(
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

                $('#edit-bab-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateBabManagement();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-bab-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-bab-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// function activate bab
$(document).ready(function () {
    $(document).on('change', '.toggle-bab', function () {
        let babId = $(this).data('id'); // Ambil ID bab dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive

        const container = document.getElementById('container-bab-management');
        const schoolName = container.dataset.schoolName;
        const schoolId = container.dataset.schoolId;
        const curriculumName = container.dataset.curriculumName;
        const curriculumId = container.dataset.curriculumId;
        const faseId = container.dataset.faseId;
        const kelasId = container.dataset.kelasId;
        const mapelId = container.dataset.mapelId;

        if (!container) return;
        if (!schoolName) return;
        if (!schoolId) return;
        if (!curriculumName) return;
        if (!curriculumId) return;
        if (!faseId) return;
        if (!kelasId) return;
        if (!mapelId) return;

        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/bab/${babId}/activate`, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status_bab: status // Kirim status baru (active / inactive)
            },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                paginateBabManagement();
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});
