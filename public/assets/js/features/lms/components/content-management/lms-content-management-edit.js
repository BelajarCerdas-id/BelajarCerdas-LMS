let isProcessing = false;

// Form Action edit content
$('#submit-button-edit-content').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const contentId = container.dataset.contentId;
    const curriculumId = container.dataset.curriculumId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;
    const babId = container.dataset.babId;
    const subBabId = container.dataset.subBabId;

    if (!container) return;
    if (!contentId) return;
    if (!curriculumId) return;
    if (!kelasId) return;
    if (!mapelId) return;
    if (!babId) return;
    if (!subBabId) return;


    const form = $('#content-management-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/content-management/${contentId}/edit-action`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-edit-content').html(`
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

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                console.log(xhr.responseJSON.errors);

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#content-management-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#content-management-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });

                Object.keys(errors).forEach(key => {
                    if (key.startsWith('files.')) {
                        const index = key.split('.')[1];

                        $(`[data-error-file="${index}"]`)
                            .text(errors[key][0]);
                    }
                });

                Object.keys(errors).forEach(key => {
                    if (key.startsWith('text.')) {
                        const parts = key.split('.'); // text.0.1
                        const ruleIndex = parts[1];
                        const rowIndex = parts[2];

                        const container = $(`[data-repeatable="${ruleIndex}"]`);

                        const row = container.find('.repeatable-item').eq(rowIndex);

                        row.find('.error-text').removeClass('hidden').text(errors[key][0]);
                    }
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});