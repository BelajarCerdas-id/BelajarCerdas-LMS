function managementClassSchoolSubscription(search_class = null, search_year = null) {
    const container = document.getElementById('container-management-class-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const majorId = container.dataset.majorId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;

    fetchClass(schoolName, schoolId, role, majorId);

    function fetchClass() {
        $.ajax({
            url: majorId
                ? `/lms/school-subscription/${schoolName}/${schoolId}/role-account/${role}/management-majors/${majorId}/management-class/paginate`
                : `/lms/school-subscription/${schoolName}/${schoolId}/role-account/${role}/management-class/paginate`,

            method: 'GET',
            data: {
                search_class: search_class,
                search_year: search_year
            },
            success: function (response) {
                const containerManagementClass = $('#grid-management-class-list');
                containerManagementClass.empty();

                if (response.data.length > 0) {
                    const schoolDetailCard = document.getElementById('school-detail-card');
                    const schoolIdentity = response.schoolIdentity;

                    const totalSiswa = response.data.reduce((total, kelas) => {
                        return total + kelas.student_school_class_count;
                    }, 0);

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
                                    <p class="text-xs text-[#0071BC] mb-1">Total Siswa Aktif</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${totalSiswa}</p>
                                </div>

                            </div>
                        </div>
                    `;
                    // tampilkan option tahun ajaran
                    const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran');
                    let optionTahunAjaran = '';

                    response.tahunAjaran.forEach((item, index) => {
                        optionTahunAjaran += `
                        <option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>
                    `;
                    });

                    containerDropdownTahunAjaran.innerHTML = `
                        <div class="flex justify-end w-full mb-6">
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
                        <div class="flex justify-end w-full mb-6">
                            <select id="dropdown-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm pr-24 cursor-pointer">
                                <option value="" class="hidden">Filter Kelas</option>
                                ${optionClass}
                            </select>
                        </div>
                    `;

                    $.each(response.data, function (index, item) {
                        let lmsManagementStudents = '';

                        if (majorId) {
                            lmsManagementStudents = response.lmsManagementStudentsWithMajor.replace(':schoolName', schoolIdentity.nama_sekolah).replace(':schoolId', schoolIdentity.id)
                                .replace(':role', role).replace(':classId', item.id).replace(':majorId', majorId);
                        } else {
                            lmsManagementStudents = response.lmsManagementStudentsNoMajor.replace(':schoolName', schoolIdentity.nama_sekolah).replace(':schoolId', schoolIdentity.id)
                                .replace(':role', role).replace(':classId', item.id);
                        }

                        const card = `
                            <!-- CARD -->
                            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                                <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold px-3 py-1 rounded-md bg-blue-100 text-blue-700">
                                        ${item.kelas?.kelas}
                                    </span>
                                </div>

                                    <!-- STATUS -->
                                    <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium ${item.status_class === 'active' ? 'text-green-600' : 'text-gray-400'}">
                                                ${item.status_class === 'active' ? 'Aktif' : 'Tidak Aktif'}
                                            </span>
                                        <div>
                                            <label class="relative items-center cursor-pointer">
                                                <input type="checkbox" class="hidden peer toggle-activate-class"
                                                    data-id="${item.id}"
                                                    ${item.status_class === 'active' ? 'checked' : ''} />
                                                <div
                                                    class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                                </div>
                                                    <div
                                                    class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-2xl font-bold text-gray-800">
                                        ${item.class_name}
                                    </h4>

                                    <button
                                        class="btn-edit-class text-xs px-2 py-1 rounded-md border border-gray-400 text-gray-500 font-bold hover:text-[#4189E0] hover:border-[#4189E0]
                                        cursor-pointer"
                                        data-class-name="${item.class_name}" data-fase-id="${item.fase_id}" data-kelas-id="${item.kelas_id}" data-nama-fase="${item.fase?.nama_fase}" 
                                        data-nama-kelas="${item.kelas?.kelas}" data-tahun-ajaran="${item.tahun_ajaran}" data-akun-wali-kelas="${item.user_account?.email}" data-id="${item.id}">
                                        Edit
                                    </button>
                                </div>

                                <p class="text-sm text-gray-500 mb-4">
                                    Tahun Ajaran ${item.tahun_ajaran}
                                </p>

                                <!-- WALI KELAS -->
                                <div class="flex items-center gap-2 mb-4">
                                    <i class="fa-solid fa-user-tie text-gray-400 text-sm"></i>
                                    <p class="text-sm text-gray-600">
                                        Wali Kelas:
                                        <span class="font-semibold text-gray-800">
                                            ${item.user_account?.school_staff_profile?.nama_lengkap}
                                        </span>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500">Jumlah Siswa Aktif</p>
                                        <p class="text-xl font-bold text-[#4189E0]">${item.student_school_class_count}</p>
                                    </div>

                                    <button class="text-sm font-semibold text-[#4189E0] flex items-center gap-2">
                                        <a href="${lmsManagementStudents}">
                                            Lihat
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </button>
                                </div>
                            </div>
                        `;
                        containerManagementClass.append(card);
                    });

                    $('#school-detail-card').show();
                    $('#empty-message-management-class-list').hide();
                } else {
                    $('#school-detail-card').hide();
                    $('#empty-message-management-class-list').show();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
}

$(document).ready(function () {
    managementClassSchoolSubscription();
});

$(document).on('change', '#dropdown-filter-class', function () {
    managementClassSchoolSubscription($(this).val(), $('#dropdown-filter-year').val());
});

$(document).on('change', '#dropdown-filter-tahun-ajaran', function () {
    managementClassSchoolSubscription($('#dropdown-filter-class').val(), $(this).val());
});

// activate class
$(document).on('change', '.toggle-activate-class', function () {
    let id = $(this).data('id'); // Ambil ID class dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive

    $.ajax({
        url: `/lms/school-subscription/management-class/${id}/activate-class`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status_class: status // Kirim status baru (active/inactive)
        },
        success: function (response) {
            managementClassSchoolSubscription($('#dropdown-filter-class').val(), $('#dropdown-filter-year').val());
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});


// Event listener tombol "tambah kelas" (open modal)
$(document).off('click', '.btn-create-class').on('click', '.btn-create-class', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

let isProcessing = false;

// Form Action create class
$('#submit-button-create-class').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-class-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const majorId = container.dataset.majorId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    const form = $('#form-create-class-lms-subscription')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: majorId
            ? `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-majors/${majorId}/management-class/create`
            : `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-class/create`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_1');

            if (modal) {
                modal.close();

                $('#alert-success-create-class').html(`
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

                $('#form-create-class-lms-subscription')[0].reset();

                managementClassSchoolSubscription($('#dropdown-filter-class').val(), $('#dropdown-filter-year').val());

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-create-class-lms-subscription').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-create-class-lms-subscription').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit kelas" (open modal)
$(document).on('click', '.btn-edit-class', function () {
    const modal = document.getElementById('my_modal_2');
    modal.showModal();

    const faseId = $(this).data('fase-id');
    const kelasId = $(this).data('kelas-id');

    // isi field biasa
    $('#edit-class-id').val($(this).data('id'));
    $('#edit-class-name').val($(this).data('class-name'));
    $('#edit-tahun-ajaran').val($(this).data('tahun-ajaran'));
    $('#edit-akun-wali-kelas').val($(this).data('akun-wali-kelas'));

    const faseSelect = $('#my_modal_2 .fase-id');
    const kelasSelect = $('#my_modal_2 .kelas-id');

    // set fase dulu
    faseSelect.val(faseId).trigger('change');

    // SIMPAN kelasId sementara
    kelasSelect.data('selected', kelasId);
});


// Form Action edit class
$('#submit-button-edit-class').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-class-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const majorId = container.dataset.majorId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    const form = $('#form-edit-class-lms-subscription')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const classId = $('#edit-class-id').val();

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: majorId
            ? `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-class/${classId}/management-majors/${majorId}/edit`
            : `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-class/${classId}/edit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_2');

            if (modal) {
                modal.close();

                $('#alert-success-edit-class').html(`
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

                $('#form-edit-class-lms-subscription')[0].reset();

                managementClassSchoolSubscription($('#dropdown-filter-class').val(), $('#dropdown-filter-year').val());

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-edit-class-lms-subscription').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-edit-class-lms-subscription').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// DROPDOWN BERTINGKAT FASE -> KELAS
$(document).on('change', '.fase-id', function () {
    const modal = $(this).closest('dialog'); // modal aktif
    const faseId = $(this).val();
    const kelasSelect = modal.find('.kelas-id');

    if (!faseId) {
        kelasSelect.prop('disabled', true).empty()
            .append('<option value="" hidden>Pilih Kelas</option>');
        return;
    }

    $.ajax({
        url: '/kelas/' + faseId,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            kelasSelect.empty()
                .append('<option value="" hidden>Pilih Kelas</option>');

            if (data.length) {
                kelasSelect.prop('disabled', false).removeClass('cursor-default').addClass('cursor-pointer').removeClass('opacity-50').addClass('opacity-100');

                data.forEach(kelas => {
                    kelasSelect.append(
                        `<option value="${kelas.id}">${kelas.kelas}</option>`
                    );
                });

                const selectedKelas = kelasSelect.data('selected');
                if (selectedKelas) {
                    kelasSelect.val(selectedKelas);
                    kelasSelect.removeData('selected');
                }
            }
        }
    });
});

