$('#btn-open-edit-logo-dekstop, #btn-open-edit-logo-mobile').on('click', function () {
    document.getElementById('edit-school-logo').showModal();
});

let selectedFile = null;

function previewSchoolLogoInput(event, target) {
    const file = event.target.files[0];

    const img = document.getElementById('preview-logo-modal');
    const inner = document.getElementById('preview-inner');
    const errorText = document.getElementById('error-school_logo');

    const defaultSrc = img.getAttribute('data-default-src') || '';

    // jika cancel file dialog
    if (!file) {
        selectedFile = null;
        event.target.value = '';

        if (defaultSrc) {
            img.src = defaultSrc;
            img.classList.remove('hidden');

            const icon = document.getElementById('icon-logo-modal');
            if (icon) icon.remove();

            inner.className = 'w-full h-full flex items-center justify-center bg-transparent';
        } else {
            img.src = '';
            img.classList.add('hidden');

            if (!document.getElementById('icon-logo-modal')) {
                inner.innerHTML += `
                    <i id="icon-logo-modal"
                        class="fa-solid fa-school text-3xl text-gray-400">
                    </i>
                `;
            }

            inner.className = 'w-full h-full rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border';
        }

        return;
    }

    errorText.innerText = '';

    selectedFile = file;

    const reader = new FileReader();
    reader.onload = function (e) {
        img.src = e.target.result;
        img.className = 'max-w-full max-h-full object-contain';

        const icon = document.getElementById('icon-logo-modal');
        if (icon) icon.remove();

        inner.className = 'w-full h-full flex items-center justify-center bg-transparent';
    };

    reader.readAsDataURL(file);
}

$('#btn-save-school-logo').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-school-logo-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const schoolName = $('#edit-school-logo-form').data('school-name');
    const schoolId = $('#edit-school-logo-form').data('school-id');

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/edit-school-logo`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('edit-school-logo');

            if (modal) {
                modal.close();

                $('#alert-success-insert-school-logo').html(`
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
            }

            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function () {
                $('#alertSuccess').remove();
            });

            isProcessing = false;
            btn.prop('disabled', false);

            window.location.reload();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;

                $.each(response.errors, function (field, messages) {
                    $(`#error-${field}`).text(messages[0]);
                    $(`[name="${field}"]`).addClass('border-red-400 border');
                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});