$(document).ready(function () {
    var oldService = $('#id_service').attr('data-old-service'); // Ambil service yang dipilih jika ada

    function resetSelect($select, placeholder) {
        $select.prop('disabled', true).removeClass('cursor-pointer opacity-100').addClass('cursor-default opacity-50').empty().append(`<option value="" class="hidden">${placeholder}</option>`);
    }

    function enableSelect($select) {
        $select.prop('disabled', false).removeClass('cursor-default opacity-50').addClass('cursor-pointer opacity-100');
    }

    $('#id_kurikulum').on('change', function () {

        resetSelect($('#id_service'), 'Pilih Service');

        let curriculumId = $(this).val();
        if (!curriculumId) return;

        // LOAD SERVICE
        $.get(`/kurikulum/${curriculumId}/service`, function (data) {
            enableSelect($('#id_service'));

            data.forEach(service => {
                $('#id_service').append(
                    `<option value="${service.id}">${service.name}</option>`
                );
            });

            if (oldService) {
                $('#id_service').val(oldService).trigger('change');
                oldService = null;
            }
        });
    });
});

let EDIT_ITEMS = [];

$('#id_service').on('change', function () {
    const serviceId = $(this).val();
    const contentId = $('#container').data('content-id');

    EDIT_ITEMS = [];
    $('#dynamic-form').empty();

    // Ambil rules dulu
    $.get(`/service/${serviceId}/rules`, function (rules) {

        // Render form
        renderDynamicForm(rules);

        // Baru hydrate edit
        if (contentId) {
            $.get(`/lms/content-management/${contentId}/form/edit`, function (res) {
                EDIT_ITEMS = res.data ?? [];
                hydrateEditData(EDIT_ITEMS);
            });
        }
    });
});


function renderDynamicForm(rules) {
    const container = $('#dynamic-form');
    container.empty();

    rules.forEach((rule, index) => {
        if (rule.upload_type === 'file') {
            renderFileInput(container, rule, index);
        }

        if (rule.upload_type === 'text') {
            renderTextInput(container, rule, index);
        }

        if (rule.upload_type === 'textarea') {
            renderTextarea(container, rule, index);
        }
    });
}

function renderFileInput(container, rule) {
    const accept = rule.allowed_extension
        ? rule.allowed_extension.map(ext => '.' + ext).join(',')
        : '';

    const labelExt = rule.allowed_extension
        ? rule.allowed_extension.map(ext => ext.toUpperCase()).join('/')
        : 'FILE';
    
    const ruleId = rule.id;

    container.append(`
        <div class="mb-6" data-file-wrapper="${ruleId}">
            <label class="block text-sm font-medium mb-2">
                Unggah File 
                <sup class="text-red-500">*</sup>
            </label>

            <!-- BUTTON UPLOAD -->
            <button type="button"
                onclick="triggerFile(${ruleId})" class="w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-600 font-semibold text-sm py-3 rounded-full 
                hover:bg-blue-100 transition cursor-pointer file-empty">

                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                Unggah ${labelExt}
            </button>

            <!-- helper text -->
            <div class="text-xs text-gray-400 my-2">
                Format: ${rule.allowed_extension?.join(', ').toUpperCase()}.
                Ukuran maksimum ${rule.max_size_mb}MB
            </div>

            <!-- hidden input -->
            <input type="file" id="file-input-${ruleId}" name="files[${ruleId}]" accept="${accept}" value="${ruleId}" class="hidden"/>
            <!-- EXISTING FILE FLAG -->
            <input type="hidden"
                name="existing_files[${ruleId}]"
                id="existing-file-${ruleId}"
                value="0"/>
            <span id="error-file-${ruleId}" class="text-red-500 font-bold text-xs pt-2 error-file" data-error-file="${ruleId}"></span>

            <!-- PREVIEW -->
            <div class="file-preview hidden">
                <div class="border rounded-lg px-4 py-3 mt-3 flex items-center justify-between bg-gray-50">
                    <div class="flex items-center gap-3 min-w-0">
                        <i class="fa-solid fa-file-lines text-blue-500 text-xl"></i>
                        <div class="text-sm truncate">
                            <div class="file-name font-medium truncate"></div>
                        </div>
                    </div>

                    <div class="flex gap-4 items-center shrink-0">
                        <button type="button"
                            onclick="triggerFile(${ruleId})"
                            class="text-blue-600 text-sm font-medium hover:underline cursor-pointer">
                            Unggah Ulang
                        </button>

                        <button type="button"
                            onclick="removeFile(${ruleId})"
                            class="text-gray-400 hover:text-red-500 text-lg leading-none cursor-pointer">
                            &times;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `);

    bindFileChange(ruleId);
}

// function untuk menghilang error text ketika user memilih file
$(document).on('change', 'input[type="file"]', function () {
    const name = $(this).attr('name'); // files[0]
    const match = name.match(/\[(\d+)\]/);

    if (!match) return;

    const index = match[1];

    // Hapus error text
    $(`[data-error-file="${index}"]`).text('');

    // Optional: hapus border error di button upload
    $(this)
        .closest('[data-file-wrapper]')
        .find('.file-empty')
        .removeClass('border border-red-400');
});

function triggerFile(ruleId) {
    const input = document.getElementById(`file-input-${ruleId}`);
    const wrapper = $(`[data-file-wrapper="${ruleId}"]`);

    // reset dulu
    input.value = '';

    // buka dialog
    input.click();

    // cek setelah explorer ditutup
    setTimeout(() => {
        if (!input.files.length) {
            // user batal → reset UI
            wrapper.find('.file-preview').addClass('hidden');
            wrapper.find('.file-empty').removeClass('hidden');

            // tandai file DIHAPUS
            wrapper.find(`#existing-file-${ruleId}`).val(0);
        }
    }, 300);
}

function bindFileChange(ruleId) {
    const input = document.getElementById(`file-input-${ruleId}`);

    input.addEventListener('change', function () {
        if (!this.files.length) return;

        const file = this.files[0];
        const wrapper = $(`[data-file-wrapper="${ruleId}"]`);

        wrapper.find('.file-empty').addClass('hidden');
        wrapper.find('.file-preview').removeClass('hidden');

        const previewContainer = wrapper.find('.file-preview');

        // bersihkan preview lama
        previewContainer.find('.preview-content').remove();

        // === VIDEO PREVIEW ===
        if (file.type.startsWith('video/')) {
            const videoURL = URL.createObjectURL(file);

            previewContainer.prepend(`
                <div class="preview-content w-full mb-3">
                    <video 
                        src="${videoURL}" 
                        controls
                        class="w-full max-h-full rounded-md bg-black"
                    ></video>
                </div>
            `);

            wrapper.find('.file-name').text(file.name);
        }
        // === FILE PREVIEW (NON VIDEO) ===
        else {
            wrapper.find('.file-name').text(file.name);
        }
    });
}

// function remove file
function removeFile(ruleId) {
    const input = document.getElementById(`file-input-${ruleId}`);
    const wrapper = $(`[data-file-wrapper="${ruleId}"]`);

    // STOP VIDEO JIKA ADA
    const video = wrapper.find('video').get(0);
    if (video) {
        video.pause();
        video.currentTime = 0;

        // revoke blob URL
        if (video.src.startsWith('blob:')) {
            URL.revokeObjectURL(video.src);
        }
    }

    // reset input
    input.value = '';

    // hapus preview sepenuhnya
    wrapper.find('.preview-content').remove();

    // toggle UI
    wrapper.find('.file-preview').addClass('hidden');
    wrapper.find('.file-empty').removeClass('hidden');

    // tandai file DIHAPUS
    wrapper.find(`#existing-file-${ruleId}`).val(0);
}

// function render input
function renderTextInput(container, rule) {
    container.append(`
        <div class="mb-6" data-repeatable="${rule.id}">
            <label class="text-sm font-medium mb-3 block">
                Input Text
                <sup class="text-red-500">*</sup>
            </label>

            <div class="repeatable-wrapper space-y-3">
                ${renderTextRow(rule.id)}
            </div>

            ${rule.is_repeatable ? `
                <button type="button"
                    class="add-repeat mt-2 text-blue-600 text-sm font-semibold cursor-pointer"
                    data-rule-id="${rule.id}">
                    + Tambah
                </button>
            ` : ''}
        </div>
    `);

    updateRemoveVisibility(rule.id);
}

// function render row
function renderTextRow(ruleId) {
    return `
        <div class="repeatable-item space-y-1">
            <!-- INPUT WRAPPER -->
            <div class="flex items-center gap-3 rounded-2xl px-4 py-3 bg-white border border-gray-200 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                <!-- TEXT INPUT -->
                <input type="text" name="text[${ruleId}][]" placeholder="Tulis di sini..." class="w-full text-sm bg-transparent outline-none placeholder-gray-400 text-gray-800"/>

                <!-- REMOVE BUTTON -->
                <button type="button" title="Hapus" class="remove-repeat flex items-center justify-center w-7 h-7 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- ERROR TEXT -->
            <span class="error-text text-xs text-red-500 font-medium pl-2 hidden"></span>
        </div>
    `;
}

// function untuk menghilang error text ketika user input text
$(document).on('input', 'input[type="text"]', function () {
    $(this)
        .closest('.repeatable-item')
        .find('.error-text')
        .text('');
});

// function add repeat
$(document).on('click', '.add-repeat', function () {
    const ruleId = $(this).data('rule-id');
    const wrapper = $(`[data-repeatable="${ruleId}"] .repeatable-wrapper`);

    wrapper.append(renderTextRow(ruleId));
    updateRemoveVisibility(ruleId);
});

// function remove repeat
$(document).on('click', '.remove-repeat', function () {
    const parent = $(this).closest('[data-repeatable]');
    const index = parent.data('repeatable');
    const wrapper = parent.find('.repeatable-wrapper');

    if (wrapper.children('.repeatable-item').length <= 1) return;

    $(this).closest('.repeatable-item').remove();
    updateRemoveVisibility(index);
});

// function update remove visibility
function updateRemoveVisibility(ruleId) {
    const wrapper = $(`[data-repeatable="${ruleId}"] .repeatable-wrapper`);
    const items = wrapper.children('.repeatable-item');

    if (items.length <= 1) {
        items.find('.remove-repeat').addClass('hidden');
    } else {
        items.find('.remove-repeat').removeClass('hidden');
    }
}

function hydrateEditData(items) {
    items.forEach(item => {
        if (item.type === 'file') {
            const ruleId = item.rule_id;
            const wrapper = $(`[data-file-wrapper="${ruleId}"]`);
    
            if (!wrapper.length) return;
    
            wrapper.find('.file-empty').addClass('hidden');
            wrapper.find('.file-preview').removeClass('hidden');
            wrapper.find(`#existing-file-${ruleId}`).val(1);

            // filename
            wrapper.find('.file-name').text(item.file_name);
    
            const previewContainer = wrapper.find('.file-preview');
            previewContainer.find('.preview-content').remove();
    
            // === VIDEO ===
            if (item.mime.startsWith('video/')) {
                previewContainer.prepend(`
                    <div class="preview-content w-full mb-3">
                        <video
                            src="${item.file_url}"
                            controls
                            class="w-full max-h-96 rounded-md bg-black">
                        </video>
                    </div>
                `);
            }
    
            // === PDF ===
            else if (item.mime === 'application/pdf') {
                previewContainer.prepend(`
                    <div class="preview-content w-full mb-3 h-80 border rounded-md overflow-hidden">
                        <iframe
                            src="${item.file_url}"
                            class="w-full h-full">
                        </iframe>
                    </div>
                `);
            }
    
            // === IMAGE ===
            else if (item.mime.startsWith('image/')) {
                previewContainer.prepend(`
                    <div class="preview-content w-full mb-3">
                        <img src="${item.file_url}"
                            class="max-h-96 rounded-md border mx-auto"/>
                    </div>
                `);
            }
            
        } else {
            const ruleId = item.rule_id;
            const wrapper = $(`[data-repeatable="${ruleId}"] .repeatable-wrapper`);

            if (!wrapper.length) return;

            // simpan value text ke data attr
            if (!wrapper.data('values')) {
                wrapper.data('values', []);
            }

            wrapper.data('values').push(item.value_text);
        }
    });

    // === HYDRATE TEXT INPUTS ===
    $('[data-repeatable]').each(function () {
        const ruleId = $(this).data('repeatable');
        const wrapper = $(this).find('.repeatable-wrapper');
        const values = wrapper.data('values');

        if (!values || !values.length) return;

        // hapus input default
        wrapper.empty();

        values.forEach(val => {
            const row = $(renderTextRow(ruleId));
            row.find('input').val(val);
            wrapper.append(row);
        });

        updateRemoveVisibility(ruleId);
    });
}