let isProcessing = false;

// Form Action tka tryout period school override
$('#button-save-tka-tryout-period-school-override').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const periodId = container.dataset.periodId;

    if (!container) return;
    if (!role) return;
    if (!periodId) return;

    const form = $('#create-tka-tryout-period-school-override-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/period-school-override-form/${periodId}/submit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-create-tka-tryout-period-school-override').html(`
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

            // RESET SEMUA
            $('#create-tka-tryout-period-school-override-form')[0].reset();
            enableFlatpickrCreate();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {

                    // Tampilkan pesan error
                    $('#create-tka-tryout-period-school-override-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-tka-tryout-period-school-override-form').find(`[name="${field}"]`).addClass('border-red-400 border');

                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

function enableFlatpickrCreate() {
    const startInput = document.getElementById('start-date');
    const endInput = document.getElementById('end-date');

    if (!startInput || !endInput) return;

    const endPicker = flatpickr(endInput, {
        dateFormat: 'Y-m-d',
        minDate: 'today',
        disableMobile: true,
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                startPicker.set('maxDate', selectedDates[0]);
            }

            const errorEndDate = document.getElementById('error-end_date');

            if (errorEndDate) {
                errorEndDate.textContent = '';
            }
        }
    });

    const startPicker = flatpickr(startInput, {
        dateFormat: 'Y-m-d',
        minDate: 'today',
        disableMobile: true,
        onChange: function (selectedDates) {
            if (selectedDates.length === 0) return;

            endPicker.set('minDate', selectedDates[0]);

            const errorStartDate = document.getElementById('error-start_date');

            if (errorStartDate) {
                errorStartDate.textContent = '';
            }
        }
    });
}

$(document).ready(function () {
    enableFlatpickrCreate();
});