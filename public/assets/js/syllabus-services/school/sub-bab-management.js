function paginateSubBabManagement(page = 1) {
    const container = document.getElementById('container-sub-bab-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;
    const babId = container.dataset.babId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;
    if (!babId) return;

    fetchDataSubBab(schoolName, schoolId, curriculumName, curriculumId, faseId, kelasId, mapelId, babId);

    function fetchDataSubBab() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/${babId}/sub-bab/paginate`,
            method: 'GET',
            data: { page: page },
            success: function (response) {
                $('#tbody-sub-bab-management').empty();
                $('.pagination-container-sub-bab-management').empty();

                // ====== HAK AKSES BERDASARKAN BAB ======
                if (response.bab && response.bab.school_partner_id !== null) {
                    $('#container-create-sub-bab').show();
                    $('.thead-action').removeClass('hidden');
                    $('.tbody-action').removeClass('hidden');
                } else {
                    $('#container-create-sub-bab').hide();
                    $('.thead-action').addClass('hidden');
                    $('.tbody-action').addClass('hidden');
                }

                const hiddenClass = response.bab && response.bab.school_partner_id !== null ? '' : 'hidden';

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

                        $('#tbody-sub-bab-management').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">
                                    ${item.sub_bab ?? '-'}
                                </td>
                                <td class="tbody-action border border-gray-300 px-3 py-2 text-center ${hiddenClass}">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-sub-bab"
                                            data-id="${item.id}"
                                            ${item.status_sub_bab === 'active' ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                            <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <div class="dropdown dropdown-left">
                                        <div tabindex="0" role="button">
                                            <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                        </div>
                                        <ul tabindex="0"
                                            class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                            <li class="${hiddenClass}">
                                                <a href="#" class="btn-edit-sub-bab" data-school-name="${schoolName}" data-school-id="${schoolId}" data-curriculum-id="${curriculumId}" 
                                                    data-fase-id="${faseId}" data-kelas-id="${kelasId}" data-mapel-id="${mapelId}" data-bab-id="${babId}" 
                                                    data-sub-bab-id="${item.id}" data-sub-bab-name="${item.sub_bab}">
                                                    <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                                    Edit Sub Bab
                                                </a>
                                            </li>
                                            <li onclick="historySubBab(this)"
                                                class="cursor-pointer"
                                                data-nama_lengkap="${item.user_account?.office_profile?.nama_lengkap || item.user_account?.school_staff_profile?.nama_lengkap}"
                                                data-role="${item.user_account?.role ?? '-'}"
                                                data-updated_at="${updatedAt}"
                                                data-school_name="${item.school_partner?.nama_sekolah ?? ''}"
                                                data-is_default="${item.school_partner_id ? 'false' : 'true'}">
                                                <span>
                                                    <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                                                    Histori Sub Bab
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });

                    // Insert pagination HTML
                    $('.pagination-container-sub-bab-management').html(response.links);

                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();

                    $('#empty-message-sub-bab-management').hide();
                    $('.thead-table-sub-bab-management').show();
                } else {
                    $('#empty-message-sub-bab-management').show();
                    $('.thead-table-sub-bab-management').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-sub-bab-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSubBabManagement(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateSubBabManagement();
});

// open modal history sub bab
function historySubBab(element) {
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

// Form Action create sub bab
$('#submit-button-create-sub-bab').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-sub-bab').text('');

    const form = $('#create-sub-bab-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container-sub-bab-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;
    const babId = container.dataset.babId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;
    if (!babId) return;

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/${babId}/sub-bab/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-sub-bab').html(
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

            $('#create-sub-bab-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateSubBabManagement();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-sub-bab-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-sub-bab-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit sub bab" (open modal)
$(document).off('click', '.btn-edit-sub-bab').on('click', '.btn-edit-sub-bab', function (e) {
    e.preventDefault();

    const schoolPartnerId = $(this).data('school-id');
    const curriculumId = $(this).data('curriculum-id');
    const faseId = $(this).data('fase-id');
    const kelasId = $(this).data('kelas-id');
    const mapelId = $(this).data('mapel-id');
    const babId = $(this).data('bab-id');
    const subBabId = $(this).data('sub-bab-id');
    const subBabName = $(this).data('sub-bab-name');

    // set value ke form
    $('#edit-school-partner-id').val(schoolPartnerId);
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-fase-id').val(faseId);
    $('#edit-kelas-id').val(kelasId);
    $('#edit-mapel-id').val(mapelId);
    $('#edit-bab-id').val(babId);
    $('#edit-sub-bab-id').val(subBabId);
    $('#edit-sub_bab').val(subBabName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit sub bab
$('#submit-button-edit-sub-bab').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-sub-bab-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container-sub-bab-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;
    const babId = container.dataset.babId;
    const subBabId = $('#edit-sub-bab-id').val();

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;
    if (!babId) return;
    if (!subBabId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/${babId}/sub-bab/${subBabId}/edit`,
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

                $('#alert-success-edit-data-sub-bab').html(
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

                $('#edit-sub-bab-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateSubBabManagement();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-sub-bab-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-sub-bab-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// function activate sub bab
$(document).ready(function () {
    $(document).on('change', '.toggle-sub-bab', function () {
        let subBabId = $(this).data('id'); // Ambil ID sub bab dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive

        const container = document.getElementById('container-sub-bab-management');
        const schoolName = container.dataset.schoolName;
        const schoolId = container.dataset.schoolId;
        const curriculumName = container.dataset.curriculumName;
        const curriculumId = container.dataset.curriculumId;
        const faseId = container.dataset.faseId;
        const kelasId = container.dataset.kelasId;
        const mapelId = container.dataset.mapelId;
        const babId = container.dataset.babId;

        if (!container) return;
        if (!schoolName) return;
        if (!schoolId) return;
        if (!curriculumName) return;
        if (!curriculumId) return;
        if (!faseId) return;
        if (!kelasId) return;
        if (!mapelId) return;
        if (!babId) return;

        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/${babId}/sub-bab/${subBabId}/activate`, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status_sub_bab: status // Kirim status baru (active / inactive)
            },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                paginateSubBabManagement();
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});
