function loadTkaTryoutPeriodSubjectForm() {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const periodId = container.dataset.periodId;
    const periodOverrideId = container.dataset.overrideId;
    if (!role || !periodId) return;

    let url = `/lms/${role}/tka-tryout-management/assign-subject-form/${periodId}/load-form`;
    if (periodOverrideId) {
        url += `?override_id=${encodeURIComponent(periodOverrideId)}`;
    }

    $.ajax({
        url: url,
        method: 'GET',
        beforeSend: function () {
            $('#tka-tryout-period-information-skeleton').removeClass('hidden');
            $('#tka-tryout-period-information').addClass('hidden');
            $('#empty-message-tka-tryout-period-information').addClass('hidden');
            $('#tka-tryout-subject-selection-skeleton').removeClass('hidden');
            $('#tka-tryout-subject-selection').addClass('hidden').empty();
            $('#empty-message-tka-tryout-subject-selection').addClass('hidden');
            $('#tka-tryout-selected-subject-summary').addClass('hidden');
            $('#tka-tryout-selected-subject-list').empty();
            $('#tka-tryout-selected-subject-count').text('0 Dipilih');
            $('#error-tka-tryout-subject-date').text('').addClass('hidden');
            $('#error-subject-ids').text('').addClass('hidden');
            $('#tka-tryout-subject-date').removeClass('border-red-400 border');
        },
        success: function (response) {
            const period = response.period ?? null;
            const dateList = response.date_list ?? [];
            const subjectList = response.data ?? [];

            if (period) {
                renderTkaTryoutPeriodInformation(period);
            } else {
                $('#tka-tryout-period-information-skeleton').addClass('hidden');
                $('#tka-tryout-period-information').addClass('hidden');
                $('#empty-message-tka-tryout-period-information').removeClass('hidden');
            }

            renderTkaTryoutDateList(dateList);
            $('#tka-tryout-subject-selection-skeleton').addClass('hidden');

            if (subjectList.length > 0) {
                $('#tka-tryout-subject-selection').empty();

                $.each(subjectList, function (index, subject) {
                    const subjectId = subject.id ?? '';
                    const subjectName = subject.mata_pelajaran ?? '-';
                    const className = subject.kelas?.kelas ?? '-';

                    $('#tka-tryout-subject-selection').append(`
                        <label for="tka-tryout-subject-${subjectId}" class="group flex cursor-pointer items-center gap-3 border-b border-slate-200 px-4 py-3.5 transition-colors duration-150 last:border-b-0 hover:bg-slate-50">
                            <input type="checkbox" id="tka-tryout-subject-${subjectId}" name="subject_ids[]" value="${subjectId}" class="tka-tryout-subject-checkbox checkbox checkbox-sm shrink-0 rounded-md border-slate-300 [--chkbg:#0071BC] [--chkfg:white] checked:border-[#0071BC] focus:outline-none focus:ring-1 focus:ring-[#0071BC]">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                                <i class="fa-solid fa-book-open text-sm text-[#0071BC]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-700">${escapeHtml(subjectName)}</p>
                                <p class="mt-0.5 text-xs text-slate-400">${escapeHtml(className)}</p>
                            </div>
                            <div class="shrink-0">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-[10px] font-medium text-slate-500">TKA</span>
                            </div>
                        </label>
                    `);
                });

                $('#tka-tryout-subject-selection').removeClass('hidden');
                $('#empty-message-tka-tryout-subject-selection').addClass('hidden');
                updateTkaTryoutSelectedSubjectSummary();
            } else {
                $('#tka-tryout-subject-selection').addClass('hidden').empty();
                $('#empty-message-tka-tryout-subject-selection').removeClass('hidden');
                $('#tka-tryout-selected-subject-summary').addClass('hidden');
                $('#tka-tryout-selected-subject-count').text('0 Dipilih');
            }
        },
        error: function () {
            $('#tka-tryout-period-information-skeleton').addClass('hidden');
            $('#tka-tryout-period-information').addClass('hidden');
            $('#empty-message-tka-tryout-period-information').removeClass('hidden');
            $('#tka-tryout-subject-selection-skeleton').addClass('hidden');
            $('#tka-tryout-subject-selection').addClass('hidden').empty();
            $('#empty-message-tka-tryout-subject-selection').removeClass('hidden');
            $('#tka-tryout-selected-subject-summary').addClass('hidden');
            $('#tka-tryout-selected-subject-list').empty();
            $('#tka-tryout-selected-subject-count').text('0 Dipilih');
        }
    });
}

function renderTkaTryoutDateList(dateList) {
    const dateSelect = $('#tka-tryout-subject-date');
    if (!dateSelect.length) return;

    dateSelect.empty().append('<option value="" class="hidden">Pilih tanggal pelaksanaan</option>');
    if (!Array.isArray(dateList) || dateList.length === 0) return;

    $.each(dateList, function (index, date) {
        const formattedDate = formatTkaTryoutDateValue(date);

        dateSelect.append(`
            <option value="${formattedDate}">${escapeHtml(formatTkaTryoutDate(date))}</option>
        `);
    });
}

function formatTkaTryoutDateValue(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function renderTkaTryoutPeriodInformation(period) {
    const periodNumber = period.period_number ?? '-';
    const academicYear = period.tahun_ajaran ?? '-';
    const startDate = period.start_date ?? null;
    const endDate = period.end_date ?? null;
    const school = period.school ?? null;
    const isOverride = period.is_override === true;

    $('#tka-tryout-period-number').text(`Periode ${periodNumber}`);
    $('#tka-tryout-period-academic-year').text(academicYear);

    if (startDate && endDate) {
        $('#tka-tryout-period-date').text(`${formatTkaTryoutDate(startDate)} - ${formatTkaTryoutDate(endDate)}`);
    } else {
        $('#tka-tryout-period-date').text('-');
    }

    const informationGrid = $('#tka-tryout-period-information-grid');
    const schoolInformation = $('#tka-tryout-period-school-information');

    if (isOverride && school) {
        informationGrid.addClass('xl:grid-cols-4');
        schoolInformation.removeClass('hidden');
        $('#tka-tryout-period-school-name').text(school.nama_sekolah ?? '-');
        $('#tka-tryout-period-school-npsn').text(`NPSN ${school.npsn ?? '-'}`);

        const schoolLogo = $('#tka-tryout-period-school-logo');

        if (school.logo) {
            schoolLogo.html(`<img src="/${school.logo}" alt="${escapeHtml(school.nama_sekolah ?? 'Logo Sekolah')}" class="h-full w-full object-cover">`);
        } else {
            schoolLogo.html(`<i class="fa-solid fa-school text-sm text-violet-600"></i>`);
        }
    } else {
        informationGrid.removeClass('xl:grid-cols-4').addClass('lg:grid-cols-2 xl:grid-cols-3');
        schoolInformation.addClass('hidden');
    }

    $('#tka-tryout-period-information-skeleton').addClass('hidden');
    $('#tka-tryout-period-information').removeClass('hidden');
    $('#empty-message-tka-tryout-period-information').addClass('hidden');
}

function updateTkaTryoutSelectedSubjectSummary() {
    const selectedSubjects = $('.tka-tryout-subject-checkbox:checked');
    const selectedCount = selectedSubjects.length;

    $('#tka-tryout-selected-subject-count').text(`${selectedCount} Dipilih`);

    if (selectedCount === 0) {
        $('#tka-tryout-selected-subject-summary').addClass('hidden');
        $('#tka-tryout-selected-subject-list').empty();
        $('#tka-tryout-summary-count').text('0 Mata Pelajaran');
        $('#tka-tryout-summary-date').text('-');
        return;
    }

    const dateSelect = $('#tka-tryout-subject-date');
    const selectedDate = dateSelect.find('option:selected').text().trim();

    $('#tka-tryout-summary-date').text(dateSelect.val() ? selectedDate : '-');
    $('#tka-tryout-summary-count').text(`${selectedCount} Mata Pelajaran`);
    $('#tka-tryout-selected-subject-list').empty();

    selectedSubjects.each(function () {
        const subjectId = $(this).val();
        const subjectName = $(this).closest('label').find('p.text-sm').text().trim();
        const className = $(this).closest('label').find('p.text-xs').text().trim();

        $('#tka-tryout-selected-subject-list').append(`
            <div class="rounded-xl border border-blue-100 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                            <i class="fa-solid fa-book-open text-xs text-[#0071BC]"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-700">${escapeHtml(subjectName)}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">${escapeHtml(className)}</p>
                        </div>
                    </div>
                    <button type="button" class="button-remove-tka-tryout-subject flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-400 transition-colors duration-150 hover:bg-red-50 hover:text-red-500" data-subject-id="${subjectId}">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Jumlah Soal
                            <span class="text-red-500">&#42;</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="total_question[${subjectId}]" min="1" value="" placeholder="Contoh: 30" class="tka-tryout-total-question input w-full rounded-xl border-slate-300 bg-white pr-14 text-sm text-slate-700 focus:border-[#0071BC] focus:outline-none focus:ring-1 focus:ring-[#0071BC]">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">soal</span>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Durasi
                            <span class="text-red-500">&#42;</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="duration[${subjectId}]" min="1" value="" placeholder="Contoh: 60" class="tka-tryout-duration input w-full rounded-xl border-slate-300 bg-white pr-14 text-sm text-slate-700 focus:border-[#0071BC] focus:outline-none focus:ring-1 focus:ring-[#0071BC]">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">menit</span>
                        </div>
                    </div>
                </div>
            </div>
        `);
    });

    $('#tka-tryout-selected-subject-summary').removeClass('hidden');
}

function formatTkaTryoutDate(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '-';

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

$(document).on('change', '#tka-tryout-subject-date', function () {
    $('#error-tka-tryout-subject-date').text('').addClass('hidden');
    $('#tka-tryout-subject-date').removeClass('border-red-400 border');
    updateTkaTryoutSelectedSubjectSummary();
});

$(document).on('click', '.button-remove-tka-tryout-subject', function () {
    const subjectId = $(this).data('subject-id');
    $(`#tka-tryout-subject-${subjectId}`).prop('checked', false);
    updateTkaTryoutSelectedSubjectSummary();
});

$(document).on('input', '.tka-tryout-total-question, .tka-tryout-duration', function () {
    $('#error-tka-tryout-subject-config').text('').addClass('hidden');
});

$(document).on('change', '.tka-tryout-subject-checkbox', function () {
    if ($('.tka-tryout-subject-checkbox:checked').length > 0) {
        $('#error-subject-ids').text('').addClass('hidden');
        $('#tka-tryout-subject-selection').removeClass('border-red-400 border');
    }

    updateTkaTryoutSelectedSubjectSummary();
});

let isProcessing = false;

$(document).on('click', '#button-save-tka-tryout-subject', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const periodId = container.dataset.periodId;
    const periodOverrideId = container.dataset.overrideId;
    if (!role || !periodId || isProcessing) return;

    const form = $('#tka-tryout-period-subject-form')[0];
    if (!form) return;

    const formData = new FormData(form);

    if (periodOverrideId) {
        formData.append('override_id', periodOverrideId);
    }

    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const url = `/lms/${role}/tka-tryout-management/assign-subject-form/${periodId}/submit-form`;

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
            $('#alert-success-assign-tka-tryout-subject').html(`
                <div class="w-full flex justify-center">
                    <div class="fixed z-9999">
                        <div id="alertSuccess" class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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

            form.reset();
            $('.tka-tryout-subject-checkbox').prop('checked', false);
            updateTkaTryoutSelectedSubjectSummary();
            $('#error-tka-tryout-subject-date').text('').addClass('hidden');
            $('#error-subject-ids').text('').addClass('hidden');
            $('#error-tka-tryout-subject-config').text('').addClass('hidden');
            $('#tka-tryout-subject-date').removeClass('border-red-400 border');
            $('#tka-tryout-subject-selection').removeClass('border-red-400 border');

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors ?? {};

                $('#error-tka-tryout-subject-date').text('').addClass('hidden');
                $('#error-subject-ids').text('').addClass('hidden');
                $('#error-tka-tryout-subject-config').text('').addClass('hidden');
                $('#tka-tryout-subject-date').removeClass('border-red-400 border');
                $('#tka-tryout-subject-selection').removeClass('border-red-400 border');

                if (errors.subject_date) {
                    $('#error-tka-tryout-subject-date').text(errors.subject_date[0]).removeClass('hidden');
                    $('#tka-tryout-subject-date').addClass('border-red-400 border');
                }

                if (errors.subject_ids) {
                    $('#error-subject-ids').text(errors.subject_ids[0]).removeClass('hidden');
                    $('#tka-tryout-subject-selection').addClass('border-red-400 border');
                }

                if (errors.total_question_and_duration) {
                    $('#error-tka-tryout-subject-config').text(errors.total_question_and_duration[0]).removeClass('hidden');
                }
            } else {
                alert(xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

$(document).ready(function () {
    loadTkaTryoutPeriodSubjectForm();
});