let isProcessing = false;

$(document).on('click', '#submit-button-update-personal-information', function (e) {
    e.preventDefault();
    
    const url = $('#update-personal-information-form').data('url');

    const form = $('#update-personal-information-form')[0];
    const formData = new FormData(form);

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: url,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_1');
            if (modal) {
                modal.close();
            }

            $('#alert-success-update-personal-information').html(`
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

            // RESET FORM
            $('#update-personal-information-form')[0].reset();

            isProcessing = false;
            btn.prop('disabled', false);

            // preview text
            $('#view-nama-lengkap').text(response.data.nama_lengkap);
            $('#view-no-hp').text(response.data.no_hp);
            $('#view-personal-email').text(response.data.personal_email);
            $('#view-nik').text(response.data.nik);

            // update input value
            $('#nama_lengkap').val(response.data.nama_lengkap);
            $('#no_hp').val(response.data.no_hp);
            $('#personal_email').val(response.data.personal_email);
            $('#nik').val(response.data.nik);
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {

                    // Tampilkan pesan error
                    $('#update-personal-information-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#update-personal-information-form').find(`[name="${field}"]`).addClass('border-red-400 border');

                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});