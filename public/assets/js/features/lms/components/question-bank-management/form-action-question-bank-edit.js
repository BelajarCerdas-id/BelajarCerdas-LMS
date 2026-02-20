const editorInstances = []; // 1. DEKLARASI GLOBAL untuk semua instance CKEditor
const previousImageUrlsMap = {};

function formQuestionBankEdit() {
    const container = document.getElementById('editor-container');
    if (!container) return;

    const source = container.dataset.source;
    const questionType = container.dataset.questionType;
    const subBabId = container.dataset.subBabId;
    const questionId = container.dataset.questionId;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!source) return;
    if (!subBabId) return;
    if (!questionId) return;
    if (!questionType) return;

    $.ajax({
        url: schoolId
            ? `/lms/school-subscription/question-bank-management/bank-soal/form/source/${source}/review/question-type/${questionType}/${subBabId}/${questionId}/${schoolName}/${schoolId}/edit`
            : `/lms/question-bank-management/bank-soal/form/source/${source}/review/question-type/${questionType}/${subBabId}/${questionId}/edit`,
        method: 'GET',
        success: function (response) {
            const question = response.editQuestion;
            const questionTypeNormalized = (questionType || '').toUpperCase();

            function renderOptionsByType(type) {
                switch (type) {
                    case 'MCQ':
                        return renderMCQ();
                    case 'MCMA':
                        return renderMCMA();
                    case 'MATCHING':
                        return renderMatching();
                    default:
                        return '';
                }
            }

            function renderMCQ() {
                const options = response.options;

                // options value
                const optionEditors = `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        ${options.map(opt => `
                            <div class="flex flex-col gap-2 p-2 border border-gray-300 rounded">
                                <label class="text-sm font-medium">
                                    ${opt.options_key}
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <textarea class="editor w-full" name="options[${opt.id}]">${opt.options_value}</textarea>
                            </div>
                        `).join('')}
                    </div>
                `;

                // Answer Key dropdown tetap dibawah grid opsi
                const answerSelect = `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 my-6">
                        <div>
                            <label class="mb-2 text-sm">
                                Answer Key
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <select name="answer_key"
                                class="w-full bg-white shadow-lg h-12 text-sm border border-gray-300 rounded px-2 cursor-pointer">
                                ${options.map(opt => `
                                    <option value="${opt.options_key}" ${opt.is_correct ? 'selected' : ''}>
                                        ${opt.options_key}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                    </div>
                `;

                return optionEditors + answerSelect;
            }

            function renderMCMA() {
                const options = response.options;

                // options value & answer key
                const optionEditors = `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        ${options.map(opt => `
                            <div class="flex flex-col gap-2 p-2 border border-gray-300 rounded">
                                <div class="flex justify-between items-center">
                                    <label class="text-sm font-medium">
                                        ${opt.options_key}
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <input
                                        type="checkbox"
                                        name="answer_key[]"
                                        value="${opt.options_key}"
                                        ${opt.is_correct ? 'checked' : ''}
                                        class="mcma-checkbox cursor-pointer"
                                    >
                                </div>

                                <textarea class="editor w-full" name="options[${opt.id}]">
                                    ${opt.options_value}
                                </textarea>

                                <span id="error-options-${opt.id}" class="text-red-500 font-bold text-xs"></span>
                            </div>

                        `).join('')}
                    </div>

                    <!-- GLOBAL ERROR MCMA -->
                    <div class="lg:col-span-2">
                        <span id="error-answer_key" class="text-red-500 font-bold text-xs"></span>
                    </div>
                `;

                return optionEditors;
            }

            function renderMatching() {
                const left = response.options.filter(o => o.extra_data?.side === 'left');
                const right = response.options.filter(o => o.extra_data?.side === 'right');

                return `
                    <div class="matching-editor grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- LEFT Column -->
                        <div>
                            <h4 class="font-bold mb-2">LEFT</h4>
                            ${left.map(l => `
                                <div class="mb-4 p-2 border border-gray-300 rounded">
                                    <label class="text-sm font-semibold">
                                        ${l.options_key}
                                        <i class="fa-solid fa-arrow-right"></i>
                                        ${response.matching[l.options_key] ?? '-'}
                                    </label>

                                    <textarea class="editor w-full" name="left[${l.id}]">${l.options_value}</textarea>
                                    <span id="error-left-${l.id}" class="text-red-500 font-bold text-xs"></span>
                                </div>
                            `).join('')}
                        </div>

                        <!-- RIGHT Column -->
                        <div>
                            <h4 class="font-bold mb-2">RIGHT</h4>
                            ${right.map(r => `
                                <div class="mb-4 p-2 border border-gray-300 rounded">
                                    <label class="text-sm font-semibold">${r.options_key}</label>
                                    <textarea class="editor w-full" name="right[${r.id}]">${r.options_value}</textarea>
                                    <span id="error-right-${r.id}" class="text-red-500 font-bold text-xs"></span>
                                </div>
                            `).join('')}
                        </div>

                    </div>
                `;
            }


            // options value select
            const optionsValue = renderOptionsByType(
                questionTypeNormalized,
            )
            
            const formHtml = `
                <form id="bank-soal-edit-question-form" data-source="${source}" data-sub-bab-id="${subBabId}" data-question-id="${questionId}" 
                    data-school-name="${schoolName}" data-school-id="${schoolId}" enctype="multipart/form-data">

                    <input type="hidden" name="question_type" value="${questionTypeNormalized}">

                    <!-- Question -->
                    <div class="leading-10 mb-6 w-full">
                        <span>Question<sup class="text-red-500 pl-1">*</sup></span>
                        <textarea name="questions" id="questions" class="editor">${question.questions}</textarea>
                        <span id="error-questions" class="text-red-500 font-bold text-xs"></span>
                    </div>

                    <div>
                        ${optionsValue}
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 my-6">
                        <div class="flex flex-col">
                            <label class="mb-2 text-sm">
                                Difficulty
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <select name="difficulty" id="difficulty" value="{{ old('difficulty') }}"
                                class="bg-white shadow-lg h-12 text-sm  border border-gray-300 outline-none rounded-md px-2 cursor-pointer">
                                    <option value="${question.difficulty}" class="hidden">
                                        ${question.difficulty}
                                    <option value="Mudah">Mudah</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Sukar">Sukar</option>
                            </select>
                            <span id="error-difficulty" class="text-red-500 font-bold text-xs pt-2"></span>
                        </div>

                        <div class="flex flex-col">
                            <label class="mb-2 text-sm">
                                Bloom
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                                <input type="text" id="bloom" name="bloom" class="bg-white shadow-lg h-12 text-sm  border border-gray-300 outline-none rounded-md px-2" value="${question.bloom}">
                            <span id="error-difficulty" class="text-red-500 font-bold text-xs pt-2"></span>
                        </div>
                    </div>

                    <div class="leading-10 w-full my-6">
                        <span>
                            Explanation
                            <sup class="text-red-500">&#42;</sup>
                        </span>
                        <textarea name="explanation" id="explanation" class="editor">${question.explanation}</textarea>
                        <span id="error-explanation" class="text-red-500 font-bold text-xs"></span>
                    </div>

                    <div class="flex justify-end mt-20 lg:mt-8">
                        <button id="submit-button" type="button" data-question-id="${questionId}"
                            class="bg-[#0071BC] text-white font-bold py-2 px-6 rounded-lg shadow-md cursor-pointer default:cursor-default">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            `;

            container.innerHTML = formHtml;

            // Inisialisasi CKEditor jika ada
            const editorContainer = document.getElementById('editor-container');
            const uploadUrl = editorContainer.getAttribute('data-upload-url');
            const deleteUrl = editorContainer.getAttribute('data-delete-url');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const editors = container.querySelectorAll('.editor');
            editors.forEach((textarea, index) => {
                ClassicEditor.create(textarea, {
                    ckfinder: {
                        uploadUrl: uploadUrl
                    },
                    toolbar: {
                        shouldNotGroupWhenFull: true
                    },  
                })
                    .then(editor => {
                        previousImageUrlsMap[index] = [];

                        editor.model.document.on('change:data', () => {
                            const currentContent = editor.getData();

                            const imageUrls = Array.from(currentContent.matchAll(/<img[^>]+src="([^">]+)"/g))
                                .map(match => match[1]);

                            const removedImages = previousImageUrlsMap[index].filter(url => !imageUrls.includes(url));

                            removedImages.forEach(url => {
                                fetch(deleteUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({ imageUrl: url })
                                })
                                    .then(response => response.json())
                                    .then(data => console.log('Gambar berhasil dihapus:', data))
                                    .catch(error => console.error('Error saat menghapus gambar:', error));
                            });

                            previousImageUrlsMap[index] = imageUrls;
                        });
                        editorInstances.push({ element: textarea, instance: editor }); // SIMPAN INSTANCE

                        // Hapus border merah & text error ketika konten CKEditor berubah
                        editor.model.document.on('change:data', () => {
                            const textarea = editor.sourceElement;

                            textarea.classList.remove('border-red-400', 'border-2');

                            const errorSpan = textarea
                                .closest('div')
                                ?.querySelector('[id^="error-"]');

                            if (errorSpan) {
                                errorSpan.textContent = '';
                            }
                        });
                    })
                    .catch(error => console.error('Error CKEditor:', error));
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    formQuestionBankEdit();
});

$(document).on('change', '.mcma-checkbox', function () {
    const checkedCount = $('.mcma-checkbox:checked').length;

    if (checkedCount > 0) {
        $('#error-answer_key').text('');
    }
});

let isProcessing = false;

// Form Action edit question
$(document).ready(function () {
    // form edit question
    $(document).on('click', '#submit-button', function (e) {
        e.preventDefault();

        // 2. Bersihkan konten CKEditor dari <p>&nbsp;</p>
        editorInstances.forEach(({ element, instance }) => {
            let content = instance.getData();
            content = content.replace(/<p>(&nbsp;|\s)*<\/p>/gi, ''); // Hapus paragraf kosong
            element.value = content; // Set ulang ke textarea
        });

        const form = $('#bank-soal-edit-question-form')[0]; // ambil DOM Form-nya
        const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol
        const questionId = $(this).data('question-id');

        if (isProcessing) return;
        isProcessing = true;

        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: `/lms/question-bank-management/${questionId}/edit`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#alert-success-bank-soal-edit-question').html(
                    `
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
                    `
                );

                setTimeout(function () {
                    document.getElementById('alertSuccess').remove();
                }, 3000);

                document.getElementById('btnClose').addEventListener('click', function () {
                    document.getElementById('alertSuccess').remove();
                });

                isProcessing = false;
                btn.prop('disabled', false);
                
                formQuestionBankEdit(questionId);
            },
            error: function (xhr, status, error) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    // Tampilkan pesan error (jika input nya ada array, seperti options_value ini)
                    $.each(errors, function (field, messages) {
                        let inputName = field;
                        let errorId = `error-${field}`;

                        // Tangani format field dengan titik seperti 'options_value.639'
                        if (field.includes('.')) {
                            const [name, index] = field.split('.');
                            inputName = `${name}[${index}]`; // options_value[639]
                            errorId = `error-${name}-${index}`; // error-options_value-639
                        }
                        
                        // Tambahkan border ke field yang error
                        $(`[name="${inputName}"]`).addClass('border-red-400 border-2');

                        // Tampilkan pesan error
                        $(`#${errorId}`).text(messages[0]);
                    });

                } else if (xhr.status === 419) {
                    alert('CSRF token mismatch. Coba refresh halaman.');
                } else {
                    alert('Terjadi kesalahan saat mengirim data.');
                }

                isProcessing = false;
                btn.prop('disabled', false);
            }
        });
    });
});
