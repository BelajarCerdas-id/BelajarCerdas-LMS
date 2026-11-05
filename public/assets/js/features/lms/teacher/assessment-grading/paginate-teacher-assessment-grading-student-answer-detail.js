let currentQuestionIndex = 0;
let cachedGradingData = null;

function escapeHtml(text) {
    if (!text) return '-';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function addClassToImgTags(html, className) {
    if (!html) return '';
    return html
        .replace(/<img\b(?![^>]*class=)[^>]*>/g, (imgTag) => {
            return imgTag.replace('<img', `<img class="${className}"`);
        })
        .replace(/<img\b([^>]*?)class="(.*?)"/g, (imgTag, before, existingClasses) => {
            return `<img ${before}class="${existingClasses} ${className}"`;
        });
}

function renderGradingView(response, selectedIndex = 0, options = {}) {
    const isAdjustment = !!options.isAdjustment;

    // Save scroll positions if not explicitly passed
    const qListEl = document.getElementById('container-questions-list');
    const savedQuestionsScroll = (options.savedQuestionsScroll !== undefined && options.savedQuestionsScroll !== null)
        ? options.savedQuestionsScroll
        : (qListEl ? qListEl.scrollTop : null);

    const savedWindowScroll = (options.savedWindowScroll !== undefined && options.savedWindowScroll !== null)
        ? options.savedWindowScroll
        : (window.scrollY || document.documentElement.scrollTop);

    const qDetailEl = document.getElementById('container-question-detail');
    const savedDetailScroll = (options.savedDetailScroll !== undefined && options.savedDetailScroll !== null)
        ? options.savedDetailScroll
        : (qDetailEl ? qDetailEl.scrollTop : null);

    const assessment = response.assessment;
    const student = response.student;
    const previousStudent = response.previousStudent;
    const nextStudent = response.nextStudent;

    // RENDER / UPDATE COMPACT HEADER
    const assessmentInfo = $('#header-assessment-info');
    if (!assessmentInfo.children().length) {
        assessmentInfo.html(`
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <h1 class="text-base sm:text-lg font-bold leading-tight wrap-break-word text-white">
                        ${escapeHtml(assessment.title ?? '-')}
                    </h1>

                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-blue-100">
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <i class="fa-solid fa-user text-[11px] text-blue-200"></i>
                            <span class="text-white font-semibold">Siswa:</span> ${escapeHtml(student.student_profile?.nama_lengkap ?? '-')}
                        </span>

                        <span class="hidden md:inline text-blue-300/60">•</span>

                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <i class="fa-solid fa-book-open text-[11px] text-blue-200"></i>
                            <span class="text-white font-semibold">Mata Pelajaran:</span> ${escapeHtml(assessment.mapel?.mata_pelajaran ?? '-')}
                        </span>

                        <span class="hidden md:inline text-blue-300/60">•</span>

                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <i class="fa-solid fa-graduation-cap text-[11px] text-blue-200"></i>
                            <span class="text-white font-semibold">Kelas:</span> ${escapeHtml(student.student_school_class?.[0]?.school_class?.class_name ?? '-')}
                        </span>
                    </div>
                </div>

                <!-- Next / Previous Student Navigation Buttons -->
                <div class="flex items-center gap-2 shrink-0 self-start md:self-center">
                    <button type="button" class="btn-navigate-student px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition ${previousStudent ? 'bg-white/20 hover:bg-white/30 text-white cursor-pointer active:scale-95 shadow-xs' : 'bg-white/10 text-white/40 cursor-not-allowed opacity-50'}"
                        ${!previousStudent ? 'disabled' : ''}
                        data-student-id="${previousStudent?.id ?? ''}"
                        title="${previousStudent ? 'Siswa Sebelumnya: ' + escapeHtml(previousStudent.name) : 'Tidak ada siswa sebelumnya'}">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        <span class="hidden sm:inline">Siswa</span> Sebelumnya
                    </button>

                    <button type="button" class="btn-navigate-student px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition ${nextStudent ? 'bg-white/20 hover:bg-white/30 text-white cursor-pointer active:scale-95 shadow-xs' : 'bg-white/10 text-white/40 cursor-not-allowed opacity-50'}"
                        ${!nextStudent ? 'disabled' : ''}
                        data-student-id="${nextStudent?.id ?? ''}"
                        title="${nextStudent ? 'Siswa Selanjutnya: ' + escapeHtml(nextStudent.name) : 'Tidak ada siswa selanjutnya'}">
                        <span class="hidden sm:inline">Siswa</span> Selanjutnya
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        `);
        assessmentInfo.show();
    }

    if (!response.data || response.data.length === 0) {
        $('#form-assessment-grading').hide();
        $('#empty-message-school-assessment-question').show();
        return;
    }

    $('#empty-message-school-assessment-question').hide();
    $('#form-assessment-grading').show();

    const questions = response.data;
    const questionsAnswer = response.questionsAnswer || {};

    if (selectedIndex < 0 || selectedIndex >= questions.length) {
        selectedIndex = 0;
    }
    currentQuestionIndex = selectedIndex;

    const question = questions[selectedIndex];
    const key = `${assessment.id}_${question.id}`;
    const answerData = questionsAnswer[key] ?? {};

    const scoreUser = answerData?.question_score ?? 0;
    const teacherFeedback = answerData?.teacher_feedback ?? '';
    const statusAnswer = answerData?.status_answer ?? 'draft';

    const questionType = question?.lms_question_bank?.tipe_soal?.toLowerCase();
    const optionsList = question?.lms_question_bank?.lms_question_option ?? [];
    const studentAnswer = question?.student_assessment_answer?.length ? question.student_assessment_answer[0].answer_value : [];

    let questionStatus = '';

    if (questionType === 'matching') {
        let pairs = {};

        if (studentAnswer) {
            try {
                pairs = typeof studentAnswer === 'string' ? JSON.parse(studentAnswer) : studentAnswer;
            } catch {
                pairs = {};
            }
        }

        const leftItems = optionsList.filter(o => o.options_key.startsWith('LEFT'));
        const correctPairs = {};
        leftItems.forEach(item => {
            correctPairs[item.options_key] = item.extra_data?.pair_with;
        });

        const total = Object.keys(correctPairs).length;

        if (!pairs || Object.keys(pairs).length === 0) {
            questionStatus = 'not_answered';
        } else {
            let correctCount = 0;
            Object.keys(correctPairs).forEach(leftKey => {
                if (pairs[leftKey] === correctPairs[leftKey]) {
                    correctCount++;
                }
            });

            if (correctCount === total) {
                questionStatus = 'correct';
            } else {
                questionStatus = 'wrong';
            }
        }
    } else if (questionType === 'pg_kompleks') {
        let answers = {};

        if (studentAnswer) {
            try {
                answers = typeof studentAnswer === 'string' ? JSON.parse(studentAnswer) : studentAnswer;
            } catch {
                answers = {};
            }
        }

        const leftItems = optionsList.filter(o => o.options_key.startsWith('ITEM'));
        const correctAnswers = {};
        leftItems.forEach(item => {
            correctAnswers[item.options_key] = item.extra_data?.answer;
        });

        const total = Object.keys(correctAnswers).length;

        if (!answers || Object.keys(answers).length === 0) {
            questionStatus = 'not_answered';
        } else {
            let correctCount = 0;
            Object.keys(correctAnswers).forEach(leftKey => {
                if (answers[leftKey] === correctAnswers[leftKey]) {
                    correctCount++;
                }
            });

            if (correctCount === total) {
                questionStatus = 'correct';
            } else {
                questionStatus = 'wrong';
            }
        }
    } else {
        if (!studentAnswer || studentAnswer.length === 0) {
            questionStatus = 'not_answered';
        } else {
            const correctOptions = optionsList.filter(o => o.is_correct == 1).map(o => o.options_key);
            const studentOptions = Array.isArray(studentAnswer) ? studentAnswer : [studentAnswer];
            const isCorrect = studentOptions.length === correctOptions.length && studentOptions.every(o => correctOptions.includes(o));
            questionStatus = isCorrect ? 'correct' : 'wrong';
        }
    }

    let statusBadge = '';
    if (questionType !== 'essay') {
        if (questionStatus === 'not_answered') {
            statusBadge = `
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700">
                    Tidak Dijawab
                </span>
            `;
        } else if (questionStatus === 'correct') {
            statusBadge = `
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700">
                    Jawaban Benar
                </span>
            `;
        } else if (questionStatus === 'wrong') {
            statusBadge = `
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700">
                    Jawaban Salah
                </span>
            `;
        }
    }

    // RENDER MCQ / MCMA
    function renderMCQOptions() {
        const optionLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        const studentOptions = Array.isArray(studentAnswer)
            ? studentAnswer.map(value => String(value).trim().toLowerCase())
            : studentAnswer !== null && studentAnswer !== undefined
                ? [String(studentAnswer).trim().toLowerCase()]
                : [];

        return optionsList.map((item, index) => {
            const optKey = String(item.options_key).trim();
            const normalizedKey = optKey.toLowerCase();

            const containsImage = /<img\s+[^>]*src=/.test(item.options_value);
            const isChosen = studentOptions.includes(normalizedKey);
            const isCorrect = item.is_correct == 1;

            let displayLetter = optionLetters[index] || '';

            if (/^[A-Za-z]$/.test(optKey)) {
                displayLetter = optKey.toUpperCase();
            } else if (/^option_?(\d+)$/i.test(optKey)) {
                const num = parseInt(optKey.replace(/\D/g, ''), 10);
                if (num >= 1 && num <= 26) {
                    displayLetter = String.fromCharCode(64 + num);
                }
            }

            if (!displayLetter) {
                displayLetter = optionLetters[index] || optKey;
            }

            let optStyle = 'bg-white text-slate-700 border-slate-200';
            let optTag = '';
            let badgeStyle = 'bg-slate-100 text-slate-600 border border-slate-200';

            if (questionStatus === 'not_answered') {
                if (isCorrect) {
                    optStyle = 'bg-emerald-50/40 text-emerald-900 border-dashed border-emerald-300 font-medium';
                    optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-bold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-key mr-1 text-emerald-600"></i>Kunci Benar</span>';
                    badgeStyle = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                }
            } else if (questionStatus === 'correct') {
                if (isChosen && isCorrect) {
                    optStyle = 'bg-emerald-50/90 text-emerald-950 border-emerald-400 font-bold shadow-2xs';
                    optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-emerald-200/90 text-emerald-900 font-extrabold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-check mr-1 text-emerald-700"></i>Pilihan Siswa & Kunci Benar</span>';
                    badgeStyle = 'bg-emerald-600 text-white shadow-2xs';
                }
            } else if (questionStatus === 'wrong') {
                if (isChosen && isCorrect) {
                    optStyle = 'bg-emerald-50/90 text-emerald-950 border-emerald-400 font-bold shadow-2xs';
                    optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-emerald-200/90 text-emerald-900 font-extrabold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-check mr-1 text-emerald-700"></i>Pilihan Siswa & Kunci Benar</span>';
                    badgeStyle = 'bg-emerald-600 text-white shadow-2xs';
                } else if (isChosen && !isCorrect) {
                    optStyle = 'bg-red-50/90 text-red-950 border-red-300 font-bold shadow-2xs';
                    optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-red-200/90 text-red-900 font-extrabold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-xmark mr-1 text-red-700"></i>Pilihan Siswa (Salah)</span>';
                    badgeStyle = 'bg-red-600 text-white shadow-2xs';
                } else if (!isChosen && isCorrect) {
                    optStyle = 'bg-emerald-50/40 text-emerald-900 border-dashed border-emerald-300 font-medium';
                    optTag = '<span class="text-[10px] px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-bold uppercase whitespace-nowrap tracking-wide"><i class="fa-solid fa-key mr-1 text-emerald-600"></i>Kunci Benar</span>';
                    badgeStyle = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                }
            }

            let content = item.options_value;
            if (containsImage) {
                content = addClassToImgTags(
                    item.options_value,
                    'max-w-[300px] rounded my-2'
                );
            }

            return `
                <div class="px-3.5 py-2.5 rounded-xl border text-xs flex items-center justify-between gap-3 transition-colors mb-3 ${optStyle}">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-7 h-7 min-w-7 rounded-lg shrink-0 flex items-center justify-center text-xs font-black select-none ${badgeStyle}" title="Key: ${escapeHtml(optKey)}">
                            ${escapeHtml(displayLetter)}
                        </span>
                        <div class="font-medium leading-relaxed wrap-break-word text-slate-800 min-w-0">
                            ${content}
                        </div>
                    </div>
                    <div class="shrink-0">${optTag}</div>
                </div>
            `;
        }).join('');
    }

    const leftItems = optionsList.filter(item => item.options_key.startsWith('LEFT'));
    const rightItems = optionsList.filter(item => item.options_key.startsWith('RIGHT'));

    // RENDER MATCHING
    function renderMatching() {
        const rightLabelMap = {};
        rightItems.forEach((item, index) => {
            rightLabelMap[item.options_key] = String.fromCharCode(65 + index);
        });

        const correctPairs = {};
        leftItems.forEach(item => {
            correctPairs[item.options_key] = item.extra_data?.pair_with;
        });

        let pairs = {};

        if (studentAnswer) {
            try {
                pairs = typeof studentAnswer === 'string' ? JSON.parse(studentAnswer) : studentAnswer;
            } catch {
                pairs = {};
            }
        }

        return `
            <!-- DESKTOP -->
            <div class="relative matching-container hidden lg:block" data-student='${JSON.stringify(pairs)}' data-correct='${JSON.stringify(correctPairs)}'>
                <!-- SVG GARIS -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines"></svg>

                <div class="grid grid-cols-2 gap-40 relative z-10">
                    <div class="flex flex-col justify-center">
                        <h4 class="font-bold mb-3">Kolom A</h4>
                        <div class="space-y-3">
                            ${leftItems.map(item => `
                                <div class="px-3 min-h-10 border rounded flex justify-between items-center left-item" data-key="${item.options_key}">
                                    <span>${item.options_value}</span>
                                    <span class="text-sm bg-blue-100 text-[#0071BC] px-2 py-1 rounded">
                                        <i class="fa-solid fa-arrow-right"></i>
                                        ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div>
                        <h4 class="font-bold mb-3">Kolom B</h4>
                        <div class="space-y-3">
                            ${rightItems.map(item => {
                                const content = addClassToImgTags(item.options_value, 'max-w-[200px] rounded');
                                return `
                                    <div class="right-item p-3 border rounded flex gap-2 items-center" data-key="${item.options_key}">
                                        <span class="font-bold">${rightLabelMap[item.options_key]}.</span>
                                        ${content}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>

                <!-- GARIS TENGAH -->
                <div class="matching-center-line absolute top-0 bottom-0 left-1/2 w-0"></div>
            </div>

            <!-- MOBILE -->
            <div class="block lg:hidden">
                <div class="grid grid-cols-1 gap-3 lg:hidden">
                    <p class="font-semibold mb-2">Kolom A:</p>
                    ${leftItems.map(item => `
                        <div class="flex justify-between items-center border rounded p-3">
                            <span>${item.options_value}</span>
                            <span class="font-bold text-[#0071BC]">
                                <i class="fa-solid fa-arrow-right"></i>
                                ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                            </span>
                        </div>
                    `).join('')}
                </div>

                <div class="mt-4 lg:hidden border-t border-gray-400 pt-3 grid grid-cols-1 gap-3 text-sm text-gray-700">
                    <p class="font-semibold mb-2">Kolom B:</p>
                    ${rightItems.map(item => {
                        const content = addClassToImgTags(item.options_value, 'max-w-[200px] rounded');
                        return `
                            <div class="right-item p-3 border rounded flex gap-2 items-center" data-key="${item.options_key}">
                                <span class="font-bold">${rightLabelMap[item.options_key]}.</span>
                                ${content}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    function renderPgKompleks() {
        if (!Array.isArray(optionsList)) return '';

        const categories = optionsList.filter(item => item.extra_data?.side === 'category');
        const items = optionsList.filter(item => item.extra_data?.side === 'item');

        let existingAnswer = answerData?.answer_value || {};
        if (typeof existingAnswer === 'string') {
            try {
                existingAnswer = JSON.parse(existingAnswer);
            } catch (e) {
                existingAnswer = {};
            }
        }

        return `
            <div class="overflow-x-auto mt-6">
                <div class="flex flex-wrap gap-4 text-xs mb-4">
                    <span class="flex items-center gap-1 text-green-600 font-semibold">
                        <i class="fa-solid fa-check"></i> Jawaban Benar
                    </span>
                    <span class="flex items-center gap-1 text-red-600 font-semibold">
                        <i class="fa-solid fa-xmark"></i> Jawaban Salah
                    </span>
                    <span class="flex items-center gap-1 text-[#4189E0] font-semibold">
                        <input type="radio" class="w-4 h-4" checked onclick="return false"> 
                        Jawaban Siswa
                    </span>
                </div>

                <table class="w-full border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-center">
                            <th class="border px-4 py-2">
                                ${question.lms_question_bank?.header_item ?? 'Pernyataan'}
                            </th>
                            ${categories.map(category => `
                                <th class="border px-4 py-2">
                                    ${category.options_value}
                                </th>
                            `).join('')}
                        </tr>
                    </thead>

                    <tbody>
                        ${items.map(item => {
                            const correctAnswer = item.extra_data?.answer;
                            const userAnswer = existingAnswer[item.options_key];

                            let rowClass = userAnswer === correctAnswer ? 'bg-green-50' : 'bg-red-50';
                            const content = addClassToImgTags(item.options_value, 'max-w-[200px] w-full rounded');

                            return `
                                <tr class="${rowClass}">
                                    <td class="border px-4 py-3">
                                        ${content}
                                    </td>

                                    ${categories.map(cat => {
                                        const selected = userAnswer === cat.options_key;
                                        const isCorrect = correctAnswer === cat.options_key;

                                        let cellClass = '';
                                        let icon = '';
                                        let badge = '';

                                        if (selected && isCorrect) {
                                            cellClass += ' bg-green-100 border-green-400';
                                            icon = '<i class="fa-solid fa-check text-green-600"></i>';
                                            badge = '<span class="text-[10px] text-green-700">jawaban Siswa</span>';
                                        } else if (selected && !isCorrect) {
                                            cellClass += ' bg-red-100 border-red-400';
                                            icon = '<i class="fa-solid fa-xmark text-red-600"></i>';
                                            badge = '<span class="text-[10px] text-red-700">Jawaban Siswa</span>';
                                        } else if (!selected && isCorrect) {
                                            cellClass += ' bg-green-50 border-green-300';
                                            icon = '<i class="fa-solid fa-check text-green-500"></i>';
                                            badge = '<span class="text-[10px] text-green-600">Jawaban Benar</span>';
                                        }

                                        return `
                                            <td class="border">
                                                <div class="flex flex-col items-center justify-center gap-1 py-2 ${cellClass}">
                                                    <input type="radio" name="pg_kompleks_${item.options_key}" value="${cat.options_key}" class="w-4 h-4" ${selected ? 'checked' : ''} 
                                                        onclick="return false">
                                                    <div class="flex flex-col items-center text-xs">
                                                        ${icon}
                                                        ${badge}
                                                    </div>
                                                </div>
                                            </td>
                                        `;
                                    }).join('')}
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    const isRemedial = assessment.assessment_category === 'remedial';
    const totalQuestions = questions.length;
    const scorePerQuestion = totalQuestions > 0 ? (100 / totalQuestions) : 0;
    const isCorrect = questionStatus === 'correct';

    let displayScore = 0;
    let maxScore = 0;

    if (isRemedial) {
        displayScore = isCorrect ? scorePerQuestion : 0;
        maxScore = scorePerQuestion;
    } else {
        displayScore = answerData?.question_score ?? 0;
        maxScore = question.question_weight ?? 100;
    }

    // RENDER ESSAY
    function renderEssay() {
        const isAnswered = statusAnswer === 'submitted';

        let attachedImagesHtml = '';
        if (studentAnswer && typeof studentAnswer === 'string') {
            const urls = [];
            const regex = /<img[^>]+src=["']([^"']+)["']/gi;
            let match;
            while ((match = regex.exec(studentAnswer)) !== null) {
                if (match[1] && !urls.includes(match[1])) {
                    urls.push(match[1]);
                }
            }

            if (urls.length > 0) {
                attachedImagesHtml = `
                    <div class="mt-4 p-4 rounded-xl border border-blue-200 bg-blue-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-images text-[#0071BC]"></i>
                                Gambar Terlampir Siswa (${urls.length} gambar):
                            </span>
                            <span class="text-[11px] text-gray-500">Klik gambar untuk memperbesar</span>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            ${urls.map((url, idx) => `
                                <div class="border border-gray-200 bg-white rounded-lg p-2 shadow-xs flex items-center gap-3 min-w-[210px] hover:border-blue-400 transition">
                                    <div class="w-14 h-14 rounded-md overflow-hidden bg-gray-100 shrink-0 border border-gray-200 cursor-pointer btn-preview-image" data-src="${url}" title="Klik untuk memperbesar">
                                        <img src="${url}" alt="Attachment ${idx + 1}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0 pr-2">
                                        <div class="text-xs font-semibold text-gray-700 truncate">Foto Jawaban #${idx + 1}</div>
                                        <button type="button" class="text-[11px] text-[#0071BC] hover:underline cursor-pointer btn-preview-image flex items-center gap-1 mt-0.5" data-src="${url}">
                                            <i class="fa-solid fa-magnifying-glass-plus text-[10px]"></i> Lihat Penuh
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        }

        return `
            <h4 class="font-semibold text-gray-800 mb-4">
                Jawaban Siswa
            </h4>

            ${!isAnswered
                ? `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 mb-4">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i>
                        Siswa tidak menjawab soal ini.
                    </div>
                `
                : ''}

            <textarea rows="6"
                class="editor w-full border border-gray-300 rounded-xl p-4 text-sm resize-none"
                ${!isAnswered ? 'disabled' : ''}>
                ${studentAnswer ?? ''}
            </textarea>

            ${attachedImagesHtml}

            <div class="mt-8 border-t border-gray-300 pt-6">
                <h4 class="font-semibold text-gray-800 mb-3">
                    Penilaian Guru
                </h4>

                <div class="flex flex-col gap-2 mb-4">
                    <label class="text-sm font-medium text-gray-700">
                        Score
                        <sup class="text-red-500 relative">&#42;</sup>
                    </label>

                    <div class="text-sm text-gray-500 flex flex-col gap-4">
                        <span class="flex items-center gap-2">
                            <input type="number" min="0" max="${question.question_weight}" id="question_score" name="question_score" value="${scoreUser ?? ''}"
                                class="w-24 border border-gray-300 rounded-lg p-2 text-sm outline-none" placeholder="0" ${!isAnswered ? 'disabled' : ''}>
                            / ${question.question_weight}
                        </span>

                        <span id="error-question_score" class="text-xs text-red-500 hidden"></span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700">
                        Feedback Guru (Optional)
                    </label>

                    <textarea rows="3" id="teacher_feedback" name="teacher_feedback" class="w-full border border-gray-300 resize-none rounded-lg p-3 text-sm outline-none"
                        placeholder="Tambahkan komentar..." ${!isAnswered ? 'disabled' : ''}>${teacherFeedback ?? ''}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-2">
                        ${previousStudent ? `
                            <button type="button" class="btn-navigate-student px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 flex items-center gap-1.5 cursor-pointer transition shadow-2xs" data-student-id="${previousStudent.id}" title="${escapeHtml(previousStudent.name)}">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                                <span>Siswa Sebelumnya</span>
                            </button>
                        ` : ''}

                        ${nextStudent ? `
                            <button type="button" class="btn-navigate-student px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 flex items-center gap-1.5 cursor-pointer transition shadow-2xs" data-student-id="${nextStudent.id}" title="${escapeHtml(nextStudent.name)}">
                                <span>Siswa Selanjutnya</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                        ` : ''}
                    </div>

                    <button type="button" id="submit-button-assessment-grading-student-answer" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm
                        ${!isAnswered ? 'opacity-50 cursor-default' : 'cursor-pointer'}"
                        ${!isAnswered ? 'disabled' : ''}>
                        Simpan Nilai
                    </button>
                </div>
            </div>
        `;
    }

    let answerHTML = '';
    if (questionType === 'essay') {
        answerHTML = renderEssay();
    } else if (questionType === 'matching') {
        answerHTML = renderMatching();
    } else if (questionType === 'pg_kompleks') {
        answerHTML = renderPgKompleks();
    } else {
        answerHTML = renderMCQOptions();
    }

    // NAVIGATION NUMBER LIST
    const nomorSoalHTML = questions.map((item, index) => {
        const itemKey = `${assessment.id}_${item.id}`;
        const answer = questionsAnswer[itemKey];
        const itemType = item?.lms_question_bank?.tipe_soal?.toLowerCase();
        const itemStatus = answer?.status_answer ?? 'draft';
        const itemGradingStatus = answer?.grading_status ?? 'pending';

        let questionTypeLabel = 'Unknown';
        if (itemType === 'mcq') questionTypeLabel = 'MCQ';
        else if (itemType === 'mcma') questionTypeLabel = 'MCMA';
        else if (itemType === 'essay') questionTypeLabel = 'Essay';
        else if (itemType === 'matching') questionTypeLabel = 'Matching';
        else if (itemType === 'pg_kompleks') questionTypeLabel = 'PG Kompleks';

        const typeBadge = `
            <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-700 text-center rounded font-medium">
                ${questionTypeLabel}
            </span>
        `;

        let questionBadge = '';
        if (!answer || itemStatus !== 'submitted') {
            questionBadge = `
                <span class="text-xs text-red-600 font-medium text-end">
                    Tidak Dijawab
                </span>
            `;
        } else if (itemType !== 'essay') {
            if (itemStatus === 'submitted') {
                questionBadge = `
                    <span class="text-xs text-green-600 font-medium text-end">
                        Sudah Dinilai
                    </span>
                `;
            }
        } else {
            if (itemStatus === 'submitted' && itemGradingStatus === 'graded') {
                questionBadge = `
                    <span class="text-xs text-green-600 font-medium text-end">
                        Sudah Dinilai
                    </span>
                `;
            } else {
                questionBadge = `
                    <span class="text-xs text-orange-500 font-medium text-end">
                        Menunggu Penilaian
                    </span>
                `;
            }
        }

        return `
            <input type="radio" id="nomor${index}" name="nomor_soal_group" class="nomor-soal-radio hidden" ${index === selectedIndex ? 'checked' : ''}>

            <label for="nomor${index}" class="nomor-soal text-xs p-3 rounded-lg border flex flex-col gap-1 cursor-pointer hover:bg-gray-100 mb-3 mr-3" data-index="${index}">
                <div class="flex items-center justify-between gap-2 font-bold">
                    Question ${index + 1}
                    ${typeBadge}
                </div>

                <div class="text-[11px]">
                    ${questionBadge}
                </div>
            </label>
        `;
    }).join('');

    // QUESTION SPLIT IMAGE
    const questionContent = question?.lms_question_bank?.questions ?? '';
    const splitQuestions = questionContent.split('<img');
    const questionTextOnly = splitQuestions[0] ?? '';

    let questionImage = '';
    let textAfterImage = '';

    if (splitQuestions.length > 1) {
        const imgSplit = splitQuestions[1].split('>');
        const imgTag = imgSplit[0];
        const restText = imgSplit.slice(1).join('>');

        questionImage = `<img class="w-full sm:max-w-[45%]" ${imgTag}>`;
        textAfterImage = restText.trim();
    }

    const questionImageAndTextAfter = `
        <div class="flex flex-col gap-4 items-start">
            ${questionImage}
            <div>${textAfterImage}</div>
        </div>
    `;

    // RIGHT COLUMN HTML
    const rightColumnHTML = `
        <div class="bg-white border rounded-xl shadow-sm p-6">
            <div class="mb-6">
                <div class="mb-4 flex flex-col gap-2">
                    <!-- Question + Score -->
                    <div class="flex items-center justify-between text-sm sm:text-base font-semibold text-gray-800">
                        <span>
                            Question ${selectedIndex + 1}
                        </span>

                        <span class="text-[#4189E0] font-semibold">
                            ${displayScore.toFixed(2)} / ${maxScore.toFixed(2)}
                        </span>
                    </div>

                    <!-- Status -->
                    <div>
                        ${statusBadge}
                    </div>
                </div>

                <!-- Question -->
                <div id="container-question-detail" class="max-h-163.5 overflow-y-auto">
                    <div class="question-content mb-6 text-sm sm:text-[15px] leading-relaxed text-gray-700 mr-3">
                        <div class="mb-4 list-style">${questionTextOnly}</div>
                        <div class="list-style">${questionImageAndTextAfter}</div>
                    </div>

                    <div class="mt-6 mr-3 list-style">
                        ${answerHTML}
                    </div>
                </div>
            </div>
        </div>
    `;

    const formAssessmentGrading = $('#form-assessment-grading');
    const existingForm = $('#teacher-assessment-grading-student-answer-form');

    if (existingForm.length === 0) {
        // Initial load: construct full grid
        const formHTML = `
            <form id="teacher-assessment-grading-student-answer-form" autocomplete="OFF">
                <input type="hidden" id="school_assessment_question_id" name="school_assessment_question_id" value="${question.id}">

                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 xl:col-span-3">
                        <div class="bg-white border rounded-xl shadow-sm p-4">
                            <h3 class="font-semibold text-gray-700 mb-4">
                                Questions
                            </h3>
                            <div id="container-questions-list" class="max-h-163.5 overflow-y-auto">
                                ${nomorSoalHTML}
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 xl:col-span-9" id="container-question-right-column">
                        ${rightColumnHTML}
                    </div>
                </div>
            </form>
        `;

        formAssessmentGrading.html(formHTML);
        $(`#nomor${selectedIndex}`).prop('checked', true);
    } else {
        // Subsequent switch or adjustment: update in-place without destroying layout
        $('#school_assessment_question_id').val(question.id);

        if (isAdjustment) {
            // Update left list status badges
            $('#container-questions-list').html(nomorSoalHTML);
        }

        $('input[name="nomor_soal_group"]').prop('checked', false);
        $(`#nomor${selectedIndex}`).prop('checked', true);

        // Update right column content
        $('#container-question-right-column').html(rightColumnHTML);

        // Restore scroll positions immediately
        if (savedQuestionsScroll !== null) {
            $('#container-questions-list').scrollTop(savedQuestionsScroll);
        }
        if (isAdjustment && savedDetailScroll !== null) {
            $('#container-question-detail').scrollTop(savedDetailScroll);
        }
        window.scrollTo({ top: savedWindowScroll, behavior: 'instant' });
    }

    // Re-enforce scroll positions in requestAnimationFrame to survive any layout calculations
    requestAnimationFrame(() => {
        if (savedQuestionsScroll !== null) {
            const list = document.getElementById('container-questions-list');
            if (list) list.scrollTop = savedQuestionsScroll;
        }
        if (isAdjustment && savedDetailScroll !== null) {
            const detail = document.getElementById('container-question-detail');
            if (detail) detail.scrollTop = savedDetailScroll;
        }
        window.scrollTo({ top: savedWindowScroll, behavior: 'instant' });
    });

    // Initialize Matching if needed
    if (questionType === 'matching') {
        requestAnimationFrame(() => {
            const matchingContainer = document.querySelector('#container-question-right-column .matching-container');
            if (matchingContainer) {
                const pairs = leftItems.filter(i => i.extra_data?.pair_with).map(i => ({
                    left: i.options_key,
                    right: i.extra_data.pair_with
                }));
                drawMatchingLines(matchingContainer, pairs);
                initMatchingContainer(matchingContainer);
            }
        });
    }

    // Initialize CKEditor on right column
    const editors = $('#container-question-right-column').find('.editor');
    editors.each((_, textarea) => {
        ClassicEditor.create(textarea, {
            toolbar: {
                shouldNotGroupWhenFull: true
            },
        })
        .then(editor => {
            editor.enableReadOnlyMode('lock-student-answer');
        })
        .catch(error => console.error('Error CKEditor:', error));
    });

    // Typeset MathJax on right column
    if (window.MathJax && window.MathJax.typesetPromise) {
        const rightCol = document.getElementById('container-question-right-column');
        if (rightCol) {
            window.MathJax.typesetPromise([rightCol]).catch(err => console.warn('MathJax error:', err));
        }
    }
}

function paginateAssessmentGradingStudentAnswer(selectedIndex = 0, forceFetch = false, scrollOptions = {}) {
    const container = document.getElementById('container-assessment-grading-student-answer');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const mode = container.dataset.mode;
    const studentId = container.dataset.studentId;

    if (!role || !schoolName || !schoolId || !assessmentId || !mode || !studentId) return;

    if (!forceFetch && cachedGradingData) {
        renderGradingView(cachedGradingData, selectedIndex, scrollOptions);
        return;
    }

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/mode/${mode}/student-list/${studentId}/scoring/paginate`,
        method: 'GET',
        success: function (response) {
            cachedGradingData = response;
            renderGradingView(response, selectedIndex, scrollOptions);
        },
        error: function (xhr) {
            console.error('Error fetching scoring data:', xhr);
        }
    });
}

$(document).ready(function () {
    paginateAssessmentGradingStudentAnswer();
});

// Navigate between students (Next / Previous Student)
$(document).on('click', '.btn-navigate-student', function (e) {
    e.preventDefault();
    const studentId = $(this).data('student-id');
    if (!studentId) return;

    const container = document.getElementById('container-assessment-grading-student-answer')
        || document.getElementById('container-assessment-grading-student-project');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const mode = container.dataset.mode || 'main';

    window.location.href = `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/mode/${mode}/student-list/${studentId}/scoring`;
});

// Switch question on clicking a question number
$(document).on('click', '.nomor-soal', function () {
    const index = parseInt($(this).data('index'));
    if (isNaN(index)) return;

    if (index === currentQuestionIndex && $('#container-question-right-column').children().length > 0) {
        return;
    }

    currentQuestionIndex = index;

    const qListEl = document.getElementById('container-questions-list');
    const savedQuestionsScroll = qListEl ? qListEl.scrollTop : null;
    const savedWindowScroll = window.scrollY || document.documentElement.scrollTop;

    if (cachedGradingData) {
        renderGradingView(cachedGradingData, index, {
            isAdjustment: false,
            savedQuestionsScroll,
            savedWindowScroll
        });
    } else {
        paginateAssessmentGradingStudentAnswer(index, false, {
            savedQuestionsScroll,
            savedWindowScroll
        });
    }
});

$(document).on('input', '#question_score', function () {
    $('#error-question_score').text('').addClass('hidden');
    $(this).removeClass('border-red-400');
});

let isProcessing = false;

// Form Action assessment grading student answer
$(document).on('click', '#submit-button-assessment-grading-student-answer', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-assessment-grading-student-answer');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const studentId = container.dataset.studentId;

    const schoolAssessmentQuestionId = $('#school_assessment_question_id').val();

    if (!role || !schoolName || !schoolId || !assessmentId || !studentId || !schoolAssessmentQuestionId) return;

    const form = $('#teacher-assessment-grading-student-answer-form')[0];
    const formData = new FormData(form);

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/student-list/${studentId}/scoring/submission/${schoolAssessmentQuestionId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-assessment-grading').html(`
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

            // Capture scroll positions before re-fetching
            const qListEl = document.getElementById('container-questions-list');
            const savedQuestionsScroll = qListEl ? qListEl.scrollTop : null;
            const savedWindowScroll = window.scrollY || document.documentElement.scrollTop;
            const qDetailEl = document.getElementById('container-question-detail');
            const savedDetailScroll = qDetailEl ? qDetailEl.scrollTop : null;

            paginateAssessmentGradingStudentAnswer(currentQuestionIndex, true, {
                isAdjustment: true,
                savedQuestionsScroll,
                savedWindowScroll,
                savedDetailScroll
            });

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
                const errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    $('#teacher-assessment-grading-student-answer-form').find(`#error-${field}`).text(messages[0]).removeClass('hidden');
                    $('#teacher-assessment-grading-student-answer-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

function drawMatchingLines(container) {

    if (!container) return;

    const svg = container.querySelector('.matching-lines');
    if (!svg) return;

    svg.innerHTML = '';

    // supaya SVG selalu mengikuti container
    svg.setAttribute('width', container.offsetWidth);
    svg.setAttribute('height', container.offsetHeight);

    const cRect = container.getBoundingClientRect();

    const studentPairs = JSON.parse(container.dataset.student || '{}');
    const correctPairs = JSON.parse(container.dataset.correct || '{}');

    Object.keys(correctPairs).forEach(leftKey => {

        const studentRight = studentPairs[leftKey];
        const correctRight = correctPairs[leftKey];

        const leftEl = container.querySelector(`[data-key="${leftKey}"]`);
        if (!leftEl) return;

        const l = leftEl.getBoundingClientRect();

        const x1 = l.right - cRect.left;
        const y1 = l.top + l.height / 2 - cRect.top;

        // GARIS JAWABAN SISWA
        if (studentRight) {

            const rightEl = container.querySelector(`[data-key="${studentRight}"]`);

            if (rightEl) {

                const r = rightEl.getBoundingClientRect();

                const x2 = r.left - cRect.left;
                const y2 = r.top + r.height / 2 - cRect.top;

                const studentPath = document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'path'
                );

                const color = studentRight === correctRight ? '#16A34A' : '#DC2626';

                studentPath.setAttribute('d', `M ${x1} ${y1} L ${x2} ${y2}`);

                studentPath.setAttribute('stroke', color);
                studentPath.setAttribute('stroke-width', '3');
                studentPath.setAttribute('fill', 'none');
                studentPath.setAttribute('stroke-linecap', 'round');

                svg.appendChild(studentPath);

            }

        }

        // GARIS JAWABAN BENAR
        if (studentRight !== correctRight) {

            const correctEl = container.querySelector(`[data-key="${correctRight}"]`);
            if (!correctEl) return;

            const r = correctEl.getBoundingClientRect();

            const x2 = r.left - cRect.left;
            const y2 = r.top + r.height / 2 - cRect.top;

            const correctPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');

            correctPath.setAttribute('d', `M ${x1} ${y1} L ${x2} ${y2}`);

            correctPath.setAttribute('stroke', '#16A34A');
            correctPath.setAttribute('stroke-width', '2');
            correctPath.setAttribute('stroke-dasharray', '6,6');
            correctPath.setAttribute('fill', 'none');

            svg.appendChild(correctPath);

        }

    });

}

const matchingObservers = new WeakMap();

function initMatchingContainer(container) {

    if (!container) return;

    const pairs = JSON.parse(container.dataset.student || '{}');

    drawMatchingLines(container, pairs);

    if (matchingObservers.has(container)) return;

    const observer = new ResizeObserver(() => {

        requestAnimationFrame(() => {
            drawMatchingLines(container, pairs);
        });

    });

    observer.observe(container);

    matchingObservers.set(container, observer);

}

// Image Preview Modal for teacher grading
$(document).on('click', '.btn-preview-image', function (e) {
    e.preventDefault();
    const src = $(this).data('src');
    if (!src) return;

    $('#modal-preview-img').attr('src', src);
    $('#modal-preview-open-tab').attr('href', src);
    const modal = document.getElementById('modal-image-preview');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
});

// Preview images when clicked inside CKEditor or answer content
$(document).on('click', '.editor img, .ck-content img, [data-key] img', function () {
    const src = $(this).attr('src');
    if (!src) return;
    $('#modal-preview-img').attr('src', src);
    $('#modal-preview-open-tab').attr('href', src);
    const modal = document.getElementById('modal-image-preview');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
});