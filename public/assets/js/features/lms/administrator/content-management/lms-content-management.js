function paginateContentManagemet(page = 1) {
    const container = document.getElementById('container');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;

    $.ajax({
        url: schoolId
            ? `/lms/school-subscription/${schoolName}/${schoolId}/content-management/paginate`
            : '/lms/content-management/paginate', 
        method: 'GET',
        data: {
            page: page
        },
        success: function (response) {
            $('#tbody-content-management-list').empty();
            $('.pagination-container-content-management-list').empty();

            if (response.data.length > 0) {
                console.log('schoolId:', schoolId, typeof schoolId);

                $.each(response.data, function (index, item) {

                    const formatDate = (dateString) => {
                        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day}-${monthName}-${year}`;
                    };

                    // Format tanggal mulai dan akhir
                    const updatedAt = item.updated_at ? formatDate(item.updated_at) : 'Tanggal tidak tersedia';

                    let editContent = '';
                    let reviewContent = '';

                    if (schoolId) {
                        editContent = response.editContentBySchool.replace(':contentId', item.id).replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                        reviewContent = response.reviewContentBySchool.replace(':contentId', item.id).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                    } else {
                        editContent = response.editContent.replace(':contentId', item.id);
                        reviewContent = response.reviewContent.replace(':contentId', item.id)
                    }

                    let toggleActivateContent = '';

                    if (schoolId) {
                        toggleActivateContent = `
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="hidden peer toggle-activate-content" data-content-id="${item.id}" 
                                    data-current-page="${response.current_page}"
                                    ${item.school_lms_content?.[0]?.is_active == true ? 'checked' : ''} />
                                <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out"></div>
                                <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5"></div>
                            </label>
                        `;
                    } else {
                        toggleActivateContent = `
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="hidden peer toggle-activate-content" data-content-id="${item.id}" 
                                    data-current-page="${response.current_page}"
                                    ${item.is_active == true ? 'checked' : ''} />
                                <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out"></div>
                                <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5"></div>
                            </label>
                        `;
                    }

                    $('#tbody-content-management-list').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.kurikulum?.nama_kurikulum}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.service?.name}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.kelas?.kelas}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.mapel?.mata_pelajaran}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.bab?.nama_bab}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.sub_bab?.sub_bab}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_partner_id ? item.school_partner?.nama_sekolah : 'belajarcerdas.id'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                ${toggleActivateContent}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <div class="dropdown dropdown-left">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                        <li class="text-md">
                                            <a href="${reviewContent}" class="btn-review-content">
                                                <i class="fa-solid fa-eye text-[#0071BC]"></i>
                                                Review Content
                                            </a>
                                        </li>
                                        <li onclick="historyContent(this)"
                                            class="cursor-pointer"
                                            data-nama_lengkap="${item.user_account?.office_profile?.nama_lengkap || item.user_account?.school_staff_profile?.nama_lengkap}"
                                            data-role="${item.user_account?.role ?? '-'}"
                                            data-updated_at="${updatedAt}"
                                            data-global_status="${item.is_active ? true : false}"
                                            data-school_status="${item.school_lms_content?.[0]?.is_active ? true : false}"
                                            data-school_name="${item.school_partner?.nama_sekolah ?? ''}"
                                            data-is_default="${item.school_partner_id ? 'false' : 'true'}">

                                            <span>
                                                <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                                                History Content
                                            </span>
                                        </li>
                                        <li class="text-md">
                                            <a href="${editContent}" class="btn-edit-content">
                                                <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                                Edit Content
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `);
                })
                $('.pagination-container-content-management-list').html(response.links);
                bindPaginationLinks();
                $('#empty-message-content-management-list').hide(); // sembunyikan pesan kosong
                $('.thead-table-content-management-list').show(); // Tampilkan tabel thead
            } else {
                $('#tbody-content-management-list').empty(); // Clear existing rows
                $('.thead-table-content-management-list').hide(); // Tampilkan tabel thead
                $('#empty-message-content-management-list').show();
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

$(document).ready(function () {
    paginateContentManagemet();
});

function bindPaginationLinks() {
    $('.pagination-container-content-management-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateContentManagemet(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

// open modal history content
function historyContent(element) {

    const namaLengkap = element.dataset.nama_lengkap;
    const role = element.dataset.role;
    const updatedAt = element.dataset.updated_at;

    const globalStatus = element.dataset.global_status === 'true'; // active / inactive
    const schoolStatus = element.dataset.school_status === 'true'; // active / inactive
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
    if (globalStatus) {
        badgeGlobal.innerText = 'AKTIF';
        badgeGlobal.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700';
    } else {
        badgeGlobal.innerText = 'NONAKTIF';
        badgeGlobal.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700';
    }

    // BADGE SCHOOL
    const badgeSchool = document.getElementById('badge-school');
    if (schoolStatus) {
        badgeSchool.innerText = 'AKTIF';
        badgeSchool.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700';
    } else {
        badgeSchool.innerText = 'NONAKTIF';
        badgeSchool.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-gray-200 text-gray-600';
    }

    // INFO MESSAGE
    const infoEl = document.getElementById('text-info');
    if (globalStatus !== true) {
        infoEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-red-500"></i> Content ini dinonaktifkan oleh platform dan tidak dapat digunakan oleh sekolah.';
        infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-red-50 text-red-700';
    } else if (schoolStatus) {
        infoEl.innerHTML = '<i class="fa-solid fa-circle-check text-green-500"></i> Content aktif dan dapat digunakan oleh guru dan siswa.';
        infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-green-50 text-green-700';
    } else {
        infoEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-yellow-500"></i> Content ini dinonaktifkan oleh sekolah dan tidak dapat digunakan oleh sekolah.';
        infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-yellow-50 text-yellow-700';
    }

    document.getElementById('my_modal_1').showModal();
}

// activate content
$(document).on('change', '.toggle-activate-content', function () {
    const container = document.getElementById('container');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;

    let contentId = $(this).data('content-id'); // Ambil content id dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 1 : 0; // Jika toggle ON maka 1, kalau OFF maka 0
    let currentPage = $(this).data('current-page'); // Ambil current page dari atribut data-current-page di checkbox

    $.ajax({
        url: schoolId
            ? `/lms/school-subscription/content-management/${contentId}/${schoolName}/${schoolId}/activate`
            : `/lms/content-management/${contentId}/activate`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            is_active: status // Kirim status baru (true / false)
        },
        success: function (response) {
            // inisialisasi update data terbaru setelah berhasil insert data
            const page = currentPage;
            paginateContentManagemet(page);
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked')); // ← GUNAKAN INI
        }
    });
});

let isProcessing = false;

// Form Action create content
$('#submit-button-create-content').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('content-management-form');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;

    const form = $('#content-management-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: schoolId
            ? `/lms/school-subscription/${schoolName}/${schoolId}/content-management/store`
            : `/lms/content-management/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-create-content').html(`
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

            $('#id_kelas').html('<option disabled selected>Pilih Kelas</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_mapel').html('<option disabled selected>Pilih Mata Pelajaran</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_bab').html('<option disabled selected>Pilih Bab</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_sub_bab').html('<option disabled selected>Pilih Bab</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_service').html('<option disabled selected>Pilih Service</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');

            // RESET SEMUA
            $('#content-management-form')[0].reset();
            $('#dynamic-form').empty();

            isProcessing = false;
            btn.prop('disabled', false);

            paginateContentManagemet();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#content-management-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#content-management-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });

                Object.keys(errors).forEach(key => {
                    if (key.startsWith('files.')) {
                        const index = key.split('.')[1];

                        $(`[data-error-file="${index}"]`)
                            .text(errors[key][0]);
                    }
                });

                Object.keys(errors).forEach(key => {
                    if (key.startsWith('text.')) {
                        const parts = key.split('.'); // text.0.1
                        const ruleIndex = parts[1];
                        const rowIndex = parts[2];

                        const container = $(`[data-repeatable="${ruleIndex}"]`);

                        const row = container.find('.repeatable-item').eq(rowIndex);

                        row.find('.error-text').removeClass('hidden').text(errors[key][0]);
                    }
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});
