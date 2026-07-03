let currentQuestionIndex = 0;
let showLoading = false;
let isReviewMode = false;
let isResultMode = false;

const containerTkaPracticeTest = $('#container-tka-practice-test-form');
const role = containerTkaPracticeTest.data('role');
const kelasId = containerTkaPracticeTest.data('kelas-id');
const mapelId = containerTkaPracticeTest.data('mapel-id');

let currentAttempt = null;

let currentQuestionId = null;
let questions = [];
let questionsAnswer = {};
let attemptId = null;

function getTimerKeys() {

    if (!attemptId) {
        return {
            START_KEY: null,
            EXPIRE_KEY: null
        };
    }

    return {
        START_KEY: `timer_tka_practice_test_start_${attemptId}`,

        EXPIRE_KEY: `timer_tka_practice_test_expire_${attemptId}`
    };
}

let finalScore = 0;
let totalCorrect = 0;
let totalSoal = 0;
let totalAnswered = 0;
let totalWrong = 0;
let totalUnanswered = 0;

function studentFormAssessment(selectedIndex = 0, showLoading = true) {

    if (!containerTkaPracticeTest.length) return;

    $.ajax({
        url: `/lms/${role}/tka-simulation/class/${kelasId}/subject/${mapelId}/practice-test/form`,
        method: 'GET',
        beforeSend: function () {
            if (showLoading) {
                $('#practice-loading').removeClass('hidden');
            }
        },
        
        success: function (response) {
            $('#practice-loading').addClass('hidden');

            if (response.attempt) {

                currentAttempt = response.attempt;

                attemptId = currentAttempt.id;

            }

            if (response.has_attempt === false) {

                $('#practice-content').addClass('hidden');
                $('#practice-placeholder').removeClass('hidden');

                $('#tka-practice-test-action-bar').hide();

                confirmStartExam();
                return;
            }

            // sembunyikan placeholder
            $('#practice-placeholder').addClass('hidden');

            // tampilkan soal
            $('#practice-content').removeClass('hidden');

            $('#tka-practice-test-action-bar').show();

            const formAssessment = $('#form-tka-practice-test');
            formAssessment.empty();

            $('#tka-title').text('Simulasi TKA');

            // Mendapatkan data soal
            questions = response.data;

            // Mendapatkan data jawaban
            questionsAnswer = response.questionsAnswer;

            const answerList = Object.values(questionsAnswer);

            totalSoal = questions.length;

            totalAnswered = answerList.filter(item => item.status_answer === 'submitted' && item.answer_value !== null).length;

            totalCorrect = answerList.filter(item => item.is_correct === true).length;

            totalWrong = answerList.filter(item => item.status_answer === 'submitted' && item.is_correct === false).length;

            totalUnanswered = totalSoal - totalAnswered;

            finalScore = totalSoal > 0 ? Number(((100 / totalSoal) * totalCorrect).toFixed(2)) : 0;
            
            const question = questions[selectedIndex];
            const questionType = question.tipe_soal?.toLowerCase();

            if (question) {

                let jumlahSoalTerjawab = Object.values(questionsAnswer).filter(q => q.status_answer === 'submitted').length;

                // Cek apakah semua soal sudah dijawab
                const isAllAnswered = jumlahSoalTerjawab === totalSoal;

                if (isAllAnswered && isResultMode) {
                    $('#practice-content').addClass('hidden');
                    $('#practice-result').removeClass('hidden');
                    $('#btn-submit-exit-tka-practice-test').show();

                    return;
                }

                $('#practice-content').removeClass('hidden');
                $('#practice-result').addClass('hidden');

                // Helper tambah class img
                function addClassToImgTags(html, className) {
                    return html
                        .replace(/<img\b(?![^>]*class=)[^>]*>/g, (imgTag) => {
                            return imgTag.replace('<img', `<img class="${className}"`);
                        })
                        .replace(/<img\b([^>]*?)class="(.*?)"/g, (imgTag, before, existingClasses) => {
                            return `<img ${before}class="${existingClasses} ${className}"`;
                        });
                }

                // ===== GENERATE OPTIONS =====
                const generateOptions = (options = []) => {

                    if (!Array.isArray(options)) return '';

                    const optionKeys = ['A', 'B', 'C', 'D', 'E'];

                    const shuffleOptions = [...options];

                    return shuffleOptions.map((item, index) => {

                        const newKey = optionKeys[index] ?? '';
                        const containsImage = /<img\s+[^>]*src=/.test(item.options_value ?? '');

                        let content = item.options_value;

                        if (containsImage) {
                            content = addClassToImgTags(content, 'max-w-[300px] rounded my-2');
                        }

                        let statusClass = '';

                        const userAnswer = questionsAnswer[question.id]?.answer_value ?? [];
                        const correctOption = options.find(opt => opt.is_correct == 1);
                        const correctKey = correctOption?.options_key;
                        const correctKeys = options.filter(opt => opt.is_correct == 1).map(opt => opt.options_key);

                        if (isAllAnswered) {
                            if (questionsAnswer[question.id]?.status_answer === 'submitted') {

                                if (questionType === 'mcma') {

                                    if (correctKeys.includes(item.options_key) && userAnswer.includes(item.options_key)) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    }
                                    else if (!correctKeys.includes(item.options_key) && userAnswer.includes(item.options_key)) {
                                        statusClass = 'bg-red-200 text-red-700 font-bold';
                                    }
                                    else if (correctKeys.includes(item.options_key)) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    }

                                } else {

                                    if (userAnswer === correctKey && item.options_key === correctKey) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    } else if (userAnswer !== correctKey && item.options_key === userAnswer) {
                                        statusClass = 'bg-red-200 text-red-700 font-bold';
                                    } else if (item.options_key === correctKey) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    }

                                }
                            }
                        } else {
                            const answerValue = questionsAnswer[question.id]?.answer_value;
                            const status = questionsAnswer[question.id]?.status_answer;

                            if (status === 'submitted') {
                                if (questionType === 'mcma') {
                                    if (Array.isArray(answerValue) && answerValue.includes(item.options_key)) {
                                        statusClass = 'bg-gray-200 font-bold opacity-70';
                                    }
                                } else {
                                    if (answerValue === item.options_key) {
                                        statusClass = 'bg-gray-200 font-bold opacity-70';
                                    }
                                }
                            }
                        }

                        let optionsValue = '';

                        const inputType = questionType === 'mcma' ? 'checkbox' : 'radio';

                        // memeriksa apakah soal sudah dijawab oleh pengguna atau belum
                        if (questionsAnswer[question.id] && questionsAnswer[question.id]?.status_answer === 'submitted') {
                            if (containsImage) {
                                optionsValue = `
                                    <div class="border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 checked-option ${statusClass}">
                                        <div class="font-bold min-w-7.5">${newKey}.</div>
                                        <div class="w-full flex flex-col gap-8 list-style">${item.options_value}</div>
                                    </div>
                                `;
                            } else {
                                optionsValue = `
                                    <div class="list-style border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 checked-option ${statusClass}">
                                        ${newKey}. ${item.options_value}
                                    </div>
                                `;
                            }
                        } else {
                            if (containsImage) {
                                optionsValue = `
                                        <input type="${inputType}" name="options_value_${question.id}${questionType === 'mcma' ? '[]' : ''}" id="soal${question.id}_${item.options_key}" value="${item.options_key}" class="hidden peer" data-soal-id="${question.id}">
                                        <label for="soal${question.id}_${item.options_key}" class="border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 cursor-pointer checked-option ${statusClass}">
                                            <div class="font-bold min-w-7.5">${newKey}.</div>
                                            <div class="w-full flex flex-col gap-8 list-style">${item.options_value}</div>
                                        </label>
                                    `;
                            } else {
                                optionsValue = `
                                        <input type="${inputType}" name="options_value_${question.id}${questionType === 'mcma' ? '[]' : ''}" id="soal${question.id}_${item.options_key}" value="${item.options_key}" class="hidden" data-soal-id="${question.id}">
                                        <label for="soal${question.id}_${item.options_key}" class="list-style border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 cursor-pointer checked-option ${statusClass}">
                                            ${newKey}. ${item.options_value}
                                        </label>
                                    `;
                            }
                        }

                        // Render opsi jawaban
                        return `
                            ${optionsValue}
                        `;
                    }).join('');
                };

                // Render Nomor Soal
                const nomorSoalHTML = questions.map((item, index) => {

                    let statusClassNumberQuestions = '';
                    const answer = questionsAnswer[item.id];

                    if (isAllAnswered) {
                        if (answer?.status_answer === 'submitted') {
                            if (answer?.is_correct === true) {
                                statusClassNumberQuestions = '!bg-green-200 text-green-600 font-bold';
                            }
                            else if (answer?.is_correct === false) {
                                statusClassNumberQuestions = '!bg-red-200 text-red-600 font-bold';
                            }
                        }

                    } else {
                        if (answer?.status_answer === 'submitted') {
                            statusClassNumberQuestions = '!bg-[#0071BC] text-white font-bold';
                        }
                    }

                    return `                    
                        <input type="radio" id="nomor${index}" name="nomorSoal" class="hidden">
                        <label for="nomor${index}" 
                            class="nomor-soal border border-gray-300 rounded-lg py-1.5 hover:bg-gray-100 transition text-center cursor-pointer ${statusClassNumberQuestions}"
                            data-index="${index}">
                            <span class="font-bold">${index + 1}</span>
                        </label>
                    `;
                }).join('');

                function generateMatching(leftItems, rightItems) {

                    const rightLabelMap = {};
                    rightItems.forEach((item, index) => {
                        rightLabelMap[item.options_key] = String.fromCharCode(65 + index);
                    });

                    let correctAnswer = '';

                    if (isAllAnswered) {
                        correctAnswer = `
                            <div class="relative matching-container bg-green-50 border border-green-200 rounded-2xl p-6 mt-8 shadow-sm">
    
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white">
                                        <i class="fa-solid fa-check text-sm"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-green-700">
                                        Jawaban Benar
                                    </h3>
                                </div>
    
                                <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines hidden lg:block"></svg>
    
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 relative z-10 mt-4">
    
                                    <!-- KOLOM A -->
                                    <div>
                                        <h4 class="font-semibold mb-4">Kolom A</h4>
                                        <div class="space-y-3">
                                            ${leftItems.map(item => `
                                                <div 
                                                    class="px-3 min-h-10 border rounded flex justify-between items-center left-item" data-key="${item.options_key}">
                                                    <span>${item.options_value}</span>
                                                    <span class="text-sm bg-blue-100 text-[#0071BC] px-2 py-1 rounded">
                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                                    </span>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
    
                                    <!-- KOLOM B -->
                                    <div>
                                        <h4 class="font-semibold mb-4">Kolom B</h4>
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
                            </div>
                        `;
                    }

                    return `
                        <div class="relative matching-container bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Jawaban Kamu
                                </h3>
                            </div>
    
                            <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines hidden lg:block"></svg>
    
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 relative z-10 ${isAllAnswered ? 'pointer-events-none' : ''}">
    
                                <!-- KOLOM A -->
                                <div>
                                    <h4 class="font-semibold mb-4">Kolom A</h4>
                                    <div class="space-y-3">
                                        ${leftItems.map(item => `
                                            <div class="matching-left p-3 flex items-center justify-between border rounded cursor-pointer hover:bg-blue-50"
                                                data-key="${item.options_key}">
                                                ${item.options_value}
    
                                                <div class="text-sm font-bold bg-blue-100 text-[#0071BC] px-2 py-1 rounded">
                                                    <span class="match-icon"><i class="fa-solid fa-arrow-right"></i></span>
                                                    <span class="match-label"> - </span>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
    
                                <!-- KOLOM B -->
                                <div>
                                    <h4 class="font-semibold mb-4">Kolom B</h4>
                                    <div class="space-y-3">
                                        ${rightItems.map((item, index) => `
                                            <div class="matching-right flex gap-2 p-3 border rounded cursor-pointer hover:bg-green-50"
                                                data-key="${item.options_key}">
                                                <span class="match-letter font-bold">${String.fromCharCode(65 + index)}.</span>
                                                ${item.options_value}
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
    
                            </div>
                        </div>
    
                        ${correctAnswer}
                    `;
                }

                function generatePgKompleks(options = []) {

                    if (!Array.isArray(options)) return '';

                    const categories = options.filter(item => item.extra_data?.side === 'category');
                    const items = options.filter(item => item.extra_data?.side === 'item');

                    let existingAnswer = questionsAnswer[question.id]?.answer_value || {};

                    if (typeof existingAnswer === 'string') {
                        try {
                            existingAnswer = JSON.parse(existingAnswer);
                        } catch (e) {
                            existingAnswer = {};
                        }
                    }

                    const isReviewMode = isAllAnswered;

                    return `
                        <div class="overflow-x-auto mt-6">

                            ${isReviewMode ? `
                                <div class="flex flex-wrap gap-4 text-xs mb-4">
                                    <span class="flex items-center gap-1 text-green-600 font-semibold">
                                        <i class="fa-solid fa-check"></i> Jawaban Benar
                                    </span>
                                    <span class="flex items-center gap-1 text-red-600 font-semibold">
                                        <i class="fa-solid fa-xmark"></i> Jawaban Salah
                                    </span>
                                    <span class="flex items-center gap-1 text-[#4189E0] font-semibold">
                                        <input type="radio" class="w-4 h-4" checked onclick="return false"> 
                                        Jawaban Kamu
                                    </span>
                                </div>
                            ` : ''}

                            <table class="w-full border border-gray-300 text-sm">
                                <thead>
                                    <tr class="bg-gray-100 text-center">
                                        <th class="border px-4 py-2">
                                            ${question.header_item ?? 'Pernyataan'}
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

                                        let rowClass = '';
                                        if (isReviewMode) {
                                            rowClass = userAnswer === correctAnswer ? 'bg-green-50' : 'bg-red-50';
                                        }

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

                                                    if (isReviewMode) {

                                                        // Jawaban benar & dipilih
                                                        if (selected && isCorrect) {
                                                            cellClass += ' bg-green-100 border-green-400';
                                                            icon = '<i class="fa-solid fa-check text-green-600"></i>';
                                                            badge = '<span class="text-[10px] text-green-700">Jawabanmu</span>';

                                                            // Jawaban salah
                                                        } else if (selected && !isCorrect) {
                                                            cellClass += ' bg-red-100 border-red-400';
                                                            icon = '<i class="fa-solid fa-xmark text-red-600"></i>';
                                                            badge = '<span class="text-[10px] text-red-700">Jawabanmu</span>';

                                                            // Kunci jawaban
                                                        } else if (!selected && isCorrect) {
                                                            cellClass += ' bg-green-50 border-green-300';
                                                            icon = '<i class="fa-solid fa-check text-green-500"></i>';
                                                            badge = '<span class="text-[10px] text-green-600">Jawaban Benar</span>';
                                                        }
                                                    }

                                                return `
                                                    <td class="border">
                                                        <div class="flex flex-col items-center justify-center gap-1 py-2 ${cellClass}">

                                                            <input type="radio" name="pg_kompleks_${item.options_key}" value="${cat.options_key}" class="w-4 h-4"
                                                                ${selected ? 'checked' : ''} ${isReviewMode ? 'onclick="return false"' : ''}>

                                                            ${isReviewMode ? `
                                                                <div class="flex flex-col items-center text-xs">
                                                                    ${icon}
                                                                    ${badge}
                                                                </div>
                                                            ` : ''}

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

                let submitAnswerType = '';

                const options = question.lms_question_option ?? [];

                const leftItems = options.filter(item => item.options_key.startsWith('LEFT'));
                const rightItems = options.filter(item => item.options_key.startsWith('RIGHT'));

                if (questionType === 'matching') {
                    submitAnswerType = generateMatching(leftItems, rightItems);
                } else if (questionType === 'pg_kompleks') {
                    submitAnswerType = generatePgKompleks(question.lms_question_option);
                } else {
                    submitAnswerType = generateOptions(question.lms_question_option);
                }

                const isAnswered = !!questionsAnswer[question.id] && questionsAnswer[question.id]?.status_answer === 'submitted';

                const isCorrect = !!questionsAnswer[question.id]?.is_correct; // jadikan boolean true or false

                let submitButtonAnswerHTML = '';

                submitButtonAnswerHTML = isAnswered
                    ? `
                        <button type="button" class="bg-gray-200 px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold opacity-70 cursor-default" disabled>
                            Simpan Jawaban
                        </button>
                    `
                    : `
                        <button type="button" id="btn-submit-save-answer" data-status-answer="submitted" class="bg-[#43AB3C] text-white px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold cursor-pointer">
                            Simpan Jawaban
                        </button>
                    `;

                let explanationButtonHTML = '';

                if (isAllAnswered) {
                    explanationButtonHTML = `
                        <button type="button" id="btn-show-explanation" data-question-id="${question.id}" class="bg-[#0071BC] text-white px-5 py-2.5 
                            rounded-md shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold cursor-pointer">
                            Lihat Pembahasan
                        </button>
                    `;
                }

                let buttonCorrectOrWrongHTML = '';

                if (questionType !== 'essay') {
                    buttonCorrectOrWrongHTML = isAllAnswered
                        ? (isCorrect
                            ? `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm text-center bg-green-200 text-green-600 font-bold rounded-md" disabled>Jawaban Benar</button>`
                            : `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm text-center bg-red-200 text-red-600 font-bold opacity-70 rounded-md" disabled>Jawaban Salah</button>`)
                        : `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm font-semibold text-center bg-gray-200 opacity-70 rounded-md" disabled>Jawaban Benar/Salah</button>`;
                }

                let resultButtonHTML = '';

                if (isAllAnswered) {
                    resultButtonHTML = `
                        <div class="mt-8 rounded-xl bg-blue-50 border border-blue-200 p-4 text-center">

                            <div class="w-12 h-12 rounded-full bg-[#0071BC] text-white flex items-center justify-center mx-auto">
                                <i class="fa-solid fa-chart-column"></i>
                            </div>

                            <h4 class="font-semibold mt-3">
                                Simulasi Selesai
                            </h4>

                            <p class="text-sm text-gray-600 mt-2">
                                Semua soal telah selesai dijawab.
                                Lihat ringkasan hasil simulasi untuk mengetahui performamu.
                            </p>

                            <button id="btn-show-result" type="button" class="mt-5 w-full bg-[#0071BC] text-white py-3 rounded-xl font-semibold hover:bg-[#005f99]
                                transition cursor-pointer">

                                <i class="fa-solid fa-chart-column mr-2"></i>
                                Lihat Hasil Simulasi

                            </button>

                        </div>
                    `;
                }

                // QUESTION SPLIT IMAGE
                const questionHtml = question?.questions ?? '';

                const splitQuestions = questionHtml.split('<img');

                const questionTextOnly = splitQuestions[0];

                let questionImage = '';
                let textAfterImage = '';

                if (splitQuestions.length > 1) {

                    const imgSplit = splitQuestions[1].split('>');

                    const imgTag = imgSplit[0];

                    const restText = imgSplit.slice(1).join('>');

                    questionImage = `<img class="w-full sm:max-w-[45%]" ${imgTag}>`;

                    textAfterImage = restText.trim();

                }

                // Gabungkan menjadi HTML: bungkus gambar dan teks
                const questionImageAndTextAfter = `
                    <div class="flex flex-col gap-4 items-start">
                        ${questionImage}
                        <div>${textAfterImage}</div>
                    </div>
                `;

                const form = `
                    <form id="tka-practice-test-submit-form">
                        <div class="max-w-450 mx-auto px-4 sm:px-6 lg:px-8 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14 items-stretch">
    
                            <!-- ================= LEFT ================= -->
                            <div class="lg:col-span-8 flex shadow-xl order-1 lg:order-0">
                                <div class="w-full bg-white rounded-2xl border border-gray-200 
                                    shadow-[0_8px_30px_rgba(0,0,0,0.04)] p-5 sm:p-6 lg:p-8 flex flex-col">
    
                                    <!-- Header Soal -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-base font-semibold text-gray-800">
                                                Soal ${selectedIndex + 1}
                                            </span>
    
                                            <span class="text-xs px-4 py-1.5 rounded-full 
                                                bg-blue-50 text-[#0071BC] font-semibold tracking-wide">
                                                ${questionType?.toUpperCase() ?? '-'}
                                            </span>
                                        </div>
    
                                        <span class="text-sm text-gray-500 font-medium">
                                            ${selectedIndex + 1} / ${questions.length}
                                        </span>
                                    </div>
    
                                    <!-- Question -->
                                    <div class="question-content mb-6 text-sm sm:text-[15px] leading-relaxed text-gray-700">
                                        <div class="mb-4 list-style">${questionTextOnly}</div>
                                        <div class="list-style">${questionImageAndTextAfter}</div>
                                    </div>
    
                                    <!-- Answer -->
                                    <div class="submit-answer-type space-y-4 grow">
                                        ${submitAnswerType}
                                    </div>
    
                                    <input type="hidden" name="question_id" value="${question.id}">
                                    <input type="hidden" name="status_answer" id="status_answer" value="${questionsAnswer[question.id]?.status_answer ?? 'draft'}">
                                    <input type="hidden" name="answer_value" id="userAnswer${question.id}" value="">
                                    <span id="error-answer_value" class="text-red-500 font-bold text-xs pt-2 mb-4"></span>
    
                                    <!-- Buttons -->
                                    <div class="flex flex-col sm:flex-row sm:justify-end items-stretch sm:items-center gap-3 sm:gap-6 mt-8 pt-6 border-t border-gray-100">
                                        ${buttonCorrectOrWrongHTML}
                                    
                                        ${explanationButtonHTML}
    
                                        ${submitButtonAnswerHTML}
                                    </div>
    
                                </div>
                            </div>
    
                            <!-- ================= RIGHT ================= -->
                            <div class="lg:col-span-4 flex shadow-xl order-0 lg:order-1">
                                <div class="w-full bg-white rounded-3xl border border-gray-200 
                                    shadow-[0_10px_40px_rgba(0,0,0,0.05)] p-5 sm:p-6 lg:p-8 flex flex-col">
    
                                    <div class="border border-gray-200 rounded-2xl p-6 flex justify-between items-center 
                                        mb-10 bg-gray-50 shadow-sm">
    
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">
                                                ${response.user?.student_profile?.nama_lengkap}
                                            </p>
    
                                            <p class="text-sm text-gray-500 mt-1">
                                                ${question.mapel?.mata_pelajaran ?? '-'}
                                            </p>
                                        </div>
                                    </div>
    
                                    <div class="grid grid-cols-5 sm:grid-cols-6 lg:grid-cols-4 xl:grid-cols-8 gap-2 sm:gap-3 text-sm">
                                        ${nomorSoalHTML}
                                    </div>
                                    ${resultButtonHTML}
                            </div>
                        </div>
                    </form>
                `;
                formAssessment.append(form);

                if (questionType === 'matching') {

                    const existingAnswer = questionsAnswer[question.id]?.answer_value;

                    studentPairs = {};

                    if (existingAnswer) {
                        try {
                            studentPairs = typeof existingAnswer === 'string' ? JSON.parse(existingAnswer) : existingAnswer;
                        } catch (e) {
                            studentPairs = {};
                        }
                    }

                    setTimeout(() => {

                        const activeContainer = Array.from(document.querySelectorAll('.matching-container'))
                            .find(el => el.offsetParent !== null);

                        if (!activeContainer) return;

                        Object.entries(studentPairs).forEach(([leftKey, rightKey]) => {

                            const rightEl = activeContainer.querySelector(`.matching-right[data-key="${rightKey}"]`);
                            const leftEl = activeContainer.querySelector(`.matching-left[data-key="${leftKey}"]`);

                            if (!rightEl || !leftEl) return;

                            const rightLabel = rightEl.querySelector('.match-letter')
                                .textContent.trim().replace('.', '');

                            leftEl.querySelector('.match-label').textContent = rightLabel;
                        });

                        drawStudentMatchingLines();
                        drawCorrectMatchingLines();

                    }, 150);
                }

                $('#btn-submit-end-tka-practice-test').show();
                $('#btn-submit-exit-tka-practice-test').hide();

                // jika semua soal sudah terjawab maka hentikan bersihkan timer
                if (isAllAnswered) {
                    examFinished = true;

                    stopTimer();

                    document.getElementById('timer-tka-practice-test').textContent = 'Waktu Habis';

                    $('#btn-submit-end-tka-practice-test').hide();
                    $('#btn-submit-exit-tka-practice-test').show();

                } else {
                    startTimer();
                }

                // Set aktif pertama
                $(`#nomor${selectedIndex}`).prop('checked', true);

                $(document).off('click', '.nomor-soal').on('click', '.nomor-soal', function () {
                    const index = parseInt($(this).data('index'));

                    currentQuestionIndex = index;

                    showQuestionTransitionLoading();

                    studentFormAssessment(index, false);

                    hideQuestionTransitionLoading();
                });

                $('#empty-message-assessment-form').hide();

            } else {
                examFinished = true;
                $('#btn-submit-end-tka-practice-test').hide();
                $('#btn-submit-exit-tka-practice-test').show();
                $('#empty-message-assessment-form').show();
            }
        },
        error: function () {

            $('#practice-loading').addClass('hidden');

            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Data latihan tidak dapat dimuat. Silakan coba kembali.',
                confirmButtonColor: '#0071BC'
            });

        }
    });
}

$(document).ready(function () {
    attemptId = null;
    studentFormAssessment(0, true);
});

$(document).on('click', '#btn-review-practice', function () {

    isReviewMode = true;
    isResultMode = false;
    studentFormAssessment(currentQuestionIndex, false);
});

$(document).on('click', '#btn-show-result', function () {

    isResultMode = true;

    $('#practice-content').addClass('hidden');
    $('#practice-result').removeClass('hidden');

    $('#result-total-question').text(totalSoal);
    $('#result-total-answered').text(totalAnswered);
    $('#result-total-unanswered').text(totalUnanswered);
    $('#result-total-score').text(finalScore);
    $('#result-total-correct').text(totalCorrect);
    $('#result-total-wrong').text(totalWrong);
});

$(document).on('click', '#btn-restart-practice', function () {
    confirmRestartPractice();
});

function showGlobalLoading() {
    $('#practice-loading').removeClass('hidden');
}

function hideGlobalLoading() {
    $('#practice-loading').addClass('hidden');
}

function showQuestionTransitionLoading() {
    $('#form-tka-practice-test').addClass('opacity-40 pointer-events-none');
}

function hideQuestionTransitionLoading() {
    $('#form-tka-practice-test').removeClass('opacity-40 pointer-events-none');
}

function parseLocalDateTime(dateStr) {
    if (!dateStr) return null;

    const [datePart, timePart] = dateStr.split(' ');
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour, minute] = timePart.split(':').map(Number);

    return new Date(year, month - 1, day, hour, minute);
}

function confirmStartExam() {
    if (document.getElementById("timer-tka-practice-test")) {

        initTimerDisplay();
        Swal.fire({
            title: 'Konfirmasi Mulai Latihan',
            text: "Klik 'Mulai Latihan' untuk mulai timer!",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Mulai Latihan',
            cancelButtonText: 'Batal',
            allowOutsideClick: false,
            allowEscapeKey: false,
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: `/lms/${role}/tka-simulation/class/${kelasId}/subject/${mapelId}/practice-test/start`,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {

                        isReviewMode = false;
                        isResultMode = false;
                        currentQuestionIndex = 0;

                        // sembunyikan placeholder
                        $('#practice-placeholder').addClass('hidden');

                        // tampilkan loading
                        $('#practice-loading').removeClass('hidden');

                        // sembunyikan content lama
                        $('#practice-content').addClass('hidden');

                        studentFormAssessment(0, false);
                    },
                    error: function () {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memulai latihan.'
                        });

                    }
                });

            } else {
                window.location.href = `/lms/${role}/tka-simulation`;
            }
        });
    }
}

function confirmRestartPractice() {
    Swal.fire({
        title: 'Mulai Latihan Lagi?',
        text: 'Latihan baru akan dimulai dari awal dengan soal yang baru. Apakah kamu ingin melanjutkan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Mulai Lagi',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0071BC',
        cancelButtonColor: '#6B7280',
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: `/lms/${role}/tka-simulation/class/${kelasId}/subject/${mapelId}/practice-test/restart`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {

                    // RESET STATE
                    isReviewMode = false;
                    isResultMode = false;
                    currentQuestionIndex = 0;

                    resetTimer();

                    $('#practice-result').addClass('hidden');
                    $('#practice-content').removeClass('hidden');

                    studentFormAssessment(0, true);
                },

                error: function () {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal memulai latihan.'
                    });

                }
            });

        }
    });
}

$(document).off('change', 'input[type=radio], input[type=checkbox]').on('change', 'input[type=radio], input[type=checkbox]', function () {
    $(`#error-answer_value`).text('');
});

// btn show explanation
$(document).on('click', '#btn-show-explanation', function () {

    const questionId = $(this).data('question-id');

    const question = questions.find(q => q.id == questionId);

    const explanation = question.explanation ?? '<p class="text-gray-500">Pembahasan belum tersedia.</p>';

    $('#explanation-content').html(explanation);

    document.getElementById('modal-explanation').showModal();
});

$(document).off('click', '.matching-left, .matching-right')
    .on('click', '.matching-left, .matching-right', function () {

        $('#error-answer_value').text('');
    });

// Listener radio -> update input hidden (MCQ, MCMA TYPE)
$(document).on('change', 'input[name^="options_value_"]', function () {
    const soalId = $(this).data('soal-id');
    if (!soalId) return;

    if ($(this).attr('type') === 'checkbox') {
        // MCMA
        let selectedValue = [];
        $(`input[name="options_value_${soalId}[]"]:checked`).each(function () {
            selectedValue.push($(this).val());
        });
        $(`#userAnswer${soalId}`).val(JSON.stringify(selectedValue));
    } else {
        // MCQ single choice
        $(`#userAnswer${soalId}`).val($(this).val());
    }
});

// Listener radio -> update input hidden (PG Kompleks)
function collectPgKompleksAnswer(soalId) {
    const result = {};

    document.querySelectorAll('input[name^="pg_kompleks_"]:checked')
        .forEach(input => {
            const key = input.name.replace('pg_kompleks_', '');
            result[key] = input.value;
        });

    // simpan ke hidden input
    $(`#userAnswer${soalId}`).val(JSON.stringify(result));

    return result;
}

$(document).on('change', 'input[name^="pg_kompleks_"]', function () {
    const soalId = $('input[name="question_id"]').val();

    collectPgKompleksAnswer(soalId);

    $('#error-answer_value').text('');
});

$(document).on('click', '#btn-submit-save-answer', function (e) {
    const status = 'submitted';
    $('#status_answer').val(status);
});

function successAssessmentTest() {
    Swal.fire({
        icon: 'success',
        title: 'Latihan Berhasil Diselesaikan',
        text: 'Semua jawaban telah dikirim.',
    });

}

let isProcessing = false;

// Submit Jawaban
$(document).on('click', '#btn-submit-save-answer', function (e) {

    e.preventDefault();

    if (isProcessing) return;

    isProcessing = true;

    const container = $('#container-tka-practice-test-form');

    const role = container.data('role');
    const kelasId = container.data('kelas-id');
    const mapelId = container.data('mapel-id');

    const statusAnswer = $(this).data('status-answer');

    const form = $('#tka-practice-test-submit-form')[0];
    const formData = new FormData(form);

    formData.set('status_answer', statusAnswer);

    const btn = $(this);

    btn.prop('disabled', true);

    $.ajax({

        url: `/lms/${role}/tka-simulation/class/${kelasId}/subject/${mapelId}/practice-test/answer/${attemptId}`,

        method: 'POST',

        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        data: formData,

        processData: false,

        contentType: false,

        success: function (response) {

            // refresh data soal + jawaban
            studentFormAssessment(currentQuestionIndex, false);

            if (response.is_finished) {

                examFinished = true;

                stopTimer();

                const { START_KEY, EXPIRE_KEY } = getTimerKeys();

                if (START_KEY) {
                    localStorage.removeItem(START_KEY);
                }

                if (EXPIRE_KEY) {
                    localStorage.removeItem(EXPIRE_KEY);
                }

                successAssessmentTest();
            }

            isProcessing = false;
            btn.prop('disabled', false);

        },

        error: function (xhr) {

            if (xhr.status === 422) {

                const response = xhr.responseJSON;

                // VALIDATION
                if (response.errors) {

                    $('.text-red-500').text('');

                    $.each(response.errors, function (field, messages) {
                        $(`#error-${field}`).text(messages[0]);
                    });
                }
            }

            isProcessing = false;
            btn.prop('disabled', false);

        }

    });

});

function autoSubmitUnSavedQuestions(onFinish = null) {

    examFinished = true;

    stopTimer();

    const { START_KEY, EXPIRE_KEY } = getTimerKeys();

    if (START_KEY) {
        localStorage.removeItem(START_KEY);
    }

    if (EXPIRE_KEY) {
        localStorage.removeItem(EXPIRE_KEY);
    }

    $.ajax({

        url: `/lms/${role}/tka-simulation/class/${kelasId}/subject/${mapelId}/practice-test/form`,
        method: "GET",

        success: function (response) {

            const questions = response.data;
            const questionsAnswer = response.questionsAnswer;

            const requests = [];

            questions.forEach((question) => {

                const isSubmitted =
                    questionsAnswer[question.id]?.status_answer === "submitted";

                if (isSubmitted) {
                    return;
                }

                const request = $.ajax({

                    url: `/lms/${role}/tka-simulation/class/${kelasId}/subject/${mapelId}/practice-test/answer/${attemptId}`,
                    method: "POST",

                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },

                    data: {
                        question_id: question.id,
                        status_answer: "submitted",
                        auto_submit: true
                    }
                });

                requests.push(request);

            });

            $.when.apply($, requests).always(function () {

                studentFormAssessment(currentQuestionIndex, false);

                if (typeof onFinish === "function") {
                    onFinish();
                }

            });

        }

    });

}

$(document).on('click', '#btn-submit-end-tka-practice-test', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Konfirmasi Akhiri Latihan',
        text: "Apakah kamu yakin ingin mengakhiri Latihan?",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Akhiri Latihan',
        cancelButtonText: 'Batal',
        allowOutsideClick: false,
        allowEscapeKey: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            examStarted = true;

            finalExamDuration = getTotalExamDuration();

            autoSubmitUnSavedQuestions(function () {
                window.location.href = `/lms/${role}/tka-simulation`;
            });

        }
    });
});

$(document).on('click', '#btn-submit-exit-tka-practice-test', function (e) {
    e.preventDefault();

    // jika Latihan sudah selesai -> langsung keluar
    if (examFinished) {
        window.location.href = `/lms/${role}/tka-simulation`;
        return;
    }
});