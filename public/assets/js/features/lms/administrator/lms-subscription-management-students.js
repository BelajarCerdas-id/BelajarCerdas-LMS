window.__shouldResetSelection = false;
function managementStudentSchoolSubscription() {
    const container = document.getElementById('container-management-lms-students-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const majorId = container.dataset.majorId;
    const classId = container.dataset.classId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    if (!classId) return;

    fetchUsers(schoolName, schoolId, role, majorId, classId);

    function fetchUsers() {
        $.ajax({
            url: majorId
                ? `/lms/school-subscription/${schoolName}/${schoolId}/role-account/${role}/management-class/${classId}/management-majors/${majorId}/management-students/paginate`
                : `/lms/school-subscription/${schoolName}/${schoolId}/role-account/${role}/management-class/${classId}/management-students/paginate`,
            method: 'GET',
            success: function (response) {
                const selectedIds = getSelectedStudentIds();

                window.__studentsData = response.data;

                $('#table-management-lms-students-list').empty(); // Clear previous entries
                $('.pagination-container-management-level').empty(); // Clear previous pagination links

                if (response.data.length > 0) {
                    const schoolDetailCard = document.getElementById('school-detail-card');
                    const detailClass = document.getElementById('class-teacher-card');
                    const BulkActionPromoteClass = document.getElementById('container-bulk-action-promote-to-next-class');
                    const schoolIdentity = response.schoolIdentity;

                    schoolDetailCard.classList.remove('hidden');
                    detailClass.classList.remove('hidden');

                    let containerMajorTransferred = '';

                    if (majorId) {
                        containerMajorTransferred = `
                            <button class="btn-move-major w-full text-left px-4 py-3 text-sm hover:bg-gray-50 flex gap-3 items-center cursor-pointer"
                                data-school-partner-id="${schoolId}" data-class-name="${response.data[0]?.school_class?.class_name}" 
                                data-tahun-ajaran="${response.data[0]?.school_class?.tahun_ajaran}">
                                <i class="fa-solid fa-diagram-project text-indigo-600"></i>
                                <span class="font-medium text-gray-800">Pindah Jurusan</span>
                            </button>
                        `;
                    } else {
                        containerMajorTransferred = ``;
                    }

                    BulkActionPromoteClass.innerHTML = `
                        <div
                            class="mb-4 bg-[#EEF6FF] border border-[#CFE2FF] rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            <div class="flex items-center gap-2 text-[#4189E0] text-sm font-medium">
                                <i class="fa-solid fa-users"></i>
                                <span>
                                    <span id="selected-count">0</span> siswa dipilih
                                </span>
                            </div>

                            <div class="relative">
                                <button id="btn-academic-action"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                        rounded-lg bg-[#4189E0] text-white hover:bg-blue-600 transition cursor-pointer">
                                    <i class="fa-solid fa-gear"></i>
                                    Aksi Akademik
                                    <i class="fa-solid fa-chevron-down text-xs arrow-icon transition-transform duration-400 rotate-0"></i>
                                </button>

                                <div id="dropdown-academic-action"
                                    class="hidden absolute left-0 sm:left-auto sm:right-0 mt-2 w-56 bg-white border border-gray-300 rounded-xl shadow-lg z-50">

                                    <button class="btn-promote-to-next-class w-full text-left px-4 py-3 text-sm hover:bg-gray-50 flex gap-3 items-center cursor-pointer"
                                        data-school-partner-id="${schoolId}" data-class-name="${response.data[0]?.school_class?.class_name}" 
                                        data-tahun-ajaran="${response.data[0]?.school_class?.tahun_ajaran}">
                                        <i class="fa-solid fa-arrow-up text-green-600"></i>
                                        <span class="font-medium text-gray-800">Naik Kelas</span>
                                    </button>

                                    <button class="btn-repeat-class w-full text-left px-4 py-3 text-sm hover:bg-gray-50 flex gap-3 items-center cursor-pointer"
                                        data-school-partner-id="${schoolId}" data-class-name="${response.data[0]?.school_class?.class_name}" 
                                        data-tahun-ajaran="${response.data[0]?.school_class?.tahun_ajaran}">
                                        <i class="fa-solid fa-rotate-left text-yellow-600"></i>
                                        <span class="font-medium text-gray-800">Mengulang Kelas</span>
                                    </button>

                                    <button class="btn-move-class w-full text-left px-4 py-3 text-sm hover:bg-gray-50 flex gap-3 items-center cursor-pointer"
                                        data-school-partner-id="${schoolId}" data-class-name="${response.data[0]?.school_class?.class_name}" 
                                        data-tahun-ajaran="${response.data[0]?.school_class?.tahun_ajaran}">
                                        <i class="fa-solid fa-right-left text-blue-600"></i>
                                        <span class="font-medium text-gray-800">Pindah Kelas</span>
                                    </button>

                                    ${containerMajorTransferred}
                                </div>
                            </div>
                        </div>
                    `;

                    schoolDetailCard.innerHTML = `
                        <div class="flex flex-col xl:flex-row xl:items-center lg:justify-between gap-6">

                            <!-- KIRI : ICON + NAMA SEKOLAH -->
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#EEF6FF] flex items-center justify-center text-[#4189E0] text-2xl shadow-sm">
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <div>
                                    <h2 class="text-sm md:text-lg font-bold text-gray-800 leading-tight">
                                        ${schoolIdentity.nama_sekolah}
                                    </h2>
                                    <p class="text-xs md:text-sm text-gray-500">
                                        Detail langganan LMS sekolah
                                    </p>
                                </div>
                            </div>

                            <!-- KANAN : INFO SEKOLAH -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full lg:w-auto">

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-gray-500 mb-1">NPSN</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.npsn}</p>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-gray-500 mb-1">NIK Kepala Sekolah</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.user_account?.school_staff_profile?.nik}</p>
                                </div>

                                <div class="bg-[#EEF6FF] rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-[#4189E0] mb-1">Total Siswa</p>
                                    <p class="font-bold text-2xl text-[#4189E0]">${response.data.length}</p>
                                </div>
                            </div>
                        </div>
                    `;

                    let detailMajor = '';

                    if (majorId) {
                        detailMajor = `
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center text-xl">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Jurusan</p>
                                    <p class="font-semibold text-gray-800">
                                        ${response.data[0]?.school_class?.school_major?.major_name ?? '-'}
                                    </p>
                                </div>
                            </div>
                        `;
                    } else {
                        detailMajor = ``;
                    }

                    detailClass.innerHTML = `
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                            <!-- GURU -->
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center text-xl">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Wali Kelas</p>
                                    <p class="font-semibold text-gray-800">
                                        ${response.data[0]?.school_class?.user_account?.school_staff_profile?.nama_lengkap}
                                    </p>
                                </div>
                            </div>

                            <!-- JURUSAN -->
                            ${detailMajor}

                            <!-- KELAS -->
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl">
                                    <i class="fa-solid fa-door-open"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Kelas</p>
                                    <p class="font-semibold text-gray-800">
                                        ${response.data[0]?.school_class?.class_name}
                                    </p>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN -->
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Tahun Ajaran</p>
                                    <p class="font-semibold text-gray-800">
                                        ${response.data[0]?.school_class?.tahun_ajaran}
                                    </p>
                                </div>
                            </div>

                        </div>
                    `;

                    $.each(response.data, function (index, item) {
                        let status_academic_action = '';

                        if (item.academic_action === 'PROMOTED_CLASS') {
                            status_academic_action = 'Naik Kelas';
                        } else if (item.academic_action === 'REPEATED_CLASS') {
                            status_academic_action = 'Mengulang Kelas';
                        } else if (item.academic_action === 'TRANSFERRED_CLASS') {
                            status_academic_action = 'Pindah Kelas';
                        } else if (item.academic_action === 'TRANSFERRED_MAJOR') {
                            status_academic_action = 'Pindah Jurusan';
                        } else {
                            status_academic_action = '-';
                        }

                        $('#table-management-lms-students-list').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <input type="checkbox" class="student-checkbox w-4 h-4 rounded border-gray-300 text-[#4189E0] cursor-pointer" value="${item.id}" data-user-id="${item.user_account?.id}">
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${index + 1}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.student_profile?.nama_lengkap}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.student_profile?.enrollment_type}</td>
                                <td class="border text-center border-gray-300">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-activate-student-in-class"
                                            data-id="${item.id}"
                                            ${item.student_class_status === 'active' ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                            <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    ${status_academic_action}
                                </td>
                            </tr>
                        `);
                    });

                    if (window.__shouldResetSelection) {
                        resetStudentSelection();
                        window.__shouldResetSelection = false;
                    } else {
                        restoreSelectedStudents(selectedIds);
                    }

                    // Append pagination links
                    $('#empty-message-lms-students').hide(); // sembunyikan pesan kosong
                    $('.thead-table-management-lms-students').show(); // Tampilkan tabel thead
                } else {
                    $('#table-management-lms-students-list').empty(); // Clear existing rows
                    $('#empty-message-lms-students').show(); // Tampilkan pesan kosong
                    $('.thead-table-management-lms-students').hide(); // sembunyikan tabel thead
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
}

$(document).ready(function () {
    managementStudentSchoolSubscription();
});

// function sweet alert jika siswa suda memiliki aksi akademik
function academicActionHasBeenAlert() {
    Swal.fire({
        icon: 'warning',
        title: 'Tidak Bisa Diproses',
        text: 'tidak dapat menggunakan akasi akademik kembali pada siswa yang telah memiliki keterangan.',
        confirmButtonText: 'OK'
    });
}

function toggleStatusStudentInClassAlert() {
    Swal.fire({
        icon: 'warning',
        title: 'Tidak Bisa Diproses',
        text: 'tidak dapat mengaktifkan status siswa di kelas pada siswa yang telah memiliki keterangan.',
        confirmButtonText: 'OK'
    });
}

// function guard untuk mengecek apakah siswa sudah memiliki aksi akademik, jika sudah ada maka disable toggle activate
function guardToggleStatusStudentInClass() {
    const toggle = $('.toggle-activate-student-in-class');

    toggle.each(function () {
        const $checkbox = $(this);
        const userId = $checkbox.data('id');
        const student = window.__studentsData?.find(s => s.id == userId);

        if (student?.has_academic_action) {
            $checkbox.prop('checked', false);
            toggleStatusStudentInClassAlert();
        }
    });

    return true;
}

// function reset student selection
function resetStudentSelection() {
    $('.student-checkbox').prop('checked', false);
    $('#check-all').prop('checked', false);
    $('#selected-count').text(0);
    $('#container-bulk-action-promote-to-next-class').addClass('hidden');
}

// function onchnage
$(document).on('click', '.toggle-activate-student-in-class', function (e) {
    const userId = $(this).data('id');
    const student = window.__studentsData?.find(s => s.id == userId);

    if (student?.has_academic_action) {
        e.preventDefault(); // cegah toggle
        guardToggleStatusStudentInClass();
    }
});


// function guard untuk mengecek apakah siswa sudah memiliki aksi akademik
function guardAcademicAction($checkbox) {
    const userId = $checkbox.data('user-id');
    const student = window.__studentsData?.find(s => s.student_id == userId);

    if (student?.has_academic_action) {
        $checkbox.prop('checked', false);
        academicActionHasBeenAlert();
        return false;
    }

    return true;
}

// function update selection UI student checkbox
function updateSelectionUI() {
    const count = $('.student-checkbox:checked').length;

    $('#selected-count').text(count);

    if (count > 0) {
        $('#container-bulk-action-promote-to-next-class').removeClass('hidden');
        $('.btn-promote-to-next-class').prop('disabled', false);
    } else {
        $('#container-bulk-action-promote-to-next-class').addClass('hidden');
        $('.btn-promote-to-next-class').prop('disabled', true);
    }

    $('#check-all').prop(
        'checked',
        $('.student-checkbox').length === count
    );
}

// function get selected student ids
function getSelectedStudentIds() {
    return $('.student-checkbox:checked')
        .map(function () {
            return $(this).val();
        })
        .get();
}

// function restore selected students
function restoreSelectedStudents(selectedIds) {
    $('.student-checkbox').each(function () {
        if (selectedIds.includes($(this).val())) {
            $(this).prop('checked', true);
        }
    });

    updateSelectionUI();
}

// function select one student checkbox
$(document).on('change', '.student-checkbox', function () {
    if ($(this).is(':checked')) {
        if (!guardAcademicAction($(this))) return;
    }

    updateSelectionUI();
});

// function select all students checkbox
$(document).on('change', '#check-all', function () {
    if (this.checked) {
        $('.student-checkbox').each(function () {
            if (!guardAcademicAction($(this))) return;
            $(this).prop('checked', true);
        });
    } else {
        $('.student-checkbox').prop('checked', false);
    }

    updateSelectionUI();
});

// dropdown script
$(document).on('click', '#btn-academic-action', function (e) {
    e.stopPropagation();
    $('.arrow-icon').toggleClass('-rotate-180');
    $('#dropdown-academic-action').toggleClass('hidden');
});

// klik di luar → close
$(document).on('click', function (e) {
    if (!$(e.target).closest('#btn-academic-action, #dropdown-academic-action').length) {
        $('.arrow-icon').removeClass('rotate-180');
        $('#dropdown-academic-action').addClass('hidden');
    }
});

// activate student in class
$(document).on('change', '.toggle-activate-student-in-class', function () {
    let id = $(this).data('id'); // Ambil ID student school class dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive

    $.ajax({
        url: `/lms/school-subscription/management-class/${id}/activate-student-in-class`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            student_class_status: status // Kirim status baru (active/inactive)
        },
        success: function (response) {
            // tampilkan pesan berhasil
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});

// Event listener tombol "naik kelas" (open modal)
$(document).off('click', '.btn-promote-to-next-class').on('click', '.btn-promote-to-next-class', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

$(document).off('click', '.btn-promote-to-next-class').on('click', '.btn-promote-to-next-class', function (e) {
    e.preventDefault();

    const class_name = $(this).data('class-name');
    const tahun_ajaran = $(this).data('tahun-ajaran');
    const selected = $('.student-checkbox:checked');
    const count = selected.length;

    const container = document.getElementById('container-management-lms-students-list');
    const schoolId = container.dataset.schoolId;
    const classId = container.dataset.classId;
    const majorId = container.dataset.majorId;

    const studentIds = [];
    selected.each(function () {
        studentIds.push($(this).data('user-id'));
    });

    $('#student-ids-promote-class').val(studentIds.join(','));

    $('#from-class-name').text(class_name);
    $('#from-class-year').text(tahun_ajaran);
    $('#promote-student-count').text(count);

    // add value to hidden input
    $('#school-partner-id-promote-class').val(schoolId);
    $('#major-id-promote-class').val(majorId);

    const classSelect = $('#target-class-id-promote');

    classSelect.empty().prop('disabled', true).removeClass('cursor-pointer').addClass('cursor-default').removeClass('opacity-100').addClass('opacity-50');

    // set url
    let url = '';

    if (majorId) {
        url = `/lms/school/${schoolId}/${majorId}/promotion-to-next-class-options`;
    } else {
        url = `/lms/school/${schoolId}/promotion-to-next-class-options`;
    }

    // AMBIL OPSI KELAS TUJUAN DI SINI
    $.get(`${url}`, { class_id: classId }, function (classes) {
        const yearSelect = $('#target-school-year-promote');
        const classSelect = $('#target-class-id-promote');

        yearSelect.empty();
        classSelect.empty().prop('disabled', true).addClass('cursor-default');
        yearSelect.append(`<option value="" class="hidden">Pilih Tahun Ajaran </option>`);
        classSelect.append(`<option value="" class="hidden">Pilih Kelas Tujuan</option>`);

        const grouped = {};

        classes.forEach(item => {
            if (!grouped[item.tahun_ajaran]) {
                grouped[item.tahun_ajaran] = [];
            }
            grouped[item.tahun_ajaran].push(item);
        });

        Object.keys(grouped).forEach(year => {
            yearSelect.append(`<option value="${year}">${year}</option>`);
        });

        // simpan cache global
        window.__promotionClasses = grouped;
    }
    );

    // buka modal
    document.getElementById('my_modal_1').showModal();
});

// dropdown bertingkat tahun ajaran -> kelas
$('#target-school-year-promote').on('change', function () {
    const year = $(this).val();
    const classSelect = $('#target-class-id-promote');

    classSelect.prop('disabled', false);
    classSelect.removeClass('cursor-default').addClass('cursor-pointer').removeClass('opacity-50').addClass('opacity-100');
    classSelect.empty().append(`<option value="" class="hidden">Pilih Kelas Tujuan</option>`);

    if (!year || !window.__promotionClasses[year]) return;

    window.__promotionClasses[year].forEach(cls => {
        classSelect.append(`
            <option value="${cls.id}">
                ${cls.class_name}
            </option>
        `);
    });
});

// Form Action promote class
let isProcessing = false;
$('#submit-button-promote-class').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-lms-students-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const classId = container.dataset.classId;
    const majorId = container.dataset.majorId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    if (!classId) return;
    const form = $('#form-promote-students')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-class/${classId}/promote-class`,
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

                $('#alert-success-promote-to-next-class').html(`
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

                $('#form-promote-students')[0].reset();

                window.__shouldResetSelection = true;
                managementStudentSchoolSubscription();

                isProcessing = false;
                btn.prop('disabled', false);

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            }
        },
        error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-promote-students').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-promote-students').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            }
            // ERROR LOGIKA BISNIS (PROMOTED CLASS SUDAH ADA)
            else if (xhr.status === 422 && xhr.responseJSON?.studentSchoolClassCheck) {
                const modal = document.getElementById('my_modal_1');

                if (modal) {
                    modal.close();
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Aksi tidak diizinkan',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });

                managementStudentSchoolSubscription();

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            }
            else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "mengulang kelas" (open modal)
$(document).off('click', '.btn-repeat-class').on('click', '.btn-repeat-class', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('my_modal_2');
    if (modal) modal.showModal();
});

$(document).off('click', '.btn-repeat-class').on('click', '.btn-repeat-class', function (e) {
    e.preventDefault();

    const class_name = $(this).data('class-name');
    const tahun_ajaran = $(this).data('tahun-ajaran');
    const selected = $('.student-checkbox:checked');
    const count = selected.length;

    const container = document.getElementById('container-management-lms-students-list');
    const schoolId = container.dataset.schoolId;
    const classId = container.dataset.classId;
    const majorId = container.dataset.majorId;

    const studentIds = [];
    selected.each(function () {
        studentIds.push($(this).data('user-id'));
    });

    $('#student-ids-repeat-class').val(studentIds.join(','));

    $('#from-class-name-repeat').text(class_name);
    $('#from-class-year-repeat').text(tahun_ajaran);
    $('#student-count-repeat-class').text(count);

    // add value to hidden input
    $('#school-partner-id-repeat-class').val(schoolId);
    $('#major-id-repeat-class').val(majorId);

    const classSelect = $('#target-class-id-repeat');

    classSelect.empty().prop('disabled', true).removeClass('cursor-pointer').addClass('cursor-default').removeClass('opacity-100').addClass('opacity-50');

    // set url
    let url = '';

    if (majorId) {
        url = `/lms/school/${schoolId}/${majorId}/repeat-class-options`;
    } else {
        url = `/lms/school/${schoolId}/repeat-class-options`;
    }

    // AMBIL OPSI KELAS TUJUAN DI SINI
    $.get(`${url}`, { class_id: classId }, function (classes) {
        const yearSelect = $('#target-school-year-repeat');
        const classSelect = $('#target-class-id-repeat');

        yearSelect.empty();
        classSelect.empty().prop('disabled', true).addClass('cursor-default');
        yearSelect.append(`<option value="" class="hidden">Pilih Tahun Ajaran </option>`);
        classSelect.append(`<option value="" class="hidden">Pilih Kelas Tujuan</option>`);

        const grouped = {};

        classes.forEach(item => {
            if (!grouped[item.tahun_ajaran]) {
                grouped[item.tahun_ajaran] = [];
            }
            grouped[item.tahun_ajaran].push(item);
        });

        Object.keys(grouped).forEach(year => {
            yearSelect.append(`<option value="${year}">${year}</option>`);
        });

        // simpan cache global
        window.__promotionClasses = grouped;
    }
    );

    // buka modal
    document.getElementById('my_modal_2').showModal();
});

// dropdown bertingkat tahun ajaran -> kelas
$('#target-school-year-repeat').on('change', function () {
    const year = $(this).val();
    const classSelect = $('#target-class-id-repeat');

    classSelect.prop('disabled', false);
    classSelect.removeClass('cursor-default').addClass('cursor-pointer').removeClass('opacity-50').addClass('opacity-100');
    classSelect.empty().append(`<option value="" class="hidden">Pilih Kelas Tujuan</option>`);

    if (!year || !window.__promotionClasses[year]) return;

    window.__promotionClasses[year].forEach(cls => {
        classSelect.append(`
            <option value="${cls.id}">
                ${cls.class_name}
            </option>
        `);
    });
});

// Form Action repeat class
$('#submit-button-repeat-class').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-lms-students-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const classId = container.dataset.classId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    if (!classId) return;
    const form = $('#form-repeat-class-students')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-class/${classId}/repeat-class`,
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

                $('#alert-success-repeat-class').html(`
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

                $('#form-repeat-class-students')[0].reset();

                window.__shouldResetSelection = true;
                managementStudentSchoolSubscription();

                isProcessing = false;
                btn.prop('disabled', false);

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            }
        },
        error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-repeat-class-students').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-repeat-class-students').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            }
            // ERROR LOGIKA BISNIS (PROMOTED CLASS SUDAH ADA)
            else if (xhr.status === 422 && xhr.responseJSON?.studentSchoolClassCheck) {
                const modal = document.getElementById('my_modal_2');

                if (modal) {
                    modal.close();
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Aksi tidak diizinkan',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });

                managementStudentSchoolSubscription();

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            }
            else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "pindah kelas" (open modal)
$(document).off('click', '.btn-move-class').on('click', '.btn-move-class', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('my_modal_3');
    if (modal) modal.showModal();
});

$(document).off('click', '.btn-move-class').on('click', '.btn-move-class', function (e) {
    e.preventDefault();

    const class_name = $(this).data('class-name');
    const tahun_ajaran = $(this).data('tahun-ajaran');
    const selected = $('.student-checkbox:checked');
    const count = selected.length;

    const container = document.getElementById('container-management-lms-students-list');
    const schoolId = container.dataset.schoolId;
    const classId = container.dataset.classId;
    const majorId = container.dataset.majorId;

    const studentIds = [];
    selected.each(function () {
        studentIds.push($(this).data('user-id'));
    });

    $('#student-ids-move-class').val(studentIds.join(','));

    $('#from-class-name-move').text(class_name);
    $('#from-class-year-move').text(tahun_ajaran);
    $('#student-count-move-class').text(count);

    // add value to hidden input
    $('#school-partner-id-move-class').val(schoolId);
    $('#major-id-move-class').val(majorId);

    const classSelect = $('#target-class-id-move');

    classSelect.empty().prop('disabled', true).removeClass('cursor-pointer').addClass('cursor-default').removeClass('opacity-100').addClass('opacity-50');

    // set url
    let url = '';

    if (majorId) {
        url = `/lms/school/${schoolId}/${majorId}/move-class-options`;
    } else {
        url = `/lms/school/${schoolId}/move-class-options`;
    }

    // AMBIL OPSI KELAS TUJUAN DI SINI
    $.get(`${url}`, { class_id: classId }, function (classes) {
        const yearSelect = $('#target-school-year-move-class');
        const classSelect = $('#target-class-id-move');

        yearSelect.empty();
        classSelect.empty().prop('disabled', true).addClass('cursor-default');
        yearSelect.append(`<option value="" class="hidden">Pilih Tahun Ajaran </option>`);
        classSelect.append(`<option value="" class="hidden">Pilih Kelas Tujuan</option>`);

        const grouped = {};

        classes.forEach(item => {
            if (!grouped[item.tahun_ajaran]) {
                grouped[item.tahun_ajaran] = [];
            }
            grouped[item.tahun_ajaran].push(item);
        });

        Object.keys(grouped).forEach(year => {
            yearSelect.append(`<option value="${year}">${year}</option>`);
        });

        // simpan cache global
        window.__promotionClasses = grouped;
    }
    );

    // buka modal
    document.getElementById('my_modal_3').showModal();
});

// dropdown bertingkat tahun ajaran -> kelas
$('#target-school-year-move-class').on('change', function () {
    const year = $(this).val();
    const classSelect = $('#target-class-id-move');

    classSelect.prop('disabled', false);
    classSelect.removeClass('cursor-default').addClass('cursor-pointer').removeClass('opacity-50').addClass('opacity-100');
    classSelect.empty().append(`<option value="" class="hidden">Pilih Kelas Tujuan</option>`);

    if (!year || !window.__promotionClasses[year]) return;

    window.__promotionClasses[year].forEach(cls => {
        classSelect.append(`
            <option value="${cls.id}">
                ${cls.class_name}
            </option>
        `);
    });
});

// form action pindah kelas
$('#submit-button-move-class').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-lms-students-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const classId = container.dataset.classId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    if (!classId) return;
    const form = $('#form-move-class-students')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-class/${classId}/move-class`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_3');

            if (modal) {
                modal.close();

                $('#alert-success-move-class').html(`
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

                $('#form-move-class-students')[0].reset();

                window.__shouldResetSelection = true;
                managementStudentSchoolSubscription();

                isProcessing = false;
                btn.prop('disabled', false);

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            }
        },
        error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-move-class-students').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-move-class-students').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            }
            // ERROR LOGIKA BISNIS (PROMOTED CLASS SUDAH ADA)
            else if (xhr.status === 422 && xhr.responseJSON?.studentSchoolClassCheck) {
                const modal = document.getElementById('my_modal_3');

                if (modal) {
                    modal.close();
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Aksi tidak diizinkan',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });

                managementStudentSchoolSubscription();

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "pindah jurusan" (open modal)
$(document).off('click', '.btn-move-major').on('click', '.btn-move-major', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('my_modal_4');
    if (modal) modal.showModal();
});

$(document).off('click', '.btn-move-major').on('click', '.btn-move-major', function (e) {
    e.preventDefault();

    const class_name = $(this).data('class-name');
    const tahun_ajaran = $(this).data('tahun-ajaran');
    const selected = $('.student-checkbox:checked');
    const count = selected.length;

    const container = document.getElementById('container-management-lms-students-list');
    const schoolId = container.dataset.schoolId;
    const classId = container.dataset.classId;
    const majorId = container.dataset.majorId;

    const studentIds = [];
    selected.each(function () {
        studentIds.push($(this).data('user-id'));
    });

    $('#student-ids-move-major').val(studentIds.join(','));

    $('#from-class-name-move-major').text(class_name);
    $('#from-class-year-move-major').text(tahun_ajaran);
    $('#student-count-move-major').text(count);

    // add value to hidden input
    $('#school-partner-id-move-major').val(schoolId);

    const majorSelect = $('#target-move-major-id');
    const classSelect = $('#target-move-major-class-id');
    
    majorSelect.empty().prop('disabled', true).removeClass('cursor-pointer').addClass('cursor-default').removeClass('opacity-100').addClass('opacity-50');
    classSelect.empty().prop('disabled', true).removeClass('cursor-pointer').addClass('cursor-default').removeClass('opacity-100').addClass('opacity-50').append(`<option value="" hidden>Pilih Kelas</option>`);

    // set url
    let url = '';

    if (majorId) {
        url = `/lms/school/${schoolId}/${majorId}/move-major-options`;
    } else {
        url = `/lms/school/${schoolId}/move-major-options`;
    }

    // AMBIL OPSI KELAS TUJUAN DI SINI
    $.get(url, { class_id: classId }, function (classes) {
        const yearSelect = $('#target-school-year-move-major');
        const majorSelect = $('#target-move-major-id');

        yearSelect.empty();
        majorSelect.empty().prop('disabled', true).addClass('cursor-default');

        yearSelect.append(`<option value="" class="hidden">Pilih Tahun Ajaran </option>`);
        majorSelect.append(`<option value="" class="hidden">Pilih Jurusan Tujuan</option>`);

        const grouped = {};

        classes.forEach(item => {
            if (!grouped[item.tahun_ajaran]) {
                grouped[item.tahun_ajaran] = [];
            }
            grouped[item.tahun_ajaran].push(item);
        });

        Object.keys(grouped).forEach(year => {
            yearSelect.append(`<option value="${year}">${year}</option>`);
        });

        window.__promotionClasses = classes;
    });

    // buka modal
    document.getElementById('my_modal_4').showModal();
});

// dropdown bertingkat tahun ajaran -> majors
$('#target-school-year-move-major').on('change', function () {
    const year = $(this).val();
    const majorSelect = $('#target-move-major-id');

    majorSelect.prop('disabled', false).removeClass('cursor-default').addClass('cursor-pointer').removeClass('opacity-50').addClass('opacity-100')
    majorSelect.empty().append(`<option value="" hidden>Pilih Jurusan Tujuan</option>`);

    if (!year) return;

    const majors = {};

    window.__promotionClasses
        .filter(cls => cls.tahun_ajaran === year)
        .forEach(cls => {
            majors[cls.major_id] = cls.school_major?.major_name + ` (${cls.school_major?.major_code}) `;
        });

    Object.entries(majors).forEach(([id, name]) => {
        majorSelect.append(`<option value="${id}">${name}</option>`);
    });
});


// dropdown bertingkat majors -> class
$('#target-move-major-id').on('change', function () {
    const majorId = $(this).val();
    const classSelect = $('#target-move-major-class-id');

    classSelect.prop('disabled', false).empty().removeClass('cursor-default').addClass('cursor-pointer').removeClass('opacity-50').addClass('opacity-100')
    classSelect.append(`<option value="" hidden>Pilih Kelas</option>`);

    window.__promotionClasses
        .filter(cls => cls.major_id == majorId)
        .forEach(cls => {
            classSelect.append(`<option value="${cls.id}">${cls.class_name}</option>`);
        });
});

// form action pindah jurusan
$('#submit-button-move-major').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-management-lms-students-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;
    const classId = container.dataset.classId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;
    if (!classId) return;
    const form = $('#form-move-major-students')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-class/${classId}/move-major`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_4');

            if (modal) {
                modal.close();

                $('#alert-success-move-major').html(`
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

                $('#form-move-major-students')[0].reset();

                window.__shouldResetSelection = true;
                managementStudentSchoolSubscription();

                isProcessing = false;
                btn.prop('disabled', false);

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            }
        },
        error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-move-major-students').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-move-major-students').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            }
            // ERROR LOGIKA BISNIS (PROMOTED CLASS SUDAH ADA)
            else if (xhr.status === 422 && xhr.responseJSON?.studentSchoolClassCheck) {
                const modal = document.getElementById('my_modal_4');

                if (modal) {
                    modal.close();
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Aksi tidak diizinkan',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });

                managementStudentSchoolSubscription();

                // reset checkbox all
                $('#check-all').prop('checked', false);

                $('#container-bulk-action-promote-to-next-class').addClass('hidden');
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});