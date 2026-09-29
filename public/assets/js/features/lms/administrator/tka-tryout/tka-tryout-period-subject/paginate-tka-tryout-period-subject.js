let tkaTryoutSubjectEditDatePicker = null;
function loadTkaTryoutPeriodSubjectForm() {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const periodId = container.dataset.periodId;
    const periodOverrideId = container.dataset.overrideId;
    if (!role || !periodId) return;

    let url = `/lms/${role}/tka-tryout-management/assign-subject-form/${periodId}/load-form`;
    if (periodOverrideId) url += `?override_id=${encodeURIComponent(periodOverrideId)}`;

    $.ajax({
        url,
        method: 'GET',
        beforeSend: function() {
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
        success: function(response) {
            const period = response.period ?? null;
            const dateList = Array.isArray(response.date_list) ? response.date_list : [];
            const subjectList = Array.isArray(response.data) ? response.data : [];

            if (period) {
                container.dataset.periodStartDate = formatTkaTryoutDateValue(period.start_date);
                container.dataset.periodEndDate = formatTkaTryoutDateValue(period.end_date);
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

                $.each(subjectList, function(index, subject) {
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
        error: function() {
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

    $.each(dateList, function(index, date) {
        const formattedDate = formatTkaTryoutDateValue(date);
        if (!formattedDate) return;

        dateSelect.append(`<option value="${formattedDate}">${escapeHtml(formatTkaTryoutDate(date))}</option>`);
    });
}

function formatTkaTryoutDateValue(dateString) {
    if (!dateString) return '';

    if (typeof dateString === 'string') {
        const match = dateString.match(/^(\d{4})-(\d{2})-(\d{2})/);

        if (match) {
            return `${match[1]}-${match[2]}-${match[3]}`;
        }
    }

    return '';
}

function formatTkaTryoutDate(dateString) {
    if (!dateString) return '-';

    const value = String(dateString).trim();
    const match = value.match(/^(\d{4})-(\d{2})-(\d{2})/);

    if (!match) return '-';

    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);

    const date = new Date(year, month - 1, day);

    if (isNaN(date.getTime())) return '-';

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
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
            schoolLogo.html('<i class="fa-solid fa-school text-sm text-violet-600"></i>');
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

    selectedSubjects.each(function() {
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

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function renderTkaTryoutSubjectList(response, options) {
    const {listId, skeletonId, emptyId, totalId, isOverride = false, periodId = null, periodOverrideId = null} = options;

    $(skeletonId).addClass('hidden');

    const subjects = Array.isArray(response?.data) ? response.data : [];
    $(totalId).text(`${subjects.length} Mata Pelajaran`);

    if (subjects.length === 0) {
        $(listId).addClass('hidden').empty();
        $(emptyId).removeClass('hidden');
        return;
    }

    $(listId).removeClass('hidden').empty();
    $(emptyId).addClass('hidden');

    const groupedSubjects = {};

    subjects.forEach(item => {
        const date = item?.subject_date ?? 'unknown';

        if (!groupedSubjects[date]) {
            groupedSubjects[date] = [];
        }

        groupedSubjects[date].push(item);
    });

    Object.keys(groupedSubjects).sort().forEach(date => {
        const dateSubjects = groupedSubjects[date];

        const periodStartDate = response?.period?.start_date ?? '';
        const periodEndDate = response?.period?.end_date ?? '';

        const subjectList = dateSubjects.map((item, index) => {
            const number = String(index + 1).padStart(2, '0');
            const subjectName = item?.mapel?.mata_pelajaran ?? '-';
            const totalQuestion = item?.total_question ?? 0;
            const duration = item?.duration ?? 0;
            const classLevel = item?.mapel?.kelas?.kelas ?? '-';

            return `
                <div class="group flex items-center justify-between gap-4 px-4 py-3.5 transition hover:bg-slate-50">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                            <span class="text-xs font-bold text-slate-500">${number}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-700">${escapeHtml(subjectName)}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                <span>${escapeHtml(totalQuestion)} Soal</span>
                                <i class="fa-solid fa-circle text-[3px] text-slate-300"></i>
                                <span>${escapeHtml(duration)} Menit</span>
                                <i class="fa-solid fa-circle text-[3px] text-slate-300"></i>
                                <span>${escapeHtml(classLevel)}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" class="button-edit-tka-tryout-subject inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 
                            bg-white px-2.5 text-xs font-semibold text-slate-600 shadow-sm transition-colors hover:border-blue-200 hover:bg-blue-50 
                            hover:text-[#0071BC] focus:outline-none focus:ring-2 focus:ring-blue-100 cursor-pointer"
                            data-id="${item.id}"
                            data-period-id="${periodId ?? ''}"
                            data-period-override-id="${periodOverrideId ?? ''}"
                            data-is-override="${isOverride ? 1 : 0}"
                            data-period-start-date="${escapeHtml(periodStartDate)}"
                            data-period-end-date="${escapeHtml(periodEndDate)}"
                            data-subject-name="${escapeHtml(subjectName)}"
                            data-subject-date="${escapeHtml(item.subject_date ?? '')}"
                            data-total-question="${escapeHtml(totalQuestion)}"
                            data-duration="${escapeHtml(duration)}"
                            data-class-level="${escapeHtml(classLevel)}">
                            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                            <span class="hidden sm:inline">Edit</span>
                        </button>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" class="hidden peer toggle-subject"
                                data-id="${item.id}"
                                data-period-id="${periodId ?? ''}"
                                data-period-override-id="${periodOverrideId ?? ''}"
                                data-is-override="${isOverride ? 1 : 0}"
                                ${Number(item.is_active) === 1 ? 'checked' : ''}>
                            <div class="h-6 w-11 rounded-full bg-gray-300 transition-colors duration-300 ease-in-out peer-checked:bg-green-500"></div>
                            <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </div>
            `;
        }).join('');

        $(listId).append(`
            <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm last:mb-0">
                <div class="border-b border-slate-200 bg-slate-50/70 px-4 py-4 sm:px-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                <i class="fa-solid fa-calendar-day text-sm text-blue-600"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800">${escapeHtml(formatTkaTryoutDate(date))}</p>
                                <p class="mt-1 text-xs text-slate-400">${dateSubjects.length} Mata Pelajaran</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-start rounded-lg bg-white px-2.5 py-1.5 ring-1 ring-inset ring-slate-200 sm:self-auto">
                            <span class="text-xs font-semibold text-slate-600">${dateSubjects.length}</span>
                            <span class="text-xs text-slate-400">Mata Pelajaran</span>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">${subjectList}</div>
            </div>
        `);
    });
}

function paginateTkaTryoutPeriodSubject(periodId) {
    const container = document.getElementById('container');
    if (!container || !periodId) return;

    const role = container.dataset.role;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/subject-list/${periodId}/paginate`,
        method: 'GET',
        beforeSend: function() {
            $('#tka-tryout-subject-list-skeleton').removeClass('hidden');
            $('#tka-tryout-subject-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-subject').addClass('hidden');
            $('#tka-tryout-subject-total').text('0 Mata Pelajaran');
        },
        success: function (response) {
            if (response.period) {
                const container = document.getElementById('container');

                if (container) {
                    container.dataset.periodStartDate = formatTkaTryoutDateValue(response.period.start_date);
                    container.dataset.periodEndDate = formatTkaTryoutDateValue(response.period.end_date);
                }
            }

            renderTkaTryoutSubjectList(response, {
                listId: '#tka-tryout-subject-list',
                skeletonId: '#tka-tryout-subject-list-skeleton',
                emptyId: '#empty-message-tka-tryout-subject',
                totalId: '#tka-tryout-subject-total',
                isOverride: false,
                periodId,
                periodOverrideId: null
            });
        },
        error: function() {
            $('#tka-tryout-subject-list-skeleton').addClass('hidden');
            $('#tka-tryout-subject-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-subject').removeClass('hidden');
            $('#tka-tryout-subject-total').text('0 Mata Pelajaran');
        }
    });
}

function paginateTkaTryoutPeriodSubjectOverride(periodOverrideId) {
    const container = document.getElementById('container');
    if (!container || !periodOverrideId) return;

    const role = container.dataset.role;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/subject-list/override/${periodOverrideId}/paginate`,
        method: 'GET',
        beforeSend: function() {
            $('#tka-tryout-override-subject-list-skeleton').removeClass('hidden');
            $('#tka-tryout-override-subject-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-override-subject').addClass('hidden');
            $('#tka-tryout-override-subject-total').text('0 Mata Pelajaran');
        },
        success: function (response) {
            if (response.period) {
                const container = document.getElementById('container');

                if (container) {
                    container.dataset.periodStartDate = formatTkaTryoutDateValue(response.period.start_date);
                    container.dataset.periodEndDate = formatTkaTryoutDateValue(response.period.end_date);
                }
            }

            renderTkaTryoutSubjectList(response, {
                listId: '#tka-tryout-override-subject-list',
                skeletonId: '#tka-tryout-override-subject-list-skeleton',
                emptyId: '#empty-message-tka-tryout-override-subject',
                totalId: '#tka-tryout-override-subject-total',
                isOverride: true,
                periodId: response?.period?.id ?? null,
                periodOverrideId
            });
        },
        error: function() {
            $('#tka-tryout-override-subject-list-skeleton').addClass('hidden');
            $('#tka-tryout-override-subject-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-override-subject').removeClass('hidden');
            $('#tka-tryout-override-subject-total').text('0 Mata Pelajaran');
        }
    });
}

$(document).on('change', '#tka-tryout-subject-date', function() {
    $('#error-tka-tryout-subject-date').text('').addClass('hidden');
    $('#tka-tryout-subject-date').removeClass('border-red-400 border');
    updateTkaTryoutSelectedSubjectSummary();
});

$(document).on('click', '.button-remove-tka-tryout-subject', function() {
    const subjectId = $(this).data('subject-id');
    $(`#tka-tryout-subject-${subjectId}`).prop('checked', false);
    updateTkaTryoutSelectedSubjectSummary();
});

$(document).on('input', '.tka-tryout-total-question, .tka-tryout-duration', function() {
    $('#error-tka-tryout-subject-config').text('').addClass('hidden');
});

$(document).on('change', '.tka-tryout-subject-checkbox', function() {
    if ($('.tka-tryout-subject-checkbox:checked').length > 0) {
        $('#error-subject-ids').text('').addClass('hidden');
        $('#tka-tryout-subject-selection').removeClass('border-red-400 border');
    }

    updateTkaTryoutSelectedSubjectSummary();
});

$(document).off('click', '.button-edit-tka-tryout-subject').on('click', '.button-edit-tka-tryout-subject', function () {
    const button = $(this);
    const subjectId = button.data('id');
    const periodId = button.data('period-id') ?? '';
    const periodOverrideId = button.data('period-override-id') ?? '';
    const isOverride = Number(button.data('is-override')) === 1;
    const subjectName = button.attr('data-subject-name') ?? '-';
    const subjectDate = formatTkaTryoutDateValue(button.attr('data-subject-date') ?? '');
    const totalQuestion = button.attr('data-total-question') ?? '';
    const duration = button.attr('data-duration') ?? '';
    const classLevel = button.attr('data-class-level') ?? '';
    const startDate = formatTkaTryoutDateValue(button.attr('data-period-start-date') ?? '');
    const endDate = formatTkaTryoutDateValue(button.attr('data-period-end-date') ?? '');

    if (!subjectId) return;

    const container = document.getElementById('container');
    if (!container) return;

    $('#edit-tka-tryout-subject-id').val(subjectId);
    $('#edit-tka-tryout-subject-period-id').val(periodId);
    $('#edit-tka-tryout-subject-period-override-id').val(periodOverrideId);
    $('#edit-tka-tryout-subject-is-override').val(isOverride ? 1 : 0);

    $('#edit-tka-tryout-subject-name').text(subjectName);
    $('#edit-tka-tryout-subject-class').text(classLevel);
    $('#edit-tka-tryout-subject-date').val(subjectDate);
    $('#edit-tka-tryout-subject-total-question').val(totalQuestion);
    $('#edit-tka-tryout-subject-duration').val(duration);

    $('#error-edit-tka-tryout-subject-date').text('').addClass('hidden');
    $('#error-edit-tka-tryout-subject-total-question').text('').addClass('hidden');
    $('#error-edit-tka-tryout-subject-duration').text('').addClass('hidden');
    $('#error-edit-tka-tryout-subject-config').text('').addClass('hidden');

    $('#edit-tka-tryout-subject-date').removeClass('border-red-400');
    $('#edit-tka-tryout-subject-total-question').removeClass('border-red-400');
    $('#edit-tka-tryout-subject-duration').removeClass('border-red-400');

    enableFlatpickrSessionEdit(startDate, endDate, subjectDate);

    const modalSubjectList = document.getElementById(isOverride ? 'modal-tka-tryout-override-subject' : 'modal-tka-tryout-subject');
    const modal = document.getElementById('modal-edit-tka-tryout-subject');

    if (modalSubjectList && typeof modalSubjectList.close === 'function' && modalSubjectList.open) {
        modalSubjectList.close();
    }

    if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
    }
});

function enableFlatpickrSessionEdit(startDate, endDate, subjectDateValue) {
    const subjectDate = document.getElementById('edit-tka-tryout-subject-date');
    if (!subjectDate || typeof flatpickr === 'undefined') return;

    if (tkaTryoutSubjectEditDatePicker) {
        tkaTryoutSubjectEditDatePicker.destroy();
        tkaTryoutSubjectEditDatePicker = null;
    }

    const minDate = formatTkaTryoutDateValue(startDate);
    const maxDate = formatTkaTryoutDateValue(endDate);
    const defaultDate = formatTkaTryoutDateValue(subjectDateValue);

    const parseLocalDate = function (dateString) {
        if (!dateString) return null;

        const match = String(dateString).match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if (!match) return null;

        return new Date(
            Number(match[1]),
            Number(match[2]) - 1,
            Number(match[3])
        );
    };

    tkaTryoutSubjectEditDatePicker = flatpickr(subjectDate, {
        dateFormat: 'Y-m-d',
        minDate: parseLocalDate(minDate),
        maxDate: parseLocalDate(maxDate),
        defaultDate: parseLocalDate(defaultDate),
        disableMobile: true,
        static: true,
        allowInput: false,
        onReady: function (selectedDates, dateStr, instance) {
            if (instance.input.parentElement) {
                instance.input.parentElement.classList.add('w-full');
            }
        },
        onChange: function () {
            $('#error-edit-tka-tryout-subject-date').text('').addClass('hidden');
            $('#edit-tka-tryout-subject-date').removeClass('border-red-400 border');
        }
    });
}

$(document).off('click', '#button-update-tka-tryout-subject').on('click', '#button-update-tka-tryout-subject', function (e) {
    e.preventDefault();
    
    const container = document.getElementById('container');
    if (!container) return;
    
    const role = container.dataset.role;
    const periodId = $('#edit-tka-tryout-subject-period-id').val();
    const subjectId = $('#edit-tka-tryout-subject-id').val();
    const periodOverrideId = $('#edit-tka-tryout-subject-period-override-id').val();
    const isOverride = Number($('#edit-tka-tryout-subject-is-override').val()) === 1;
    
    if (!role || !periodId || !subjectId || isProcessing) return;
    
    const form = document.getElementById('edit-tka-tryout-subject-form');
    if (!form) return;
    
    const formData = new FormData(form);
    const btn = $(this);
    
    isProcessing = true;
    btn.prop('disabled', true);
    
    const url = `/lms/${role}/tka-tryout-management/assign-subject-form/${periodId}/${subjectId}/edit`;
    $.ajax({
        url,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
    
        success: function (response) {
            clearEditTkaTryoutSubjectErrors();
    
            $('#alert-success-update-tka-tryout-subject').html(`
                <div class="w-full flex justify-center">
                    <div class="fixed z-9999">
                        <div id="alertSuccess"
                            class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 shrink-0 stroke-current text-green-600"
                                fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-600 text-sm">
                                ${escapeHtml(response.message ?? 'Mata pelajaran berhasil diperbarui.')}
                            </span>
                            <i class="fas fa-times cursor-pointer text-green-600" id="btnClose"></i>
                        </div>
                    </div>
                </div>
            `);
    
            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);
    
            $('#btnClose').off('click').on('click', function () {
                $('#alertSuccess').remove();
            });
    
            const modal = document.getElementById('modal-edit-tka-tryout-subject');
    
            if (modal && typeof modal.close === 'function' && modal.open) {
                modal.close();
            }
    
            if (isOverride) {
                if (periodOverrideId) {
                    paginateTkaTryoutPeriodSubjectOverride(periodOverrideId);
                }
            } else {
                paginateTkaTryoutPeriodSubject(periodId);
            }
    
            isProcessing = false;
            btn.prop('disabled', false);
        },
    
        error: function (xhr) {
            clearEditTkaTryoutSubjectErrors();
    
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors ?? {};
    
                if (errors.subject_date) {
                    $('#error-edit-tka-tryout-subject-date').text(errors.subject_date[0]).removeClass('hidden');
                    $('#edit-tka-tryout-subject-date').addClass('border-red-400 border');
                }
    
                if (errors.total_question) {
                    $('#error-edit-tka-tryout-subject-total-question').text(errors.total_question[0]).removeClass('hidden');
                    $('#edit-tka-tryout-subject-total-question').addClass('border-red-400');
                }
    
                if (errors.duration) {
                    $('#error-edit-tka-tryout-subject-duration').text(errors.duration[0]).removeClass('hidden');
                    $('#edit-tka-tryout-subject-duration').addClass('border-red-400');
                }
    
                if (errors.message) {
                    $('#error-edit-tka-tryout-subject').text(errors.message[0]).removeClass('hidden');
                }
            } else {
                alert(xhr.responseJSON?.message ?? 'Terjadi kesalahan saat memperbarui mata pelajaran.');
            }
    
            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

function clearEditTkaTryoutSubjectErrors() {
    $('#error-edit-tka-tryout-subject').text('').addClass('hidden');
    $('#error-edit-tka-tryout-subject-date').text('').addClass('hidden');
    $('#error-edit-tka-tryout-subject-total-question').text('').addClass('hidden');
    $('#error-edit-tka-tryout-subject-duration').text('').addClass('hidden');
    $('#edit-tka-tryout-subject-date').removeClass('border-red-400 border');
    $('#edit-tka-tryout-subject-total-question').removeClass('border-red-400');
    $('#edit-tka-tryout-subject-duration').removeClass('border-red-400');
}


$(document).on('click', '#button-save-tka-tryout-subject', function(e) {
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
        url,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#alert-success-assign-tka-tryout-subject').html(`
                <div class="w-full flex justify-center">
                    <div class="fixed z-9999">
                        <div id="alertSuccess" class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-600 text-sm">${escapeHtml(response.message ?? 'Mata pelajaran berhasil ditambahkan.')}</span>
                            <i class="fas fa-times cursor-pointer text-green-600" id="btnClose"></i>
                        </div>
                    </div>
                </div>
            `);

            setTimeout(function() {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function() {
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
        error: function(xhr) {
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

$(document).ready(function() {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    if (!role) return;

    loadTkaTryoutPeriodSubjectForm();

    $(document).off('change', '.toggle-subject').on('change', '.toggle-subject', function() {
        const checkbox = $(this);
        const tkaTryoutSubjectId = checkbox.data('id');
        const periodId = checkbox.data('period-id');
        const periodOverrideId = checkbox.data('period-override-id');
        const isOverride = Number(checkbox.data('is-override')) === 1;
        const status = checkbox.is(':checked') ? 1 : 0;

        if (!tkaTryoutSubjectId) return;

        checkbox.prop('disabled', true);

        let url = '';

        if (isOverride) {
            if (!periodOverrideId) {
                checkbox.prop('disabled', false);
                return;
            }

            url = `/lms/${role}/tka-tryout-management/assign-subject-form/override/${periodOverrideId}/${tkaTryoutSubjectId}/activate`;
        } else {
            if (!periodId) {
                checkbox.prop('disabled', false);
                return;
            }

            url = `/lms/${role}/tka-tryout-management/assign-subject-form/${periodId}/${tkaTryoutSubjectId}/activate`;
        }

        $.ajax({
            url,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                is_active: status
            },
            success: function() {
                if (isOverride) {
                    paginateTkaTryoutPeriodSubjectOverride(periodOverrideId);
                } else {
                    paginateTkaTryoutPeriodSubject(periodId);
                }
            },
            error: function() {
                checkbox.prop('checked', !status);
                checkbox.prop('disabled', false);
                alert('Gagal mengubah status.');
            }
        });
    });
});