let isProcessing = false;

// Form Action tryout tka period
$('#button-save-tka-tryout-session').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const periodId = container.dataset.periodId;
    const periodOverrideId = container.dataset.periodOverrideId;

    if (!container) return;
    if (!role) return;

    const form = $('#create-tka-tryout-session-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: periodOverrideId
            ? `/lms/${role}/tka-tryout-management/session-form/${periodOverrideId}/override/submit`
            : `/lms/${role}/tka-tryout-management/session-form/${periodId}/submit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-create-tka-tryout-session').html(`
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
            $('#create-tka-tryout-session-form')[0].reset();
            enableFlatpickrCreate();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-tka-tryout-session-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-tka-tryout-session-form').find(`[name="${field}"]`).addClass('border-red-400 border');

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
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const periodOverrideId = container.dataset.periodOverrideId;

    if (!container) return;
    if (!role) return;

    let startDate = null;
    let endDate = null;

    if (periodOverrideId) {
        startDate = container.dataset.overrideStartDate;
        endDate = container.dataset.overrideEndDate;
    } else {
        startDate = container.dataset.startDate;
        endDate = container.dataset.endDate;
    };

    if (!startDate || !endDate) return;

    const sessionDate = document.getElementById('session-date');
    const startTime = document.getElementById('start-time');
    const endTime = document.getElementById('end-time');

    if (sessionDate) {
        flatpickr(sessionDate, {
            dateFormat: 'Y-m-d',
            minDate: startDate,
            maxDate: endDate,
            disableMobile: true,
            onChange: function (selectedDates) {
                if (selectedDates.length === 0) return;

                const error = document.getElementById('error-session_date');

                if (error) {
                    error.textContent = '';
                }
            }
        });
    }

    if (startTime) {
        flatpickr(startTime, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            disableMobile: true,
            onChange: function (selectedDates) {
                if (selectedDates.length === 0) return;

                const error = document.getElementById('error-start_time');

                if (error) {
                    error.textContent = '';
                }
            }
        });
    }

    if (endTime) {
        flatpickr(endTime, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            disableMobile: true,
            onChange: function (selectedDates) {
                if (selectedDates.length === 0) return;

                const error = document.getElementById('error-end_time');

                if (error) {
                    error.textContent = '';
                }
            }
        });
    }
}

$(document).ready(function () {
    enableFlatpickrCreate();
});