function teacherGradebookAssessmentPreview() {
    const container = document.getElementById('container-teacher-gradebook-assessment-preview');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const assessmentTypeId = container.dataset.assessmentTypeId;
    const studentId = container.dataset.studentId;
    const semester = container.dataset.semester;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!subjectTeacherId) return;
    if (!assessmentTypeId) return;
    if (!studentId) return;
    if (!semester) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/gradebook/classes/subject-teacher/${subjectTeacherId}/assessment-type/${assessmentTypeId}/student/${studentId}/preview/semester/${semester}/paginate`,
        method: 'GET',
        success: function (response) {
            $('#tbody-teacher-gradebook-assessment-preview').empty();
            $('.pagination-container-teacher-gradebook-assessment-preview').empty();

            const teacherMapel = response.teacherMapel;
            const gradebookInfo = $('#header-gradebook-info');

            gradebookInfo.html(`
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">

                    <!-- TITLE -->
                    <div>
                        <h1 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-book-open"></i>
                            Buku Nilai
                        </h1>

                        <p class="text-sm text-blue-100 mt-2 flex items-center gap-2">
                            ${teacherMapel.mapel?.mata_pelajaran}
                            <i class="fa-solid fa-circle text-[5px]"></i>

                            ${teacherMapel.school_class?.class_name}
                            <i class="fa-solid fa-circle text-[5px]"></i>

                            ${teacherMapel.school_class?.tahun_ajaran ?? '-'}
                        </p>
                    </div>
                </div>
            `);

            gradebookInfo.show();

            if (response.data.length > 0) {
                const bulkActionFinalScore = document.getElementById('container-bulk-action-final-score-input');

                bulkActionFinalScore.innerHTML = `
                    <div id="bulk-action-bar"
                        class="mb-4 bg-[#EEF6FF] border border-[#CFE2FF] rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                        <!-- LEFT INFO -->
                        <div class="flex items-center gap-2 text-[#4189E0] text-sm font-medium">
                            <i class="fa-solid fa-check-double"></i>
                            <span>
                                <span id="selected-count">0</span> asesmen dipilih
                            </span>
                        </div>

                        <!-- ACTION -->
                        <div class="flex items-center gap-2">
                            <button id="btn-save-score"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                rounded-lg bg-[#4189E0] text-white hover:bg-blue-600 transition cursor-pointer opacity-50 pointer-events-none">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Simpan Nilai
                            </button>
                        </div>
                    </div>
                `;

                $.each(response.data, function (index, item) {
                    const formatDate = (dateString) => {
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day}-${monthName}-${year}`;
                    };

                    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                    });

                    // Format tanggal mulai dan akhir
                    const startDate = item.start_date ? `${formatDate(item.start_date)}, ${timeFormatter.format(new Date(item.start_date))}` : 'Tanggal tidak tersedia';
                    const endDate = item.end_date ? `${formatDate(item.end_date)}, ${timeFormatter.format(new Date(item.end_date))}` : 'Tanggal tidak tersedia';
                    
                    $('#tbody-teacher-gradebook-assessment-preview').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <input type="checkbox" class="row-check cursor-pointer" data-id="${item.assessment_id}">
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.student_name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.assessment_type ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.title ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${startDate} - ${endDate}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <input type="number" min="0" max="100" name="final_score[${item.assessment_id}]" data-id="${item.assessment_id}" placeholder="Masukkan nilai"
                                        value="${item.final_score ?? ''}" 
                                        class="final-score-input w-full h-9 text-sm text-center border border-gray-300 bg-gray-100 rounded-lg outline-none" disabled>
                                    <span id="error-final_score_${item.assessment_id}" class="text-xs text-red-500 hidden"></span>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                $('.thead-table-teacher-gradebook-assessment-preview').show(); // Tampilkan tabel thead
                $('#empty-message-teacher-gradebook-assessment-preview').hide(); // sembunyikan pesan kosong
            } else {
                $('#tbody-teacher-gradebook-assessment-preview').empty(); // Clear existing rows
                $('.thead-table-teacher-gradebook-assessment-preview').hide(); // Tampilkan tabel thead
                $('#empty-message-teacher-gradebook-assessment-preview').show();
            }
        },

        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}

$(document).ready(function () {
    teacherGradebookAssessmentPreview();
});

// jika ada terjadi input, maka reset text error
$(document).on('input', '.final-score-input', function () {
    const assessmentId = $(this).data('id');

    // hilangkan error di row ini
    $(`#error-final_score_${assessmentId}`).addClass('hidden').text('');
});

// function untuk update bulk action UI
function updateBulkActionUI() {
    const checked = $('.row-check:checked').length;

    const containerBulkAction = $('#container-bulk-action-final-score-input');
    const countText = $('#selected-count');
    const saveBtn = $('#btn-save-score');

    countText.text(checked);

    if (checked > 0) {
        containerBulkAction.removeClass('hidden');

        saveBtn.removeClass('opacity-50 pointer-events-none');
    } else {
        containerBulkAction.addClass('hidden');

        saveBtn.addClass('opacity-50 pointer-events-none');
    }
}

// jika ada terjadi on change dari checkbox satuan, maka update UI
$(document).on('change', '.row-check', function () {
    const row = $(this).closest('tr');
    const input = row.find('.final-score-input');

    if ($(this).is(':checked')) {
        input.prop('disabled', false);
        input.removeClass('bg-gray-100').addClass('bg-white');
    } else {
        input.prop('disabled', true);
        input.addClass('bg-gray-100').removeClass('bg-white');
    }

    updateBulkActionUI();
});

// jika ada terjadi on change dari checkbox all, maka update UI
$(document).on('change', '#check-all', function () {
    const isChecked = $(this).is(':checked');

    $('.row-check').prop('checked', isChecked).trigger('change');
});

$(document).on('click', '#btn-save-score', function () {
    const container = $('#container-teacher-gradebook-assessment-preview');

    const role = container.data('role');
    const schoolName = container.data('schoolName');
    const schoolId = container.data('schoolId');
    const subjectTeacherId = container.data('subjectTeacherId');
    const assessmentTypeId = container.data('assessmentTypeId');
    const studentId = container.data('studentId');
    const semester = container.data('semester');

    let payload = [];

    $('.row-check:checked').each(function () {
        const assessmentId = $(this).data('id');
        const input = $(`.final-score-input[data-id="${assessmentId}"]`);

        payload.push({
            assessment_id: assessmentId,
            final_score: input.val()
        });
    });

    if (payload.length === 0) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/gradebook/classes/subject-teacher/${subjectTeacherId}/assessment-type/${assessmentTypeId}/student/${studentId}/semester/${semester}/bulk-update`,
        method: 'POST',
        data: {
            data: payload,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            $('#alert-success-final-score-input').html(`
                        <div class=" w-full flex justify-center">
                            <div class="fixed z-9999">
                                <div id="alertSuccess"
                                    class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-green-600 text-sm">${res.message}</span>
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

            // refresh table
            teacherGradebookAssessmentPreview();

            // RESET UI STATE
            $('#container-bulk-action-final-score-input').addClass('hidden');
            $('#selected-count').text(0);
            $('#check-all').prop('checked', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                // reset semua error
                $('.text-red-500').addClass('hidden').text('');

                Object.keys(errors).forEach(key => {
                    const match = key.match(/data\.(\d+)\.final_score/);

                    if (match) {
                        const index = match[1];
                        const assessmentId = payload[index].assessment_id;

                        $(`#error-final_score_${assessmentId}`).removeClass('hidden').text(errors[key][0]);
                    }
                });
            }
        }
    });
});