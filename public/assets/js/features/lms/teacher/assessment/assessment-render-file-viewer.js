function assessmentFilePreview() {
    const containerEl = document.getElementById('container-edit-assessment');
    if (!containerEl) return;

    const role = containerEl.dataset.role;
    const schoolName = containerEl.dataset.schoolName;
    const schoolId = containerEl.dataset.schoolId;
    const assessmentId = containerEl.dataset.assessmentId;

    if (!role || !schoolName || !schoolId || !assessmentId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/${assessmentId}/edit/form`,
        method: 'GET',
        success: function (response) {

            if (!response || !response.data) return;

            renderDynamicForm();
            hydrateEditData(response.data);
        }
    });

    function renderDynamicForm() {
        const container = $('#dynamic-form');
        container.empty();
        renderFileInput(container);
    }

    // function render file input
    function renderFileInput(container) {

        container.append(`
        <div class="mb-6" data-file-wrapper>
            <label class="block text-sm font-medium mb-2">
                Unggah File 
                <sup class="text-red-500">*</sup>
            </label>

            <button type="button"
                onclick="triggerFile()" 
                class="w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-600 font-semibold text-sm py-3 rounded-full 
                hover:bg-blue-100 transition cursor-pointer file-empty">

                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                Unggah File
            </button>

            <div class="text-xs text-gray-400 my-2">
                Format: PDF / MP4.
                Ukuran maksimum 100MB
            </div>

            <input type="file" id="file-input" name="assessment_value_file" accept="application/pdf, video/mp4" class="hidden"/>
            <span id="error-assessment_value_file" class="text-red-500 text-xs mt-1 font-bold"></span>

            <input type="hidden" name="existing_files" id="existing-file" value="0"/>

            <span id="error-file" 
                class="text-red-500 font-bold text-xs pt-2 error-file" data-error-file>
            </span>

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
                            onclick="triggerFile()"
                            class="text-blue-600 text-sm font-medium hover:underline cursor-pointer">
                            Unggah Ulang
                        </button>

                        <button type="button"
                            onclick="removeFile()"
                            class="text-gray-400 hover:text-red-500 text-lg leading-none cursor-pointer">
                            &times;
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `);

        bindFileChange();
    }


    // REMOVE ERROR WHEN SELECT FILE
    $(document).on('change', 'input[type="file"]', function () {

        $('[data-error-file]').text('');

        $(this).closest('[data-file-wrapper]').find('.file-empty').removeClass('border border-red-400');
    });

    // TRIGGER FILE
    window.triggerFile = function () {

        const input = document.getElementById('file-input');
        const wrapper = $('[data-file-wrapper]');

        if (!input) return;

        input.value = '';
        input.click();

        setTimeout(() => {
            if (!input.files.length) {

                wrapper.find('.file-preview').addClass('hidden');
                wrapper.find('.file-empty').removeClass('hidden');
                wrapper.find('#existing-file').val(0);
            }
        }, 300);
    }

    // BIND FILE CHANGE
    function bindFileChange() {

        const input = document.getElementById('file-input');
        if (!input) return;

        input.addEventListener('change', function () {

            if (!this.files.length) return;

            const file = this.files[0];
            const wrapper = $('[data-file-wrapper]');

            wrapper.find('.file-empty').addClass('hidden');
            wrapper.find('.file-preview').removeClass('hidden');

            const previewContainer = wrapper.find('.file-preview');
            previewContainer.find('.preview-content').remove();

            if (file.type.startsWith('video/')) {

                const videoURL = URL.createObjectURL(file);

                previewContainer.prepend(`
                    <div class="preview-content w-full mb-3">
                        <video 
                            src="${videoURL}" 
                            controls
                            class="w-full max-h-full rounded-md bg-black">
                        </video>
                    </div>
                `);

                wrapper.find('.file-name').text(file.name);

            } else {

                wrapper.find('.file-name').text(file.name);
            }
        });
    }

    // REMOVE FILE
    window.removeFile = function () {

        const input = document.getElementById('file-input');
        const wrapper = $('[data-file-wrapper]');

        const video = wrapper.find('video').get(0);

        if (video) {
            video.pause();
            video.currentTime = 0;

            if (video.src.startsWith('blob:')) {
                URL.revokeObjectURL(video.src);
            }
        }

        input.value = '';
        wrapper.find('.preview-content').remove();

        wrapper.find('.file-preview').addClass('hidden');
        wrapper.find('.file-empty').removeClass('hidden');
        wrapper.find('#existing-file').val(0);
    }

    // HYDRATE EDIT DATA
    function hydrateEditData(items) {

        items.forEach(item => {

            if (item.type !== 'file') return;

            const wrapper = $('[data-file-wrapper]');
            if (!wrapper.length) return;

            wrapper.find('.file-empty').addClass('hidden');
            wrapper.find('.file-preview').removeClass('hidden');
            wrapper.find('#existing-file').val(1);

            wrapper.find('.file-name').text(item.file_name);

            const previewContainer = wrapper.find('.file-preview');
            previewContainer.find('.preview-content').remove();

            if (item.mime && item.mime.startsWith('video/')) {

                previewContainer.prepend(`
                    <div class="preview-content w-full mb-3">
                        <video
                            src="${item.file_url}"
                            controls
                            class="w-full max-h-96 rounded-md bg-black">
                        </video>
                    </div>
                `);

            } else if (item.mime === 'application/pdf') {

                previewContainer.prepend(`
                    <div class="preview-content w-full mb-3 h-80 border rounded-md overflow-hidden">
                        <iframe
                            src="${item.file_url}"
                            class="w-full h-full">
                        </iframe>
                    </div>
                `);

            } else if (item.mime && item.mime.startsWith('image/')) {

                previewContainer.prepend(`
                    <div class="preview-content w-full mb-3">
                        <img src="${item.file_url}"
                            class="max-h-96 rounded-md border mx-auto"/>
                    </div>
                `);
            }
        });
    }
}

$(document).ready(function () {
    assessmentFilePreview();
});