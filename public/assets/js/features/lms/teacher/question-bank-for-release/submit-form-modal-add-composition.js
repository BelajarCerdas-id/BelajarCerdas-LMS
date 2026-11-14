$(document).on('click', '[data-action="add-assessment-composition"]', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const assessmentId = Number($(this).data('assessment-id'));

    openTeacherQuestionReleaseAddCompositionModal(assessmentId);
});

function openTeacherQuestionReleaseAddCompositionModal(assessmentId) {
    const assessment = teacherQuestionReleaseAssessments.find(
        item => Number(item.id) === Number(assessmentId)
    );

    if (!assessment) {
        console.error('Assessment tidak ditemukan:', assessmentId);
        return;
    }

    const modal = document.getElementById('teacher-question-release-add-composition-modal');
    const title = document.getElementById('teacher-question-release-add-composition-assessment-title');
    const info = document.getElementById('teacher-question-release-add-composition-assessment-info');
    const input = document.getElementById('teacher-question-release-composition-name');

    // Simpan assessment yang sedang diproses.
    teacherQuestionReleaseAddingCompositionAssessmentId = Number(assessment.id);

    // Isi informasi assessment.
    title.textContent = assessment.title || assessment.name || 'Assessment Tanpa Judul';

    info.textContent = [assessment.school_class?.class_name || '-', assessment.mapel?.mata_pelajaran || '-'].join(' • ');

    modal.showModal();

    // Fokus ke input setelah modal terbuka.
    setTimeout(() => input.focus(), 100);
}

// Form tambah komposisi soal
$('#teacher-question-release-save-composition').on('click', function (e) {
    e.preventDefault();

    const form = $('#teacher-question-release-add-composition-form')[0];
    const formData = new FormData(form);

    const assessmentId = teacherQuestionReleaseAddingCompositionAssessmentId;

    if (isProcessing) return;

    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    // Tambahkan ID Assessment ke FormData.
    formData.append('school_assessment_id', assessmentId);

    const container = document.getElementById('container-form-teacher-question-bank-for-release');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/composition/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Menutup modal.
            const modal = document.getElementById('teacher-question-release-add-composition-modal');

            if (modal) {
                modal.close();

                // Menampilkan notifikasi berhasil.
                $('#alert-success-add-assessment-composition').html(
                    `
                    <div class="w-full flex justify-center">
                        <div class="fixed z-9999">
                            <div id="alert-success-submit-data"
                                class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-green-600 text-sm">${response.message}</span>
                                <i class="fas fa-times cursor-pointer text-green-600" id="btn-close-alert"></i>
                            </div>
                        </div>
                    </div>
                    `
                );

                setTimeout(function () {
                    $('#alert-success-submit-data').remove();
                }, 3000);

                $('#btn-close-alert').on('click', function () {
                    $('#alert-success-submit-data').remove();
                });

                // Reset form.
                $('#teacher-question-release-add-composition-form')[0].reset();

                // Perbarui data komposisi pada assessment.
                if (response.composition) {
                    const assessment = teacherQuestionReleaseAssessments.find(
                        item => Number(item.id) === Number(assessmentId)
                    );

                    if (assessment) {
                        if (!Array.isArray(assessment.compositions)) {
                            assessment.compositions = [];
                        }

                        assessment.compositions.push(response.composition);
                    }
                }

                // Render ulang daftar assessment.
                renderTeacherQuestionReleaseAssessments();

                // Reset assessment yang sedang diproses.
                teacherQuestionReleaseAddingCompositionAssessmentId = null;

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#teacher-question-release-add-composition-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#teacher-question-release-add-composition-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});