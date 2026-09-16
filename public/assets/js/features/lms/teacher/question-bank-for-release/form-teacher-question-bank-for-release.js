let teacherQuestionReleaseStep = 1;
let teacherQuestionReleaseSelectedAssessment = null;
let teacherQuestionReleaseAssessments = [];
let teacherQuestionReleaseQuestionBanks = [];
let teacherQuestionReleaseSelectedBank = null;
let teacherQuestionReleaseQuestions = [];
let teacherQuestionReleaseSelectedQuestions = new Set();
let teacherQuestionReleaseSelectedQuestionWeights = {};
let teacherQuestionReleaseSelectedQuestionData = new Map();
let teacherQuestionReleaseExpandedBanks = new Set();
let teacherQuestionReleaseSelectedClassLevel = null;
let teacherQuestionReleaseLoading = false;
let teacherQuestionReleaseSelectedAssessmentHasAnswers = false;

function formQuestionForRelease(search_year = null, search_class = null, search_assessment_type = null, search_subject = null, search_semester = null, search_question = null, kurikulum_id = null, kelas_id = null, mapel_id = null, bab_id = null, sub_bab_id = null, preserveSelection = false) {
    const container = document.getElementById('container-form-teacher-question-bank-for-release');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    // Simpan state sebelum reload agar pilihan tidak hilang saat filter berubah.
    const previousSelectedAssessment = teacherQuestionReleaseSelectedAssessment;
    const previousSelectedQuestions = new Set(teacherQuestionReleaseSelectedQuestions);
    const previousSelectedQuestionWeights = {...teacherQuestionReleaseSelectedQuestionWeights};
    const previousSelectedQuestionData = new Map(teacherQuestionReleaseSelectedQuestionData);
    const previousStep = teacherQuestionReleaseStep;

    teacherQuestionReleaseStep = preserveSelection ? previousStep : 1;
    teacherQuestionReleaseSelectedAssessment = preserveSelection ? previousSelectedAssessment : null;
    teacherQuestionReleaseSelectedBank = null;
    teacherQuestionReleaseQuestions = [];
    teacherQuestionReleaseExpandedBanks.clear();
    teacherQuestionReleaseSelectedClassLevel = preserveSelection ? teacherQuestionReleaseSelectedClassLevel : null;
    teacherQuestionReleaseLoading = true;

    if (!preserveSelection) {
        teacherQuestionReleaseSelectedQuestions.clear();
        teacherQuestionReleaseSelectedQuestionWeights = {};
        teacherQuestionReleaseSelectedQuestionData.clear();
    }

    $('.question-release-panel, #question-release-panel-1, #question-release-panel-2, #question-release-panel-3').addClass('hidden');
    $(`#question-release-panel-${teacherQuestionReleaseStep}`).removeClass('hidden');

    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();
    showTeacherQuestionReleaseAssessmentLoading();

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/form`,
        method: 'GET',
        data: {
            search_year,
            search_class,
            search_assessment_type,
            search_subject,
            search_semester,
            search_question,
            kurikulum_id,
            kelas_id: null,
            mapel_id,
            bab_id,
            sub_bab_id
        },
        success: function (response) {
            teacherQuestionReleaseLoading = false;

            teacherQuestionReleaseAssessments = Array.isArray(response.data) ? response.data : [];
            teacherQuestionReleaseQuestionBanks = Array.isArray(response.questionBank) ? response.questionBank : [];

            teacherQuestionReleaseSelectedAssessmentHasAnswers = Boolean(response.selectedAssessmentHasAnswers);

            // Kembalikan pilihan sebelumnya setelah data berhasil dimuat.
            if (preserveSelection) {
                teacherQuestionReleaseSelectedQuestions = previousSelectedQuestions;
                teacherQuestionReleaseSelectedQuestionWeights = previousSelectedQuestionWeights;
                teacherQuestionReleaseSelectedQuestionData = previousSelectedQuestionData;

                if (previousSelectedAssessment) {
                    const refreshedAssessment = teacherQuestionReleaseAssessments.find(
                        item => Number(item.id) === Number(previousSelectedAssessment.id)
                    );

                    if (refreshedAssessment) {
                        teacherQuestionReleaseSelectedAssessment = refreshedAssessment;
                    }
                }
            }

            renderTeacherQuestionReleaseFilters(response, search_year, search_class, search_assessment_type, search_subject);

            renderTeacherQuestionReleaseAssessments();

            if (preserveSelection && teacherQuestionReleaseSelectedAssessment) {
                if (teacherQuestionReleaseStep === 2) {
                    renderTeacherQuestionReleaseSelectedAssessment();
                    renderTeacherQuestionReleaseClassLevels();
                    renderTeacherQuestionReleaseQuestionBanks();
                    renderTeacherQuestionReleaseSelectedQuestions();
                    updateTeacherQuestionReleaseQuestionSummary();
                } else if (teacherQuestionReleaseStep === 3) {
                    loadTeacherQuestionReleaseStep3();
                }
            }

            updateTeacherQuestionReleaseStepper();
            updateTeacherQuestionReleasePublishButton();
        },
        error: function (error) {
            teacherQuestionReleaseLoading = false;

            console.error('Failed to load question release form:', error);

            showTeacherQuestionReleaseAssessmentError();
            updateTeacherQuestionReleaseStepper();
            updateTeacherQuestionReleasePublishButton();
        }
    });
}

function addClassToTeacherQuestionReleaseImgTags(html, className) {
    if (!html) return '';

    return String(html).replace(
        /<img\b(?![^>]*class=)[^>]*>/gi,
        imgTag => imgTag.replace('<img', `<img class="${className}"`)
    ).replace(
        /<img\b([^>]*?)class="(.*?)"/gi, (imgTag, before, existingClasses) => {
            return `<img ${before}class="${existingClasses} ${className}"`;
        }
    );
}

function renderTeacherQuestionReleasePreview(question) {
    const modalContent = document.getElementById('modal-preview-content');

    if (!modalContent || !question) return;

    let optionsHtml = '';

    const questionType =
        String(question.tipe_soal || '').toLowerCase();

    if (['mcq', 'mcma'].includes(questionType)) {
        const options = Array.isArray(question.lms_question_option) ? question.lms_question_option : [];

        optionsHtml = `
            <div class="mt-4 space-y-2">
                ${options.map((opt, index) => `
                    <div class="flex items-start gap-3 p-3 rounded-xl border
                        ${opt.is_correct ? 'border-green-400 bg-green-400 opacity-70 text-white font-bold' : 'bg-white border-gray-200'}">

                        <div class="text-sm font-semibold">
                            ${String.fromCharCode(65 + index)}.
                        </div>

                        <div class="text-sm">
                            ${addClassToTeacherQuestionReleaseImgTags(opt.options_value || '', 'max-w-[100px] rounded')}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    const options = Array.isArray(question.lms_question_option) ? question.lms_question_option : [];

    const leftItems = options.filter(item =>
        String(item.options_key || '').startsWith('LEFT')
    );

    const rightItems = options.filter(item =>
        String(item.options_key || '').startsWith('RIGHT')
    );

    const rightLabelMap = {};

    rightItems.forEach((item, index) => {
        rightLabelMap[item.options_key] =
            String.fromCharCode(65 + index);
    });

    const pairsData = leftItems.filter(item => item.extra_data?.pair_with).map(item => ({
        left: item.options_key,
        right: item.extra_data.pair_with
    }));

    const matchingHTML = `
        <div class="relative matching-container hidden lg:block" data-pairs='${JSON.stringify(pairsData)}'>
            <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines"></svg>

            <div class="grid grid-cols-2 gap-40 relative z-10">
                <div class="flex flex-col justify-center">
                    <h4 class="font-bold mb-3">Kolom A</h4>

                    <div class="space-y-3">
                        ${leftItems.map(item => {
                            const content =
                                addClassToTeacherQuestionReleaseImgTags(
                                    item.options_value || '',
                                    'max-w-[100px] rounded'
                                );

                                return `
                                    <div class="px-3 min-h-10 border rounded flex justify-between items-center left-item" data-key="${item.options_key}">
                                        <span>${content}</span>

                                        <span class="text-sm bg-blue-100 text-[#0071BC] px-2 py-1 rounded">
                                            <i class="fa-solid fa-arrow-right"></i>
                                            ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                        </span>
                                    </div>
                                `;
                        }).join('')}
                    </div>
                </div>

                <div>
                    <h4 class="font-bold mb-3">Kolom B</h4>

                    <div class="space-y-3">
                        ${rightItems.map(item => {
                            const content =
                                addClassToTeacherQuestionReleaseImgTags(
                                    item.options_value || '',
                                    'max-w-[100px] rounded'
                                );

                                return `
                                    <div class="right-item p-3 border rounded flex gap-2 items-center" data-key="${item.options_key}">
                                        <span class="font-bold">
                                            ${rightLabelMap[item.options_key] ?? '-'}.
                                        </span>

                                        ${content}
                                    </div>
                                `;
                            }).join('')}
                    </div>
                </div>
            </div>

            <div class="matching-center-line absolute top-0 bottom-0 left-1/2 w-0"></div>
        </div>

        <div class="block lg:hidden">
            <div class="grid grid-cols-1 gap-3">
                <p class="font-semibold mb-2">Kolom A:</p>

                ${leftItems.map(item => {
                    const content =
                        addClassToTeacherQuestionReleaseImgTags(
                            item.options_value || '',
                            'max-w-[100px] rounded'
                        );

                        return `
                            <div class="flex justify-between items-center border rounded p-3">
                                <span>${content}</span>

                                <span class="font-bold text-[#0071BC]">
                                    <i class="fa-solid fa-arrow-right"></i>
                                    ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                </span>
                            </div>
                        `;
                    }).join('')}
            </div>

            <div class="mt-4 border-t border-gray-400 pt-3 grid grid-cols-1 gap-3 text-sm text-gray-700">
                <p class="font-semibold mb-2">Kolom B:</p>

                ${rightItems.map(item => {
                    const content =
                        addClassToTeacherQuestionReleaseImgTags(
                            item.options_value || '',
                            'max-w-[100px] rounded'
                        );

                        return `
                            <div class="right-item p-3 border rounded flex gap-2 items-center" data-key="${item.options_key}">
                                <span class="font-bold">
                                    ${rightLabelMap[item.options_key] ?? '-'}.
                                </span>

                                ${content}
                            </div>
                        `;
                    }).join('')}
            </div>
        </div>
    `;

    let pgKompleksHTML = '';

    if (question.tipe_soal === 'PG_KOMPLEKS') {
        const categories = options.filter(item => item.extra_data?.side === 'category');
        const items = options.filter(item => item.extra_data?.side === 'item');

        pgKompleksHTML = `
            <div class="overflow-x-auto mt-6">
                <table class="w-full border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-center">
                            <th class="border px-4 py-2">
                                ${question.header_item ?? 'Pernyataan'}
                            </th>

                            ${categories.map(category => `
                                <th class="border px-4 py-2">
                                    ${category.options_value ?? '-'}
                                </th>
                            `).join('')}
                        </tr>
                    </thead>

                    <tbody>
                        ${items.map(item => {
                            const answer = item.extra_data?.answer;

                            const content =
                                addClassToTeacherQuestionReleaseImgTags(
                                    item.options_value || '',
                                    'max-w-[100px] sm:max-w-[200px] rounded'
                                );

                                return `
                                <tr>
                                    <td class="border px-4 py-3">
                                        ${content}
                                    </td>

                                    ${categories.map(category => `
                                        <td class="border text-center">
                                            ${answer === category.options_key
                                                ? `
                                                    <div class="flex justify-center items-center">
                                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600">
                                                            <i class="fa-solid fa-check text-xs"></i>
                                                        </span>
                                                    </div>
                                                    `
                                                : ''
                                            }
                                        </td>
                                    `).join('')}
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    let answerSectionHTML = '';

    if (question.tipe_soal === 'PG_KOMPLEKS') {
        answerSectionHTML = pgKompleksHTML;
    } else if (question.tipe_soal === 'MATCHING') {
        answerSectionHTML = matchingHTML;
    } else {
        answerSectionHTML = optionsHtml;
    }

    const sourceName =
        question.school_partner_id
            ? (question.school_partner?.nama_sekolah || question.school_partner?.name || question.school_partner?.school_name || 'Sekolah')
            : 'belajarcerdas.id';

    modalContent.innerHTML = `
        <div class="bg-white rounded-2xl space-y-6">

            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 mb-2 uppercase tracking-wide">
                    Soal
                </h3>

                <div class="question-bank-preview leading-relaxed text-gray-800">
                    <div>
                        ${addClassToTeacherQuestionReleaseImgTags(
                            question.questions || '',
                            'max-w-full md:max-w-[300px] h-auto'
                        )}
                    </div>

                    <div>
                        ${answerSectionHTML}
                    </div>
                </div>
            </div>

            ${question.explanation
                ? `
                    <div class="bg-green-50 p-5 rounded-xl border border-green-200">
                        <h3 class="text-sm font-semibold text-green-700 mb-2 uppercase tracking-wide">
                            Pembahasan
                        </h3>

                        <div class="question-bank-preview text-sm text-gray-700 leading-relaxed">
                            ${question.explanation}
                        </div>
                    </div>
                `
            : ''
        }

            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-4 mb-4">

                    <span class="px-3 py-1 text-xs rounded-full font-bold bg-green-100 text-green-700 text-center">
                        ${question.difficulty ?? '-'}
                    </span>

                    <span class="px-3 py-1 text-xs rounded-full font-bold bg-blue-100 text-blue-700 text-center">
                        ${question.tipe_soal ?? '-'}
                    </span>

                    <span class="px-3 py-1 text-xs rounded-full font-bold bg-blue-100 text-blue-700 text-center">
                        ${question.question_category ?? '-'}
                    </span>

                    <span class="px-3 py-1 text-xs rounded-full font-bold bg-purple-100 text-purple-700 text-center">
                        ${question.mapel?.mata_pelajaran ?? '-'}
                    </span>

                    <span class="px-3 py-1 text-xs rounded-full font-bold bg-indigo-100 text-indigo-700 text-center">
                        ${question.kelas?.kelas ?? '-'}
                    </span>

                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-5">

                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                            Struktur Materi
                        </p>

                        <div class="flex flex-col md:flex-row md:items-center gap-4 text-xs font-medium text-gray-700">

                            <div class="flex items-center gap-2">
                                <span class="bg-gray-100 px-3 py-1 rounded-lg wrap-break-word">
                                    ${question.kurikulum?.nama_kurikulum ?? '-'}
                                </span>

                                <i class="fas fa-chevron-right text-gray-400 text-[10px] hidden md:inline"></i>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="bg-gray-100 px-3 py-1 rounded-lg wrap-break-word">
                                    ${question.bab?.nama_bab ?? '-'}
                                </span>

                                <i class="fas fa-chevron-right text-gray-400 text-[10px] hidden md:inline"></i>
                            </div>

                            <div>
                                <span class="bg-gray-100 px-3 py-1 rounded-lg wrap-break-word">
                                    ${question.sub_bab?.sub_bab ?? '-'}
                                </span>
                            </div>

                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">
                            Sumber Soal
                        </p>

                        <div class="text-sm text-gray-700 bg-gray-50 px-3 py-2 rounded-lg border border-gray-300">
                            ${sourceName}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    `;

    const modal = document.getElementById('my_modal_1');

    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }

    setTimeout(() => {
        document
            .querySelectorAll('.matching-container')
            .forEach(container => {
                const pairs =
                    JSON.parse(
                        container.dataset.pairs || '[]'
                    );

                drawMatchingLines(
                    container,
                    pairs
                );
            });
    }, 270);
}

function showTeacherQuestionReleaseAssessmentLoading() {
    $('#question-release-assessment-loading').removeClass('hidden');
    $('#question-release-assessment-list').addClass('hidden').empty();
    $('#question-release-assessment-empty').addClass('hidden');
    $('#question-release-assessment-error').addClass('hidden');
    $('#question-release-assessment-count').removeClass('bg-red-50 text-red-600').addClass('bg-gray-100 text-gray-500').text('Memuat...');
    $('#question-release-next-step-1').prop('disabled', true);
}

function showTeacherQuestionReleaseAssessmentError() {
    $('#question-release-assessment-loading').addClass('hidden');
    $('#question-release-assessment-list').addClass('hidden').empty();
    $('#question-release-assessment-empty').addClass('hidden');
    $('#question-release-assessment-error').removeClass('hidden');
    $('#question-release-assessment-count').removeClass('bg-gray-100 text-gray-500').addClass('bg-red-50 text-red-600').text('Gagal dimuat');
    $('#question-release-next-step-1').prop('disabled', true);
}

function renderTeacherQuestionReleaseFilters(response, searchYear = null, searchClass = null, searchAssessmentType = null, searchSubject = null) {
    const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran');

    if (containerDropdownTahunAjaran && Array.isArray(response.tahunAjaran)) {
        containerDropdownTahunAjaran.innerHTML = `
            <select id="dropdown-filter-tahun-ajaran" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                <option value="">Pilih Tahun Ajaran</option>
                ${response.tahunAjaran.map(item => `
                    <option value="${escapeTeacherQuestionReleaseHtml(item)}" ${String(searchYear ?? response.selectedYear ?? '') === String(item) ? 'selected' : ''}>
                        Tahun Ajaran ${escapeTeacherQuestionReleaseHtml(item)}
                    </option>
                `).join('')}
            </select>
        `;
    }

    const containerDropdownClass = document.getElementById('container-dropdown-class');

    if (containerDropdownClass && Array.isArray(response.className)) {
        containerDropdownClass.innerHTML = `
            <select id="dropdown-filter-class" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                <option value="">Pilih Kelas</option>
                ${response.className.map(item => `
                    <option value="${escapeTeacherQuestionReleaseHtml(item)}" ${String(searchClass ?? response.selectedClass ?? '') === String(item) ? 'selected' : ''}>
                        Kelas ${escapeTeacherQuestionReleaseHtml(item)}
                    </option>
                `).join('')}
            </select>
        `;
    }

    const containerAssessmentType = document.getElementById('container-dropdown-assessment-type');

    if (containerAssessmentType && Array.isArray(response.schoolAssessmentType)) {
        containerAssessmentType.innerHTML = `
            <select id="dropdown-assessment-type" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                <option value="">Pilih Jenis Asesmen</option>
                ${response.schoolAssessmentType.map(item => `
                    <option value="${escapeTeacherQuestionReleaseHtml(item.id)}" ${String(searchAssessmentType ?? '') === String(item.id) ? 'selected' : ''}>
                        ${escapeTeacherQuestionReleaseHtml(item.name)}
                    </option>
                `).join('')}
            </select>
        `;
    }

    const containerDropdownSubject = document.getElementById('container-dropdown-subject-rombel-class');

    if (containerDropdownSubject && Array.isArray(response.subject)) {
        containerDropdownSubject.innerHTML = `
            <select id="dropdown-filter-mapel" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                <option value="">Pilih Mata Pelajaran</option>
                ${response.subject.map(item => `
                    <option value="${escapeTeacherQuestionReleaseHtml(item.id)}" ${String(searchSubject ?? '') === String(item.id) ? 'selected' : ''}>
                        ${escapeTeacherQuestionReleaseHtml(item.name)}
                    </option>
                `).join('')}
            </select>
        `;
    }
}

function getTeacherQuestionReleaseAssignedQuestions(assessment) {
    if (!assessment || !Array.isArray(assessment.assigned_questions)) {
        return [];
    }

    return assessment.assigned_questions;
}

function getTeacherQuestionReleaseAssignedQuestionIds(assessment) {
    return getTeacherQuestionReleaseAssignedQuestions(assessment).map(item => String(item.question_id)).filter(Boolean);
}

function getTeacherQuestionReleaseAssignedQuestionId(item) {
    if (!item || item.question_id === null || item.question_id === undefined) {
        return null;
    }

    return String(item.question_id);
}

function getTeacherQuestionReleaseAssignedQuestionWeight(item) {
    if (!item || item.weight === null || item.weight === undefined) {
        return null;
    }

    const weight = Number(item.weight);

    return Number.isNaN(weight) ? null : weight;
}

function hydrateTeacherQuestionReleaseAssessmentSelection(assessment) {
    teacherQuestionReleaseSelectedQuestions.clear();
    teacherQuestionReleaseSelectedQuestionWeights = {};
    teacherQuestionReleaseSelectedQuestionData.clear();

    if (!assessment) return;

    const allQuestions = getTeacherQuestionReleaseAllQuestions();
    const assignedQuestions = getTeacherQuestionReleaseAssignedQuestions(assessment);

    assignedQuestions.forEach(item => {
        const questionId = String(item.question_id);

        if (!questionId) return;

        teacherQuestionReleaseSelectedQuestions.add(questionId);

        const currentQuestion = allQuestions.find(question => String(question.id) === questionId);

        if (currentQuestion) {
            teacherQuestionReleaseSelectedQuestionData.set(questionId, currentQuestion);
        } else if (item.question) {
            teacherQuestionReleaseSelectedQuestionData.set(questionId, item.question);
        }

        const weight = Number(item.weight);

        if (!Number.isNaN(weight)) {
            teacherQuestionReleaseSelectedQuestionWeights[questionId] = Number(weight.toFixed(2));
        }
    });
}

function getTeacherQuestionReleaseAssignedQuestionCount(assessment) {
    return Number(assessment?.assigned_question_count) || 0;
}

function getTeacherQuestionReleaseAssignedTotalWeight(assessment) {
    if (!assessment) return 0;

    const totalWeight = Number(assessment.assigned_total_weight);

    return Number.isNaN(totalWeight) ? 0 : Number(totalWeight.toFixed(2));
}

function renderTeacherQuestionReleaseAssessments() {
    const container = $('#question-release-assessment-list');
    const empty = $('#question-release-assessment-empty');
    const loading = $('#question-release-assessment-loading');
    const error = $('#question-release-assessment-error');
    const count = $('#question-release-assessment-count');

    if (!container.length) return;

    loading.addClass('hidden');
    error.addClass('hidden');

    const assessments = teacherQuestionReleaseAssessments || [];

    count.removeClass('bg-red-50 text-red-600').addClass('bg-gray-100 text-gray-500').text(`${assessments.length} asesmen`);

    if (!assessments.length) {
        container.addClass('hidden').empty();
        empty.removeClass('hidden');
        $('#question-release-next-step-1').prop('disabled', true);

        return;
    }

    empty.addClass('hidden');
    container.removeClass('hidden');

    container.html(`
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            ${assessments.map(renderTeacherQuestionReleaseAssessmentCard).join('')}
        </div>
    `);

    $('#question-release-next-step-1').prop('disabled', !teacherQuestionReleaseSelectedAssessment);
}

function renderTeacherQuestionReleaseAssessmentCard(assessment) {
    const isSelected = teacherQuestionReleaseSelectedAssessment && Number(teacherQuestionReleaseSelectedAssessment.id) === Number(assessment.id);
    const status = assessment.status || 'Belum Dirilis';

    const statusClass = status === 'Sudah Dirilis' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200';
    const mapelName = assessment.mapel?.mata_pelajaran || '-';
    const className = assessment.school_class?.class_name || '-';
    const assessmentType = assessment.school_assessment_type?.name || '-';

    const title = assessment.title || assessment.name || 'Asesmen Tanpa Judul';
    const startDate = formatTeacherQuestionReleaseDateTime(assessment.start_date);
    const endDate = formatTeacherQuestionReleaseDateTime(assessment.end_date);

    const assignedQuestionCount = getTeacherQuestionReleaseAssignedQuestionCount(assessment);
    const assignedTotalWeight = getTeacherQuestionReleaseAssignedTotalWeight(assessment);

    let schedule = '-';

    if (assessment.start_date && assessment.end_date) {
        schedule = `${startDate} - ${endDate}`;
    } else if (assessment.start_date) {
        schedule = startDate;
    }

    return `
        <button type="button" data-question-release-assessment="${escapeTeacherQuestionReleaseHtml(assessment.id)}"
            class="w-full text-left rounded-2xl border-2 p-4 sm:p-5 transition-all duration-200 ${isSelected ? 'border-[#0071BC] bg-[#EAF6FF]/60 ring-2 ring-[#0071BC]/10' : 'border-gray-200 bg-white hover:border-[#B9DDF5] hover:bg-gray-50'}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                    <span class="rounded-lg bg-[#EAF6FF] px-2 py-1 text-[10px] font-bold text-[#0071BC]">${escapeTeacherQuestionReleaseHtml(className)}</span>
                    <span class="rounded-lg bg-gray-100 px-2 py-1 text-[10px] font-medium text-gray-600">${escapeTeacherQuestionReleaseHtml(assessmentType)}</span>
                </div>
                <span class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-semibold ${statusClass}">${escapeTeacherQuestionReleaseHtml(status)}</span>
            </div>

            <div class="mt-4">
                <h3 class="text-sm font-bold text-gray-800">${escapeTeacherQuestionReleaseHtml(title)}</h3>

                <div class="mt-3 space-y-2">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="fa-regular fa-calendar w-4 text-gray-400"></i>
                        <span>${escapeTeacherQuestionReleaseHtml(schedule)}</span>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="fa-solid fa-book-open w-4 text-gray-400"></i>
                        <span>${escapeTeacherQuestionReleaseHtml(mapelName)}</span>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="fa-solid fa-graduation-cap w-4 text-gray-400"></i>
                        <span>${escapeTeacherQuestionReleaseHtml(className)}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
                <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5">
                    <div class="flex items-center gap-1.5 text-[9px] text-gray-400">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Soal Terpasang</span>
                    </div>
                    <div class="mt-1 text-xs font-black text-gray-700">
                        ${assignedQuestionCount} <span class="font-medium text-gray-400">butir</span>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5">
                    <div class="flex items-center gap-1.5 text-[9px] text-gray-400">
                        <i class="fa-solid fa-percent"></i>
                        <span>Total Bobot</span>
                    </div>
                    <div class="mt-1 text-xs font-black ${assignedTotalWeight === 100 ? 'text-emerald-600' : assignedTotalWeight > 0 ? 'text-amber-600' : 'text-gray-400'}">
                        ${formatTeacherQuestionReleaseWeight(assignedTotalWeight)}
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end border-t border-gray-100 pt-3">
                <div class="flex items-center gap-1.5 text-xs font-semibold ${isSelected ? 'text-[#0071BC]' : 'text-gray-400'}">
                    ${isSelected ? '<i class="fa-solid fa-circle-check"></i><span>Terpilih</span>' : '<span>Pilih</span><i class="fa-solid fa-arrow-right text-[10px]"></i>'}
                </div>
            </div>
        </button>
    `;
}

function selectTeacherQuestionReleaseAssessment(assessmentId) {
    const assessment = teacherQuestionReleaseAssessments.find(
        item => Number(item.id) === Number(assessmentId)
    );

    if (!assessment) return;

    const previousAssessmentId = teacherQuestionReleaseSelectedAssessment ? Number(teacherQuestionReleaseSelectedAssessment.id) : null;

    const isSameAssessment = previousAssessmentId !== null && previousAssessmentId === Number(assessment.id);

    teacherQuestionReleaseSelectedAssessment = assessment;

    teacherQuestionReleaseSelectedAssessmentHasAnswers = Boolean(assessment.has_student_answers);

    const assessmentClassLevel = getTeacherQuestionReleaseAssessmentClassLevel();

    if (assessmentClassLevel) {
        teacherQuestionReleaseSelectedClassLevel = assessmentClassLevel;
    }

    if (!isSameAssessment) {
        teacherQuestionReleaseSelectedBank = null;
        teacherQuestionReleaseQuestions = [];
        teacherQuestionReleaseExpandedBanks.clear();

        hydrateTeacherQuestionReleaseAssessmentSelection(assessment);
    }

    $('#question-release-selected-question-bank').addClass('hidden');

    renderTeacherQuestionReleaseAssessments();
    renderTeacherQuestionReleaseSelectedAssessment();
    renderTeacherQuestionReleaseSelectedQuestions();
    updateTeacherQuestionReleaseQuestionSummary();
    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();
}

function getTeacherQuestionReleaseAssessmentMapelId() {
    const assessment = teacherQuestionReleaseSelectedAssessment;
    return Number(assessment?.mapel_id || 0);
}

function getTeacherQuestionReleaseAssessmentKelasId() {
    const assessment = teacherQuestionReleaseSelectedAssessment;
    return Number(assessment?.school_class_id || 0);
}

function getTeacherQuestionReleaseAssessmentClassLevel() {
    const assessment = teacherQuestionReleaseSelectedAssessment;
    if (!assessment) return 0;

    return extractTeacherQuestionReleaseClassLevel(
        assessment.school_class?.class_name || ''
    );
}

function extractTeacherQuestionReleaseClassLevel(value) {
    if (value === null || value === undefined) return 0;

    const match = String(value).match(/\d+/);

    return match ? Number(match[0]) : 0;
}

function getTeacherQuestionReleaseClassLevels() {
    const assessmentLevel = getTeacherQuestionReleaseAssessmentClassLevel();

    if (assessmentLevel >= 1 && assessmentLevel <= 6) {
        return [1, 2, 3, 4, 5, 6];
    }

    if (assessmentLevel >= 7 && assessmentLevel <= 9) {
        return [7, 8, 9];
    }

    if (assessmentLevel >= 10 && assessmentLevel <= 12) {
        return [10, 11, 12];
    }

    return [];
}

function renderTeacherQuestionReleaseClassLevels() {
    const container = $('#question-release-class-level-list');

    if (!container.length) return;

    const levels = getTeacherQuestionReleaseClassLevels();

    if (!levels.length) {
        container.html(`
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500">
                Tidak ada kelas yang tersedia.
            </div>
        `);
        return;
    }

    if (!teacherQuestionReleaseSelectedClassLevel) {
        teacherQuestionReleaseSelectedClassLevel = getTeacherQuestionReleaseAssessmentClassLevel() || levels[0];
    }

    container.html(`
        <div class="flex flex-wrap items-center gap-2">
            ${levels.map(level => {
                const checked = Number(teacherQuestionReleaseSelectedClassLevel) === Number(level);

                return `
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 transition-all ${checked ? 'border-[#0071BC] bg-[#EAF6FF] text-[#0071BC]' : 'border-gray-200 bg-white text-gray-600 hover:border-[#B9DDF5]'}">
                        <input type="radio" name="question-release-class-level" value="${level}"
                            class="question-release-class-level-radio h-4 w-4 border-gray-300 text-[#0071BC] focus:ring-[#0071BC]" ${checked ? 'checked' : ''}>
                        <span class="text-xs font-semibold">Kelas ${level}</span>
                    </label>
                `;
            }).join('')}
        </div>
    `);
}

function getTeacherQuestionReleaseRelevantQuestions() {
    if (!teacherQuestionReleaseSelectedAssessment) {
        return [];
    }

    const assessment = teacherQuestionReleaseSelectedAssessment;
    const assessmentMapelName = String(assessment.mapel?.mata_pelajaran || '').trim().toLowerCase();
    const selectedClassLevel = Number(teacherQuestionReleaseSelectedClassLevel);

    return (teacherQuestionReleaseQuestionBanks || []).filter(question => {
        const questionMapelName = String(question.mapel?.mata_pelajaran || '').trim().toLowerCase();

        const questionClassLevel = extractTeacherQuestionReleaseClassLevel(
            question.kelas?.kelas || ''
        );

        const matchMapel = assessmentMapelName && questionMapelName === assessmentMapelName;
        const matchClass = questionClassLevel === selectedClassLevel;

        return matchMapel && matchClass;
    });
}

function getTeacherQuestionReleaseBankKey(question) {
    return [
        question.kurikulum_id ?? 'null',
        question.kelas_id ?? 'null',
        question.mapel_id ?? 'null',
        question.bab_id ?? 'null',
        question.sub_bab_id ?? 'null',
        question.question_category ?? 'null',
        question.school_partner_id ?? 'null'
    ].join('-');
}

function getTeacherQuestionReleaseBankName(question) {
    const mapel = question.mapel?.mata_pelajaran || '-';
    const kelas = question.kelas?.kelas || '-';

    return `Bank Soal ${mapel} • ${kelas}`;
}

function getTeacherQuestionReleaseBankGroups() {
    const questions = getTeacherQuestionReleaseRelevantQuestions();

    if (!questions.length) return [];

    const groups = new Map();

    questions.forEach(question => {
        const key = getTeacherQuestionReleaseBankKey(question);

        if (!groups.has(key)) {
            groups.set(key, {
                id: key,
                kurikulum_id: question.kurikulum_id ?? null,
                kelas_id: question.kelas_id ?? null,
                mapel_id: question.mapel_id ?? null,
                bab_id: question.bab_id ?? null,
                sub_bab_id: question.sub_bab_id ?? null,
                question_category: question.question_category ?? null,
                school_partner_id: question.school_partner_id ?? null,
                name: getTeacherQuestionReleaseBankName(question),
                questions: []
            });
        }

        groups.get(key).questions.push(question);
    });

    return Array.from(groups.values());
}

function getTeacherQuestionReleaseBankMeta(bank) {
    const questions = bank.questions || [];
    const firstQuestion = questions[0] || {};
    const schoolPartner = firstQuestion.school_partner || null;

    let source = firstQuestion.question_source_name || null;

    if (!source) {
        if (firstQuestion.school_partner_id === null || firstQuestion.school_partner_id === undefined) {
            source = 'BelajarCerdas.id';
        } else {
            source = schoolPartner?.nama_sekolah || firstQuestion.question_source || 'Sekolah';
        }
    }

    let rawDate = firstQuestion.created_at || firstQuestion.updated_at || null;
    if (!rawDate && questions.length > 0) {
        const found = questions.find(q => q.created_at || q.updated_at);
        if (found) {
            rawDate = found.created_at || found.updated_at;
        }
    }

    const uploadDate = formatTeacherQuestionReleaseDate(rawDate);

    return {
        kurikulum: firstQuestion.kurikulum?.nama_kurikulum || '-',
        mapel: firstQuestion.mapel?.mata_pelajaran || '-',
        kelas: firstQuestion.kelas?.kelas || '-',
        bab: firstQuestion.bab?.nama_bab || '-',
        subBab: firstQuestion.sub_bab?.sub_bab || '-',
        category: firstQuestion.question_category || 'Umum',
        source,
        uploadDate
    };
}

function renderTeacherQuestionReleaseQuestionBanks() {
    const loading = $('#question-release-question-bank-loading');
    const list = $('#question-release-question-bank-list');
    const empty = $('#question-release-question-bank-empty');
    const error = $('#question-release-question-bank-error');
    const count = $('#question-release-question-bank-count');

    if (!list.length) {
        return;
    }

    loading.addClass('hidden');
    error.addClass('hidden');

    const banks =
        getTeacherQuestionReleaseBankGroups();

    count.removeClass('bg-red-50 text-red-600').addClass('bg-gray-100 text-gray-500').text(`${banks.length} bank soal`);

    if (!banks.length) {
        list.addClass('hidden').empty();
        empty.removeClass('hidden');

        return;
    }

    empty.addClass('hidden');
    list.removeClass('hidden');

    list.html(`
        <div class="space-y-3">
            ${banks.map(renderTeacherQuestionReleaseBankCard).join('')}
        </div>
    `);
}

function renderTeacherQuestionReleaseBankCard(bank) {
    const expanded = teacherQuestionReleaseExpandedBanks.has(String(bank.id));
    const questions = bank.questions || [];

    const selectedCount = questions.filter(question =>
        teacherQuestionReleaseSelectedQuestions.has(String(question.id))
    ).length;

    const allSelected = questions.length > 0 && selectedCount === questions.length;

    const meta = getTeacherQuestionReleaseBankMeta(bank);

    return `
        <div class="rounded-2xl border ${expanded ? 'border-[#75BFFF] shadow-sm' : 'border-gray-200'} bg-white overflow-hidden">
            <div class="flex items-center gap-3 px-4 py-3 ${expanded ? 'bg-[#F7FCFF]' : 'bg-white'}">
                <button type="button" data-question-release-bank-toggle="${escapeTeacherQuestionReleaseHtml(bank.id)}"
                    class="flex min-w-0 flex-1 items-start gap-3 text-left">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${expanded ? 'bg-[#0071BC] text-white' : 'bg-gray-100 text-gray-500'}">
                        <i class="fa-solid ${expanded ? 'fa-chevron-down' : 'fa-chevron-right'} text-[10px]"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rounded-md bg-[#EAF6FF] px-2 py-1 text-[10px] font-semibold text-[#0071BC]">${escapeTeacherQuestionReleaseHtml(meta.mapel)}</span>
                            <span class="rounded-md bg-purple-50 px-2 py-1 text-[10px] font-semibold text-purple-600">${escapeTeacherQuestionReleaseHtml(meta.category)}</span>
                            <span class="rounded-md bg-gray-100 px-2 py-1 text-[10px] font-medium text-gray-500">${escapeTeacherQuestionReleaseHtml(meta.kelas)}</span>
                        </div>

                        <h4 class="mt-2 text-xs font-bold text-gray-800">${escapeTeacherQuestionReleaseHtml(meta.bab !== '-' ? meta.bab : 'Bank Soal')}</h4>

                        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                <span class="text-gray-400">Kurikulum:</span>
                                <span class="font-medium text-gray-600">${escapeTeacherQuestionReleaseHtml(meta.kurikulum)}</span>
                            </span>

                            <span class="text-gray-300" aria-hidden="true">•</span>

                            <span class="inline-flex items-center gap-1">
                                <span class="text-gray-400">Bab:</span>
                                <span class="font-medium text-gray-600">${escapeTeacherQuestionReleaseHtml(meta.bab)}</span>
                            </span>

                            <span class="text-gray-300" aria-hidden="true">•</span>

                            <span class="inline-flex items-center gap-1">
                                <span class="text-gray-400">Sub Bab:</span>
                                <span class="font-medium text-gray-600">${escapeTeacherQuestionReleaseHtml(meta.subBab)}</span>
                            </span>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-2 py-1">
                                <i class="fa-solid fa-database text-[9px] text-gray-400"></i>
                                <span class="text-[9px] font-medium text-gray-500">Sumber:</span>
                                <span class="text-[9px] font-bold text-gray-700">${escapeTeacherQuestionReleaseHtml(meta.source)}</span>
                            </div>

                            ${meta.uploadDate ? `
                                <div class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-2 py-1">
                                    <i class="fa-regular fa-clock text-[9px] text-gray-400"></i>
                                    <span class="text-[9px] font-medium text-gray-500">Tanggal Upload:</span>
                                    <span class="text-[9px] font-bold text-gray-700">${escapeTeacherQuestionReleaseHtml(meta.uploadDate)}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </button>

                <div class="flex shrink-0 flex-col sm:flex-row items-end sm:items-center gap-2">
                    <span class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-semibold text-gray-500 whitespace-nowrap">
                        ${selectedCount}/${questions.length} dipilih
                    </span>

                    <button type="button" data-question-release-bank-select-all="${escapeTeacherQuestionReleaseHtml(bank.id)}"
                        class="inline-flex items-center gap-1.5 rounded-lg border ${allSelected ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 hover:border-amber-400' : 'border-[#0071BC]/30 bg-[#EAF6FF] text-[#0071BC] hover:bg-[#0071BC] hover:text-white'} px-2.5 py-1.5 text-[10px] font-bold shadow-xs cursor-pointer select-none transition-all">
                        <i class="fa-solid ${allSelected ? 'fa-square-minus' : 'fa-check-double'} text-[10px] pointer-events-none"></i>
                        <span class="pointer-events-none">${allSelected ? 'Batal Pilih' : 'Pilih Semua'}</span>
                    </button>
                </div>
            </div>

            <div class="${expanded ? '' : 'hidden'} border-t border-gray-100 bg-gray-50/40 px-3 py-3">
                <div class="mb-3 flex items-center justify-between gap-2 px-1">
                    <span class="text-[11px] font-medium text-gray-500">${selectedCount} dari ${questions.length} butir soal dipilih</span>

                    <button type="button" data-question-release-bank-select-all="${escapeTeacherQuestionReleaseHtml(bank.id)}"
                        class="inline-flex items-center gap-1.5 rounded-lg border ${allSelected ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 hover:border-amber-400' : 'border-[#0071BC]/30 bg-white text-[#0071BC] hover:bg-[#EAF6FF]'} px-2.5 py-1 text-[10px] font-semibold cursor-pointer select-none shadow-xs transition-all">
                        <i class="fa-solid ${allSelected ? 'fa-square-minus' : 'fa-check-double'} text-[10px] pointer-events-none"></i>
                        <span class="pointer-events-none">${allSelected ? 'Batal Pilih Semua Soal' : 'Pilih Semua Soal'}</span>
                    </button>
                </div>

                <div class="space-y-2">
                    ${questions.map((question, index) =>
                        renderTeacherQuestionReleaseQuestionCard(question, index)
                    ).join('')}
                </div>
            </div>
        </div>
    `;
}

function toggleTeacherQuestionReleaseBank(bankId) {
    const id = String(bankId);

    if (teacherQuestionReleaseExpandedBanks.has(id)) {
        teacherQuestionReleaseExpandedBanks.delete(id);
    } else {
        teacherQuestionReleaseExpandedBanks.add(id);
    }

    renderTeacherQuestionReleaseQuestionBanks();
}

function selectTeacherQuestionReleaseBank(bankId) {
    toggleTeacherQuestionReleaseBank(bankId);
}

function selectAllTeacherQuestionReleaseBank(bankId) {
    const banks = getTeacherQuestionReleaseBankGroups();

    const bank = banks.find(
        item => String(item.id) === String(bankId)
    );

    if (!bank || !bank.questions.length) return;

    const allSelected = bank.questions.every(question =>
        teacherQuestionReleaseSelectedQuestions.has(String(question.id))
    );

    if (allSelected) {
        bank.questions.forEach(question => {
            const id = String(question.id);

            teacherQuestionReleaseSelectedQuestions.delete(id);
            delete teacherQuestionReleaseSelectedQuestionWeights[id];
            teacherQuestionReleaseSelectedQuestionData.delete(id);
        });
    } else {
        bank.questions.forEach(question => {
            const id = String(question.id);

            teacherQuestionReleaseSelectedQuestions.add(id);
            teacherQuestionReleaseSelectedQuestionData.set(id, question);
        });
    }

    normalizeTeacherQuestionReleaseWeights();
    renderTeacherQuestionReleaseQuestionBanks();
    renderTeacherQuestionReleaseSelectedQuestions();
    updateTeacherQuestionReleaseQuestionSummary();
    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();

    if (teacherQuestionReleaseStep === 3) {
        loadTeacherQuestionReleaseStep3();
    }
}

function renderTeacherQuestionReleaseQuestionCard(question, index) {
    const id = String(question.id);
    const selected = teacherQuestionReleaseSelectedQuestions.has(id);

    const questionType = question.tipe_soal || question.question_type || question.type || 'Soal';

    const difficulty = question.difficulty || 'Umum';

    const questionText = stripHtmlAndLimit(question.questions || question.question || '-', 220);

    const typeLower = String(questionType).toLowerCase();

    const typeClass = typeLower.includes('pilihan') ? 'bg-blue-50 text-blue-600' : typeLower.includes('benar') ? 'bg-emerald-50 text-emerald-600'
        : typeLower.includes('menjodoh') ? 'bg-purple-50 text-purple-600' : 'bg-gray-100 text-gray-600';

    const difficultyLower = String(difficulty).toLowerCase();

    const difficultyClass = difficultyLower === 'sukar' ? 'bg-red-50 text-red-600' : difficultyLower === 'sedang' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600';

    return `
        <label class="group block cursor-pointer rounded-xl border ${selected ? 'border-[#0071BC] bg-[#EAF6FF]/50' : 'border-gray-200 bg-white hover:border-[#B9DDF5] hover:bg-gray-50'} p-3 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="pt-0.5">
                    <input type="checkbox" class="question-release-question-checkbox h-4 w-4 rounded border-gray-300 text-[#0071BC] focus:ring-[#0071BC]"
                        data-question-id="${escapeTeacherQuestionReleaseHtml(id)}"
                        ${selected ? 'checked' : ''}>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-md bg-gray-100 px-1.5 py-1 text-[9px] font-bold text-gray-500">#${index + 1}</span>

                        <span class="rounded-md ${typeClass} px-1.5 py-1 text-[9px] font-semibold">
                            <i class="fa-solid fa-shapes mr-0.5 text-[8px]"></i>
                            ${escapeTeacherQuestionReleaseHtml(questionType)}
                        </span>

                        <span class="rounded-md ${difficultyClass} px-1.5 py-1 text-[9px] font-semibold">
                            ${escapeTeacherQuestionReleaseHtml(difficulty)}
                        </span>

                        <span class="text-[9px] text-gray-400">
                            q-${escapeTeacherQuestionReleaseHtml(id)}
                        </span>

                        ${question.created_at ? `
                            <span class="inline-flex items-center gap-1 text-[9px] text-gray-400">
                                <span>•</span>
                                <i class="fa-regular fa-calendar text-[8px]"></i>
                                <span>${escapeTeacherQuestionReleaseHtml(formatTeacherQuestionReleaseDate(question.created_at))}</span>
                            </span>
                        ` : ''}
                    </div>

                    <p class="mt-2 text-[11px] leading-relaxed text-gray-700">
                        ${escapeTeacherQuestionReleaseHtml(questionText)}
                    </p>
                </div>

                <div class="shrink-0 pt-0.5 text-gray-400">
                    <i class="fa-regular fa-eye text-xs btn-preview-question"
                        data-question-id="${escapeTeacherQuestionReleaseHtml(id)}"></i>
                </div>
            </div>
        </label>
    `;
}

function toggleTeacherQuestionReleaseQuestion(questionId, checked) {
    const id = String(questionId);

    if (checked) {
        teacherQuestionReleaseSelectedQuestions.add(id);

        const question = getTeacherQuestionReleaseAllQuestions().find(
            item => String(item.id) === id
        );

        if (question) {
            teacherQuestionReleaseSelectedQuestionData.set(id, question);
        }
    } else {
        teacherQuestionReleaseSelectedQuestions.delete(id);
        delete teacherQuestionReleaseSelectedQuestionWeights[id];
        teacherQuestionReleaseSelectedQuestionData.delete(id);
    }

    normalizeTeacherQuestionReleaseWeights();
    renderTeacherQuestionReleaseQuestionBanks();
    renderTeacherQuestionReleaseSelectedQuestions();
    updateTeacherQuestionReleaseQuestionSummary();
    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();

    if (teacherQuestionReleaseStep === 3) {
        loadTeacherQuestionReleaseStep3();
    }
}

function renderTeacherQuestionReleaseSelectedQuestions() {
    const list = $('#question-release-selected-question-list');
    const empty = $('#question-release-selected-question-empty');

    if (!list.length) return;

    const selectedIds = Array.from(teacherQuestionReleaseSelectedQuestions);

    if (!selectedIds.length) {
        list.addClass('hidden').empty();
        empty.removeClass('hidden');
        return;
    }

    empty.addClass('hidden');
    list.removeClass('hidden');

    const allQuestions = getTeacherQuestionReleaseAllQuestions();

    const selectedQuestions =Array.from(teacherQuestionReleaseSelectedQuestions).map(id => {
        const currentQuestion = allQuestions.find(
            question => String(question.id) === String(id)
        );
    
        if (currentQuestion) {
            teacherQuestionReleaseSelectedQuestionData.set(
                String(id),
                currentQuestion
            );
    
            return currentQuestion;
        }
    
        return teacherQuestionReleaseSelectedQuestionData.get(
            String(id)
        ) || null;
    })
    .filter(Boolean);

    list.html(
        selectedQuestions.map((question, index) =>
            renderTeacherQuestionReleaseSelectedQuestionCard(question, index)
        ).join('')
    );
}

function renderTeacherQuestionReleaseSelectedQuestionCard(question, index) {
    const id = String(question.id);
    const weight = getTeacherQuestionReleaseQuestionWeight(id);

    const questionText = stripHtmlAndLimit(question.questions || question.question || '-', 180);

    const bank = getTeacherQuestionReleaseQuestionBankForQuestion(question);

    const questionType = question.tipe_soal || question.question_type || question.type || 'Soal';

    return `
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <div class="flex items-start gap-3">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#EAF6FF] text-[10px] font-bold text-[#0071BC]">
                    ${index + 1}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-md bg-blue-50 px-1.5 py-1 text-[9px] font-semibold text-blue-600">
                            ${escapeTeacherQuestionReleaseHtml(questionType)}
                        </span>
                    </div>

                    <p class="mt-1.5 text-[11px] font-semibold leading-relaxed text-gray-800">
                        ${escapeTeacherQuestionReleaseHtml(questionText)}
                    </p>

                    <p class="mt-1 text-[9px] text-gray-400 truncate">
                        ${escapeTeacherQuestionReleaseHtml(bank)}
                    </p>
                </div>

                <button type="button" data-question-release-remove="${escapeTeacherQuestionReleaseHtml(id)}"
                    class="shrink-0 text-gray-400 hover:text-red-500">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <div class="mt-3 flex items-center justify-end gap-2 border-t border-gray-100 pt-3">
                <span class="text-[10px] text-gray-400">Bobot:</span>

                <div class="flex items-center">
                    <input type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        inputmode="decimal"
                        data-question-release-weight="${escapeTeacherQuestionReleaseHtml(id)}"
                        value="${escapeTeacherQuestionReleaseHtml(formatTeacherQuestionReleaseWeight(weight))}"
                        class="w-16 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-center text-[11px] font-bold text-[#0071BC] outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-[#EAF6FF]">
                </div>
            </div>
        </div>
    `;
}

function getTeacherQuestionReleaseAllQuestions() {
    return Array.isArray(teacherQuestionReleaseQuestionBanks)
        ? [...teacherQuestionReleaseQuestionBanks]
        : [];
}

function getTeacherQuestionReleaseQuestionBankForQuestion(question) {
    const groups = getTeacherQuestionReleaseBankGroups();

    const bank = groups.find(group =>
        group.questions.some(
            item => String(item.id) === String(question.id)
        )
    );

    if (bank) return bank.name;

    return getTeacherQuestionReleaseBankName(question);
}

function getTeacherQuestionReleaseQuestionWeight(questionId) {
    const id = String(questionId);

    if (
        teacherQuestionReleaseSelectedQuestionWeights[id] !== undefined &&
        teacherQuestionReleaseSelectedQuestionWeights[id] !== null
    ) {
        return Number(teacherQuestionReleaseSelectedQuestionWeights[id]);
    }

    return 0;
}

function normalizeTeacherQuestionReleaseWeights() {
    const selectedIds = Array.from(teacherQuestionReleaseSelectedQuestions);

    if (!selectedIds.length) {
        teacherQuestionReleaseSelectedQuestionWeights = {};
        return;
    }

    const weight = 100 / selectedIds.length;
    const roundedWeight = Math.floor(weight * 100) / 100;
    const remainder = Number(
        (100 - roundedWeight * selectedIds.length).toFixed(2)
    );

    selectedIds.forEach((id, index) => {
        teacherQuestionReleaseSelectedQuestionWeights[id] = Number(
            (
                roundedWeight +
                (index === selectedIds.length - 1 ? remainder : 0)
            ).toFixed(2)
        );
    });

    Object.keys(teacherQuestionReleaseSelectedQuestionWeights).forEach(id => {
        if (!teacherQuestionReleaseSelectedQuestions.has(id)) {
            delete teacherQuestionReleaseSelectedQuestionWeights[id];
        }
    });
}

function updateTeacherQuestionReleaseQuestionWeight(questionId, value) {
    const id = String(questionId);

    if (!teacherQuestionReleaseSelectedQuestions.has(id)) return;

    let weight = Number(value);

    if (Number.isNaN(weight)) {
        weight = 0;
    }

    weight = Math.max(0, Math.min(100, weight));

    let otherQuestionsWeight = 0;

    teacherQuestionReleaseSelectedQuestions.forEach(questionId => {
        const currentId = String(questionId);

        if (currentId === id) return;

        otherQuestionsWeight += Number(
            teacherQuestionReleaseSelectedQuestionWeights[currentId] || 0
        );
    });

    otherQuestionsWeight = Number(
        otherQuestionsWeight.toFixed(2)
    );

    const maxWeight = Number(
        Math.max(0, 100 - otherQuestionsWeight).toFixed(2)
    );

    if (weight > maxWeight) {
        weight = maxWeight;
    }

    teacherQuestionReleaseSelectedQuestionWeights[id] =
        Number(weight.toFixed(2));

    const input = $(
        `[data-question-release-weight="${id}"]`
    );

    if (input.length && Number(input.val()) !== weight) {
        input.val(weight);
    }

    updateTeacherQuestionReleaseQuestionSummary();
    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();

    if (teacherQuestionReleaseStep === 3) {
        renderTeacherQuestionReleaseReview();
    }
}

function resetTeacherQuestionReleaseWeights() {
    normalizeTeacherQuestionReleaseWeights();
    renderTeacherQuestionReleaseSelectedQuestions();
    updateTeacherQuestionReleaseQuestionSummary();
    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();

    if (teacherQuestionReleaseStep === 3) {
        loadTeacherQuestionReleaseStep3();
    }
}

function getTeacherQuestionReleaseTotalWeight() {
    return Array.from(
        teacherQuestionReleaseSelectedQuestions
    ).reduce(
        (total, id) =>
            total + getTeacherQuestionReleaseQuestionWeight(id),
        0
    );
}

function isTeacherQuestionReleaseWeightValid() {
    if (!teacherQuestionReleaseSelectedQuestions.size) {
        return false;
    }

    const totalWeight = getTeacherQuestionReleaseTotalWeight();

    return Math.abs(
        Number(totalWeight.toFixed(2)) - 100
    ) < 0.01;
}

function updateTeacherQuestionReleaseQuestionSummary() {
    const selectedCount = teacherQuestionReleaseSelectedQuestions.size;

    const totalWeight = getTeacherQuestionReleaseTotalWeight();

    const averageWeight = selectedCount > 0 ? 100 / selectedCount : 0;

    $('#question-release-selected-question-count').text(selectedCount);

    $('#question-release-selected-question-weight').text(
        formatTeacherQuestionReleaseWeight(totalWeight)
    );

    $('#question-release-selected-question-total-weight').text(
        `${formatTeacherQuestionReleaseWeight(totalWeight)}`
    );

    const weightStatus =
        $('#question-release-weight-status');

    if (weightStatus.length) {
        weightStatus.removeClass(
            'bg-gray-100 text-gray-500 bg-emerald-50 text-emerald-600 bg-red-50 text-red-600 bg-amber-50 text-amber-600'
        );

        if (selectedCount === 0) {
            weightStatus.addClass('bg-gray-100 text-gray-500').html(
                `Total Bobot: <span id="question-release-selected-question-weight">0</span>`
            );
        } else if (isTeacherQuestionReleaseWeightValid()) {
            weightStatus.addClass('bg-emerald-50 text-emerald-600').html(
                `Total Bobot: <span id="question-release-selected-question-weight">${formatTeacherQuestionReleaseWeight(totalWeight)}</span>`
            );
        } else if (totalWeight > 100) {
            weightStatus.addClass('bg-red-50 text-red-600').html(`Total Bobot: <span id="question-release-selected-question-weight">
                ${formatTeacherQuestionReleaseWeight(totalWeight)}</span>`
            );
        } else {
            weightStatus.addClass('bg-amber-50 text-amber-600').html(
                `Total Bobot: <span id="question-release-selected-question-weight">${formatTeacherQuestionReleaseWeight(totalWeight)}</span>`
            );
        }
    }

    $('#question-release-total-weight-label').removeClass('text-gray-700 text-emerald-700 text-red-700 text-amber-700')
        .addClass(selectedCount === 0 ? 'text-gray-700' : isTeacherQuestionReleaseWeightValid() ? 'text-emerald-700' : totalWeight > 100 ? 'text-red-700' : 'text-amber-700')
        .text(`${formatTeacherQuestionReleaseWeight(totalWeight)}`);

    const warning = $('#question-release-weight-warning');

    if (warning.length) {
        if ( selectedCount > 0 && !isTeacherQuestionReleaseWeightValid()) {
            warning.removeClass('hidden');

            warning.text(
                totalWeight > 100 ? `Bobot melebihi 100 (${formatTeacherQuestionReleaseWeight(totalWeight)})` : `Bobot belum mencapai 100 (${formatTeacherQuestionReleaseWeight(totalWeight)})`
            );
        } else {
            warning.addClass('hidden');
        }
    }

    const selectedCountElement = $('#question-release-selected-question-count-badge');

    if (selectedCountElement.length) {
        selectedCountElement.text(`${selectedCount} butir`);
    }

    $('#question-release-selected-question-average-weight').text(
        formatTeacherQuestionReleaseWeight(averageWeight)
    );
}

function resetTeacherQuestionReleaseQuestionBankState(clearSelection = true) {
    teacherQuestionReleaseSelectedBank = null;
    teacherQuestionReleaseQuestions = [];
    teacherQuestionReleaseExpandedBanks.clear();

    if (clearSelection) {
        teacherQuestionReleaseSelectedQuestions.clear();
        teacherQuestionReleaseSelectedQuestionWeights = {};
    }

    $('#question-release-question-bank-loading').addClass('hidden');
    $('#question-release-question-bank-list').addClass('hidden').empty();
    $('#question-release-question-bank-empty').addClass('hidden');
    $('#question-release-question-bank-error').addClass('hidden');
    $('#question-release-question-bank-count').text('Memuat...');

    if (clearSelection) {
        $('#question-release-selected-question-count').text('0');
        $('#question-release-selected-question-weight').text('0');
        $('#question-release-selected-question-total-weight').text('0');
        $('#question-release-selected-question-list').addClass('hidden').empty();
        $('#question-release-selected-question-empty').removeClass('hidden');
    }
}

function loadTeacherQuestionReleaseStep2() {
    const loading = $('#question-release-question-bank-loading');
    const list = $('#question-release-question-bank-list');
    const empty = $('#question-release-question-bank-empty');
    const error = $('#question-release-question-bank-error');

    if (!teacherQuestionReleaseSelectedAssessment) {
        loading.addClass('hidden');
        list.addClass('hidden').empty();
        empty.removeClass('hidden');
        error.addClass('hidden');

        $('#question-release-question-bank-count').text('0 bank soal');

        renderTeacherQuestionReleaseSelectedQuestions();
        updateTeacherQuestionReleaseQuestionSummary();
        updateTeacherQuestionReleaseStepper();
        updateTeacherQuestionReleasePublishButton();
        return;
    }

    loading.removeClass('hidden');
    list.addClass('hidden');
    empty.addClass('hidden');
    error.addClass('hidden');

    $('#question-release-question-bank-count').text('Memuat...');

    requestAnimationFrame(() => {
        try {
            renderTeacherQuestionReleaseClassLevels();

            const banks = getTeacherQuestionReleaseBankGroups();

            loading.addClass('hidden');

            if (!banks.length) {
                list.addClass('hidden').empty();
                empty.removeClass('hidden');

                $('#question-release-question-bank-count') .text('0 bank soal');

                renderTeacherQuestionReleaseSelectedQuestions();
                updateTeacherQuestionReleaseQuestionSummary();
                updateTeacherQuestionReleaseStepper();
                updateTeacherQuestionReleasePublishButton();

                return;
            }

            renderTeacherQuestionReleaseQuestionBanks();
            renderTeacherQuestionReleaseSelectedQuestions();
            updateTeacherQuestionReleaseQuestionSummary();
            updateTeacherQuestionReleaseStepper();
            updateTeacherQuestionReleasePublishButton();
        } catch (exception) {
            console.error(
                'Error loadTeacherQuestionReleaseStep2:',
                exception
            );

            loading.addClass('hidden');
            list.addClass('hidden').empty();
            empty.addClass('hidden');
            error.removeClass('hidden');

            $('#question-release-question-bank-count').removeClass('bg-gray-100 text-gray-500').addClass('bg-red-50 text-red-600').text('Gagal memuat bank soal');
        }
    });
}

function renderTeacherQuestionReleaseSelectedAssessment() {
    const container = $('#question-release-selected-assessment');

    if (!container.length) return;

    const assessment = teacherQuestionReleaseSelectedAssessment;

    if (!assessment) {
        container.empty();
        return;
    }

    const mapelName = assessment.mapel?.mata_pelajaran || '-';
    const className = assessment.school_class?.class_name || '-';
    const assessmentType = assessment.school_assessment_type?.name || '-';
    const assignedQuestionCount = getTeacherQuestionReleaseAssignedQuestionCount(assessment);
    const assignedTotalWeight = getTeacherQuestionReleaseAssignedTotalWeight(assessment);

    container.html(`
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-lg bg-white border border-[#CFEAFF] px-2 py-1 text-[10px] font-bold text-[#0071BC]">
                        ${escapeTeacherQuestionReleaseHtml(className)}
                    </span>
                    <span class="rounded-lg bg-white border border-[#E5E7EB] px-2 py-1 text-[10px] font-medium text-gray-500">
                        ${escapeTeacherQuestionReleaseHtml(assessmentType)}
                    </span>
                </div>
                <h3 class="mt-2 text-sm font-bold text-gray-800 truncate">
                    ${escapeTeacherQuestionReleaseHtml(assessment.title || '-')}
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                    ${escapeTeacherQuestionReleaseHtml(mapelName)}
                    •
                    ${escapeTeacherQuestionReleaseHtml(className)}
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <div class="rounded-xl bg-white border border-[#CFEAFF] px-3 py-2.5 text-right">
                    <div class="text-[9px] text-gray-400">Sudah Terpasang</div>
                    <div class="mt-0.5 text-xs font-black text-gray-700">
                        ${assignedQuestionCount} Butir
                    </div>
                </div>
                <div class="rounded-xl bg-white border border-[#CFEAFF] px-3 py-2.5 text-right">
                    <div class="text-[9px] text-gray-400">Bobot</div>
                    <div class="mt-0.5 text-xs font-black ${assignedTotalWeight === 100 ? 'text-emerald-600' : 'text-[#0071BC]'}">
                        ${formatTeacherQuestionReleaseWeight(assignedTotalWeight)}
                    </div>
                </div>
            </div>
        </div>
    `);
}

function loadTeacherQuestionReleaseStep3() {
    const loading = $('#question-release-review-loading');
    const empty = $('#question-release-review-empty');
    const content = $('#question-release-review-content');

    if (!content.length) return;

    const hasAssessment = Boolean(teacherQuestionReleaseSelectedAssessment);

    const hasQuestions = teacherQuestionReleaseSelectedQuestions.size > 0;

    loading.removeClass('hidden');
    empty.addClass('hidden');
    content.addClass('hidden');

    requestAnimationFrame(() => {
        if (!hasAssessment || !hasQuestions) {
            loading.addClass('hidden');
            content.addClass('hidden');
            empty.removeClass('hidden');
            updateTeacherQuestionReleasePublishButton();
            return;
        }

        renderTeacherQuestionReleaseReview();

        loading.addClass('hidden');
        empty.addClass('hidden');
        content.removeClass('hidden');

        updateTeacherQuestionReleasePublishButton();
    });
}

function renderTeacherQuestionReleaseReview() {
    const container =
        $('#question-release-review-content');

    if (!container.length) return;

    const assessment = teacherQuestionReleaseSelectedAssessment;

    if (!assessment) {
        container.empty();
        return;
    }

    const mapelName = assessment.mapel?.mata_pelajaran || '-';
    const className = assessment.school_class?.class_name || '-';
    const assessmentType = assessment.school_assessment_type?.name || '-';

    const selectedCount = teacherQuestionReleaseSelectedQuestions.size;

    const totalWeight = getTeacherQuestionReleaseTotalWeight();
    const validWeight = isTeacherQuestionReleaseWeightValid();
    const allQuestions = getTeacherQuestionReleaseAllQuestions();

    const selectedQuestions = Array.from(teacherQuestionReleaseSelectedQuestions).map(id => allQuestions.find(question => String(question.id) === String(id))).filter(Boolean);

    const selectedBankMap = new Map();

    selectedQuestions.forEach(question => {
        const currentBank = getTeacherQuestionReleaseBankGroups().find(group => group.questions.some(item => String(item.id) === String(question.id)));

        const bankKey = currentBank ? String(currentBank.id) : getTeacherQuestionReleaseBankKey(question);

        if (!selectedBankMap.has(bankKey)) {
            if (currentBank) {
                selectedBankMap.set(bankKey, {
                    ...currentBank,
                    questions: []
                });
            } else {
                selectedBankMap.set(bankKey, {
                    id: bankKey,
                    kurikulum_id: question.kurikulum_id ?? null,
                    kelas_id: question.kelas_id ?? null,
                    mapel_id: question.mapel_id ?? null,
                    bab_id: question.bab_id ?? null,
                    sub_bab_id: question.sub_bab_id ?? null,
                    question_category: question.question_category ?? null,
                    school_partner_id: question.school_partner_id ?? null,
                    name: getTeacherQuestionReleaseBankName(question),
                    questions: []
                });
            }
        }

        selectedBankMap.get(bankKey).questions.push(question);
    });

    const selectedBanks = Array.from(selectedBankMap.values());

    const weightStatusClass = validWeight ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200';
    const weightTextClass = validWeight ? 'text-emerald-700' : 'text-red-700';
    const weightLabelClass = validWeight ? 'text-emerald-600' : 'text-red-600';

    container.html(`
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        Asesmen Target
                    </span>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EAF6FF] text-[#0071BC]">
                            <i class="fa-solid fa-clipboard-list text-sm"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-bold text-gray-800">
                                ${escapeTeacherQuestionReleaseHtml(assessment.title || assessment.name || '-')}
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                ${escapeTeacherQuestionReleaseHtml(className)}
                                •
                                ${escapeTeacherQuestionReleaseHtml(mapelName)}
                                •
                                ${escapeTeacherQuestionReleaseHtml(assessmentType)}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                            <div class="text-[10px] text-gray-400">Soal Dipilih</div>
                            <div class="mt-1 text-sm font-black text-gray-700">
                                ${selectedCount}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                            <div class="text-[10px] text-gray-400">Bank Soal</div>
                            <div class="mt-1 text-sm font-black text-gray-700">
                                ${selectedBanks.length}
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-3">
                            <div class="text-[10px] text-gray-400">Bobot Rata-rata</div>
                            <div class="mt-1 text-sm font-black text-gray-700">
                                ${formatTeacherQuestionReleaseWeight(
                                    selectedCount > 0 ? 100 / selectedCount : 0
                                )}
                            </div>
                        </div>

                        <div class="rounded-xl ${weightStatusClass} border p-3">
                            <div class="text-[10px] ${weightLabelClass}">
                                Total Bobot
                            </div>

                            <div class="mt-1 text-sm font-black ${weightTextClass}">
                                ${formatTeacherQuestionReleaseWeight(totalWeight)}
                            </div>
                        </div>
                    </div>

                    ${!validWeight ? `
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5">
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-circle-exclamation mt-0.5 text-xs text-red-500"></i>
                                <p class="text-[10px] leading-relaxed text-red-600">
                                    Total bobot harus tepat 100 sebelum asesmen dapat dirilis.
                                </p>
                            </div>
                        </div>
                    ` : `
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5">
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-circle-check mt-0.5 text-xs text-emerald-500"></i>
                                <p class="text-[10px] leading-relaxed text-emerald-600">
                                    Seluruh bobot soal sudah valid dan berjumlah 100.
                                </p>
                            </div>
                        </div>
                    `}
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div>
                        <div class="text-xs font-bold text-gray-700">
                            Soal yang Akan Dirilis
                        </div>

                        <div class="mt-0.5 text-[10px] text-gray-400">
                            ${selectedCount} butir dari ${selectedBanks.length} bank soal
                        </div>
                    </div>

                    <div class="shrink-0 rounded-lg bg-[#EAF6FF] px-2.5 py-1.5 text-[10px] font-bold text-[#0071BC]">
                        ${formatTeacherQuestionReleaseWeight(totalWeight)}
                    </div>
                </div>

                <div class="p-4 sm:p-5 space-y-4">
                    ${selectedBanks.map((bank, bankIndex) =>
                        renderTeacherQuestionReleaseReviewBank(bank, bankIndex)
                    ).join('')}
                </div>
            </div>
        </div>
    `);
}

function renderTeacherQuestionReleaseReviewBank(bank, bankIndex) {
    const meta =
        getTeacherQuestionReleaseBankMeta(bank);

    const questions = bank.questions || [];

    return `
        <div class="rounded-xl border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-100 px-3 py-3">
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EAF6FF] text-[10px] font-black text-[#0071BC]">
                        ${bankIndex + 1}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rounded-md bg-[#EAF6FF] px-2 py-1 text-[9px] font-semibold text-[#0071BC]">
                                ${escapeTeacherQuestionReleaseHtml(meta.mapel)}
                            </span>

                            <span class="rounded-md bg-purple-50 px-2 py-1 text-[9px] font-semibold text-purple-600">
                                ${escapeTeacherQuestionReleaseHtml(meta.category)}
                            </span>

                            <span class="rounded-md bg-gray-100 px-2 py-1 text-[9px] font-medium text-gray-500">
                                ${escapeTeacherQuestionReleaseHtml(meta.kelas)}
                            </span>
                        </div>

                        <h4 class="mt-1.5 text-xs font-bold text-gray-800">
                            ${escapeTeacherQuestionReleaseHtml(meta.bab !== '-' ? meta.bab : 'Bank Soal')}
                        </h4>

                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-[9px] text-gray-400">
                            <span>
                                Kurikulum:
                                <strong class="text-gray-500">
                                    ${escapeTeacherQuestionReleaseHtml(meta.kurikulum)}
                                </strong>
                            </span>

                            <span>
                                Bab:
                                <strong class="text-gray-500">
                                    ${escapeTeacherQuestionReleaseHtml(meta.bab)}
                                </strong>
                            </span>

                            <span>
                                Sub Bab:
                                <strong class="text-gray-500">
                                    ${escapeTeacherQuestionReleaseHtml(meta.subBab)}
                                </strong>
                            </span>

                            <span>
                                Sumber:
                                <strong class="text-gray-500">
                                    ${escapeTeacherQuestionReleaseHtml(meta.source)}
                                </strong>
                            </span>

                            ${meta.uploadDate ? `
                                <span>
                                    Tanggal Upload:
                                    <strong class="text-gray-500">
                                        ${escapeTeacherQuestionReleaseHtml(meta.uploadDate)}
                                    </strong>
                                </span>
                            ` : ''}
                        </div>
                    </div>

                    <div class="shrink-0 rounded-lg bg-white border border-gray-200 px-2 py-1 text-[9px] font-semibold text-gray-500">
                        ${questions.length} soal
                    </div>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                ${questions.map((question, index) =>
        renderTeacherQuestionReleaseReviewQuestion(
            question,
            index
        )
    ).join('')}
            </div>
        </div>
    `;
}

function renderTeacherQuestionReleaseReviewQuestion(question, index) {
    const id = String(question.id);

    const questionType =
        question.tipe_soal ||
        question.question_type ||
        question.type ||
        'Soal';

    const difficulty =
        question.difficulty ||
        'Umum';

    const questionText =
        stripHtmlAndLimit(
            question.questions ||
            question.question ||
            '-',
            220
        );

    const weight =
        getTeacherQuestionReleaseQuestionWeight(id);

    return `
        <div class="px-3 py-3 bg-white">
            <div class="flex items-start gap-3">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-gray-100 text-[9px] font-bold text-gray-500">
                    ${index + 1}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-md bg-blue-50 px-1.5 py-1 text-[9px] font-semibold text-blue-600">
                            ${escapeTeacherQuestionReleaseHtml(questionType)}
                        </span>

                        <span class="rounded-md bg-gray-100 px-1.5 py-1 text-[9px] font-medium text-gray-500">
                            ${escapeTeacherQuestionReleaseHtml(difficulty)}
                        </span>

                        <span class="text-[9px] text-gray-400">
                            q-${escapeTeacherQuestionReleaseHtml(id)}
                        </span>
                    </div>

                    <p class="mt-1.5 text-[10px] leading-relaxed text-gray-700">
                        ${escapeTeacherQuestionReleaseHtml(questionText)}
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <div class="text-[9px] text-gray-400">
                        Bobot
                    </div>

                    <div class="mt-0.5 text-xs font-black text-[#0071BC]">
                        ${formatTeacherQuestionReleaseWeight(weight)}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function updateTeacherQuestionReleasePublishButton() {
    const button =
        $('#question-release-publish');

    if (!button.length) return;

    const canPublish =
        Boolean(teacherQuestionReleaseSelectedAssessment) &&
        teacherQuestionReleaseSelectedQuestions.size > 0 &&
        isTeacherQuestionReleaseWeightValid();

    button.prop('disabled', !canPublish);

    button.removeClass(
        'bg-gray-300 text-gray-500 cursor-not-allowed bg-emerald-600 text-white hover:bg-emerald-700'
    );

    if (canPublish) {
        button.addClass(
            'bg-emerald-600 text-white hover:bg-emerald-700'
        );
    } else {
        button.addClass(
            'bg-gray-300 text-gray-500 cursor-not-allowed'
        );
    }
}

function goToTeacherQuestionReleaseStep(step) {
    if (step < 1 || step > 3) return;

    const hasAssessment =
        Boolean(teacherQuestionReleaseSelectedAssessment);

    const hasQuestions =
        teacherQuestionReleaseSelectedQuestions.size > 0;

    const validWeight =
        isTeacherQuestionReleaseWeightValid();

    if (step >= 2 && !hasAssessment) return;

    if (
        step === 3 &&
        (!hasQuestions || !validWeight)
    ) {
        return;
    }

    teacherQuestionReleaseStep = step;

    $('.question-release-panel, #question-release-panel-1, #question-release-panel-2, #question-release-panel-3')
        .addClass('hidden');

    $(`#question-release-panel-${step}`)
        .removeClass('hidden');

    if (step === 1) {
        renderTeacherQuestionReleaseAssessments();
    } else if (step === 2) {
        renderTeacherQuestionReleaseSelectedAssessment();
        loadTeacherQuestionReleaseStep2();
    } else if (step === 3) {
        loadTeacherQuestionReleaseStep3();
    }

    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();

    const wizard =
        $('#question-for-release-wizard');

    if (wizard.length) {
        window.scrollTo({
            top: wizard.offset().top - 30,
            behavior: 'smooth'
        });
    }
}

function updateTeacherQuestionReleaseStepper() {
    const currentStep =
        teacherQuestionReleaseStep;

    const hasAssessment =
        Boolean(teacherQuestionReleaseSelectedAssessment);

    const hasQuestions =
        teacherQuestionReleaseSelectedQuestions.size > 0;

    const validWeight =
        isTeacherQuestionReleaseWeightValid();

    for (let step = 1; step <= 3; step++) {
        const stepButton =
            $(`#question-release-step-${step}`);

        const number =
            stepButton.find(
                '.question-release-step-number'
            );

        const label =
            stepButton.find(
                '.question-release-step-label'
            );

        const canAccess =
            step === 1 ||
            (step === 2 && hasAssessment) ||
            (
                step === 3 &&
                hasAssessment &&
                hasQuestions &&
                validWeight
            );

        if (step < currentStep) {
            stepButton
                .prop('disabled', false)
                .removeClass(
                    'cursor-not-allowed opacity-60'
                )
                .addClass('cursor-pointer');

            number
                .removeClass(
                    'bg-gray-100 text-gray-400 bg-[#0071BC] text-white ring-4 ring-[#EAF6FF]'
                )
                .addClass(
                    'bg-emerald-500 text-white'
                )
                .html(
                    '<i class="fa-solid fa-check text-xs"></i>'
                );

            label
                .removeClass(
                    'text-gray-400 text-[#0071BC]'
                )
                .addClass(
                    'text-emerald-600'
                );

            continue;
        }

        if (step === currentStep) {
            stepButton
                .prop('disabled', false)
                .removeClass(
                    'cursor-not-allowed opacity-60'
                )
                .addClass(
                    'cursor-pointer'
                );

            number
                .removeClass(
                    'bg-gray-100 text-gray-400 bg-emerald-500'
                )
                .addClass(
                    'bg-[#0071BC] text-white ring-4 ring-[#EAF6FF]'
                )
                .text(step);

            label
                .removeClass(
                    'text-gray-400 text-emerald-600'
                )
                .addClass(
                    'text-[#0071BC]'
                );

            continue;
        }

        stepButton
            .prop(
                'disabled',
                !canAccess
            )
            .toggleClass(
                'cursor-pointer',
                canAccess
            )
            .toggleClass(
                'cursor-not-allowed opacity-60',
                !canAccess
            );

        number
            .removeClass(
                'bg-[#0071BC] text-white ring-4 ring-[#EAF6FF] bg-emerald-500'
            )
            .addClass(
                'bg-gray-100 text-gray-400'
            )
            .text(step);

        label
            .removeClass(
                'text-[#0071BC] text-emerald-600'
            )
            .addClass(
                'text-gray-400'
            );
    }

    $('#question-release-connector-1')
        .toggleClass(
            'bg-[#0071BC]',
            hasAssessment && currentStep >= 2
        )
        .toggleClass(
            'bg-gray-200',
            !hasAssessment || currentStep < 2
        );

    $('#question-release-connector-2')
        .toggleClass(
            'bg-[#0071BC]',
            hasQuestions &&
            validWeight &&
            currentStep >= 3
        )
        .toggleClass(
            'bg-gray-200',
            !hasQuestions ||
            !validWeight ||
            currentStep < 3
        );

    $('#question-release-next-step-1')
        .prop(
            'disabled',
            !hasAssessment
        );

    $('#question-release-next-step-2')
        .prop(
            'disabled',
            !hasQuestions ||
            !validWeight
        );
}

function formatTeacherQuestionReleaseWeight(value) {
    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return '0';
    }

    return Number(value)
        .toFixed(2)
        .replace(/\.00$/, '')
        .replace(/(\.\d)0$/, '$1');
}

function formatTeacherQuestionReleaseDateTime(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    ).format(date);
}

function formatTeacherQuestionReleaseDate(dateString) {
    if (!dateString) return null;

    const safeDateString = typeof dateString === 'string' && dateString.includes(' ') && !dateString.includes('T')
        ? dateString.replace(' ', 'T')
        : dateString;

    const date = new Date(safeDateString);
    if (isNaN(date.getTime())) {
        return typeof dateString === 'string' ? dateString : null;
    }

    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const day = date.getDate();
    const monthName = months[date.getMonth()];
    const year = date.getFullYear();

    return `${day} ${monthName} ${year}`;
}

function stripHtmlAndLimit(html, limit = 120) {
    const tempDiv =
        document.createElement('div');

    tempDiv.innerHTML = html || '';

    tempDiv
        .querySelectorAll('img')
        .forEach(img => img.remove());

    let text =
        tempDiv.textContent ||
        tempDiv.innerText ||
        '';

    text = text.trim();

    if (text.length > limit) {
        text =
            text.substring(0, limit) +
            '...';
    }

    return text;
}

function escapeTeacherQuestionReleaseHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function bindTeacherQuestionReleaseEvents() {
    $(document).off(
        '.teacherQuestionRelease'
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '[data-question-release-assessment]',
        function () {
            selectTeacherQuestionReleaseAssessment(
                $(this).attr(
                    'data-question-release-assessment'
                )
            );
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '[data-question-release-bank-toggle]',
        function () {
            toggleTeacherQuestionReleaseBank(
                $(this).attr(
                    'data-question-release-bank-toggle'
                )
            );
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '[data-question-release-bank-select-all]',
        function (e) {
            e.preventDefault();
            e.stopPropagation();

            selectAllTeacherQuestionReleaseBank(
                $(this).attr(
                    'data-question-release-bank-select-all'
                )
            );
        }
    );

    $(document).on(
        'change.teacherQuestionRelease',
        '.question-release-question-checkbox',
        function (e) {
            e.stopPropagation();

            toggleTeacherQuestionReleaseQuestion(
                $(this).attr('data-question-id'),
                $(this).is(':checked')
            );
        }
    );

    $(document)
        .off(
            'change.teacherQuestionRelease',
            '.question-release-class-level-radio'
        )
        .on(
            'change.teacherQuestionRelease',
            '.question-release-class-level-radio',
            function () {
                const newClassLevel =
                    Number($(this).val());

                teacherQuestionReleaseSelectedClassLevel =
                    newClassLevel;

                teacherQuestionReleaseSelectedBank = null;
                teacherQuestionReleaseQuestions = [];
                teacherQuestionReleaseExpandedBanks.clear();

                renderTeacherQuestionReleaseQuestionBanks();
                renderTeacherQuestionReleaseSelectedQuestions();
                updateTeacherQuestionReleaseQuestionSummary();
                updateTeacherQuestionReleaseStepper();
                updateTeacherQuestionReleasePublishButton();

                if (teacherQuestionReleaseStep === 3) {
                    loadTeacherQuestionReleaseStep3();
                }
            }
        );

    $(document).on(
        'input.teacherQuestionRelease',
        '[data-question-release-weight]',
        function () {
            updateTeacherQuestionReleaseQuestionWeight(
                $(this).attr(
                    'data-question-release-weight'
                ),
                $(this).val()
            );
        }
    );

    $(document).on(
        'blur.teacherQuestionRelease',
        '[data-question-release-weight]',
        function () {
            const id =
                $(this).attr(
                    'data-question-release-weight'
                );

            $(this).val(
                formatTeacherQuestionReleaseWeight(
                    getTeacherQuestionReleaseQuestionWeight(id)
                )
            );

            updateTeacherQuestionReleaseQuestionSummary();
            updateTeacherQuestionReleaseStepper();
            updateTeacherQuestionReleasePublishButton();

            if (teacherQuestionReleaseStep === 3) {
                renderTeacherQuestionReleaseReview();
            }
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '[data-question-release-remove]',
        function () {
            if (teacherQuestionReleaseSelectedAssessmentHasAnswers) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Asesmen Sudah Dikerjakan',
                    text: 'Soal tidak dapat dihapus karena sudah ada siswa yang menjawab asesmen ini.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#0071BC'
                });

                return;
            }

            const id = String(
                $(this).attr(
                    'data-question-release-remove'
                )
            );

            teacherQuestionReleaseSelectedQuestions.delete(id);
            delete teacherQuestionReleaseSelectedQuestionWeights[id];
            teacherQuestionReleaseSelectedQuestionData.delete(id);

            normalizeTeacherQuestionReleaseWeights();
            renderTeacherQuestionReleaseQuestionBanks();
            renderTeacherQuestionReleaseSelectedQuestions();
            updateTeacherQuestionReleaseQuestionSummary();
            updateTeacherQuestionReleaseStepper();
            updateTeacherQuestionReleasePublishButton();

            if (teacherQuestionReleaseStep === 3) {
                loadTeacherQuestionReleaseStep3();
            }
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-normalize-weight, #question-release-reset-weight',
        function () {
            resetTeacherQuestionReleaseWeights();
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-next-step-1',
        function () {
            if (!teacherQuestionReleaseSelectedAssessment) {
                return;
            }

            goToTeacherQuestionReleaseStep(2);
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-prev-step-2',
        function () {
            goToTeacherQuestionReleaseStep(1);
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-next-step-2',
        function () {
            if (
                !teacherQuestionReleaseSelectedQuestions.size
            ) {
                return;
            }

            if (
                !isTeacherQuestionReleaseWeightValid()
            ) {
                return;
            }

            goToTeacherQuestionReleaseStep(3);
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-prev-step-3',
        function () {
            goToTeacherQuestionReleaseStep(2);
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-review-empty-back',
        function () {
            goToTeacherQuestionReleaseStep(2);
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '.question-release-step[data-step="1"]',
        function () {
            goToTeacherQuestionReleaseStep(1);
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '.question-release-step[data-step="2"]',
        function () {
            if (
                teacherQuestionReleaseSelectedAssessment
            ) {
                goToTeacherQuestionReleaseStep(2);
            }
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '.question-release-step[data-step="3"]',
        function () {
            if (
                teacherQuestionReleaseSelectedAssessment &&
                teacherQuestionReleaseSelectedQuestions.size > 0 &&
                isTeacherQuestionReleaseWeightValid()
            ) {
                goToTeacherQuestionReleaseStep(3);
            }
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-assessment-retry',
        function () {
            formQuestionForRelease();
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-question-bank-retry',
        function () {
            loadTeacherQuestionReleaseStep2();
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-open-all-banks',
        function () {
            getTeacherQuestionReleaseBankGroups()
                .forEach(bank => {
                    teacherQuestionReleaseExpandedBanks.add(
                        String(bank.id)
                    );
                });

            renderTeacherQuestionReleaseQuestionBanks();
        }
    );

    $(document).on(
        'click.teacherQuestionRelease',
        '#question-release-close-all-banks',
        function () {
            teacherQuestionReleaseExpandedBanks.clear();
            renderTeacherQuestionReleaseQuestionBanks();
        }
    );

    $(document).on(
        'change.teacherQuestionRelease',
        '#dropdown-filter-tahun-ajaran',
        function () {
            formQuestionForRelease(
                $(this).val(),
                $('#dropdown-filter-class').val(),
                $('#dropdown-assessment-type').val(),
                $('#dropdown-filter-mapel').val(),
                $('#dropdown-filter-semester').val(),
                $('#search_question').val()
            );
        }
    );

    $(document).on(
        'change.teacherQuestionRelease',
        '#dropdown-filter-class',
        function () {
            formQuestionForRelease(
                $('#dropdown-filter-tahun-ajaran').val(),
                $(this).val(),
                $('#dropdown-assessment-type').val(),
                $('#dropdown-filter-mapel').val(),
                $('#dropdown-filter-semester').val(),
                $('#search_question').val(),
                null,
                null,
                null,
                null,
                null,
                true
            );
        }
    );

    $(document).on(
        'change.teacherQuestionRelease',
        '#dropdown-assessment-type',
        function () {
            formQuestionForRelease(
                $('#dropdown-filter-tahun-ajaran').val(),
                $('#dropdown-filter-class').val(),
                $(this).val(),
                $('#dropdown-filter-mapel').val(),
                $('#dropdown-filter-semester').val(),
                $('#search_question').val()
            );
        }
    );

    $(document).on(
        'change.teacherQuestionRelease',
        '#dropdown-filter-mapel',
        function () {
            formQuestionForRelease(
                $('#dropdown-filter-tahun-ajaran').val(),
                $('#dropdown-filter-class').val(),
                $('#dropdown-assessment-type').val(),
                $(this).val(),
                $('#dropdown-filter-semester').val(),
                $('#search_question').val()
            );
        }
    );

    $(document).on(
        'change.teacherQuestionRelease',
        '#dropdown-filter-semester',
        function () {
            formQuestionForRelease(
                $('#dropdown-filter-tahun-ajaran').val(),
                $('#dropdown-filter-class').val(),
                $('#dropdown-assessment-type').val(),
                $('#dropdown-filter-mapel').val(),
                $(this).val(),
                $('#search_question').val()
            );
        }
    );

    $(document).on(
        'input.teacherQuestionRelease',
        '#search_question',
        function () {
            formQuestionForRelease(
                $('#dropdown-filter-tahun-ajaran').val(),
                $('#dropdown-filter-class').val(),
                $('#dropdown-assessment-type').val(),
                $('#dropdown-filter-mapel').val(),
                $('#dropdown-filter-semester').val(),
                $(this).val()
            );
        }
    );

    $(document).on(
        'change.teacherQuestionRelease',
        '#id_kurikulum, #id_kelas, #id_mapel, #id_bab, #id_sub_bab',
        function () {
            formQuestionForRelease(
                $('#dropdown-filter-tahun-ajaran').val(),
                $('#dropdown-filter-class').val(),
                $('#dropdown-assessment-type').val(),
                $('#dropdown-filter-mapel').val(),
                $('#dropdown-filter-semester').val(),
                $('#search_question').val(),
                $('#id_kurikulum').val(),
                $('#id_kelas').val(),
                $('#id_mapel').val(),
                $('#id_bab').val(),
                $('#id_sub_bab').val()
            );
        }
    );
}

$(document).ready(function () {
    bindTeacherQuestionReleaseEvents();
    updateTeacherQuestionReleaseStepper();
    updateTeacherQuestionReleasePublishButton();
    formQuestionForRelease();
});

// Preview question
$(document)
    .off(
        'click.teacherQuestionReleasePreview',
        '.btn-preview-question'
    )
    .on(
        'click.teacherQuestionReleasePreview',
        '.btn-preview-question',
        function (e) {
            e.preventDefault();
            e.stopPropagation();

            const questionId = String(
                $(this).attr('data-question-id') || ''
            );

            if (!questionId) return;

            const question =
                teacherQuestionReleaseQuestionBanks.find(
                    item => String(item.id) === questionId
                );

            if (!question) {
                console.error(
                    'Question preview data not found:',
                    questionId
                );

                return;
            }

            renderTeacherQuestionReleasePreview(question);
        }
    );

let isProcessing = false;

// Submit assessment release
$('#question-release-publish').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById(
        'container-form-teacher-question-bank-for-release'
    );

    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role || !schoolName || !schoolId) return;

    if (!teacherQuestionReleaseSelectedAssessment) {
        alert('Silakan pilih asesmen terlebih dahulu.');
        return;
    }

    if (!teacherQuestionReleaseSelectedQuestions.size) {
        alert('Silakan pilih minimal satu soal.');
        return;
    }

    if (!isTeacherQuestionReleaseWeightValid()) {
        alert('Total bobot soal harus tepat 100.');
        return;
    }

    const form = $('#teacher-create-question-bank-for-release-form')[0];

    if (!form) return;

    if (isProcessing) return;

    isProcessing = true;

    const btn = $(this);

    btn.prop('disabled', true);

    const formData = new FormData(form);

    // Assessment
    formData.set(
        'school_assessment_id',
        teacherQuestionReleaseSelectedAssessment.id
    );

    // Questions + weights
    Array.from(teacherQuestionReleaseSelectedQuestions).forEach(questionId => {
        const id = String(questionId);

        const weight =
            teacherQuestionReleaseSelectedQuestionWeights[id] ?? 0;

        formData.append('question_id[]', id);
        formData.append(`question_weight[${id}]`, weight);
    });

    // Total weight
    const totalWeight = getTeacherQuestionReleaseTotalWeight();

    formData.set(
        'total_weight',
        Number(totalWeight.toFixed(2))
    );

    // Status
    const status = $(this).data('status');
    formData.set('status', status);

    for (const [key, value] of formData.entries()) {
        console.log(key, value);
    }

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,

        success: function (response) {
            $('#alert-success-create-question-for-release').html(`
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

            // Reset form
            form.reset();

            // Reset wizard state
            teacherQuestionReleaseSelectedAssessment = null;
            teacherQuestionReleaseSelectedBank = null;
            teacherQuestionReleaseQuestions = [];
            teacherQuestionReleaseSelectedQuestions.clear();
            teacherQuestionReleaseSelectedQuestionWeights = {};
            teacherQuestionReleaseExpandedBanks.clear();
            teacherQuestionReleaseSelectedClassLevel = null;

            isProcessing = false;
            btn.prop('disabled', false);

            formQuestionForRelease();
            paginateQuestionForRelease();
        },

        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON?.errors || {};

                console.error('Validation errors:', errors);

                $.each(errors, function (field, messages) {

                    if (field.startsWith('question_weight.')) {

                        const questionId = field.split('.')[1];

                        const input = document.querySelector(
                            `[data-question-release-weight="${questionId}"]`
                        );

                        if (input) {
                            input.classList.add('border-red-400');
                        }

                        return;
                    }

                    $(`#error-${field}`)
                        .removeClass('hidden')
                        .text(messages[0]);
                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});