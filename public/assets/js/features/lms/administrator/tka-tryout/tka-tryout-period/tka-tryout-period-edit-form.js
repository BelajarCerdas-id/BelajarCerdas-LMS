$(document).off('click', '#btn-edit-tka-tryout-period').on('click', '#btn-edit-tka-tryout-period', function (e) {
    e.preventDefault();

    const periodId = $(this).data('period-id');
    const academicYear = $(this).data('academic-year');
    const startDate = $(this).data('start-date');
    const endDate = $(this).data('end-date');
    const isReview = $(this).data('is-review');

    const periodListModal = document.getElementById('modal-tka-tryout-period');
    const editPeriodModal = document.getElementById('modal-edit-tka-tryout-period');

    if (!editPeriodModal) return;

    periodListModal?.close();

    $('#edit-tka-tryout-period-id').val(periodId || '');
    $('#academic-year').val(academicYear || '');
    $('#edit-tka-tryout-period-start-date').val(startDate || '');
    $('#edit-tka-tryout-period-end-date').val(endDate || '');
    $('#edit-tka-tryout-period-is-review').prop('checked', isReview == 1 || isReview === true || isReview === '1');

    $('#edit-tka-tryout-period-form').find('[id^="error-edit-"]').text('');
    $('#edit-tka-tryout-period-form').find('.border-red-400').removeClass('border-red-400');

    editPeriodModal.showModal();
    enableFlatpickrPeriodEdit();
});


function enableFlatpickrPeriodEdit() {
    const startInput = document.getElementById('edit-tka-tryout-period-start-date');
    const endInput = document.getElementById('edit-tka-tryout-period-end-date');

    if (!startInput || !endInput) return;

    if (startInput._flatpickr) {
        startInput._flatpickr.destroy();
    }

    if (endInput._flatpickr) {
        endInput._flatpickr.destroy();
    }

    const startDate = startInput.value || null;
    const endDate = endInput.value || null;

    let startPicker;
    let endPicker;

    startPicker = flatpickr(startInput, {
        dateFormat: 'Y-m-d',
        defaultDate: startDate,
        minDate: startDate || 'today',
        disableMobile: true,
        static: true,
        onReady: function (selectedDates, dateStr, instance) {
            instance.input.parentElement.classList.add('w-full');
        },
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                endPicker.set('minDate', selectedDates[0]);
            }

            $('#error-edit-start_date').text('');
            $(startInput).removeClass('border-red-400');
        }
    });

    endPicker = flatpickr(endInput, {
        dateFormat: 'Y-m-d',
        defaultDate: endDate,
        minDate: startDate || 'today',
        disableMobile: true,
        static: true,
        onReady: function (selectedDates, dateStr, instance) {
            instance.input.parentElement.classList.add('w-full');
        },
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                startPicker.set('maxDate', selectedDates[0]);
            }

            $('#error-edit-end_date').text('');
            $(endInput).removeClass('border-red-400');
        }
    });

    if (startDate) {
        endPicker.set('minDate', startDate);
    }

    if (endDate) {
        startPicker.set('maxDate', endDate);
    }
}


// edit TKA tryout period school override
$(document).off('click', '#btn-edit-tka-tryout-period-school-override').on('click', '#btn-edit-tka-tryout-period-school-override', function (e) {
    e.preventDefault();

    const periodId = $(this).data('period-id');
    const overrideId = $(this).data('override-id');
    const schoolName = $(this).data('school-name');
    const startDate = $(this).data('start-date');
    const endDate = $(this).data('end-date');
    const isReview = $(this).data('is-review');

    const periodListModal = document.getElementById('modal-tka-tryout-override');
    const editOverrideModal = document.getElementById('modal-edit-tka-tryout-period-school-override');

    if (!editOverrideModal) return;

    periodListModal?.close();

    $('#edit-tka-tryout-period-override-period-id').val(periodId || '');
    $('#edit-tka-tryout-period-override-id').val(overrideId || '');
    $('#edit-tka-tryout-override-school-name').text(schoolName || '-');
    $('#edit-start-date').val(startDate || '');
    $('#edit-end-date').val(endDate || '');
    $('#edit-is-review').prop('checked', isReview == 1 || isReview === true || isReview === '1');

    $('#edit-tka-tryout-period-school-override-form').find('[id^="error-edit-"]').text('');
    $('#edit-tka-tryout-period-school-override-form').find('.border-red-400').removeClass('border-red-400');

    editOverrideModal.showModal();
    enableFlatpickrPeriodOverrideEdit();
});


function enableFlatpickrPeriodOverrideEdit() {
    const startInput = document.getElementById('edit-start-date');
    const endInput = document.getElementById('edit-end-date');

    if (!startInput || !endInput) return;

    if (startInput._flatpickr) {
        startInput._flatpickr.destroy();
    }

    if (endInput._flatpickr) {
        endInput._flatpickr.destroy();
    }

    const startDate = startInput.value || null;
    const endDate = endInput.value || null;

    let startPicker;
    let endPicker;

    startPicker = flatpickr(startInput, {
        dateFormat: 'Y-m-d',
        defaultDate: startDate,
        minDate: startDate || 'today',
        disableMobile: true,
        static: true,
        onReady: function (selectedDates, dateStr, instance) {
            instance.input.parentElement.classList.add('w-full');
        },
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                endPicker.set('minDate', selectedDates[0]);
            }

            $('#error-edit-start_date').text('');
            $(startInput).removeClass('border-red-400');
        }
    });

    endPicker = flatpickr(endInput, {
        dateFormat: 'Y-m-d',
        defaultDate: endDate,
        minDate: startDate || 'today',
        disableMobile: true,
        static: true,
        onReady: function (selectedDates, dateStr, instance) {
            instance.input.parentElement.classList.add('w-full');
        },
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                startPicker.set('maxDate', selectedDates[0]);
            }

            $('#error-edit-end_date').text('');
            $(endInput).removeClass('border-red-400');
        }
    });

    if (startDate) {
        endPicker.set('minDate', startDate);
    }

    if (endDate) {
        startPicker.set('maxDate', endDate);
    }
}

$(document).off('click', '#button-update-tka-tryout-period').on('click', '#button-update-tka-tryout-period', function (e) {
    e.preventDefault();
    
    if (isProcessing) return;
    
    const container = document.getElementById('container');
    const form = document.getElementById('edit-tka-tryout-period-form');
    const role = container.dataset.role;
    const periodId = $('#edit-tka-tryout-period-id').val();

    if (!container) return;
    if (!role || !periodId) return;
    if (!form) return;
    
    const formData = new FormData(form);
    formData.set('is_review', $('#edit-tka-tryout-period-is-review').is(':checked') ? '1' : '0');
    
    $('#edit-tka-tryout-period-form').find('[id^="error-edit-"]').text('');
    $('#edit-tka-tryout-period-form').find('.border-red-400').removeClass('border-red-400');
    
    isProcessing = true;
    
    const btn = $(this);
    btn.prop('disabled', true);
    
    $.ajax({
        url: `/lms/${role}/tka-tryout-management/update-periode-form/${periodId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':
                $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-update-tka-tryout-period').html(`
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
    
            const editModal =
                document.getElementById(
                    'modal-edit-tka-tryout-period'
                );
    
            if (editModal) {
                editModal.close();
            }
    
            const startInput = document.getElementById('edit-start-date');
            const endInput = document.getElementById('edit-end-date');
    
            if (startInput?._flatpickr) {
                startInput._flatpickr.destroy();
            }
    
            if (endInput?._flatpickr) {
                endInput._flatpickr.destroy();
            }
    
            form.reset();
    
            $('#edit-tka-tryout-period-id').val('');
            $('#edit-tahun-ajaran').val('');
            $('#edit-start-date').val('');
            $('#edit-end-date').val('');
            $('#edit-tka-tryout-period-is-review').prop('checked', false);
    
            isProcessing = false;
            btn.prop('disabled', false);
    
            paginateTryoutTKAPeriod($('#search-academic-year').val(), 1);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
    
                $.each(errors, function (field, messages) {
                    const errorElement = $(`#error-edit-${field}`);
    
                    if (errorElement.length) {
                        errorElement.text(messages[0]);
                    }
    
                    const inputElement = $(`#edit-tka-tryout-period-form [name="${field}"]`);
    
                    if (inputElement.length) {
                        inputElement.addClass('border-red-400');
                    }
                });
            } else {
                alert(xhr.responseJSON?.message || 'Terjadi kesalahan saat memperbarui periode Tryout TKA.');
            }
    
            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

$(document).off('click', '#button-cancel-edit-tka-tryout-period').on('click', '#button-cancel-edit-tka-tryout-period',
    function (e) {
        e.preventDefault();

        const editModal = document.getElementById('modal-edit-tka-tryout-period');
        if (!editModal) return;

        editModal.close();
    }
);