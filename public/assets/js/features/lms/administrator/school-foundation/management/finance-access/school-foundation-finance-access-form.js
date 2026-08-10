let isProcessing = false;

// form action school foundation create finance access link
$('#btn-create-finance-access-link').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container) return;
    if (!role) return;
    if (!schoolFoundationId) return;

    const form = $('#create-finance-access-link-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/finance-access-control/${schoolFoundationId}/create-link`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_1');
            if (modal) modal.close();

            $('#alert-success-create-finance-access-link').html(`
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

            $('#create-finance-access-link-form')[0].reset();

            paginateSchoolFoundationFinanceAccessLink();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {

            isProcessing = false;
            btn.prop('disabled', false);

            if (xhr.status === 422) {

                const response = xhr.responseJSON;
                $.each(response.errors, function (field, messages) {

                    const errorText = $(`#error-${field}`);

                    errorText.text(messages[0]);

                    $(`[name="${field}"]`).addClass('!border-red-400');
                });

                return;
            }

            alert('Terjadi kesalahan saat mengirim data.');
        }
    });
});

$('#finance-access-link').on('input', function () {
    $(this).removeClass('!border-red-400');
    $('#error-link').text('');
});

$('#finance-school-id').on('change', function () {
    $(this).removeClass('!border-red-400');
    $('#error-school_partner_id').text('');
});