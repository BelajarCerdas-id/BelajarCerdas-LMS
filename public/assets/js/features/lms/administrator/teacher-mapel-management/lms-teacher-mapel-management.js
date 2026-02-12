function paginateLmsTeacherSubject(search_class = null, search_year = null, search_teacher = null, page = 1) {
    const container = document.getElementById('container');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/subject-teacher-management/paginate`,
        method: 'GET',
        data: {
            search_class: search_class,
            search_year: search_year,
            search_teacher: search_teacher,
            page: page
        },
        success: function (response) {
            $('#tbody-subject-teacher-management').empty();
            $('.pagination-container-subject-teacher-management').empty();

            if (response.data.length > 0) {
                // tampilkan option tahun ajaran
                const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran');
                let optionTahunAjaran = '';

                response.tahunAjaran.forEach((item, index) => {
                    optionTahunAjaran += `
                        <option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>
                    `;
                });

                containerDropdownTahunAjaran.innerHTML = `
                        <div class="flex flex-col w-full mb-2">
                            <label class="text-sm font-medium text-gray-600 mb-1">
                                Filter Tahun Ajaran
                            </label>
                            <select id="dropdown-filter-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm pr-6 cursor-pointer">
                                <option value="" class="hidden">Filter Tahun Ajaran</option>
                                ${optionTahunAjaran}
                            </select>
                        </div>
                    `;
                
                // tampilkan option class
                const containerDropdownClass = document.getElementById('container-dropdown-class');
                let optionClass = '';

                response.className.forEach((item, index) => {
                    optionClass += `
                            <option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>
                        `;
                });

                containerDropdownClass.innerHTML = `
                        <div class="flex flex-col w-full mb-2">
                            <label class="text-sm font-medium text-gray-600 mb-1">
                                Filter Kelas
                            </label>
                            <select id="dropdown-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm pr-24 cursor-pointer">
                                <option value="" class="hidden">Filter Kelas</option>
                                ${optionClass}
                            </select>
                        </div>
                    `;

                $.each(response.data, function (index, item) {

                    $('#tbody-subject-teacher-management').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.school_staff_profile?.nama_lengkap}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.mapel?.mata_pelajaran}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.class_name}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.tahun_ajaran}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="hidden peer toggle-activate-subject-teacher" data-subject-teacher-id="${item.id}" 
                                        data-current-page="${response.current_page}"
                                        ${item.is_active == true ? 'checked' : ''} />
                                    <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out"></div>
                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5"></div>
                                </label>
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <div class="dropdown dropdown-left">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                        <li class="text-md">
                                            <a href="" class="btn-edit-subject-teacher"
                                                data-subject-teacher-id="${item.id}"
                                                data-teacher-email="${item.user_account?.email}"
                                                data-mapel-id="${item.mapel_id}"
                                                data-school-class-id="${item.school_class_id}">
                                                <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                                Edit Teacher
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `);
                })
                $('.pagination-container-subject-teacher-management').html(response.links);
                bindPaginationLinks();
                $('#empty-message-subject-teacher-management').hide(); // sembunyikan pesan kosong
                $('.thead-table-subject-teacher-management').show(); // Tampilkan tabel thead
                $('#container-search-teacher').show();
            } else {
                $('#tbody-subject-teacher-management').empty(); // Clear existing rows
                $('.thead-table-subject-teacher-management').hide(); // Tampilkan tabel thead
                $('#container-search-teacher').show();
                let emptyText = 'Tidak ada guru yang terdaftar.';

                if (response.selectedYear) {
                    emptyText = `Tidak ada guru mapel yang terdaftar untuk Tahun Ajaran ${response.selectedYear}.`;
                }

                if (response.selectedClass) {
                    emptyText += ` (Kelas ${response.selectedClass})`;
                }

                $('#empty-message-subject-teacher-management').html(`
                    <span class="flex h-full items-center text-center justify-center text-gray-500">
                        ${emptyText}
                    </span>
                `).show();
            }
        }
    });
}

$(document).ready(function () {
    paginateLmsTeacherSubject();
})

function bindPaginationLinks() {
    $('.pagination-container-subject-teacher-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_class = $('#dropdown-filter-class').val();
        const search_year = $('#dropdown-filter-tahun-ajaran').val();
        const search_teacher = $('#search_teacher').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateLmsTeacherSubject(search_class, search_year, search_teacher, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).on('change', '#dropdown-filter-class', function () {
    paginateLmsTeacherSubject($(this).val(), $('#dropdown-filter-tahun-ajaran').val(), $('#search_teacher').val());
});

$(document).on('change', '#dropdown-filter-tahun-ajaran', function () {
    paginateLmsTeacherSubject($('#dropdown-filter-class').val(), $(this).val(), $('#search_teacher').val());
});

$(document).on('input', '#search_teacher', function () {
    paginateLmsTeacherSubject($('#dropdown-filter-class').val(), $('#dropdown-filter-tahun-ajaran').val(), $(this).val());
})

let isProcessing = false;

// Form Action create assessment type
$('#submit-button-create-subject-teacher').on('click', function (e) {
    e.preventDefault();

    const form = $('#create-teacher-mapel-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/subject-teacher-management/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-teacher-mapel').html(
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

            $('#id_mapel').html('<option disabled selected>Pilih Mata Pelajaran</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_kelas').html('<option disabled selected>Pilih Kelas</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#school_class_id').html('<option disabled selected>Pilih Rombel Kelas</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');

            // reset form
            $('#create-teacher-mapel-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateLmsTeacherSubject();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-teacher-mapel-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-teacher-mapel-form').find(`[name="${field}"]`).addClass('border-red-400 border');
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
$(document).off('click', '.btn-edit-subject-teacher').on('click', '.btn-edit-subject-teacher', function (e) {
    e.preventDefault();

    const teacherId = $(this).data('subject-teacher-id');
    const teacherName = $(this).data('teacher-email');
    const mapelId = $(this).data('mapel-id');
    const schoolClassId = $(this).data('school-class-id');

    // set value ke form
    $('#edit-mapel-id').val(mapelId);
    $('#edit-school-class-id').val(schoolClassId);
    $('#edit-subject-teacher-id').val(teacherId);
    $('#edit-teacher').val(teacherName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit mapel
$('#submit-button-edit-subject-teacher').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-subject-teacher-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const teacherSubjectId = $('#edit-subject-teacher-id').val();

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!teacherSubjectId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/subject-teacher-management/${teacherSubjectId}/edit`,
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

                $('#alert-success-edit-data-teacher-mapel').html(
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

                $('#edit-subject-teacher-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateLmsTeacherSubject();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-subject-teacher-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-subject-teacher-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// function activate teacher subject
$(document).on('change', '.toggle-activate-subject-teacher', function () {
    let teacherSubjectId = $(this).data('subject-teacher-id'); // Ambil ID teacher subject dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 1 : 0; // Jika toggle ON maka 1, kalau OFF maka 0

    const container = document.getElementById('container');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/subject-teacher-management/${teacherSubjectId}/activate`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            is_active: status // Kirim status baru (true / false)
        },
        success: function (response) {
            paginateLmsTeacherSubject();
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});

