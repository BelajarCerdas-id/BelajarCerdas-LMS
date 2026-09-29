let studentClassData = [];
let studentSearchTimer = null;
let selectedStudentIds = new Set();
let isProcessing = false;

// show loading skeleton
function showStudentSkeleton() {
    $('#student-skeleton').removeClass('hidden');
    $('#student-list-items').addClass('hidden');
    $('#student-empty').addClass('hidden');
}

// hide loading skeleton
function hideStudentSkeleton() {
    $('#student-skeleton').addClass('hidden');
    $('#student-list-items').removeClass('hidden');
}

// load manage student form
function manageStudentForm(schoolId, search = '', preserveClassId = null) {
    const container = document.getElementById('container');
    if (!container || !schoolId) return;

    const role = container.dataset.role;
    const periodId = container.dataset.periodId;
    const sessionId = container.dataset.sessionId;
    if (!role || !periodId || !sessionId) return;

    const classSelect = $('#select-class');
    const currentClassId = preserveClassId || classSelect.val();

    showStudentSkeleton();

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/session-form/${periodId}/manage-students/${sessionId}/${schoolId}/form`,
        method: 'GET',
        data: { search },
        success: function(response) {
            if (!response.success || !response.data?.length) {
                $('#student-list-items').empty();
                $('#student-content').removeClass('hidden');
                $('#student-initial-state').addClass('hidden');
                $('#student-empty').removeClass('hidden');

                hideStudentSkeleton();
                updateStudentCount();

                return;
            }

            classSelect.empty();

            studentClassData = response.data;

            response.data.forEach(function(item) {
                classSelect.append(`<option value = "${item.id}" > ${ item.kelas ?? '-' }</option>`);
            });

            classSelect.prop('disabled', false).removeClass('bg-slate-50 text-slate-400 cursor-default').addClass('bg-white text-slate-700 cursor-pointer');

            let targetClassId = currentClassId;
            const currentClassExists = response.data.some(function(item) {
                return String(item.id) === String(currentClassId);
            });

            if (!currentClassExists) {
                targetClassId = response.data[0].id;
            }

            // ambil kelas pertama
            classSelect.val(targetClassId);

            const selectedClass = studentClassData.find(function(item) {
                return String(item.id) === String(targetClassId);
            });

            const students = selectedClass?.students ?? [];

            students.forEach(function(student) {
                const studentId = String(student.student_id);
                if (student.is_selected === true) selectedStudentIds.add(studentId);
            });

            $('#student-initial-state').addClass('hidden');
            $('#student-content').removeClass('hidden');
            renderStudentList(students);
            hideStudentSkeleton();
        },
        error: function(xhr) {
            console.log(xhr);
            studentClassData = [];
            classSelect.empty().append('<option value="">Gagal memuat kelas</option>').prop('disabled', true);

            $('#student-list-items').empty();
            $('#student-content').removeClass('hidden');
            $('#student-initial-state').addClass('hidden');
            $('#student-empty').removeClass('hidden');
            hideStudentSkeleton();
            updateStudentCount();
        }
    });
}

// load student list
function renderStudentList(students) {
    const studentList = $('#student-list-items');
    studentList.empty();

    if (!students.length) {
        $('#student-empty').removeClass('hidden');
        hideStudentSkeleton();
        updateStudentCount();
        return;
    }

    $('#student-empty').addClass('hidden');

    students.forEach(function(student) {
        const user = student.user_account ?? {};
        const profile = user.student_profile ?? {};
        const studentId = String(student.student_id);
        const name = profile.nama_lengkap ?? '-';
        const rombelClass = student.school_class?.class_name ?? '-';
        const isSelected = selectedStudentIds.has(studentId);

        studentList.append(`
            <label class="student-item flex cursor-pointer items-center gap-3 px-4 py-3.5 transition hover:bg-slate-50" data-student-id="${studentId}" data-student-name="${name}">
                <input type="checkbox" name="student_ids[]" value="${studentId}" class="student-checkbox checkbox checkbox-sm rounded-md border-slate-300 [--chkbg:#0071BC] 
                [--chkfg:white]" ${isSelected ? 'checked' : ''}>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                    <i class="fa-solid fa-user text-sm text-[#0071BC]"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-700">${name}</p>
                    <p class="mt-0.5 text-xs text-slate-400">Kelas: ${rombelClass}</p>
                </div>
            </label>
        `);
    });

    hideStudentSkeleton();
    updateStudentCount();
}

// reset student state
function resetStudentState(clearSelection = false) {
    clearTimeout(studentSearchTimer);

    $('#student-content').addClass('hidden');
    $('#student-initial-state').removeClass('hidden');
    $('#target-summary').addClass('hidden');
    $('#student-search').val('');
    $('#btn-clear-student-search').addClass('hidden').removeClass('flex');
    $('#student-select-all').prop('checked', false).prop('indeterminate', false);
    $('#student-list-items').empty();
    $('#student-empty').addClass('hidden');

    if (clearSelection) selectedStudentIds.clear();

    showStudentSkeleton();
    updateStudentCount();
}

// update student count
function updateStudentCount() {
    const items = $('#student-list-items .student-item');
    const total = items.length;
    const visibleSelected = $('#student-list-items .student-checkbox:checked').length;
    const totalSelected = selectedStudentIds.size;

    $('#student-total').text(total);
    $('#student-visible-count').text(`${total} siswa`);
    $('#student-selected-count').text(totalSelected);
    $('#save-selected-count').text(totalSelected);

    $('#student-select-all').prop('checked', total > 0 && visibleSelected === total).prop('indeterminate', visibleSelected > 0 && visibleSelected < total);
}

// document ready
$(document).ready(function() {
    const schoolId = $('#select-school').val();
    if (schoolId) manageStudentForm(schoolId);
});

// select school
$(document).on('change', '#select-school', function() {
    const schoolId = $(this).val();

    resetStudentState(true);

    if (!schoolId) {
        studentClassData = [];
        $('#select-class').empty().append('<option value="">Pilih sekolah terlebih dahulu</option>').prop('disabled', true);
        return;
    }

    manageStudentForm(schoolId);
});

// select class
$(document).on('change', '#select-class', function() {
    const classId = $(this).val();

    if (!classId) {
        $('#student-content').addClass('hidden');
        $('#student-initial-state').removeClass('hidden');
        $('#student-list-items').empty();
        $('#student-empty').addClass('hidden');
        updateStudentCount();
        return;
    }

    const selectedClass = studentClassData.find(function(item) {
        return String(item.id) === String(classId);
    });

    if (!selectedClass) return;

    const students = selectedClass.students ?? [];

    students.forEach(function(student) {
        const studentId = String(student.student_id);
        if (student.is_selected === true) selectedStudentIds.add(studentId);
    });

    $('#student-initial-state').addClass('hidden');
    $('#student-content').removeClass('hidden');
    renderStudentList(students);
});

// select student
$(document).on('change', '.student-checkbox', function() {
    const studentId = String($(this).val());

    if ($(this).is(':checked')) {
        selectedStudentIds.add(studentId);
    } else {
        selectedStudentIds.delete(studentId);
    }

    updateStudentCount();
});

// select all student
$(document).on('change', '#student-select-all', function() {
    const checked = $(this).is(':checked');

    $('#student-list-items .student-item:visible').each(function() {
        const checkbox = $(this).find('.student-checkbox');
        const studentId = String(checkbox.val());

        checkbox.prop('checked', checked);

        if (checked) {
            selectedStudentIds.add(studentId);
        } else {
            selectedStudentIds.delete(studentId);
        }
    });

    updateStudentCount();
});

// search student
$(document).on('input', '#student-search', function() {
    const keyword = $(this).val().trim();
    const schoolId = $('#select-school').val();
    const currentClassId = $('#select-class').val();

    clearTimeout(studentSearchTimer);

    if (!schoolId) return;

    studentSearchTimer = setTimeout(function() {
        manageStudentForm(schoolId, keyword, currentClassId);
    }, 400);
});

// save student
$(document).on('click', '#btn-save-students', function(e) {
    e.preventDefault();

    if (isProcessing) return;

    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const periodId = container.dataset.periodId;
    const sessionId = container.dataset.sessionId;

    if (!role || !periodId || !sessionId) return;

    const schoolId = $('#select-school').val();
    const classId = $('#select-class').val();

    if (!schoolId || !classId) return;

    const form = $('#tka-tryout-period-session-manage-student-form')[0];
    const formData = new FormData(form);

    formData.delete('student_ids[]');

    selectedStudentIds.forEach(function(studentId) {
        formData.append('student_ids[]', studentId);
    });

    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/session-form/${periodId}/manage-students/${sessionId}/submit-form`,
        method: 'POST',
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#alert-success-tka-tryout-period-session-save-students').html(`
                <div class="w-full flex justify-center">
                    <div class="fixed z-9999">
                        <div id="alertSuccess"
                            class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-600 text-sm">
                                ${response.message}
                            </span>
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

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors ?? {};
                const formElement = $('#tka-tryout-period-session-manage-student-form');

                formElement.find('[id^="error-"]').text('').addClass('hidden');
                formElement.find('select, input').removeClass('border-red-400');

                $.each(errors, function (field, messages) {
                    const errorElement = formElement.find(`#error-${field}`);

                    if (errorElement.length) {
                        errorElement.text(messages[0]).removeClass('hidden');
                    }

                    if (field === 'student_ids') {
                        formElement.find('.student-checkbox').addClass('border-red-400');
                    } else {
                        formElement.find(`[name="${field}"]`).addClass('border-red-400');
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