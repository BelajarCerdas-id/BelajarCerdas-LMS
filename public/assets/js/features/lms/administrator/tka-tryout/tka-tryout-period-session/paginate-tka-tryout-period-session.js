function renderTkaTryoutSessionList(response, options) {
    const { listId, skeletonId, emptyId, totalId, isOverride = false } = options;
    $(skeletonId).addClass('hidden');

    const data = response?.data ?? [];

    if (!Array.isArray(data) || data.length === 0) {
        $(listId).addClass('hidden').empty();
        $(emptyId).removeClass('hidden');
        $(totalId).text('0 Sesi');
        return;
    }

    $(listId).removeClass('hidden').empty();
    $(emptyId).addClass('hidden');
    $(totalId).text(`${response.total_session ?? 0} Sesi`);

    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    const formatDate = (dateString) => {
        if (!dateString || dateString === 'tidak_diketahui') return 'Tanggal tidak tersedia';

        const parts = String(dateString).split('-');

        if (parts.length === 3) {
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);

            if (!isNaN(year) && !isNaN(month) && !isNaN(day) && months[month]) {
                return `${day} ${months[month]} ${year}`;
            }
        }

        const date = new Date(dateString);

        if (isNaN(date.getTime())) return 'Tanggal tidak tersedia';

        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    };

    const formatTime = (time) => {
        return time ? String(time).substring(0, 5) : 'Jam tidak tersedia';
    };

    const escapeHtml = (value) => {
        if (value === null || value === undefined) return '';

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const normalizeGroups = (items) => {
        if (!Array.isArray(items)) return [];

        if (items.some(item => item && Array.isArray(item.sessions))) {
            return items.map(group => ({
                session_date: group.session_date ?? null,
                sessions: Array.isArray(group.sessions) ? group.sessions : []
            }));
        }

        const grouped = new Map();

        items.forEach(item => {
            const date = item?.session_date ?? 'tidak_diketahui';

            if (!grouped.has(date)) grouped.set(date, []);

            grouped.get(date).push(item);
        });

        return Array.from(grouped.entries()).map(([session_date, sessions]) => ({
            session_date,
            sessions
        }));
    };

    const groupedSessions = normalizeGroups(data);

    if (groupedSessions.length === 0) {
        $(listId).addClass('hidden').empty();
        $(emptyId).removeClass('hidden');
        $(totalId).text('0 Sesi');
        return;
    }

    groupedSessions.forEach(group => {
        const groupDate = group.session_date ?? group.sessions?.[0]?.session_date ?? null;
        const sessions = Array.isArray(group.sessions) ? group.sessions : [];

        if (sessions.length === 0) return;

        $(listId).append(`
            <div class="mb-6">
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                            <i class="fa-regular fa-calendar text-sm text-slate-500"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">${escapeHtml(formatDate(groupDate))}</p>
                    </div>
                </div>
                <div class="space-y-3">
                    ${sessions.map(item => {
                        const itemPeriodId = item?.tka_tryout_period?.id ?? '';
                        const periodOverrideId = item?.tka_tryout_period_sch_override?.id ?? '';
                        const periodNumber = item?.tka_tryout_period?.period_number ?? item?.tka_tryout_period_sch_override?.tka_tryout_period?.period_number ?? '';
                        const academicYear = item?.tka_tryout_period?.tahun_ajaran ?? item?.tka_tryout_period_sch_override?.tka_tryout_period?.tahun_ajaran ?? '';
                        const startDate = isOverride ? item?.tka_tryout_period_sch_override?.start_date ?? item?.tka_tryout_period?.start_date ?? '' : item?.tka_tryout_period?.start_date ?? '';
                        const endDate = isOverride ? item?.tka_tryout_period_sch_override?.end_date ?? item?.tka_tryout_period?.end_date ?? '' : item?.tka_tryout_period?.end_date ?? '';
                        const schoolName = item?.tka_tryout_period_sch_override?.school_partner?.nama_sekolah ?? '';
                        const sessionDate = item?.session_date ?? groupDate ?? '';
                        const startTime = formatTime(item?.start_time);
                        const endTime = formatTime(item?.end_time);
                        const editButtonClass = isOverride ? 'btn-edit-tka-tryout-override-session' : 'btn-edit-tka-tryout-session';
                        const manageStudentButtonClass = isOverride ? 'btn-tka-tryout-override-manage-students' : 'btn-tka-tryout-manage-students';

                        return `
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                                            <span class="text-sm font-bold text-emerald-600">${escapeHtml(item?.session_number ?? '-')}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-700">Sesi ${escapeHtml(item?.session_number ?? '-')}</p>
                                            <p class="mt-0.5 text-xs text-slate-400">${escapeHtml(startTime)} - ${escapeHtml(endTime)}</p>
                                        </div>
                                    </div>

                                    <div class="flex w-full items-center gap-2 sm:w-auto">

                                        <button type="button" class="btn btn-sm flex-1 rounded-lg border border-emerald-600 bg-white text-emerald-600 
                                            hover:border-emerald-600 hover:bg-emerald-600 hover:text-white sm:flex-none ${manageStudentButtonClass}"
                                            data-session-id="${escapeHtml(item?.id ?? '')}"
                                            data-period-id="${isOverride ? periodOverrideId : itemPeriodId}">
                                            <i class="fa-solid fa-users"></i>
                                            <span>Kelola Siswa</span>
                                        </button>

                                        <button type="button" class="btn btn-sm flex-1 rounded-lg border-blue-200 bg-blue-50 px-3 text-blue-600 hover:border-blue-600
                                            hover:bg-blue-600 hover:text-white sm:flex-none ${editButtonClass}"
                                            data-session-id="${escapeHtml(item?.id ?? '')}"
                                            data-period-id="${escapeHtml(itemPeriodId)}"
                                            data-period-override-id="${escapeHtml(periodOverrideId)}"
                                            data-session-date="${escapeHtml(sessionDate)}"
                                            data-start-date="${escapeHtml(startDate)}"
                                            data-end-date="${escapeHtml(endDate)}"
                                            data-start-time="${escapeHtml(item?.start_time ?? '')}"
                                            data-end-time="${escapeHtml(item?.end_time ?? '')}"
                                            data-session-number="${escapeHtml(item?.session_number ?? '')}"
                                            data-period-number="${escapeHtml(periodNumber)}"
                                            data-academic-year="${escapeHtml(academicYear)}"
                                            data-school-name="${escapeHtml(schoolName)}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            <span>Edit</span>
                                        </button>

                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `);
    });
}

function paginateTryoutTKAPeriodSession(periodId) {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container || !periodId) return;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/session/${periodId}/paginate`,
        method: 'GET',
        beforeSend: function () {
            $('#tka-tryout-session-list-skeleton').removeClass('hidden');
            $('#tka-tryout-session-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-session').addClass('hidden');
        },
        success: function (response) {
            renderTkaTryoutSessionList(response, {
                listId: '#tka-tryout-session-list',
                skeletonId: '#tka-tryout-session-list-skeleton',
                emptyId: '#empty-message-tka-tryout-session',
                totalId: '#tka-tryout-session-total',
                isOverride: false
            });
        },
        error: function (err) {
            $('#tka-tryout-session-list-skeleton').addClass('hidden');
            $('#tka-tryout-session-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-session').removeClass('hidden');
            $('#tka-tryout-session-total').text('0 Sesi');

            console.log(err);
        }
    });
}

function paginateTryoutTKAPeriodSessionOverride(periodOverrideId) {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container || !periodOverrideId) return;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/session/override/${periodOverrideId}/paginate`,
        method: 'GET',
        beforeSend: function () {
            $('#tka-tryout-override-session-list-skeleton').removeClass('hidden');
            $('#tka-tryout-override-session-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-override-session').addClass('hidden');
        },
        success: function (response) {
            $('#tka-tryout-override-session-list-skeleton').addClass('hidden');
            renderTkaTryoutSessionList(response, {
                listId: '#tka-tryout-override-session-list',
                skeletonId: '#tka-tryout-override-session-list-skeleton',
                emptyId: '#empty-message-tka-tryout-override-session',
                totalId: '#tka-tryout-override-session-total',
                isOverride: true
            });
        },
        error: function (err) {
            $('#tka-tryout-override-session-list-skeleton').addClass('hidden');
            $('#tka-tryout-override-session-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-override-session').removeClass('hidden');
            $('#tka-tryout-override-session-total').text('0 Sesi');

            console.log(err);
        }
    });
}

$(document).off('click', '.btn-tka-tryout-manage-students, .btn-tka-tryout-override-manage-students').on('click', '.btn-tka-tryout-manage-students, .btn-tka-tryout-override-manage-students', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;

    if (!role) return;

    const sessionId = $(this).data('session-id');
    const periodId = $(this).data('period-id');

    if (!sessionId || !periodId) return;

    window.location.href = `/lms/${role}/tka-tryout-management/session-form/${periodId}/manage-students/${sessionId}`;
}); 

// BTN EDIT SESSION NON OVERRIDE
$(document).off('click', '.btn-edit-tka-tryout-session').on('click', '.btn-edit-tka-tryout-session', function (e) {
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

    const modalTkaTryoutSession = document.getElementById('modal-tka-tryout-session');
    const editSessionModal = document.getElementById('modal-edit-tka-tryout-session');

    if (!editSessionModal) return;

    modalTkaTryoutSession?.close();

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

    enableFlatpickrEdit(startDate, endDate, sessionDate, startTime, endTime);
});

function enableFlatpickrEdit(startDate, endDate, sessionDateValue, startTimeValue, endTimeValue) {
    const sessionDate = document.getElementById('edit-session-date');
    const startTime = document.getElementById('edit-start-time');
    const endTime = document.getElementById('edit-end-time');

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

                const error = document.getElementById('error-edit-session_date');

                if (error) {
                    error.textContent = '';
                }

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

                const error = document.getElementById('error-edit-start_time');

                if (error) {
                    error.textContent = '';
                }

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

                const error = document.getElementById('error-edit-end_time');

                if (error) {
                    error.textContent = '';
                }

                $(endTime).removeClass('border-red-400');
            }
        });
    }
}