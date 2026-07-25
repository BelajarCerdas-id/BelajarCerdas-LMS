let currentUploadRequest = null;

// Form Action create content
$('#submit-button-bulkUpload-syllabus').on('click', function (e) {
    e.preventDefault();

    const form = $('#bulkUpload-syllabus-form')[0];
    const formData = new FormData(form);

    if (isProcessing) return;

    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const defaultButtonHtml = btn.html();

    btn.prop('disabled', true).html(`
        <i class="fa-solid fa-spinner fa-spin"></i>
        Sedang Memvalidasi...
    `);

    $.ajax({
        url: `/syllabus/bulkupload/syllabus/validate`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function () {
            startUpload(formData, btn, defaultButtonHtml);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;
                const formErrors = response.errors.form_errors ?? {};
                const wordErrors = response.errors.excel_validation_errors ?? [];

                showValidationError(formErrors);

                if (wordErrors.length > 0) {
                    showWordValidation(wordErrors);
                }
            } else {
                alert('Terjadi kesalahan saat validasi.');
            }

            isProcessing = false;
            btn.prop('disabled', false).html(defaultButtonHtml);
        }
    });
});

function startUpload(formData, btn, defaultButtonHtml) {
    const modal = document.getElementById('my_modal_2');
    modal.close();

    const startTime = Date.now();

    setUploadFileInfo(formData);

    document.getElementById('upload-progress-modal').showModal();

    currentUploadRequest = $.ajax({
        url: `/syllabus/bulkupload/syllabus`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        xhr: function () {
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function (e) {
                if (!e.lengthComputable) return;

                const percent = Math.round((e.loaded / e.total) * 100);
                const elapsed = (Date.now() - startTime) / 1000;
                const speed = e.loaded / elapsed;
                const remain = speed > 0 ? (e.total - e.loaded) / speed : 0;

                $('#upload-progress-bar').css('width', percent + '%');
                $('#upload-percent').text(percent + '%');
                $('#upload-size').text(`${formatFileSize(e.loaded)} / ${formatFileSize(e.total)}`);
                $('#upload-speed').text(`${formatFileSize(speed)}/s`);
                $('#upload-remaining').text(formatRemainingTime(remain));
            });

            return xhr;
        },
        success: function (response) {
            $('#upload-progress-bar').css('width', '100%');
            $('#upload-percent').text('100%');
            $('#upload-status').text('Content berhasil diupload.');

            document.getElementById('upload-progress-modal').close();

            $('#alert-success-import-syllabus').html(`
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

            // Reset form
            $('#bulkUpload-syllabus-form')[0].reset();
            $('#excelPreviewContainer-bulkUpload-excel').addClass('hidden');
            $('#textPreview-bulkUpload-excel').text('');
            $('#textSize-bulkUpload-excel').text('');
            $('#textPages-bulkUpload-excel').text('');
            $('#textCircle-bulkUpload-excel').html('');
            $('#logo-bulkUpload-excel img').attr('src', '').hide();

            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function () {
                $('#alertSuccess').remove();
            });

            // inisialisasi paginate curriculum setelah import BulkUpload syllabus
            fetchFilteredDataSyllabusCurriculum();

            isProcessing = false;
            currentUploadRequest = null;
            btn.prop('disabled', false).html(defaultButtonHtml);
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors;

                // tampilkan error form
                if (errors.form_errors) {
                    showValidationError(errors.form_errors);
                }

                // tampilkan error validasi word
                if (errors.excel_validation_errors &&
                    errors.excel_validation_errors.length > 0) {

                    showWordValidation(errors.excel_validation_errors);
                }

            } else {
                alert('Terjadi kesalahan saat validasi.');
            }

            isProcessing = false;
            btn.prop('disabled', false).html(defaultButtonHtml);
        }
    });
}

function showValidationError(errors) {

    errors = errors.form_errors ?? errors;

    $('#bulkUpload-syllabus-form .text-red-500').text('');
    $('#bulkUpload-syllabus-form').find('.border-red-400').removeClass('border-red-400 border');

    $.each(errors, function (field, messages) {
        $(`#error-${field}`).text(messages[0]);
        $(`[name="${field}"]`).addClass('border border-red-400');
    });
}

function showWordValidation(errors) {

    let errorList = '';

    errors.forEach(err => {
        errorList += `<li class="text-sm">${err}</li>`;
    });

    const html = `
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-2 rounded">
            <span class="font-bold text-sm">Terjadi Kesalahan :</span>
            <ul class="text-red-500 text-sm list-disc pl-5 mt-2">
                ${errorList}
            </ul>
        </div>
    `;

    $('#error-bulkUpload').html(html);
}

window.addEventListener("beforeunload", function (e) {

    if (!isProcessing) return;

    e.preventDefault();

    e.returnValue = "";

});

function formatFileSize(bytes) {

    if (bytes === 0) return "0 B";

    const k = 1024;

    const sizes = ["B", "KB", "MB", "GB"];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return (bytes / Math.pow(k, i)).toFixed(2) + " " + sizes[i];

}

function setUploadFileInfo(formData) {

    let file = null;

    for (const pair of formData.entries()) {

        if (pair[1] instanceof File && pair[1].size > 0) {
            file = pair[1];
            break;
        }

    }

    if (!file) return;

    $('#upload-file-name').text(file.name);

    $('#upload-file-type').text(getFileType(file));

    $('#upload-size').text(
        `0 MB / ${formatFileSize(file.size)}`
    );

    $('#upload-speed').text('-');

    $('#upload-percent').text('0%');

    $('#upload-progress-bar').css('width', '0%');

    $('#upload-remaining').text('-');

    const icon = $('#upload-file-icon');

    icon.removeClass();

    if (file.type === 'application/pdf') {

        icon.addClass('fa-solid fa-file-pdf text-red-500 text-3xl');

    } else if (file.type.startsWith('video/')) {

        icon.addClass('fa-solid fa-file-video text-blue-500 text-3xl');

    } else if (file.type.startsWith('image/')) {

        icon.addClass('fa-solid fa-file-image text-green-500 text-3xl');

    } else {

        icon.addClass('fa-solid fa-file text-slate-500 text-3xl');

    }

}

function getFileType(file) {

    if (file.type === 'application/pdf')
        return 'PDF Document';

    if (file.type.startsWith('video/'))
        return 'Video File';

    if (file.type.startsWith('image/'))
        return 'Image File';

    if (file.type.includes('word'))
        return 'Microsoft Word';

    if (file.type.includes('excel'))
        return 'Microsoft Excel';

    if (file.type.includes('powerpoint'))
        return 'Microsoft PowerPoint';

    return 'Document';

}

function formatRemainingTime(seconds) {

    seconds = Math.ceil(seconds);

    if (seconds <= 0)
        return "Selesai";

    const hours = Math.floor(seconds / 3600);

    const minutes = Math.floor((seconds % 3600) / 60);

    const secs = seconds % 60;

    let result = [];

    if (hours > 0)
        result.push(`${hours} jam`);

    if (minutes > 0)
        result.push(`${minutes} menit`);

    if (secs > 0 || result.length === 0)
        result.push(`${secs} detik`);

    return result.join(" ");
}