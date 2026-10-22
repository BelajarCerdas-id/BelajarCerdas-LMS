let currentQuestionIndex = 0;
const editorInstances = [];
const previousImageUrlsMap = {};

const containerFormAssessment = $('#container-assessment-test-form');
const role = containerFormAssessment.data('role');
const schoolName = containerFormAssessment.data('school-name');
const schoolId = containerFormAssessment.data('school-id');
const curriculumId = containerFormAssessment.data('curriculum-id');
const mapelId = containerFormAssessment.data('mapel-id');
const assessmentTypeId = containerFormAssessment.data('assessment-type-id');
const semester = containerFormAssessment.data('semester');
const assessmentId = containerFormAssessment.data('assessment-id');

const START_KEY = `timer_assessment_test_start_${assessmentId}`;

const questionDurations = {};
let currentQuestionId = null;
let questionStartTime = null;
let questions = [];
let questionsAnswer = {};
let examStarted = false;
let cachedFormResponse = null;

// ============================================================
//  IMAGE ATTACHMENT HELPERS FOR TEXT / ESSAY QUESTIONS
// ============================================================

function escapeRegExp(string) {
    return (string || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function extractImageUrlsFromHtml(html) {
    if (!html || typeof html !== 'string') return [];
    const urls = [];
    const regex = /<img[^>]+src=["']([^"']+)["']/gi;
    let match;
    while ((match = regex.exec(html)) !== null) {
        if (match[1] && !urls.includes(match[1])) {
            urls.push(match[1]);
        }
    }
    return urls;
}

function getQuestionImageCount(questionId) {
    let content = '';
    const editorObj = editorInstances.find(item => item.element && item.element.id === `essay_${questionId}`);
    if (editorObj && editorObj.instance && typeof editorObj.instance.getData === 'function') {
        content = editorObj.instance.getData();
    } else {
        content = questionsAnswer[questionId]?.answer_value ?? $(`#userAnswer${questionId}`).val() ?? '';
    }
    return extractImageUrlsFromHtml(content).length;
}

function updateModalCameraCount(questionId) {
    const count = getQuestionImageCount(questionId);
    const badge = $('#modal-web-camera-count');
    const finishBtn = $('#btn-finish-web-camera');
    if (count > 0) {
        badge.text(`${count} foto terlampir`).removeClass('hidden');
        finishBtn.removeClass('hidden');
    } else {
        badge.text('').addClass('hidden');
        finishBtn.addClass('hidden');
    }
}

function updateAttachedImagesGallery(questionId, editorInstance) {
    const container = $(`#attached-images-list-${questionId}`);
    if (!container.length) return;

    let content = '';
    if (editorInstance && typeof editorInstance.getData === 'function') {
        content = editorInstance.getData();
    } else {
        content = questionsAnswer[questionId]?.answer_value ?? $(`#userAnswer${questionId}`).val() ?? '';
    }

    const imageUrls = extractImageUrlsFromHtml(content);
    const countEl = $(`#attached-count-${questionId}`);
    if (countEl.length) {
        countEl.text(`${imageUrls.length} foto`);
    }

    const isReadOnly = (questionsAnswer[questionId]?.status_answer === 'submitted') || (typeof isAfter !== 'undefined' && isAfter);

    if (imageUrls.length === 0) {
        container.html(`
            <div class="text-xs text-gray-400 italic py-1">
                Belum ada foto yang dilampirkan.
            </div>
        `);
        return;
    }

    const html = imageUrls.map((url, idx) => `
        <div class="relative group border border-gray-200 bg-white rounded-lg p-2 shadow-xs flex items-center gap-3 w-full sm:w-auto min-w-[210px] max-w-full hover:border-blue-300 transition">
            <div class="w-14 h-14 rounded-md overflow-hidden bg-gray-100 shrink-0 border border-gray-200 cursor-pointer btn-preview-image" data-src="${url}" title="Klik untuk memperbesar">
                <img src="${url}" alt="Attachment ${idx + 1}" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0 pr-2">
                <div class="text-xs font-semibold text-gray-700 truncate">Foto Jawaban #${idx + 1}</div>
                <button type="button" class="text-[11px] text-[#0071BC] hover:underline cursor-pointer btn-preview-image flex items-center gap-1 mt-0.5" data-src="${url}">
                    <i class="fa-solid fa-magnifying-glass-plus text-[10px]"></i> Lihat Penuh
                </button>
            </div>
            ${!isReadOnly ? `
                <button type="button" class="btn-delete-attached-image p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition cursor-pointer shrink-0" data-question-id="${questionId}" data-url="${url}" title="Hapus gambar">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            ` : ''}
        </div>
    `).join('');

    container.html(html);
}

function uploadWebCameraSnapshot(questionId, blob, continueCapture = false) {
    if (!blob) {
        if (typeof window.setSelectingFile === 'function') {
            window.setSelectingFile(false);
        }
        return;
    }

    const loading = $('#web-camera-loading');
    const loadingText = $('#web-camera-loading-text');
    loadingText.text('Menyimpan foto jawaban...');
    loading.removeClass('hidden');

    const editorContainer = document.getElementById('container-assessment-test-form');
    const uploadUrl = editorContainer ? editorContainer.getAttribute('data-upload-url') : null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const fileName = `foto_jawaban_soal_${questionId}_${Date.now()}.jpg`;
    const formData = new FormData();
    formData.append('upload', blob, fileName);

    $.ajax({
        url: uploadUrl,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            loading.addClass('hidden');

            if (response && response.url) {
                const imageUrl = response.url;
                const editorObj = editorInstances.find(item => item.element && item.element.id === `essay_${questionId}`);

                if (editorObj && editorObj.instance) {
                    try {
                        editorObj.instance.execute('insertImage', { source: imageUrl });
                    } catch (err) {
                        const curData = editorObj.instance.getData();
                        editorObj.instance.setData(curData + `<p><img src="${imageUrl}" alt="Lampiran Foto Jawaban" /></p>`);
                    }
                    $(`#userAnswer${questionId}`).val(editorObj.instance.getData());
                    updateAttachedImagesGallery(questionId, editorObj.instance);
                } else {
                    const curVal = $(`#userAnswer${questionId}`).val() || '';
                    const newVal = curVal + `<p><img src="${imageUrl}" alt="Lampiran Foto Jawaban" /></p>`;
                    $(`#userAnswer${questionId}`).val(newVal);
                    if (questionsAnswer[questionId]) {
                        questionsAnswer[questionId].answer_value = newVal;
                    }
                    updateAttachedImagesGallery(questionId, null);
                }

                $('#error-answer_value').text('');

                if (continueCapture) {
                    if (capturedPhotoUrl) {
                        URL.revokeObjectURL(capturedPhotoUrl);
                        capturedPhotoUrl = null;
                    }
                    capturedPhotoBlob = null;

                    $('#web-camera-captured-img').addClass('hidden').attr('src', '');
                    $('#web-camera-video').removeClass('hidden');
                    $('#web-camera-overlay').removeClass('hidden');
                    $('#web-camera-preview-controls').addClass('hidden');
                    $('#web-camera-live-controls').removeClass('hidden');

                    updateModalCameraCount(questionId);

                    const videoEl = document.getElementById('web-camera-video');
                    if (videoEl && videoEl.paused && currentCameraStream) {
                        videoEl.play().catch(e => console.warn('Video resume error:', e));
                    }

                    const totalImages = getQuestionImageCount(questionId);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Foto tersimpan (${totalImages} foto terlampir)! Silakan ambil foto berikutnya.`,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    closeWebCameraModal();

                    const totalImages = getQuestionImageCount(questionId);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Foto jawaban berhasil dilampirkan (${totalImages} foto)!`,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan Foto',
                    text: response?.error?.message || 'Terjadi kesalahan saat menyimpan foto ke server.'
                });
            }
        },
        error: function (xhr) {
            loading.addClass('hidden');
            const errRes = xhr.responseJSON;
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengunggah Foto',
                text: errRes?.error?.message || errRes?.message || 'Gagal mengirim foto jawaban ke server.'
            });
        }
    });
}

function uploadAttachmentFile(questionId, file) {
    Swal.fire({
        icon: 'warning',
        title: 'Unggah File Dinonaktifkan',
        text: 'Pengunggahan file dari perangkat tidak diperkenankan demi integritas ujian. Harap gunakan tombol "Ambil Foto Jawaban" untuk mengambil foto langsung melalui kamera web.'
    });
    if (typeof window.setSelectingFile === 'function') {
        window.setSelectingFile(false);
    }
}

function renderAssessmentFormContent(selectedIndex, response) {

    const formAssessment = $('#form-assessment-test');
    formAssessment.empty();

    const isBefore = response.status === 'not_started';
    const isActive = response.status === 'active';
    const isAfter = response.status === 'expired';

    function formatAssessmentDate(start, end, timezone, timezoneLabel) {

        if (!start || !end || !timezone) {
            return {
                date: '-',
                time: '-'
            };
        }

        const startDate = new Date(start);
        const endDate = new Date(end);

        const dateFormatter = new Intl.DateTimeFormat('id-ID', {
            timeZone: timezone,
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

        const timeFormatter = new Intl.DateTimeFormat('id-ID', {
            timeZone: timezone,
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23'
        });

        return {
            date: dateFormatter.format(startDate),
            time: `${timeFormatter.format(startDate)} - ${timeFormatter.format(endDate)} ${timezoneLabel ?? timezone}`
        };
    }

    const formatted = formatAssessmentDate(response.start_date_iso, response.end_date_iso, response.timezone, response.timezone_label);

    $('#assessment-date').text(formatted.date);
    $('#assessment-time').text(formatted.time);
    $('#assessment-title').text(response.assessment_title + ' - Semester ' + response.semester);

    // Mendapatkan data soal
    questions = response.data;

    // Mendapatkan data jawaban
    questionsAnswer = response.questionsAnswer;

    const question = questions[selectedIndex];
    const questionType = question?.lms_question_bank?.tipe_soal?.toLowerCase();
    const showScore = response.schoolAssessment.show_score ?? false;
    const showAnswer = response.schoolAssessment.show_answer ?? false;

    if (question) {
        const totalSoal = questions.length;

        let jumlahSoalTerjawab = Object.values(questionsAnswer).filter(q => q.status_answer === 'submitted').length;

        // Cek apakah semua soal sudah dijawab
        const isAllAnswered = jumlahSoalTerjawab === totalSoal;

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

        const assessmentCategory = response.schoolAssessment.assessment_category;
        const totalQuestions = questions.length;

        const weight = calculateQuestionWeight(question, totalQuestions, assessmentCategory);

        function calculateQuestionWeight(question, totalQuestions, assessmentCategory) {

            // jika bukan remedial -> pakai dari DB
            if (!['remedial'].includes((assessmentCategory || '').toLowerCase())) {
                return question.question_weight;
            }

            // jika remedial -> hitung ulang
            let rawScore = 100 / totalQuestions;

            // ambil 2 desimal dengan pembulatan normal
            let rounded = Math.round(rawScore * 100) / 100;

            return rounded;
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

                if (isAllAnswered && showAnswer) {
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

                    if (status === 'submitted' || status === 'draft') {
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

                // memeriksa apakah soal sudah dijawab oleh pengguna atau jawaban masih ditandai
                if (!questionsAnswer[question.id] || questionsAnswer[question.id]?.status_answer === 'draft') {
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
                } else if (questionsAnswer[question.id] && questionsAnswer[question.id]?.status_answer === 'submitted') {
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
                }

                // Render opsi jawaban
                return `
                            ${optionsValue}
                        `;
            }).join('');
        };

        const generateEssay = (weight) => {
            const teacherFeedback = questionsAnswer[question.id]?.teacher_feedback ?? '';
            const score = questionsAnswer[question.id]?.question_score ?? null;

            let renderEssay = '';
            const isSubmitted = questionsAnswer[question.id]?.status_answer === 'submitted';
            const isReadOnly = isSubmitted || isAfter;

            if (!isReadOnly) {
                renderEssay = `
                            <div class="space-y-4">
                                <div class="relative">
                                    <textarea id="essay_${question.id}" name="essay_${question.id}" rows="6" class="editor w-full border border-gray-300 rounded-xl p-4 text-sm resize-none transition"
                                    placeholder="Tulis jawaban kamu di sini...">${questionsAnswer[question.id]?.answer_value ?? ''}</textarea>
                                </div>

                                <!-- In-Web Live Camera Attachment Box (Strictly No File Upload) -->
                                <div id="essay-attachment-box-${question.id}" class="essay-attachment-box rounded-xl border border-blue-200 bg-blue-50/40 p-4 transition-all duration-200" data-question-id="${question.id}">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                        <div class="flex items-start sm:items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-blue-100 text-[#0071BC] flex items-center justify-center shrink-0 shadow-xs">
                                                <i class="fa-solid fa-camera text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-semibold text-gray-800 text-sm">Lampirkan Foto Jawaban Langsung</h4>
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-[#0071BC] font-medium flex items-center gap-1">
                                                        <i class="fa-solid fa-layer-group text-[9px]"></i> Bisa Lebih Dari 1 Foto
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-gray-500 mt-0.5">Ambil satu atau beberapa foto lembar jawaban langsung melalui kamera</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 w-full sm:w-auto">
                                            <button type="button" class="btn-open-web-camera w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#0071BC] hover:bg-[#005a96] text-white text-xs font-semibold rounded-lg shadow-xs transition cursor-pointer" data-question-id="${question.id}">
                                                <i class="fa-solid fa-camera"></i>
                                                <span>Ambil Foto Jawaban</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Attached Images List -->
                                    <div class="mt-4 pt-3 border-t border-blue-100/80">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                                <i class="fa-solid fa-images text-[#0071BC] text-[11px]"></i>
                                                Foto Jawaban Terlampir:
                                            </span>
                                            <span id="attached-count-${question.id}" class="text-[11px] text-gray-500 font-medium">0 foto</span>
                                        </div>
                                        <div id="attached-images-list-${question.id}" class="flex flex-wrap gap-2.5">
                                            <div class="text-xs text-gray-400 italic py-1">Belum ada foto jawaban yang dilampirkan.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
            } else {
                renderEssay = `
                            <div class="space-y-4">
                                <div class="relative">
                                    <textarea id="essay_${question.id}" name="essay_${question.id}" rows="6" class="editor w-full border border-gray-300 rounded-xl p-4 text-sm resize-none transition"
                                    placeholder="Tulis jawaban kamu di sini...">${questionsAnswer[question.id]?.answer_value ?? ''}</textarea>
                                </div>

                                <!-- Read-only Attached Images Gallery -->
                                <div id="essay-attachment-box-${question.id}" class="rounded-xl border border-gray-200 bg-gray-50/70 p-4" data-question-id="${question.id}">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                            <i class="fa-solid fa-images text-[#0071BC] text-[11px]"></i>
                                            Gambar Terlampir:
                                        </span>
                                        <span id="attached-count-${question.id}" class="text-[11px] text-gray-500 font-medium">0 gambar</span>
                                    </div>
                                    <div id="attached-images-list-${question.id}" class="flex flex-wrap gap-2.5">
                                        <div class="text-xs text-gray-400 italic py-1">Memuat lampiran...</div>
                                    </div>
                                </div>
                            </div>
                        `;
            }

            if (questionType === 'essay' && isAllAnswered && showAnswer && teacherFeedback) {

                renderEssay += `
                            <div class="mt-6 p-5 rounded-2xl bg-purple-50 border border-purple-200">

                                <div class="flex items-start gap-3">

                                    <div class="text-purple-600 text-lg">
                                        <i class="fas fa-comment-dots"></i>
                                    </div>

                                    <div class="w-full">

                                        <h3 class="font-semibold text-purple-800 text-sm">
                                            Teacher Feedback
                                        </h3>

                                        <div class="text-sm text-purple-700 mt-2 leading-relaxed list-style">
                                            ${teacherFeedback}
                                        </div>

                                        ${score !== null ?
                        `
                                                <div class="mt-3 text-sm font-semibold text-gray-700">
                                                    Score: ${score} / ${weight}
                                                </div>`
                        : ''
                    }

                                    </div>

                                </div>

                            </div>
                        `;
            }

            return renderEssay;
        };

        // Render Nomor Soal
        const nomorSoalHTML = questions.map((item, index) => {

            let statusClassNumberQuestions = '';
            const answer = questionsAnswer[item.id];
            const itemType = item?.lms_question_bank?.tipe_soal?.toLowerCase();

            // === ESSAY (manual grading) ===
            if (itemType === 'essay') {
                if (answer?.status_answer === 'submitted') {
                    statusClassNumberQuestions = '!bg-[#F79D65] text-white font-bold';
                } else if (answer?.status_answer === 'draft') {
                    statusClassNumberQuestions = '!bg-[#F79D65] text-white font-bold';
                }

            } else {
                if (isAllAnswered && showAnswer) {
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
                    else if (answer?.status_answer === 'draft') {
                        statusClassNumberQuestions = '!bg-[#FFC107] text-white font-bold';
                    }
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

            if (isAllAnswered && showAnswer) {
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

            const isReviewMode = isAllAnswered && showAnswer;

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

        const options = question?.lms_question_bank?.lms_question_option ?? [];

        const leftItems = options.filter(item => item.options_key.startsWith('LEFT'));
        const rightItems = options.filter(item => item.options_key.startsWith('RIGHT'));

        if (questionType === 'essay') {
            submitAnswerType = generateEssay(weight);
        } else if (questionType === 'matching') {
            submitAnswerType = generateMatching(leftItems, rightItems);
        } else if (questionType === 'pg_kompleks') {
            submitAnswerType = generatePgKompleks(question.lms_question_bank?.lms_question_option);
        } else {
            submitAnswerType = generateOptions(question?.lms_question_bank?.lms_question_option);
        }

        const isAnswered = !!questionsAnswer[question.id] && questionsAnswer[question.id]?.status_answer === 'submitted';

        const isCorrect = !!questionsAnswer[question.id]?.is_correct; // jadikan boolean true or false

        let submitButtonAnswerHTML = '';

        submitButtonAnswerHTML = isAnswered
            ? `
                        <button type="button" class="bg-gray-200 px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold opacity-70 cursor-default" disabled>
                            Sudah Disimpan
                        </button>
                    `
            : `
                        <button type="button" id="btn-submit-save-answer" data-status-answer="submitted" class="bg-[#43AB3C] hover:bg-[#399632] text-white px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold cursor-pointer flex items-center justify-center gap-2">
                            <span>Simpan Jawaban</span>
                            ${selectedIndex < questions.length - 1 ? '<i class="fa-solid fa-arrow-right text-xs"></i>' : ''}
                        </button>
                    `;

        const btnPrevHTML = selectedIndex > 0
            ? `
                        <button type="button" class="btn-nav-question flex items-center justify-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-100 px-5 py-2.5 rounded-md font-semibold text-sm transition cursor-pointer" data-index="${selectedIndex - 1}">
                            <i class="fa-solid fa-arrow-left text-xs"></i> Soal Sebelumnya
                        </button>
                    `
            : '';

        const btnNextHTML = selectedIndex < questions.length - 1
            ? `
                        <button type="button" class="btn-nav-question flex items-center justify-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-100 px-5 py-2.5 rounded-md font-semibold text-sm transition cursor-pointer" data-index="${selectedIndex + 1}">
                            Soal Selanjutnya <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    `
            : '';

        let btnMarkAnswerHTML = '';

        btnMarkAnswerHTML = isAnswered
            ? `
                        <button type="button" class="bg-gray-200 px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold opacity-70 cursor-default" disabled>
                            Tandai Jawaban
                        </button>
                    `
            : `
                        <button type="button" id="btn-submit-draft-answer" data-status-answer="draft" class="px-5 py-2.5 rounded-md text-sm font-semibold border
                            bg-[#FFC107] text-white shadow-md hover:shadow-lg transition cursor-pointer">
                            Tandai Jawaban
                        </button>
                    `;

        let explanationButtonHTML = '';

        if (isAllAnswered && showAnswer) {
            explanationButtonHTML = `
                        <button type="button" id="btn-show-explanation" data-question-id="${question.id}" class="bg-[#0071BC] text-white px-5 py-2.5 
                            rounded-md shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold cursor-pointer">
                            Lihat Pembahasan
                        </button>
                    `;
        }

        let buttonCorrectOrWrongHTML = '';

        if (questionType !== 'essay') {
            buttonCorrectOrWrongHTML = isAllAnswered && showAnswer
                ? (isCorrect
                    ? `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm text-center bg-green-200 text-green-600 font-bold rounded-md" disabled>Jawaban Benar</button>`
                    : `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm text-center bg-red-200 text-red-600 font-bold opacity-70 rounded-md" disabled>Jawaban Salah</button>`)
                : `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm font-semibold text-center bg-gray-200 opacity-70 rounded-md" disabled>Jawaban Benar/Salah</button>`;
        }

        // QUESTION SPLIT IMAGE
        const splitQuestions = question?.lms_question_bank?.questions.split('<img');
        const questionTextOnly = splitQuestions[0];

        let questionImage = '', textAfterImage = '';

        if (splitQuestions.length > 1) {
            const imgSplit = splitQuestions[1].split('>'); // pisahkan tag <img> dan sisa teks
            const imgTag = imgSplit[0]; // bagian src dan atribut gambar
            const restText = imgSplit.slice(1).join('>'); // gabungkan sisa setelah tag img

            questionImage = `<img class="w-full sm:max-w-[45%]" ${imgTag}>`; // Susun tag <img> lengkap dengan class tambahan
            textAfterImage = restText.trim(); // Hapus spasi berlebih pada teks setelah gambar
        }

        // Gabungkan menjadi HTML: bungkus gambar dan teks
        const questionImageAndTextAfter = `
                    <div class="flex flex-col gap-4 items-start">
                        ${questionImage}
                        <div>${textAfterImage}</div>
                    </div>
                `;

        function generateModeInfo() {

            if (isAfter && !isAllAnswered) {
                return `
                            <div class="mb-6 p-5 rounded-2xl bg-red-50 border border-red-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-red-600 text-xl">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-red-800 text-sm">
                                            Assessment Telah Selesai
                                        </h3>
                                        <p class="text-xs text-red-700 mt-1">
                                            Waktu pengerjaan telah berakhir. Jawaban tidak dapat diubah.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
            }

            // ===== CASE 1: Review Only =====
            if (isAllAnswered && !showScore && showAnswer) {
                return `
                            <div class="mb-6 p-5 rounded-2xl bg-yellow-50 border border-yellow-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-yellow-600 text-xl">
                                        <i class="fas fa-user-lock"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-yellow-800 text-sm">
                                            Mode Review Aktif
                                        </h3>
                                        <p class="text-xs text-yellow-700 mt-1">
                                            Nilai belum dirilis oleh guru. 
                                            Kamu dapat melihat kembali jawaban yang telah dikirimkan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
            }

            // ===== CASE 2: Score Only =====
            if (isAllAnswered && showScore && !showAnswer) {
                return `
                            <div class="mb-6 p-5 rounded-2xl bg-blue-50 border border-blue-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-blue-600 text-xl">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-blue-800 text-sm">
                                            Nilai Telah Dirilis
                                        </h3>
                                        <p class="text-xs text-blue-700 mt-1">
                                            Kamu dapat melihat hasil evaluasi, 
                                            tetapi tidak dapat mereview kembali detail jawaban.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
            }

            // ===== CASE 3: Full Access =====
            if (isAllAnswered && showScore && showAnswer) {
                return `
                            <div class="mb-6 p-5 rounded-2xl bg-green-50 border border-green-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-green-600 text-xl">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-green-800 text-sm">
                                            Hasil Telah Dipublikasikan
                                        </h3>
                                        <p class="text-xs text-green-700 mt-1">
                                            Kamu dapat melihat nilai dan detail jawaban.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
            }

            // ===== CASE 4: Locked =====
            if (isAllAnswered) {
                return `
                            <div class="mb-6 p-5 rounded-2xl bg-gray-100 border border-gray-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-gray-600 text-xl">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 text-sm">
                                            Menunggu Publikasi
                                        </h3>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Hasil Assessment belum tersedia.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
            }

            return '';
        }

        // tampilkan link result test ketika semua pertanyaan sudah dijawab (hanya jika attempt bukan in_progress)
        let linkResultTest = '';
        const resultTestHref = response.resultTestHref.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumId', curriculumId)
            .replace(':mapelId', mapelId).replace(':assessmentTypeId', assessmentTypeId).replace(':semester', semester).replace(':assessmentId', assessmentId);

        const isAttemptActive = response.attempt?.status === 'in_progress';

        if ((isAllAnswered && !isAttemptActive) || isAfter) {
            linkResultTest = `
                        <div class="mt-10 pt-6 border-t border-gray-100">
                            <a href="${resultTestHref}" 
                            class="w-full flex items-center justify-center gap-3 bg-green-500 hover:bg-green-600 transition text-white text-sm font-semibold py-3 rounded-xl shadow-md">
                                <i class="fas fa-chart-line"></i>
                                Lihat Nilai Assessment
                            </a>
                        </div>
                    `;
        }

        const form = `
                    <form id="assessment-test-submit-form" translate="no" class="notranslate">
                        <div class="max-w-450 mx-auto px-4 sm:px-6 lg:px-8 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14 items-stretch">
    
                            <!-- ================= LEFT ================= -->
                            <div class="lg:col-span-8 flex shadow-xl order-1 lg:order-0">
                                <div class="w-full bg-white rounded-2xl border border-gray-200 
                                    shadow-[0_8px_30px_rgba(0,0,0,0.04)] p-5 sm:p-6 lg:p-8 flex flex-col">
    
                                    ${generateModeInfo()}
    
                                    <!-- Header Soal -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-base font-semibold text-gray-800">
                                                Soal ${selectedIndex + 1}
                                            </span>
    
                                            <span class="text-[11px] sm:text-xs px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 font-semibold">
                                                Bobot: ${weight}
                                            </span>
    
                                            <span class="text-xs px-4 py-1.5 rounded-full 
                                                bg-blue-50 text-[#0071BC] font-semibold tracking-wide">
                                                ${questionType?.toUpperCase() ?? '-'}
                                            </span>
                                        </div>
    
                                        <div class="flex items-center gap-3">
                                            <button type="button" class="btn-refresh-questions-action flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0071BC] border border-blue-200 text-xs font-semibold transition cursor-pointer shadow-xs" title="Refresh semua soal dari database">
                                                <i class="fa-solid fa-rotate text-xs"></i>
                                                <span>Refresh Soal</span>
                                            </button>

                                            <span class="text-sm text-gray-500 font-medium">
                                                ${selectedIndex + 1} / ${questions.length}
                                            </span>
                                        </div>
                                    </div>
    
                                    <!-- Question -->
                                    <div class="question-content notranslate mb-6 text-sm sm:text-[15px] leading-relaxed text-gray-700" translate="no">
                                        <div class="mb-4 list-style">${questionTextOnly}</div>
                                        <div class="list-style">${questionImageAndTextAfter}</div>
                                    </div>
    
                                    <!-- Answer -->
                                    <div class="submit-answer-type notranslate space-y-4 grow" translate="no">
                                        ${submitAnswerType}
                                    </div>
    
                                    <input type="hidden" name="school_assessment_question_id" value="${question.id}">
                                    <input type="hidden" name="status_answer" id="status_answer" value="${questionsAnswer[question.id]?.status_answer ?? 'draft'}">
                                    <input type="hidden" name="answer_value" id="userAnswer${question.id}" value="">
                                    <span id="error-answer_value" class="text-red-500 font-bold text-xs pt-2 mb-4"></span>
                                    <input type="hidden" name="question_score" id="question_score" value="${question.question_weight}">
                                    <input type="hidden" name="answer_duration" id="answer_duration" value="0">
                                    <input type="hidden" name="total_exam_duration" id="total_exam_duration" value="0">
                                    <input type="hidden" name="status_attempt" id="status_attempt" value="submitted">
    
                                    <!-- Buttons -->
                                    <div class="flex flex-col sm:flex-row sm:justify-between items-stretch sm:items-center gap-3 sm:gap-4 mt-8 pt-6 border-t border-gray-100">
                                        <div>
                                            ${btnPrevHTML}
                                        </div>
                                        <div class="flex flex-wrap items-center justify-end gap-3 sm:gap-4">
                                        ${buttonCorrectOrWrongHTML}
                                    
                                        ${explanationButtonHTML}
    
                                        ${btnMarkAnswerHTML}
    
                                        ${submitButtonAnswerHTML}

                                        ${btnNextHTML}
                                    </div>
                                </div>
    
                                </div>
                            </div>
    
                            <!-- ================= RIGHT ================= -->
                            <div class="lg:col-span-4 flex shadow-xl order-0 lg:order-1">
                                <div class="w-full bg-white rounded-3xl border border-gray-200 
                                    shadow-[0_10px_40px_rgba(0,0,0,0.05)] p-5 sm:p-6 lg:p-8 flex flex-col">
    
                                    <h3 class="bg-[#0071BC] text-white text-center text-sm py-3 
                                        rounded-2xl font-semibold mb-8">
                                        Kartu Assessment Siswa
                                    </h3>
    
                                    <div class="border border-gray-200 rounded-2xl p-6 flex justify-between items-center 
                                        mb-10 bg-gray-50 shadow-sm">
    
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">
                                                ${response.user?.student_profile?.nama_lengkap}
                                            </p>
    
                                            <p class="text-sm text-gray-500 mt-1">
                                                ${question?.lms_question_bank?.mapel?.mata_pelajaran ?? '-'}
                                            </p>
                                        </div>
                                    </div>
    
                                    <div class="grid grid-cols-5 sm:grid-cols-6 lg:grid-cols-4 xl:grid-cols-8 gap-2 sm:gap-3 text-sm">
                                        ${nomorSoalHTML}
                                    </div>
    
                                    ${linkResultTest}
                                </div>
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

        if (isAfter) {
            $('input[type="radio"], input[type="checkbox"]').removeClass('cursor-pointer').addClass('cursor-default').prop('disabled', true);
            $('label.checked-option').removeClass('cursor-pointer').addClass('cursor-default').css('pointer-events', 'none');

            $('.matching-container').addClass('pointer-events-none opacity-70');

            $('#btn-submit-save-answer').hide();
            $('#btn-submit-draft-answer').hide();
        } else if (isBefore) {
            $('.question-content').addClass('blur-xs');
            $('.submit-answer-type').addClass('blur-xs');

            $('#btn-submit-save-answer').hide();
            $('#btn-submit-draft-answer').hide();
        }

        $('#btn-submit-end-assessment-test').show();
        $('#btn-submit-exit-assessment-test').hide();

        // jika semua soal sudah terjawab dan attempt sudah disubmit/selesai, maka bersihkan timer
        if ((isAllAnswered && !isAttemptActive) || isAfter) {
            examFinished = true;

            stopQuestionTimer();
            resetAllQuestionDurations();
            stopTimer();
            stopHeartbeat();

            document.getElementById('timer-assessment-test').textContent = 'Waktu Habis';

            $('#btn-submit-end-assessment-test').hide();
            $('#btn-submit-exit-assessment-test').show();

        } else if (isAnswered) {
            stopQuestionTimer();
            startTimer();

        } else if (examStarted && isActive) {
            startQuestionTimer(question.id);

        } else if (!examStarted && isActive) {
            confirmStartExam();
        }

        $('#answer_duration').val(questionDurations[question.id] || 0);
        $('#total_exam_duration').val(getTotalExamDuration());

        // Set aktif pertama
        $(`#nomor${selectedIndex}`).prop('checked', true);

        $(document).off('click', '.nomor-soal').on('click', '.nomor-soal', function () {
            saveQuestionDuration();

            const index = parseInt($(this).data('index'));
            currentQuestionIndex = index;
            studentFormAssessment(index);
        });

        $(document).off('click', '.btn-nav-question').on('click', '.btn-nav-question', function (e) {
            e.preventDefault();
            saveQuestionDuration();
            const targetIndex = parseInt($(this).data('index'));
            if (!isNaN(targetIndex) && targetIndex >= 0 && targetIndex < questions.length) {
                currentQuestionIndex = targetIndex;
                studentFormAssessment(targetIndex);
            }
        });

        // Bersihkan editor sebelumnya jika ada
        while (editorInstances.length) {
            const prevEditor = editorInstances.pop();
            if (prevEditor && prevEditor.instance && typeof prevEditor.instance.destroy === 'function') {
                prevEditor.instance.destroy().catch(() => { });
            }
        }

        // Inisialisasi CKEditor jika ada
        const editorContainer = document.getElementById('container-assessment-test-form');
        const uploadUrl = editorContainer ? editorContainer.getAttribute('data-upload-url') : null;
        const deleteUrl = editorContainer ? editorContainer.getAttribute('data-delete-url') : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const editors = formAssessment.find('.editor');
        editors.each((index, textarea) => {
            const soalId = textarea.id.split('_')[1];

            // Render galeri awal dengan data yang ada
            updateAttachedImagesGallery(soalId, null);

            ClassicEditor.create(textarea, {
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'underline',
                        'strikethrough',
                        '|',
                        'bulletedList',
                        'numberedList',
                        'blockQuote',
                        '|',
                        'undo',
                        'redo'
                    ],
                    shouldNotGroupWhenFull: true
                },
            })
                .then(editor => {
                    previousImageUrlsMap[index] = [];

                    // Cegah drop atau paste file gambar langsung ke dalam CKEditor
                    if (editor.editing && editor.editing.view && editor.editing.view.document) {
                        editor.editing.view.document.on('clipboardInput', (evt, data) => {
                            const dataTransfer = data.dataTransfer;
                            if (dataTransfer && dataTransfer.files && dataTransfer.files.length > 0) {
                                evt.stop();
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Tempel File Dinonaktifkan',
                                    text: 'Pengunggahan file gambar dari luar tidak diperkenankan. Harap gunakan tombol "Ambil Foto Jawaban" untuk mengambil foto langsung via kamera web.'
                                });
                            }
                        });
                    }

                    $(`#userAnswer${soalId}`).val(editor.getData());

                    const isSubmitted = questionsAnswer[soalId]?.status_answer === 'submitted';

                    if (isSubmitted || isAfter) {
                        editor.enableReadOnlyMode('assessment-lock');
                    }

                    // Update galeri dengan instance editor yang aktif
                    updateAttachedImagesGallery(soalId, editor);

                    // Sinkronkan data, gambar, dan galeri ketika CKEditor berubah
                    editor.model.document.on('change:data', () => {
                        const currentContent = editor.getData();
                        const imageUrls = Array.from(currentContent.matchAll(/<img[^>]+src="([^">]+)"/g)).map(match => match[1]);

                        const removedImages = (previousImageUrlsMap[index] || []).filter(url => !imageUrls.includes(url));

                        removedImages.forEach(url => {
                            if (deleteUrl && csrfToken) {
                                fetch(deleteUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({ imageUrl: url })
                                })
                                    .then(response => response.json())
                                    .catch(error => console.error('Error saat menghapus gambar:', error));
                            }
                        });

                        previousImageUrlsMap[index] = imageUrls;
                        $(`#userAnswer${soalId}`).val(currentContent);
                        $('#error-answer_value').text('');
                        updateAttachedImagesGallery(soalId, editor);
                    });

                    editorInstances.push({ element: textarea, instance: editor });
                })
                .catch(error => {
                    console.error('Error CKEditor:', error);
                    updateAttachedImagesGallery(soalId, null);
                });
        });

        $('#empty-message-assessment-form').hide();

        if (window.MathJax && window.MathJax.typesetPromise) {
            window.MathJax.typesetPromise([formAssessment[0]]).catch(err => console.warn('MathJax error:', err));
        }

    } else {
        examFinished = true;
        $('#btn-submit-end-assessment-test').hide();
        $('#btn-submit-exit-assessment-test').show();
        $('#empty-message-assessment-form').show();
    }
}

function studentFormAssessment(selectedIndex = 0, forceFetch = false) {

    if (!containerFormAssessment.length) return;

    if (cachedFormResponse && !forceFetch) {
        renderAssessmentFormContent(selectedIndex, cachedFormResponse);
        return;
    }

    const refreshUrl = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}` + (forceFetch ? `?force_refresh=1&_t=${Date.now()}` : '');

    $.ajax({
        url: refreshUrl,
        method: 'GET',
        success: function (response) {
            cachedFormResponse = response;
            renderAssessmentFormContent(selectedIndex, cachedFormResponse);
        }
    });
}

// Handler Refresh Soal (Memuat ulang semua soal langsung dari database)
$(document).on('click', '#btn-refresh-exam-questions, .btn-refresh-questions-action', function (e) {
    e.preventDefault();

    const $allBtns = $('#btn-refresh-exam-questions, .btn-refresh-questions-action');
    const $icons = $allBtns.find('i');

    $allBtns.prop('disabled', true).addClass('opacity-50 cursor-not-allowed pointer-events-none');
    $icons.addClass('fa-spin');

    // Simpan durasi soal yang sedang dikerjakan sebelum refresh
    if (typeof saveQuestionDuration === 'function') {
        saveQuestionDuration();
    }

    // Reset cache respons lokal
    cachedFormResponse = null;

    const targetIndex = (typeof currentQuestionIndex === 'number' && currentQuestionIndex >= 0) ? currentQuestionIndex : 0;

    const refreshUrl = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}?force_refresh=1&_t=${Date.now()}`;

    $.ajax({
        url: refreshUrl,
        method: 'GET',
        success: function (response) {
            cachedFormResponse = response;
            const validIndex = (response.data && targetIndex < response.data.length) ? targetIndex : 0;
            currentQuestionIndex = validIndex;
            renderAssessmentFormContent(validIndex, cachedFormResponse);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Soal Diperbarui',
                    text: 'Seluruh butir soal berhasil diperbarui dari database.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });
            }
        },
        error: function (xhr) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memperbarui Soal',
                    text: 'Terjadi kendala saat memuat ulang soal dari server.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        },
        complete: function () {
            $allBtns.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed pointer-events-none');
            $icons.removeClass('fa-spin');
        }
    });
});

$(document).ready(function () {
    $.getJSON(`/lms/check-assessment-status/${assessmentId}`, function (response) {

        if (response.status === 'not_started') {
            Swal.fire({
                icon: 'warning',
                title: 'Assessment Belum Dimulai',
                text: 'Sesi Assessment belum dimulai. Silakan kembali saat waktu Assessment telah dimulai.',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
                }
            });
            return;
        }

        if (response.status === 'expired') {
            Swal.fire({
                icon: 'warning',
                title: 'Assessment Telah Berakhir',
                text: 'Waktu pengerjaan Assessment telah berakhir.',
                confirmButtonText: 'OK',
            });
            return;
        }
    });

    if (sessionStorage.getItem('exam_reloading')) {
        isPageReloading = true;
        antiCheatCooldown = true;
        sessionStorage.removeItem('exam_reloading');

        // aktifkan kembali anti-cheat setelah page stabil
        setTimeout(() => {
            isPageReloading = false;
            antiCheatCooldown = false;
        }, 1500);
    }

    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };

    const hasTimer = localStorage.getItem(START_KEY);
    const isAlreadyCompleted = localStorage.getItem(`assessment_${assessmentId}_all_answered`);

    if (hasTimer && !isAlreadyCompleted) {
        examStarted = true;
        startTimer();
    } else if (isAlreadyCompleted) {
        // Exam was already completed — clean up any leftover timer state
        examFinished = true;
        localStorage.removeItem(`timer_assessment_test_start_${assessmentId}`);
        localStorage.removeItem(`timer_assessment_test_expire_${assessmentId}`);
    }

    studentFormAssessment(0);
    checkAttemptStatus();
    initAntiCheatSystem();

});

window.addEventListener('beforeunload', function () {
    isPageReloading = true;
    sessionStorage.setItem('exam_reloading', '1');
    saveQuestionDuration();
});

// listener untuk matching
$(document).off('click', '.matching-right').on('click', '.matching-right', function () {

    if (!handleMatchingRightClick(this)) return;

    const soalId = $('#assessment-test-submit-form input[name="school_assessment_question_id"]').val();

    $(`#userAnswer${soalId}`).val(JSON.stringify(studentPairs));
});

function confirmStartExam() {
    if (examStarted) return;

    // cek apakah ada timer
    if (document.getElementById("timer-assessment-test")) {

        Swal.fire({
            title: 'Konfirmasi Mulai Assessment',
            text: "Klik 'Mulai Assessment' untuk masuk mode fullscreen dan mulai timer!",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Mulai Assessment',
            cancelButtonText: 'Batal',
            allowOutsideClick: false,
            allowEscapeKey: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                examStarted = true;
                enterFullscreen();
                studentFormAssessment(0); // load soal pertama
                startQuestionTimer(questions[0].id); // mulai timer soal pertama
                startTimer(); // mulai timer global
                checkAttemptStatus();
                initAntiCheatSystem();
            } else {
                // jika user batal, redirect ke halaman preview assessment
                window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
            }
        });
    }
}

$(document).off('change', 'input[type=radio], input[type=checkbox]').on('change', 'input[type=radio], input[type=checkbox]', function () {
    $(`#error-answer_value`).text('');
});


// btn show explanation
$(document).on('click', '#btn-show-explanation', function () {

    const questionId = $(this).data('question-id');

    const question = questions.find(q => q.id == questionId);

    const explanation = question?.lms_question_bank?.explanation ?? '<p class="text-gray-500">Pembahasan belum tersedia.</p>';

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
    const soalId = $('input[name="school_assessment_question_id"]').val();

    collectPgKompleksAnswer(soalId);

    $('#error-answer_value').text('');
});

function successAssessmentTest() {
    Swal.fire({
        icon: 'success',
        title: 'Assessment Berhasil Diselesaikan',
        text: 'Semua jawaban telah dikirim.',
    });

}

function resetQuestionDuration(questionId) {

    // hapus dari memory
    delete questionDurations[questionId];

    // hapus dari localStorage
    localStorage.removeItem('duration_' + questionId);

    // reset input
    $('#answer_duration').val(0);
}

// Submit form jawaban (Simpan Jawaban & Tandai Jawaban di Background)
$(document).on('click', '#btn-submit-save-answer, #btn-submit-draft-answer', function (e) {
    e.preventDefault();

    const btn = $(this);
    const statusAnswer = btn.data('status-answer') || (btn.text().toLowerCase().includes('tandai') ? 'draft' : 'submitted');

    const form = $('#assessment-test-submit-form')[0];
    if (!form) return;

    const formData = new FormData(form);
    formData.set('status_answer', statusAnswer);

    const questionId = formData.get('school_assessment_question_id');
    const answerVal = formData.get('answer_value');

    // Validasi: jika 'submitted', jawaban tidak boleh kosong
    let isEmptyAnswer = !answerVal || answerVal.trim() === '' || answerVal === '[]' || answerVal === '{}';
    if (!isEmptyAnswer && typeof answerVal === 'string') {
        const hasImg = /<img\b[^>]*src=/i.test(answerVal);
        const strippedText = answerVal.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
        // Jika tidak ada gambar dan teks bersih kosong (misal hanya tag kosong <p></p> atau spasi)
        if (!hasImg && strippedText === '') {
            isEmptyAnswer = true;
        }
    }

    if (statusAnswer === 'submitted' && isEmptyAnswer) {
        $('#error-answer_value').text('Harap pilih, isi jawaban, atau lampirkan gambar terlebih dahulu.');
        return;
    }

    $('#error-answer_value').text('');

    saveQuestionDuration();
    stopQuestionTimer();
    resetQuestionDuration(questionId);

    const totalQuestions = questions.length;
    const totalSubmitted = Object.values(questionsAnswer).filter(q => q.status_answer === 'submitted').length;
    if (totalSubmitted + 1 === totalQuestions && statusAnswer === 'submitted') {
        formData.append('total_exam_duration', getTotalExamDuration());
    }

    // 1. Optimistic Local Update
    let parsedVal = answerVal;
    try {
        parsedVal = JSON.parse(answerVal);
    } catch (err) { }

    const updatedAnswerData = {
        school_assessment_question_id: questionId,
        status_answer: statusAnswer,
        answer_value: parsedVal,
        is_correct: questionsAnswer[questionId]?.is_correct ?? false,
    };

    questionsAnswer[questionId] = updatedAnswerData;
    if (cachedFormResponse && cachedFormResponse.questionsAnswer) {
        cachedFormResponse.questionsAnswer[questionId] = updatedAnswerData;
    }

    // 2. Berpindah ke soal berikutnya secara otomatis jika Simpan Jawaban
    const shouldGoNext = (statusAnswer === 'submitted' && currentQuestionIndex < questions.length - 1);
    if (shouldGoNext) {
        currentQuestionIndex = currentQuestionIndex + 1;
    }

    // Render soal seketika (0ms delay dari cache memori)
    studentFormAssessment(currentQuestionIndex);

    // 3. Simpan ke database di background secara asynchronous
    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/answer`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Exam only ends via manual "Akhiri Assessment" button or timer expiry.
            // Student can freely review and change answers after answering all questions.
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;

                // EXAM EXPIRED
                if (response?.status === 'expired') {
                    finalExamDuration = getTotalExamDuration();
                    autoSubmitUnSavedQuestions(function () {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Waktu Assessment Habis',
                            text: response.message,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            location.reload();
                        });
                    });
                    return;
                }

                if (response?.errors) {
                    $.each(response.errors, function (field, messages) {
                        $(`#error-${field}`).text(messages[0]);
                    });
                }
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: 'Terjadi kendala saat menyimpan jawaban ke server. Silakan periksa kembali jawaban Anda.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000
            });
        }
    });
});

function autoSubmitUnSavedQuestions(onFinish = null, statusAttempt = 'timeout') {

    examFinished = true;

    stopTimer();
    stopQuestionTimer();
    stopHeartbeat();

    // reset timer storage
    localStorage.removeItem(`timer_assessment_test_start_${assessmentId}`);
    localStorage.removeItem(`timer_assessment_test_expire_${assessmentId}`);

    // simpan durasi terakhir
    if (currentQuestionId && questionStartTime) {

        const now = Date.now();
        const duration = Math.floor((now - questionStartTime) / 1000);

        if (!questionDurations[currentQuestionId]) {
            questionDurations[currentQuestionId] = 0;
        }

        questionDurations[currentQuestionId] += duration;

        questionStartTime = null;
    }

    // Ambil jawaban dari form aktif saat ini jika ada yang belum tersimpan
    const currentForm = $('#assessment-test-submit-form')[0];
    let currentQuestionIdToSend = null;
    let currentAnswerDataToSend = null;
    let currentDurationToSend = 0;

    if (currentForm) {
        const formData = new FormData(currentForm);
        currentQuestionIdToSend = formData.get('school_assessment_question_id');
        const answerVal = formData.get('answer_value');
        if (answerVal && answerVal.trim() !== '' && answerVal !== '[]' && answerVal !== '{}') {
            currentAnswerDataToSend = answerVal;
        }
        currentDurationToSend = questionDurations[currentQuestionIdToSend] || 0;
    }

    // Kumpulkan durasi per soal dari localStorage jika di memory belum lengkap
    if (questions && questions.length) {
        questions.forEach((q) => {
            if (!questionDurations[q.id]) {
                const saved = localStorage.getItem('duration_' + q.id);
                if (saved) {
                    questionDurations[q.id] = parseInt(saved);
                }
            }
        });
    }

    const payload = {
        status_attempt: statusAttempt,
        total_exam_duration: finalExamDuration || getTotalExamDuration(),
        current_question_id: currentQuestionIdToSend,
        current_answer_value: currentAnswerDataToSend,
        current_answer_duration: currentDurationToSend,
        durations: questionDurations,
    };

    // Stagger network traffic with tiny random jitter (0 - 1200ms) to prevent concurrent thundering herd
    const jitter = Math.floor(Math.random() * 1200);

    setTimeout(function () {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/end`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: payload,
            success: function () {
                studentFormAssessment(currentQuestionIndex);
                if (typeof onFinish === "function") {
                    onFinish();
                }
            },
            error: function () {
                studentFormAssessment(currentQuestionIndex);
                if (typeof onFinish === "function") {
                    onFinish();
                }
            }
        });
    }, jitter);
}

$(document).on('click', '#btn-submit-end-assessment-test', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Konfirmasi Akhiri Assessment',
        text: "Apakah kamu yakin ingin mengakhiri Assessment?",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Akhiri Assessment',
        cancelButtonText: 'Batal',
        allowOutsideClick: false,
        allowEscapeKey: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            examStarted = true;

            saveQuestionDuration();
            finalExamDuration = getTotalExamDuration();

            localStorage.setItem(`assessment_${assessmentId}_all_answered`, '1');

            autoSubmitUnSavedQuestions(function () {
                window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
            }, 'submitted');

        }
    });
});

$(document).on('click', '#btn-submit-exit-assessment-test', function (e) {
    e.preventDefault();

    // jika Assessment sudah selesai -> langsung keluar
    if (examFinished) {
        window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
        return;
    }
});

// ============================================================
//  IN-WEB LIVE CAMERA CONTROLLER (No File Upload Allowed)
// ============================================================

let currentCameraQuestionId = null;
let currentCameraStream = null;
let currentCameraFacingMode = 'environment'; // default to back camera for photographing sheets
let capturedPhotoBlob = null;
let capturedPhotoUrl = null;

function stopWebCameraStream() {
    if (currentCameraStream) {
        currentCameraStream.getTracks().forEach(track => {
            try {
                track.stop();
            } catch (e) {
                console.warn('Error stopping camera track:', e);
            }
        });
        currentCameraStream = null;
    }
    const video = document.getElementById('web-camera-video');
    if (video) {
        video.srcObject = null;
    }
}

function startWebCameraStream() {
    const video = document.getElementById('web-camera-video');
    const loading = $('#web-camera-loading');
    const loadingText = $('#web-camera-loading-text');
    const errorBox = $('#web-camera-error');
    const errorMsg = $('#web-camera-error-msg');
    const overlay = $('#web-camera-overlay');
    const capturedImg = $('#web-camera-captured-img');
    const liveControls = $('#web-camera-live-controls');
    const previewControls = $('#web-camera-preview-controls');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        loading.addClass('hidden');
        errorMsg.text('Browser Anda tidak mendukung input kamera. Harap gunakan browser modern seperti Google Chrome, Microsoft Edge, atau Safari.');
        errorBox.removeClass('hidden');
        return;
    }

    // Reset preview state
    stopWebCameraStream();
    if (capturedPhotoUrl) {
        URL.revokeObjectURL(capturedPhotoUrl);
        capturedPhotoUrl = null;
    }
    capturedPhotoBlob = null;

    $(video).removeClass('hidden');
    capturedImg.addClass('hidden').attr('src', '');
    errorBox.addClass('hidden');
    loadingText.text('Menghubungkan ke kamera...');
    loading.removeClass('hidden');
    liveControls.removeClass('hidden');
    previewControls.addClass('hidden');
    overlay.removeClass('hidden');

    const constraints = {
        video: {
            facingMode: currentCameraFacingMode,
            width: { ideal: 1920 },
            height: { ideal: 1080 }
        },
        audio: false
    };

    navigator.mediaDevices.getUserMedia(constraints)
        .then(stream => {
            currentCameraStream = stream;
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play().then(() => {
                    loading.addClass('hidden');
                }).catch(err => {
                    console.warn('Autoplay prevented:', err);
                    loading.addClass('hidden');
                });
            };
        })
        .catch(err => {
            console.warn('First camera attempt failed:', err);
            // Fallback: If 'environment' failed (e.g. desktop webcam without back camera), try fallback with simple video: true
            if (currentCameraFacingMode === 'environment') {
                navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                    .then(stream => {
                        currentCameraFacingMode = 'user';
                        currentCameraStream = stream;
                        video.srcObject = stream;
                        video.onloadedmetadata = () => {
                            video.play().then(() => {
                                loading.addClass('hidden');
                            }).catch(() => {
                                loading.addClass('hidden');
                            });
                        };
                    })
                    .catch(err2 => {
                        handleCameraError(err2);
                    });
            } else {
                handleCameraError(err);
            }
        });

    function handleCameraError(error) {
        loading.addClass('hidden');
        let message = 'Tidak dapat mengakses kamera. Harap pastikan izin kamera telah diberikan di browser Anda.';
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            message = 'Akses kamera ditolak oleh pengguna atau sistem. Harap izinkan akses kamera di pengaturan izin situs peramban Anda.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            message = 'Perangkat kamera tidak ditemukan di komputer/perangkat Anda.';
        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            message = 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi yang memakai kamera lalu coba lagi.';
        }
        errorMsg.text(message);
        errorBox.removeClass('hidden');
    }
}

function closeWebCameraModal() {
    stopWebCameraStream();
    if (capturedPhotoUrl) {
        URL.revokeObjectURL(capturedPhotoUrl);
        capturedPhotoUrl = null;
    }
    capturedPhotoBlob = null;
    currentCameraQuestionId = null;

    $('#web-camera-captured-img').addClass('hidden').attr('src', '');
    $('#web-camera-video').removeClass('hidden');
    $('#web-camera-overlay').removeClass('hidden');
    $('#web-camera-live-controls').removeClass('hidden');
    $('#web-camera-preview-controls').addClass('hidden');
    $('#btn-finish-web-camera').addClass('hidden');
    $('#modal-web-camera-count').text('').addClass('hidden');

    if (typeof window.setSelectingFile === 'function') {
        window.setSelectingFile(false);
    }

    const modal = document.getElementById('modal-web-camera');
    if (modal && typeof modal.close === 'function' && modal.open) {
        modal.close();
    }
}

// Buka kamera web ujian saat tombol Ambil Foto Jawaban diklik
$(document).on('click', '.btn-open-web-camera', function (e) {
    e.preventDefault();
    const questionId = $(this).data('question-id');
    currentCameraQuestionId = questionId;

    if (typeof window.setSelectingFile === 'function') {
        window.setSelectingFile(true);
    }

    const modal = document.getElementById('modal-web-camera');
    if (modal && typeof modal.showModal === 'function') {
        updateModalCameraCount(questionId);
        modal.showModal();
        startWebCameraStream();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Kamera Tidak Tersedia',
            text: 'Komponen modal kamera tidak dapat dimuat.'
        });
        if (typeof window.setSelectingFile === 'function') {
            window.setSelectingFile(false);
        }
    }
});

// Tombol ganti kamera depan/belakang
$(document).on('click', '#btn-switch-camera', function () {
    currentCameraFacingMode = (currentCameraFacingMode === 'environment' ? 'user' : 'environment');
    startWebCameraStream();
});

// Coba lagi saat kamera error
$(document).on('click', '#btn-retry-web-camera', function () {
    startWebCameraStream();
});

// Ambil foto (shutter snapshot ke hidden canvas)
$(document).on('click', '#btn-capture-photo', function () {
    const video = document.getElementById('web-camera-video');
    const canvas = document.getElementById('web-camera-canvas');
    if (!video || !canvas || !currentCameraStream) return;

    const width = video.videoWidth || 1280;
    const height = video.videoHeight || 720;
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, width, height);

    // Watermark keamanan ujian
    ctx.save();
    ctx.font = '14px sans-serif';
    ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
    ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
    ctx.shadowBlur = 4;
    const timeStr = new Date().toLocaleString('id-ID');
    ctx.fillText(`Ujian BelajarCerdas • ${timeStr}`, 16, height - 16);
    ctx.restore();

    canvas.toBlob((blob) => {
        if (!blob) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menangkap Gambar',
                text: 'Terjadi kesalahan saat memproses frame kamera.'
            });
            return;
        }

        capturedPhotoBlob = blob;
        if (capturedPhotoUrl) {
            URL.revokeObjectURL(capturedPhotoUrl);
        }
        capturedPhotoUrl = URL.createObjectURL(blob);

        $('#web-camera-captured-img').attr('src', capturedPhotoUrl).removeClass('hidden');
        $(video).addClass('hidden');
        $('#web-camera-overlay').addClass('hidden');
        $('#web-camera-live-controls').addClass('hidden');
        $('#web-camera-preview-controls').removeClass('hidden');
    }, 'image/jpeg', 0.92);
});

// Foto ulang (retake)
$(document).on('click', '#btn-retake-photo', function () {
    if (capturedPhotoUrl) {
        URL.revokeObjectURL(capturedPhotoUrl);
        capturedPhotoUrl = null;
    }
    capturedPhotoBlob = null;

    $('#web-camera-captured-img').addClass('hidden').attr('src', '');
    $('#web-camera-video').removeClass('hidden');
    $('#web-camera-overlay').removeClass('hidden');
    $('#web-camera-preview-controls').addClass('hidden');
    $('#web-camera-live-controls').removeClass('hidden');
});

// Simpan dan selesai (tutup modal)
$(document).on('click', '#btn-use-photo', function () {
    if (!capturedPhotoBlob || !currentCameraQuestionId) {
        Swal.fire({
            icon: 'warning',
            title: 'Foto Belum Siap',
            text: 'Harap ambil foto terlebih dahulu sebelum menggunakan.'
        });
        return;
    }

    uploadWebCameraSnapshot(currentCameraQuestionId, capturedPhotoBlob, false);
});

// Simpan dan ambil foto lagi (kamera tetap aktif)
$(document).on('click', '#btn-use-photo-and-continue', function () {
    if (!capturedPhotoBlob || !currentCameraQuestionId) {
        Swal.fire({
            icon: 'warning',
            title: 'Foto Belum Siap',
            text: 'Harap ambil foto terlebih dahulu sebelum menggunakan.'
        });
        return;
    }

    uploadWebCameraSnapshot(currentCameraQuestionId, capturedPhotoBlob, true);
});

// Selesai mengambil foto saat berada di tampilan kamera langsung
$(document).on('click', '#btn-finish-web-camera', function () {
    closeWebCameraModal();
});

// Tutup / batal modal kamera
$(document).on('click', '#btn-close-web-camera, #btn-cancel-web-camera, #btn-backdrop-web-camera', function (e) {
    e.preventDefault();
    closeWebCameraModal();
});

// Listener dialog close / cancel native
const modalWebCameraEl = document.getElementById('modal-web-camera');
if (modalWebCameraEl) {
    modalWebCameraEl.addEventListener('close', function () {
        closeWebCameraModal();
    });
    modalWebCameraEl.addEventListener('cancel', function (e) {
        e.preventDefault();
        closeWebCameraModal();
    });
}

// ============================================================
//  SECURITY: STRICTLY BLOCK EXTERNAL FILE UPLOADS / DROPS / PASTES
// ============================================================

// Blokir drag and drop file di seluruh halaman ujian
$(document).on('dragover dragenter', function (e) {
    e.preventDefault();
});

$(document).on('drop', function (e) {
    const dt = e.originalEvent?.dataTransfer;
    if (dt && dt.files && dt.files.length > 0) {
        e.preventDefault();
        e.stopPropagation();
        Swal.fire({
            icon: 'warning',
            title: 'Unggah File Dinonaktifkan',
            text: 'Demi integritas ujian, pengunggahan file dari perangkat dinonaktifkan. Anda hanya dapat melampirkan foto jawaban secara langsung melalui kamera web dengan tombol "Ambil Foto Jawaban".',
            confirmButtonText: 'Mengerti'
        });
    }
});

// Blokir penempelan (paste) file/gambar dari clipboard eksternal
$(document).on('paste', function (e) {
    const clipboardData = e.originalEvent?.clipboardData || e.clipboardData;
    if (clipboardData && clipboardData.items) {
        for (let i = 0; i < clipboardData.items.length; i++) {
            const item = clipboardData.items[i];
            if (item.kind === 'file' || (item.type && item.type.indexOf('image') !== -1)) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tempel Gambar Dinonaktifkan',
                    text: 'Penyalinan file/gambar dari luar tidak diperkenankan demi integritas ujian. Gunakan tombol "Ambil Foto Jawaban" untuk mengambil foto lembar jawaban secara langsung melalui kamera web.',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }
        }
    }
});

// Delete an attached image
$(document).on('click', '.btn-delete-attached-image', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const btn = $(this);
    const questionId = btn.data('question-id');
    const targetUrl = btn.data('url');

    Swal.fire({
        title: 'Hapus Gambar?',
        text: 'Gambar ini akan dihapus dari jawaban Anda.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Cari instance editor untuk soal ini
        const editorObj = editorInstances.find(item => item.element && item.element.id === `essay_${questionId}`);
        if (editorObj && editorObj.instance) {
            let data = editorObj.instance.getData();
            const figureRegex = new RegExp(`<figure[^>]*>\\s*<img[^>]*src=["']${escapeRegExp(targetUrl)}["'][^>]*>\\s*<\\/figure>`, 'gi');
            const pRegex = new RegExp(`<p>\\s*<img[^>]*src=["']${escapeRegExp(targetUrl)}["'][^>]*>\\s*<\\/p>`, 'gi');
            const imgRegex = new RegExp(`<img[^>]*src=["']${escapeRegExp(targetUrl)}["'][^>]*>`, 'gi');

            let newData = data.replace(figureRegex, '').replace(pRegex, '').replace(imgRegex, '');
            editorObj.instance.setData(newData);
            $(`#userAnswer${questionId}`).val(newData);
        } else {
            let curVal = $(`#userAnswer${questionId}`).val() || '';
            const figureRegex = new RegExp(`<figure[^>]*>\\s*<img[^>]*src=["']${escapeRegExp(targetUrl)}["'][^>]*>\\s*<\\/figure>`, 'gi');
            const pRegex = new RegExp(`<p>\\s*<img[^>]*src=["']${escapeRegExp(targetUrl)}["'][^>]*>\\s*<\\/p>`, 'gi');
            const imgRegex = new RegExp(`<img[^>]*src=["']${escapeRegExp(targetUrl)}["'][^>]*>`, 'gi');
            let newVal = curVal.replace(figureRegex, '').replace(pRegex, '').replace(imgRegex, '');
            $(`#userAnswer${questionId}`).val(newVal);
            if (questionsAnswer[questionId]) {
                questionsAnswer[questionId].answer_value = newVal;
            }
        }

        // Hapus file fisik di server
        const editorContainer = document.getElementById('container-assessment-test-form');
        const deleteUrl = editorContainer ? editorContainer.getAttribute('data-delete-url') : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (deleteUrl && csrfToken) {
            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ imageUrl: targetUrl })
            }).catch(err => console.warn('Gagal menghapus file gambar:', err));
        }

        updateAttachedImagesGallery(questionId, editorObj?.instance);
    });
});

// Image Preview Modal
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

// Preview images when clicked in question content or editor
$(document).on('click', '.submit-answer-type img, .question-content img, .ck-content img', function () {
    const src = $(this).attr('src');
    if (!src) return;
    $('#modal-preview-img').attr('src', src);
    $('#modal-preview-open-tab').attr('href', src);
    const modal = document.getElementById('modal-image-preview');
    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
});