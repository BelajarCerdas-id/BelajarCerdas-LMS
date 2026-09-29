function resetEditTkaTryoutOverrideSessionError() {
    $('#error-edit-override-session_date').text('');
    $('#error-edit-override-start_time').text('');
    $('#error-edit-override-end_time').text('');
    $('#edit-override-session-date').removeClass('border-red-400');
    $('#edit-override-start-time').removeClass('border-red-400');
    $('#edit-override-end-time').removeClass('border-red-400');
}

function resetEditTkaTryoutOverrideSessionForm() {
    const form = document.getElementById('edit-tka-tryout-override-session-form');
    const sessionDateInput = document.getElementById('edit-override-session-date');
    const startTimeInput = document.getElementById('edit-override-start-time');
    const endTimeInput = document.getElementById('edit-override-end-time');

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

    $('#edit-tka-tryout-override-period-id').val('');
    $('#edit-tka-tryout-override-session-id').val('');
    $('#edit-override-session-date').val('');
    $('#edit-override-start-time').val('');
    $('#edit-override-end-time').val('');
    $('#edit-tka-tryout-override-session-information').html('-');

    resetEditTkaTryoutOverrideSessionError();
}

$(document).off('click', '.btn-edit-tka-tryout-override-session').on('click', '.btn-edit-tka-tryout-override-session', function (e) {
    e.preventDefault();

    const sessionId = $(this).data('session-id');
    const periodOverrideId = $(this).data('period-override-id');
    const sessionDate = $(this).data('session-date');
    const startDate = $(this).data('start-date');
    const endDate = $(this).data('end-date');
    const startTime = $(this).data('start-time');
    const endTime = $(this).data('end-time');
    const periodNumber = $(this).data('period-number');
    const academicYear = $(this).data('academic-year');
    const schoolName = $(this).data('school-name');

    const modalTkaTryoutOverrideSession = document.getElementById('modal-tka-tryout-override-session');
    const editOverrideSessionModal = document.getElementById('modal-edit-tka-tryout-override-session');

    if (!editOverrideSessionModal) return;

    modalTkaTryoutOverrideSession?.close();

    $('#edit-tka-tryout-override-session-information').html(`
        Periode ${periodNumber}
        <i class="fa-solid fa-circle mx-1 text-[4px] align-middle"></i>
        Tahun Ajaran ${academicYear}
        ${schoolName ? `
            <i class="fa-solid fa-circle mx-1 text-[4px] align-middle"></i>
            ${schoolName}
        ` : ''}
    `);

    $('#edit-tka-tryout-override-period-id').val(periodOverrideId || '');
    $('#edit-tka-tryout-override-session-id').val(sessionId || '');

    $('#edit-override-session-date').val(sessionDate || '');
    $('#edit-override-start-time').val(startTime || '');
    $('#edit-override-end-time').val(endTime || '');

    editOverrideSessionModal.showModal();

    enableFlatpickrTkaTryoutOverrideSessionEdit(startDate, endDate, sessionDate, startTime, endTime);
});

function enableFlatpickrTkaTryoutOverrideSessionEdit(startDate, endDate, sessionDateValue, startTimeValue, endTimeValue) {
    const sessionDate = document.getElementById('edit-override-session-date');
    const startTime = document.getElementById('edit-override-start-time');
    const endTime = document.getElementById('edit-override-end-time');

    if (sessionDate) {
        if (sessionDate._flatpickr) {
            sessionDate._flatpickr.destroy();
        }

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

                $('#error-edit-override-session_date').text('');
                $(sessionDate).removeClass('border-red-400');
            }
        });
    }

    if (startTime) {
        if (startTime._flatpickr) {
            startTime._flatpickr.destroy();
        }

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

                $('#error-edit-override-start_time').text('');
                $(startTime).removeClass('border-red-400');
            }
        });
    }

    if (endTime) {
        if (endTime._flatpickr) {
            endTime._flatpickr.destroy();
        }

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

                $('#error-edit-override-end_time').text('');
                $(endTime).removeClass('border-red-400');
            }
        });
    }
}

$(document).off('click', '#button-update-tka-tryout-override-session').on('click', '#button-update-tka-tryout-override-session', function (e) {
    e.preventDefault();

    if (isProcessing) return;

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const periodOverrideId = $('#edit-tka-tryout-override-period-id').val();
    const sessionId = $('#edit-tka-tryout-override-session-id').val();

    if (!role || !periodOverrideId || !sessionId) return;

    const form = document.getElementById('edit-tka-tryout-override-session-form');

    if (!form) return;

    resetEditTkaTryoutOverrideSessionError();

    const formData = new FormData(form);

    isProcessing = true;

    const btn = $(this);

    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/session-form/override/${periodOverrideId}/${sessionId}/update`,
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

            const editModal = document.getElementById('modal-edit-tka-tryout-override-session');

            if (editModal) {
                editModal.close();
            }

            resetEditTkaTryoutOverrideSessionForm();

            isProcessing = false;
            btn.prop('disabled', false);

            paginateTryoutTKAPeriodSessionOverride(periodOverrideId);
        },
        error: function (xhr) {
            const response = xhr.responseJSON || {};
            const errors = response.errors || {};

            if (xhr.status === 422) {
                if (Object.keys(errors).length > 0) {
                    $.each(errors, function (field, messages) {
                        const errorElement = $(`#error-edit-override-${field}`);

                        if (errorElement.length) {
                            errorElement.text(messages[0]);
                        }

                        const inputElement = $(`#edit-tka-tryout-override-session-form [name="${field}"]`);

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

$(document).off('click', '#button-cancel-edit-tka-tryout-override-session').on('click', '#button-cancel-edit-tka-tryout-override-session', function (e) {
    e.preventDefault();

    const editSessionModal = document.getElementById('modal-edit-tka-tryout-override-session');

    if (!editSessionModal) return;

    editSessionModal.close();

    resetEditTkaTryoutOverrideSessionForm();
});