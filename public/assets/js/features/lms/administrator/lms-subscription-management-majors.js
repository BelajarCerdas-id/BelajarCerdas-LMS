function managementMajorsSchoolSubscription() {
    const container = document.getElementById('container-management-major-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;

    fetchMajors(schoolName, schoolId, role);

    function fetchMajors() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/role-account/${role}/management-majors/paginate`,
            method: 'GET',
            success: function (response) {
                const containerMajorList = $('#grid-management-major-list');
                containerMajorList.empty();

                if (response.data.length > 0) {
                    const schoolDetailCard = document.getElementById('school-detail-card');
                    const schoolIdentity = response.schoolIdentity;

                    const totalClass = response.data.reduce((total, kelas) => {
                        return total + kelas.school_class_count;
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
                                    <p class="text-xs text-[#0071BC] mb-1">Total Kelas Aktif</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${totalClass}</p>
                                </div>

                            </div>
                        </div>
                    `;
                    $.each(response.data, function (index, item) {

                        const lmsManagementClass = response.lmsManagementClass.replace(':schoolName', schoolIdentity.nama_sekolah).replace(':schoolId', schoolIdentity.id).replace(':role', role)
                            .replace(':majorId', item.id);

                        const card = `
                            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg transition duration-300 flex flex-col justify-between">

                                <!-- HEADER -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-layer-group text-gray-400"></i>
                                        <span class="text-xs font-semibold px-3 py-1 rounded-md bg-green-100 text-green-700">
                                            Jurusan
                                        </span>
                                    </div>

                                    <div class="dropdown dropdown-left">
                                        <div tabindex="0" role="button">
                                            <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                        </div>
                                            <ul tabindex="0"
                                                class="dropdown-content menu rounded-box w-max p-2 shadow-lg z-9999 bg-white">
                                                <li class="text-xs">
                                                    <a href="${lmsManagementClass}"
                                                    class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100">
                                                        <i class="fa-solid fa-users-gear text-[#4189E0] font-bold"></i>
                                                        Kelola Kelas
                                                    </a>
                                                </li>
                                                <li class="text-xs">
                                                    <a href=""
                                                    class="btn-edit-major flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100"
                                                    data-major-id="${item.id}" data-major-name="${item.major_name}" data-major-code="${item.major_code}">
                                                        <i class="fa-solid fa-pen text-[#4189E0] font-bold"></i>
                                                        Edit Jurusan
                                                    </a>
                                                </li>
                                            </ul>
                                    </div>
                                </div>

                                <!-- NAMA JURUSAN -->
                                <div>
                                    <span class="text-md font-bold text-gray-800 mb-1">
                                        ${item.major_name}
                                    </span>
                                    <p class="text-sm text-gray-500">
                                        Kode Jurusan: <span class="font-semibold text-gray-700">${item.major_code}</span>
                                    </p>
                                </div>

                                <!-- FOOTER -->
                                <div class="flex items-center justify-between mt-6">
                                    <!-- TOTAL KELAS AKTIF SETIAP JURUSAN -->
                                    <div>
                                        <p class="text-xs text-gray-500">Total Kelas Aktif</p>
                                        <p class="text-lg font-bold text-[#4189E0]">
                                            ${item.school_class_count}
                                        </p>
                                    </div>

                                    <!-- TOGGLE STATUS -->
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs text-gray-500">Status</p>
                                            <span class="text-gray-500 opacity-50"> | </span>
                                            <span class="text-xs font-medium ${item.status_major === 'active' ? 'text-green-600' : 'text-gray-400'}">
                                                ${item.status_major === 'active' ? 'Aktif' : 'Tidak Aktif'}
                                            </span>
                                        </div>

                                        <div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="hidden peer toggle-activate-major"
                                                    data-id="${item.id}"
                                                    ${item.status_major === 'active' ? 'checked' : ''} />
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
                            </div>
                        `;

                        containerMajorList.append(card);
                    });

                    $('#school-detail-card').show();
                    $('#empty-message-management-majors-list').hide();
                } else {
                    $('#school-detail-card').hide();
                    $('#empty-message-management-majors-list').show();
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
}

$(document).ready(function () {
    managementMajorsSchoolSubscription();
});

// activate major
$(document).on('change', '.toggle-activate-major', function () {
    let id = $(this).data('id'); // Ambil ID major dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive

    $.ajax({
        url: `/lms/school-subscription/management-class/${id}/activate-major`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status_major: status // Kirim status baru (active / inactive)
        },
        success: function (response) {
            managementMajorsSchoolSubscription();
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});


// Event listener tombol "tambah jurusan" (open modal)
$(document).off('click', '.btn-create-major').on('click', '.btn-create-major', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

let isProcessing = false;

// Form Action create major
$('#submit-button-create-major').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-major-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    const form = $('#form-create-major-lms-subscription')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-majors/create`,
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

                $('#alert-success-create-majors').html(`
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

                $('#form-create-major-lms-subscription')[0].reset();

                managementMajorsSchoolSubscription();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-create-major-lms-subscription').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-create-major-lms-subscription').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit jurusan" (open modal)
$(document).off('click', '.btn-edit-major').on('click', '.btn-edit-major', function (e) {
    e.preventDefault();

    const majorId = $(this).data('major-id');
    const majorName = $(this).data('major-name');
    const majorCode = $(this).data('major-code');

    // set value ke form
    $('#edit-major-id').val(majorId);
    $('#edit-major-name').val(majorName);
    $('#edit-major-code').val(majorCode);

    // buka modal
    const modal = document.getElementById('my_modal_2');
    if (modal) modal.showModal();
});

// form edit major
$('#submit-button-edit-major').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-major-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;

    const majorId = $('#edit-major-id').val();

    const form = $('#form-edit-major-lms-subscription')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-majors/${majorId}/edit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Menutup modal
            const modal = document.getElementById('my_modal_2');
            if (modal) {
                modal.close();

                $('#alert-success-edit-major').html(
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

                // Memanggil fungsi untuk memuat ulang data
                managementMajorsSchoolSubscription();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-edit-major-lms-subscription').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-edit-major-lms-subscription').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});