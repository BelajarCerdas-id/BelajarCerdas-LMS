let teacherQuestionReleaseEditingComposition = { assessment_id: null, composition_id: null, is_legacy: false };

$(document).on('click', '[data-action="edit-assessment-composition"], [data-action="edit-legacy-assessment-composition"]',
    function (e) {
        e.preventDefault();
        e.stopPropagation();

        const assessmentId = Number($(this).data('assessment-id'));
        const isLegacy = $(this).data('action') === 'edit-legacy-assessment-composition';
        const compositionId = isLegacy ? null : Number($(this).data('composition-id'));

        openTeacherQuestionReleaseEditCompositionModal(assessmentId, compositionId, isLegacy);
    }
);

function openTeacherQuestionReleaseEditCompositionModal(assessmentId, compositionId = null, isLegacy = false) {
    const assessment = teacherQuestionReleaseAssessments.find(
        item => Number(item.id) === Number(assessmentId)
    );

    if (!assessment) {
        console.error('Assessment tidak ditemukan:', assessmentId);
        return;
    }

    const composition = isLegacy ? null : (assessment.compositions || []).find(item => Number(item.id) === Number(compositionId));

    if (!isLegacy && !composition) {
        console.error('Komposisi tidak ditemukan:', compositionId);
        return;
    }

    const modal = document.getElementById('teacher-question-release-edit-composition-modal');
    const title = document.getElementById('teacher-question-release-edit-composition-assessment-title');
    const info = document.getElementById('teacher-question-release-edit-composition-assessment-info');
    const input = document.getElementById('teacher-question-release-edit-composition-name');
    const legacyInfo = document.getElementById('teacher-question-release-edit-composition-legacy-info');

    if (!modal || !title || !info || !input) {
        console.error('Elemen modal edit composition tidak ditemukan.');
        return;
    }

    teacherQuestionReleaseEditingComposition = {
        assessment_id: Number(assessment.id),
        composition_id: isLegacy ? null : Number(composition.id),
        is_legacy: Boolean(isLegacy)
    };

    title.textContent = assessment.title || assessment.name || 'Assessment Tanpa Judul';
    info.textContent = [assessment.school_class?.class_name || '-', assessment.mapel?.mata_pelajaran || '-'].join(' • ');
    input.value = isLegacy ? 'Tanpa Nama' : composition.composition_name;

    if (legacyInfo) {
        legacyInfo.classList.toggle('hidden', !isLegacy);
    }

    modal.showModal();
}

$('#teacher-question-release-update-composition').on('click', function (e) {
    e.preventDefault();

    if (isProcessing) {
        return;
    }

    const container = document.getElementById('container-form-teacher-question-bank-for-release');

    if (!container) {
        console.error('Container teacher question release tidak ditemukan.');
        return;
    }

    const assessmentId = teacherQuestionReleaseEditingComposition.assessment_id;

    if (!assessmentId) {
        console.error('Assessment belum dipilih.');
        return;
    }
        
    const btn = $(this);
    btn.prop('disabled', true);

    const form = $('#teacher-question-release-edit-composition-form')[0];
    const formData = new FormData(form);
    const button = document.getElementById('teacher-question-release-update-composition');
    const error = document.getElementById('error-edit-composition_name');

    formData.append('school_assessment_id', assessmentId);
    formData.append('composition_id', teacherQuestionReleaseEditingComposition.composition_id ?? '');
    formData.append('is_legacy', teacherQuestionReleaseEditingComposition.is_legacy ? 1 : 0);

    error.textContent = '';

    isProcessing = true;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/composition/update`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function (response) {
            if (!response.success) {
                return;
            }

            const assessment = teacherQuestionReleaseAssessments.find(
                item => Number(item.id) === Number(assessmentId)
            );

            if (assessment) {
                if (!Array.isArray(assessment.compositions)) {
                    assessment.compositions = [];
                }

                if (response.is_legacy_converted) {

                    // LEGACY BERHASIL DIKONVERSI MENJADI KOMPOSISI
                    assessment.compositions.push(response.composition);

                    // Hilangkan kelompok legacy dari data assessment
                    assessment.legacy_assigned_question_count = 0;
                    assessment.legacy_assigned_total_weight = 0;

                    // Perbarui pilihan komposisi jika sebelumnya memilih legacy
                    if (
                        teacherQuestionReleaseSelectedComposition &&
                        Number(teacherQuestionReleaseSelectedComposition.assessment_id) === Number(assessmentId) &&
                        teacherQuestionReleaseSelectedComposition.is_legacy
                    ) {
                        teacherQuestionReleaseSelectedComposition = {
                            assessment_id: Number(assessmentId),
                            composition_id: Number(response.composition.id),
                            is_legacy: false
                        };
                    }
                } else {
                    // EDIT KOMPOSISI YANG SUDAH ADA
                    const compositionIndex = assessment.compositions.findIndex(
                        item => Number(item.id) === Number(response.composition.id)
                    );

                    if (compositionIndex !== -1) {
                        assessment.compositions[compositionIndex] = {
                            ...assessment.compositions[compositionIndex],
                            ...response.composition
                        };
                    }
                }
            }

            renderTeacherQuestionReleaseAssessments();
            updateTeacherQuestionReleaseStepper();

            const modal = document.getElementById('teacher-question-release-edit-composition-modal');

            if (modal) {
                modal.close();

                $('#alert-success-edit-assessment-composition').html(`
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
                `);
    
                setTimeout(function () {
                    $('#alert-success-submit-data').remove();
                }, 3000);

                $('#btn-close-alert').on('click', function () {
                    $('#alert-success-submit-data').remove();
                });

                // Render ulang daftar assessment.
                renderTeacherQuestionReleaseAssessments();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};

                $.each(errors, function (field, messages) {
                    $('#teacher-question-release-edit-composition-form').find(`#error-edit-${field}`).text(messages[0]);
                    $('#teacher-question-release-edit-composition-form').find(`[name="${field}"]`).addClass('border-red-400');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});