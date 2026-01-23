function paginateBankSoalDetail() {
    const container = document.getElementById('container-bank-soal-detail');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subBabId = container.dataset.subBabId;
    const source = container.dataset.source;

    if (!container) return;
    if (!subBabId) return;
    if (!source) return;

    fetchBankSoalDetail(schoolName, schoolId, subBabId);

    function fetchBankSoalDetail() {
        $.ajax({
            url: schoolId
                ? `/lms/question-bank-management/source/${source}/review/${subBabId}/school-subscription/${schoolName}/${schoolId}/paginate`
                : `/lms/question-bank-management/source/${source}/review/${subBabId}/paginate`,

            method: 'GET',
            success: function (response) {
                const containerQuestion = $('#grid-list-soal');
                containerQuestion.empty();

                if (response.data.length > 0) {
                    response.data.forEach((group, index) => {

                    // Ambil item pertama buat pertanyaan
                    const first = group[0]; // Karena setiap group itu array dari soal yang sama

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

                    const optionsHTML = group.map((item) => {
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
                                    ${item.options_key === item.answer_key ? 'border-green-400 bg-green-400 text-white font-bold' : ''}">
                                    <div class="font-bold min-w-7.5">${item.options_key}.</div>
                                    <div class="w-full">${content}</div>
                                </div>
                            `;
                        } else {
                            optionsValue = `
                                <div class="max-w-7xl border border-gray-300 rounded-md p-2 px-4 mb-4 text-sm my-6 flex gap-1
                                    ${item.options_key === item.answer_key ? 'border-green-400 bg-green-400 text-white font-bold' : ''}">
                                    ${item.options_key}. ${content}
                                </div>
                            `;
                        }

                        return `
                            ${optionsValue}
                        `;
                    }).join('');

                    // Ambil videoId yang sesuai dengan index pada masing" group soal
                    const videoId = response.videoIds[index];

                    const imageInExplanation = /<img\s+[^>]*src=/.test(first.explanation);

                    // Tambahkan class img jika ada gambar
                    if (imageInExplanation) {
                        first.explanation = addClassToImgTags(first.explanation, 'max-w-[350px] rounded my-2');
                    }

                    // Tampilkan video jika explanation itu adalah link video, jika tidak tampilkan explanation teks
                    const videoExplanation = videoId ? `
                        <div class="border max-w-sm h-60 flex justify-start">
                            <div class="w-full h-full">
                                <iframe class="w-full h-full" src="https://www.youtube.com/embed/${videoId}" frameborder="0"
                                    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>
                    ` : `<div class="max-w-7xl flex flex-col items-start gap-4">${imageInExplanation ? first.explanation : first.explanation}</div>`;
                    
                    // untuk memisahkan teks sebelum dengan img dan text setelah img
                    const splitQuestions = first.questions.split('<img'); // split sebelum <img>
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

                    const card = `
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
                                        <div>${optionsHTML}</div>
                                    <div class="flex flex-col gap-6 mb-8 mt-6">
                                        <div>
                                            <span class="font-bold opacity-70">Jawaban Benar:</span>
                                            <span class="font-bold text-green-400">${first.answer_key}</span>
                                        </div>
                                        <div>
                                            <p class="font-bold opacity-70 mb-4">Penjelasan:</p>
                                            ${videoExplanation}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                        containerQuestion.append(card);
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
