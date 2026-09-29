function resetEditTkaTryoutSessionError() {
    $('#error-edit-session_date').text('');
    $('#error-edit-start_time').text('');
    $('#error-edit-end_time').text('');
    $('#edit-session-date').removeClass('border-red-400');
    $('#edit-start-time').removeClass('border-red-400');
    $('#edit-end-time').removeClass('border-red-400');
}

function resetEditTkaTryoutSessionForm() {
    const form = document.getElementById('edit-tka-tryout-session-form');
    const sessionDateInput = document.getElementById('edit-session-date');
    const startTimeInput = document.getElementById('edit-start-time');
    const endTimeInput = document.getElementById('edit-end-time');

    if (sessionDateInput?._flatpickr) {
        sessionDateInput._flatpickr.destroy();
    }

    if (startTimeInput?._flatpickr) {
        startTimeInput._flatpickr.destroy();
    }

    if (endTimeInput?._flatpickr) {
        endTimeInput._flatpickr.destroy();
    }

    if (form) {
        form.reset();
    }

    $('#edit-tka-tryout-period-id').val('');
    $('#edit-session-id').val('');
    $('#edit-session-date').val('');
    $('#edit-start-time').val('');
    $('#edit-end-time').val('');
    $('#edit-tka-tryout-session-information').html('-');

    resetEditTkaTryoutSessionError();
}

$(document).off('click', '#btn-edit-tka-tryout-session').on('click', '#btn-edit-tka-tryout-session', function (e) {
    e.preventDefault();

    const sessionId = $(this).data('session-id');
    const periodId = $(this).data('period-id');
    const sessionDate = $(this).data('session-date');
    const startDate = $(this).data('start-date');
    const endDate = $(this).data('end-date');
    const startTime = $(this).data('start-time');
    const endTime = $(this).data('end-time');
    const periodNumber = $(this).data('period-number');
    const academicYear = $(this).data('academic-year');

    const editSessionModal = document.getElementById('modal-edit-tka-tryout-session');

    if (!editSessionModal) return;

    resetEditTkaTryoutSessionForm();

    $('#edit-tka-tryout-session-information').html(`
        Periode ${periodNumber}
        <i class="fa-solid fa-circle mx-1 text-[4px] align-middle"></i>
        Tahun Ajaran ${academicYear}
    `);

    $('#edit-tka-tryout-period-id').val(periodId || '');
    $('#edit-session-id').val(sessionId || '');
    $('#edit-session-date').val(sessionDate || '');
    $('#edit-start-time').val(startTime || '');
    $('#edit-end-time').val(endTime || '');

    editSessionModal.showModal();

    enableFlatpickrSessionEdit(startDate, endDate, sessionDate, startTime, endTime);
});

function enableFlatpickrSessionEdit(startDate, endDate, sessionDateValue, startTimeValue, endTimeValue) {
    const sessionDate = document.getElementById('edit-session-date');
    const startTime = document.getElementById('edit-start-time');
    const endTime = document.getElementById('edit-end-time');

    if (sessionDate) {
        flatpickr(sessionDate, {
            dateFormat: 'Y-m-d',
            minDate: startDate || null,
            maxDate: endDate || null,
            defaultDate: sessionDateValue || null,
            disableMobile: true,
            static: true,
            onReady: function (selectedDates, dateStr, instance) {
                instance.input.parentElement.classList.add('w-full');
            },
            onChange: function (selectedDates) {
                if (selectedDates.length === 0) return;

                $('#error-edit-session_date').text('');
                $(sessionDate).removeClass('border-red-400');
            }
        });
    }

    if (startTime) {
        flatpickr(startTime, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            defaultDate: startTimeValue || null,
            disableMobile: true,
            static: true,
            onReady: function (selectedDates, dateStr, instance) {
                instance.input.parentElement.classList.add('w-full');
            },
            onChange: function (selectedDates) {
                if (selectedDates.length === 0) return;

                $('#error-edit-start_time').text('');
                $(startTime).removeClass('border-red-400');
            }
        });
    }

    if (endTime) {
        flatpickr(endTime, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            defaultDate: endTimeValue || null,
            disableMobile: true,
            static: true,
            onReady: function (selectedDates, dateStr, instance) {
                instance.input.parentElement.classList.add('w-full');
            },
            onChange: function (selectedDates) {
                if (selectedDates.length === 0) return;

                $('#error-edit-end_time').text('');
                $(endTime).removeClass('border-red-400');
            }
        });
    }
}

$(document).off('click', '#button-update-tka-tryout-session').on('click', '#button-update-tka-tryout-session', function (e) {
    e.preventDefault();

    if (isProcessing) return;

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const periodId = $('#edit-tka-tryout-period-id').val();
    const sessionId = $('#edit-session-id').val();

    if (!role || !periodId || !sessionId) return;

    const form = document.getElementById('edit-tka-tryout-session-form');

    if (!form) return;

    resetEditTkaTryoutSessionError();

    const formData = new FormData(form);

    isProcessing = true;

    const btn = $(this);

    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/session-form/${periodId}/${sessionId}/update`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-update-tka-tryout-session').html(`
                <div class="w-full flex justify-center">
                    <div class="fixed z-9999">
                        <div id="alertSuccess"
                            class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-600 text-sm">
                                ${response.message}
                            </span>
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

            const editModal = document.getElementById('modal-edit-tka-tryout-session');

            if (editModal) {
                editModal.close();
            }

            resetEditTkaTryoutSessionForm();

            isProcessing = false;
            btn.prop('disabled', false);

            paginateTryoutTKAPeriodSession(periodId);
        },
        error: function (xhr) {
            const response = xhr.responseJSON || {};
            const errors = response.errors || {};

            if (xhr.status === 422) {
                if (Object.keys(errors).length > 0) {
                    $.each(errors, function (field, messages) {
                        const errorElement = $(`#error-edit-${field}`);

                        if (errorElement.length) {
                            errorElement.text(messages[0]);
                        }

                        const inputElement = $(`#edit-tka-tryout-session-form [name="${field}"]`);

                        if (inputElement.length) {
                            inputElement.addClass('border-red-400');
                        }
                    });
                }
            } else if (xhr.status === 404) {
                alert(response.message || 'Sesi Tryout TKA tidak ditemukan.');
            } else {
                alert(response.message || 'Terjadi kesalahan saat memperbarui sesi.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

$(document).off('click', '#button-cancel-edit-tka-tryout-session').on('click', '#button-cancel-edit-tka-tryout-session', function (e) {
    e.preventDefault();

    const editSessionModal = document.getElementById('modal-edit-tka-tryout-session');

    if (!editSessionModal) return;

    editSessionModal.close();

    resetEditTkaTryoutSessionForm();
});