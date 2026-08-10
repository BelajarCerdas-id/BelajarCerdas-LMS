let isProcessing = false;
function loadSchoolFoundationDetail() {

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/edit-form/${schoolFoundationId}/load-data`,
        method: 'GET',

        beforeSend: function () {
            $('#school-foundation-form-loading').removeClass('hidden');
            $('#school-foundation-form-content').addClass('hidden');

            $('#school-foundation-summary-loading').removeClass('hidden');
            $('#school-foundation-summary-content').addClass('hidden');
        },

        success: function (response) {

            const foundation = response.data;

            $('#nama_yayasan').val(foundation.nama_yayasan);

            $('#summary-foundation-name').text(
                foundation.nama_yayasan
            );

            if (foundation.logo) {
                $('#logoPreview')
                    .attr('src', '/' + foundation.logo)
                    .removeClass('hidden');

                $('#logoPlaceholder').addClass('hidden');
            }

            $('#school-foundation-form-loading').addClass('hidden');
            $('#school-foundation-form-content').removeClass('hidden');

            $('#school-foundation-summary-loading').addClass('hidden');
            $('#school-foundation-summary-content').removeClass('hidden');
        },

        error: function () {
            $('#school-foundation-form-loading').addClass('hidden');
            $('#school-foundation-form-content').removeClass('hidden');

            $('#school-foundation-summary-loading').addClass('hidden');
            $('#school-foundation-summary-content').removeClass('hidden');
        }
    });
}

$(document).ready(function () {
    loadSchoolFoundationDetail();
});

$('#nama_yayasan').on('input', function () {
    const value = $(this).val().trim();

    $('#summary-foundation-name').text(value === '' ? 'Belum diisi' : value);
});

$('#submit-button-create-school-foundation').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container) return;
    if (!role) return;
    if (!schoolFoundationId) return;

    const form = $('#edit-school-foundation-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/edit-form/${schoolFoundationId}/submit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-edit-school-foundation').html(`
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

            loadSchoolFoundationDetail();
        },
        error: function (xhr) {

            isProcessing = false;
            btn.prop('disabled', false);

            if (xhr.status === 422) {

                const response = xhr.responseJSON;

                $.each(response.errors, function (field, messages) {
                    const errorText = $(`#error-${field}`);

                    errorText.removeClass('hidden').text(messages[0]);
                    $(`[name="${field}"]`).addClass('border-red-400 border');
                });
                return;
            }

            alert('Terjadi kesalahan saat mengirim data.');
        }
    });
});