function paginateBankSoalDetail() {
    const container = document.getElementById('container-bank-soal-detail');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subBabId = container.dataset.subBabId;
    const source = container.dataset.source;
    const questionType = container.dataset.questionType;

    if (!container) return;
    if (!role) return;
    if (!subBabId) return;
    if (!source) return;
    if (!questionType) return;

    fetchBankSoalDetail(schoolName, schoolId, subBabId, questionType);

    function fetchBankSoalDetail() {
        $.ajax({
            url: schoolId
                ? `/lms/question-bank-management/source/${source}/review/question-type/${questionType}/${subBabId}/school-subscription/${schoolName}/${schoolId}/paginate`
                : `/lms/question-bank-management/source/${source}/review/question-type/${questionType}/${subBabId}/paginate`,

            method: 'GET',
            success: function (response) {
                const containerQuestion = $('#grid-list-soal');
                containerQuestion.empty();

                if (response.data.length > 0) {
                    response.data.forEach((question, index) => {
                    const options = question.lms_question_option || [];

                    // Ambil item pertama buat pertanyaan
                    // const first = options[0]; // Karena setiap options itu array dari soal yang sama

                    // Mengiterasi setiap opsi dari soal tersebut
                    function addClassToImgTags(html, className) {
                        return html
                        .replace(/<img\b(?![^>]*class=)[^>]*>/g, (imgTag) => {
                            // Tambahkan class jika belum ada atribut class
                            return imgTag.replace('<img', `<img class="${className}"`);
                        })
                        .replace(/<img\b([^>]*?)class="(.*?)"/g, (imgTag, before, existingClasses) => {
                            // Tambahkan class ke img yang sudah punya class
                            return `<img ${before}class="${existingClasses} ${className}"`;
                        });
                    } 
                    
                    const optionsMap = {
                        OPTION1: 'A',
                        OPTION2: 'B',
                        OPTION3: 'C',
                        OPTION4: 'D',
                        OPTION5: 'E'
                    };

                    const optionsHTML = options.map(item => {
                        const containsImage = /<img\s+[^>]*src=/.test(item.options_value);
                        let content = item.options_value;
                        let optionsValue = '';

                        // Tambahkan class img jika ada gambar
                        if (containsImage) {
                            content = addClassToImgTags(item.options_value, 'max-w-[300px] rounded my-2');
                        }

                        // cek apakah optionsValue mengandung image
                        if (containsImage) {
                            optionsValue = `
                                <div class="max-w-7xl border border-gray-300 rounded-md p-2 px-4 mb-4 text-sm my-6 flex gap-1
                                    ${item.is_correct == true ? 'border-green-400 bg-green-400 text-white font-bold' : ''}">
                                    <div class="font-bold min-w-7.5">${[optionsMap[item.options_key]]}.</div>
                                    <div class="w-full">${content}</div>
                                </div>
                            `;
                        } else {
                            optionsValue = `
                                <div class="max-w-7xl border border-gray-300 rounded-md p-2 px-4 mb-4 text-sm my-6 flex gap-1
                                    ${item.is_correct == true ? 'border-green-400 bg-green-400 text-white font-bold' : ''}">
                                    ${[optionsMap[item.options_key]]}. ${content}
                                </div>
                            `;
                        }

                        return `
                            ${optionsValue}
                        `;
                    }).join('');

                    // Ambil videoId yang sesuai dengan index pada masing" options soal
                    const videoId = response.videoIds[index];

                        const imageInExplanation = /<img\s+[^>]*src=/.test(question.explanation);

                    // Tambahkan class img jika ada gambar
                    if (imageInExplanation) {
                        imageInExplanation = addClassToImgTags(imageInExplanation, 'max-w-[350px] rounded my-2');
                    }

                    // Tampilkan video jika explanation itu adalah link video, jika tidak tampilkan explanation teks
                    const videoExplanation = videoId ? `
                        <div class="border max-w-sm h-60 flex justify-start">
                            <div class="w-full h-full">
                                <iframe class="w-full h-full" src="https://www.youtube.com/embed/${videoId}" frameborder="0"
                                    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>
                    ` : `<div class="max-w-7xl flex flex-col items-start gap-4">${imageInExplanation ? question.explanation : question.explanation}</div>`;
                    
                    // untuk memisahkan teks sebelum dengan img dan text setelah img
                    const splitQuestions = question.questions.split('<img') ?? ''; // split sebelum <img>
                    const questionTextOnly = splitQuestions[0]; // sebelum <img> ( [0] dan [1] digunakan untuk memisahkan 2 element berbeda )
                        
                    const previewLimit = 350;

                    const previewTextOnly = questionTextOnly.length > previewLimit ? questionTextOnly.substring(0, previewLimit) + '...' : questionTextOnly;

                    // Inisialisasi variabel kosong untuk menampung elemen gambar dan teks setelah gambar
                    let questionImage = '', textAfterImage = '';

                    // Cek apakah hasil split punya bagian setelah <img (artinya ada gambar)
                    if (splitQuestions.length > 1) {
                        const imgSplit = splitQuestions[1].split('>'); // pisahkan tag <img> dan sisa teks
                        const imgTag = imgSplit[0]; // bagian src dan atribut gambar
                        const restText = imgSplit.slice(1).join('>'); // gabungkan sisa setelah tag img

                        questionImage = `<img class="max-w-[25%]" ${imgTag}>`; // Susun tag <img> lengkap dengan class tambahan
                        textAfterImage = restText.trim(); // Hapus spasi berlebih pada teks setelah gambar
                    }

                    // Gabungkan menjadi HTML: bungkus gambar dan teks
                    const questionHTML = `
                        <div class="flex flex-col gap-10 items-start">
                            ${questionImage}
                            <div>${textAfterImage}</div>
                        </div>
                    `;
                        
                    const canEdit = !schoolId || question.school_partner_id;

                    let buttonEditQuestion = '';
                    let lmsEditQuestion = '';

                    if (canEdit) {

                        let urlTemplate;

                        if (schoolId && response.lmsEditQuestionBySchool) {
                            urlTemplate = response.lmsEditQuestionBySchool;
                        } else {
                            urlTemplate = response.lmsEditQuestion;
                        }

                        lmsEditQuestion = urlTemplate.replace(':role', role ?? '').replace(':schoolName', schoolName ?? '').replace(':schoolId', schoolId ?? '').replace(':source', source)
                            .replace(':questionType', questionType).replace(':subBabId', subBabId).replace(':questionId', question.id);

                        buttonEditQuestion = `
                            <div class="w-full flex justify-end gap-2 items-center">
                                <a href="${lmsEditQuestion}" class="w-max cursor-pointer text-sm text-[#4189e0] font-bold mx-2 mt-5">
                                    <span>Edit</span>
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        `;
                    }
                    
                    // tampilkan opsi jawaban benar pada tipe soal selain matching
                    let matchingContainer = '';
                        
                    // ambil opsi jawaban benar
                    const correctAnswers = options.filter(item => item.is_correct).map(item => [optionsMap[item.options_key]]).join(', ');
                        
                    if (question.tipe_soal !== 'MATCHING' && question.tipe_soal !== 'ESSAY') {
                        matchingContainer = `
                            <div>
                                <span class="font-bold opacity-70">Jawaban Benar:</span>
                                <span class="font-bold text-green-400">${correctAnswers}</span>
                            </div>
                        `;
                    }
                        
                        const leftItems = options.filter(item => item.options_key.startsWith('LEFT'));
                        const rightItems = options.filter(item => item.options_key.startsWith('RIGHT'));
                        
                        const rightLabelMap = {};
                        rightItems.forEach((item, index) => {
                            rightLabelMap[item.options_key] = String.fromCharCode(65 + index); // A, B, C
                        });

                        const pairsData = leftItems.filter(i => i.extra_data?.pair_with).map(i => ({
                            left: i.options_key,
                            right: i.extra_data.pair_with
                        }));

                        const matchingHTML = `
                            <!-- DEKSTOP -->
                            <div class="relative matching-container hidden lg:block" data-pairs='${JSON.stringify(pairsData)}'>

                                <!-- SVG GARIS -->
                                <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines"></svg>

                                <div class="grid grid-cols-2 gap-40 relative z-10">
                                    <div class="flex flex-col justify-center">
                                        <h4 class="font-bold mb-3">Kolom A</h4>
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

                                <!-- GARIS TENGAH (JALUR MERAH) -->
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

                        const card = `
                            ${buttonEditQuestion}
                        
                            <div class="wrapper-content-accordion-questions bg-white border border-gray-300 px-5 mt-5 rounded-[7px]">

                                    <div class="toggleButton-questions w-full flex items-center justify-between bg-transparent border-none outline-none cursor-pointer py-3.75">
                                        <div class="flex gap-1 max-w-362.5">
                                            <span>${index + 1}.</span>
                                            <span class="preview-text-only w-full" data-fulltext="${questionTextOnly}">${previewTextOnly}</span>
                                        </div>
                                        <i class="fa-solid fa-chevron-up icon"></i>
                                    </div>

                                    <div class="content-accordion relative text-justify h-0 overflow-hidden transition-all duration-500 ease-in-out">
                                        <div class="max-w-7xl text-sm mt-6">
                                            <div>${questionHTML}</div>
                                            <div>${question.tipe_soal === 'MATCHING' ? matchingHTML : optionsHTML}</div>
                                        <div class="flex flex-col gap-6 mb-8 mt-6">
                                            ${matchingContainer}
                                            <div>
                                                <p class="font-bold opacity-70 mb-4">Penjelasan:</p>
                                                ${videoExplanation}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        const $card = $(card); // ubah string jadi jQuery element
                        containerQuestion.append($card);

                        if (question.tipe_soal === 'MATCHING') {
                            requestAnimationFrame(() => {
                                const container = $card.find('.matching-container')[0]; // ambil element DOM

                                if (!container) return;

                                const pairs = leftItems
                                    .filter(i => i.extra_data?.pair_with)
                                    .map(i => ({
                                        left: i.options_key,
                                        right: i.extra_data.pair_with
                                    }));

                                drawMatchingLines(container, pairs);
                            });
                        }
                    });
                    initAccordionQuestion();
                    $('.pagination-container-bank-soal-detail').html(response.links);
                    $('#emptyMessageBankSoalDetail').hide();
                    $('.thead-table-bank-soal-detail').show();
                } else {
                    $('#emptyMessageBankSoalDetail').show();
                    $('.thead-table-bank-soal-detail').hide();
                }
            }
        });
    }
}

$(document).ready(function () {
    paginateBankSoalDetail();
});

window.addEventListener('resize', () => {
    document.querySelectorAll('.matching-container').forEach(container => {
        const pairs = JSON.parse(container.dataset.pairs || '[]');
        drawMatchingLines(container, pairs);
    });
});

function drawMatchingLines(container, pairs) {
    const svg = container.querySelector('.matching-lines');
    const centerLine = container.querySelector('.matching-center-line');
    if (!svg || !centerLine) return;

    svg.innerHTML = '';

    const cRect = container.getBoundingClientRect();
    const centerX = centerLine.getBoundingClientRect().left - cRect.left;

    pairs.forEach(pair => {
        const leftEl = container.querySelector(`[data-key="${pair.left}"]`);
        const rightEl = container.querySelector(`[data-key="${pair.right}"]`);
        if (!leftEl || !rightEl) return;

        const l = leftEl.getBoundingClientRect();
        const r = rightEl.getBoundingClientRect();

        const y1 = l.top + l.height / 2 - cRect.top;
        const y2 = r.top + r.height / 2 - cRect.top;

        const xLeft = l.right - cRect.left;
        const xRight = r.left - cRect.left;

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        path.setAttribute(
            'd',
            `
                M ${xLeft} ${y1}
                L ${xRight} ${y2}
            `
        );

        path.setAttribute('stroke', '#0071BC');
        path.setAttribute('stroke-width', '2.5');
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke-linecap', 'round');

        svg.appendChild(path);
    });
}


function initAccordionQuestion() {
    let toggles = document.getElementsByClassName('toggleButton-questions');
    let contentDiv = document.getElementsByClassName('content-accordion');
    let icons = document.getElementsByClassName('icon');
    let previewTexts = document.getElementsByClassName('preview-text-only');

    //ini buat buka accordion nya
    for (let i = 0; i < toggles.length; i++) {

        const fullText = previewTexts[i].dataset.fulltext;
        const shortText = fullText.length > 350 ? fullText.slice(0, 350) + "..." : fullText;

        // set default
        previewTexts[i].innerHTML = shortText;

        toggles[i].addEventListener('click', () => {

            const isOpen = parseInt(contentDiv[i].style.height) === contentDiv[i].scrollHeight;

            // ====== TUTUP SEMUA ACCORDION LAIN ======
            for (let j = 0; j < contentDiv.length; j++) {
                if (j !== i) { 
                    contentDiv[j].style.height = "0px";
                    toggles[j].style.color = "#111130";
                    icons[j].classList.remove('fa-chevron-down');
                    icons[j].classList.add('fa-chevron-up');

                    // kembalikan shortText accordion lain
                    const otherFullText = previewTexts[j].dataset.fulltext;
                    const otherShortText =
                        otherFullText.length > 350
                            ? otherFullText.slice(0, 350) + "..."
                            : otherFullText;

                    previewTexts[j].innerHTML = otherShortText;
                }
            }

            // ====== TOGGLE ACCORDION YANG DIKLIK ======
            if (!isOpen) {
                // buka
                previewTexts[i].innerHTML = fullText;
                contentDiv[i].style.height = contentDiv[i].scrollHeight + "px";
                setTimeout(() => {
                    const matchingContainer = contentDiv[i].querySelector('.matching-container');
                    if (matchingContainer) {
                        drawMatchingLines(
                            matchingContainer,
                            JSON.parse(matchingContainer.dataset.pairs)
                        );
                    }
                }, 350);
                toggles[i].style.color = "";
                icons[i].classList.remove('fa-chevron-up');
                icons[i].classList.add('fa-chevron-down');
            } else {
                // tutup
                previewTexts[i].innerHTML = shortText;
                contentDiv[i].style.height = "0px";
                toggles[i].style.color = "#111130";
                icons[i].classList.remove('fa-chevron-down');
                icons[i].classList.add('fa-chevron-up');
            }
        });
    }
}
