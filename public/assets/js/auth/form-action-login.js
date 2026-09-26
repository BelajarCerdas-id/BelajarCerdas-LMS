let isProcessing = false;
let attemptErrorTimer = null;

function handleLoginSubmit(e) {
    if (e) {
        e.preventDefault();
    }

    if (isProcessing) return; // abaikan jika sedang proses

    isProcessing = true; // tandai sedang proses

    const form = $('#form-login')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const btn = $('#submit-button');
    btn.prop('disabled', true); // Disable button UI

    $.ajax({
        url: '/login',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            window.location.href = res.redirect;

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {

            // general validation
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#form-login').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#form-login').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            
            // jika email / password salah, maka tampilkan error
            } else if (xhr.status === 422 && xhr.responseJSON?.invalidCredentials) {
                const containerError = $('#container-error-attempt-login');
                const textError = $('#text-error-attempt-login');
                const closeError = $('#xmark-icon');

                if (attemptErrorTimer) {
                    clearTimeout(attemptErrorTimer);
                }

                // jika ada error maka tampilkan error
                containerError.removeClass('hidden');
                textError.text(xhr.responseJSON.message);

                // tambahkan event click untuk menutup error
                closeError.off('click').on('click', function () {
                    containerError.addClass('hidden');
                    textError.text('');
                });

                // set timeout untuk menyembunyikan error
                attemptErrorTimer = setTimeout(() => {
                    containerError.addClass('hidden');
                    textError.text('');
                }, 3000);

                // hapus error ketika user mengetik
                $('#form-login input').off('input.loginError').on('input.loginError', function () {
                    containerError.addClass('hidden');
                    textError.text('');
                });

            // jika akun tidak aktif, tampilkan error
            } else if (xhr.status === 422 && xhr.responseJSON?.isAccountInactive) { 
                Swal.fire({
                    icon: 'warning',
                    title: 'tidak dapat login',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });
            } else if (xhr.status === 403 && xhr.responseJSON?.contractExpired) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
}

// Handle form submit (supports Enter key submission from any input field inside the form)
$('#form-login').on('submit', function (e) {
    handleLoginSubmit(e);
});

// Handle submit button click
$('#submit-button').on('click', function (e) {
    handleLoginSubmit(e);
});

// Explicit Enter key handler on form inputs for complete reliability
$('#form-login input').on('keydown', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        handleLoginSubmit(e);
    }
});
