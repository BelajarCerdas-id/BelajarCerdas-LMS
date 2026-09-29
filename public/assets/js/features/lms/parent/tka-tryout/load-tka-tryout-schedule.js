let tkaTryoutScheduleShowHistory = false;
let tkaTryoutScheduleData = null;

function loadTkaTryoutSchedule() {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const studentId = container.dataset.studentId;

    const skeleton = $('#tka-tryout-schedule-skeleton');
    const content = $('#tka-tryout-schedule-content');

    skeleton.removeClass('hidden');
    content.addClass('hidden').empty();

    if (!role || !schoolName || !schoolId || !studentId) {
        renderTkaTryoutScheduleEmpty();

        skeleton.addClass('hidden');
        content.removeClass('hidden');

        return;
    }

    const url = `/lms/${encodeURIComponent(role)}/${encodeURIComponent(schoolName)}/${encodeURIComponent(schoolId)}/parent/tka-tryout-monitoring/student/${encodeURIComponent(studentId)}/load-schedule`;

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        headers: {
            'Accept': 'application/json'
        },
        success: function (response) {
            tkaTryoutScheduleData = response;
            tkaTryoutScheduleShowHistory = false;

            renderTkaTryoutSchedule(response);

            skeleton.addClass('hidden');
            content.removeClass('hidden');
        },
        error: function (xhr) {
            console.error('Load TKA tryout schedule error:', {
                status: xhr.status,
                response: xhr.responseJSON || xhr.responseText
            });

            tkaTryoutScheduleData = null;

            renderTkaTryoutScheduleEmpty();

            skeleton.addClass('hidden');
            content.removeClass('hidden');
        }
    });
}

function renderTkaTryoutSchedule(response) {
    const content = $('#tka-tryout-schedule-content');

    if (!response || !Array.isArray(response.periods) || response.periods.length === 0) {
        renderTkaTryoutScheduleEmpty();
        return;
    }

    const activePeriod = response.periods.find(period => period.status === 'active') || response.periods.find(period => period.status === 'upcoming') || response.periods[0];

    const periods = tkaTryoutScheduleShowHistory ? response.periods : [activePeriod];

    if (!periods.length) {
        renderTkaTryoutScheduleEmpty();
        return;
    }

    content.html(renderTkaTryoutScheduleWrapper(periods, activePeriod, response));
}

function renderTkaTryoutScheduleWrapper(periods, activePeriod, response) {
    return `
        <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <i class="fa-solid fa-calendar-days pointer-events-none absolute -right-6 -top-8 rotate-12 text-[110px] text-[#0071BC]/5 sm:text-[140px]"></i>

            <div class="relative z-10 p-5 sm:p-6 lg:p-7">
                ${renderTkaTryoutScheduleHeader(activePeriod, response)}

                <div class="mt-6 max-h-130 overflow-y-auto pr-1 sm:max-h-150 lg:max-h-162.5">
                    ${renderTkaTryoutScheduleTimeline(periods)}
                </div>

                ${renderTkaTryoutScheduleFooter(response)}
            </div>
        </div>
    `;
}

function renderTkaTryoutScheduleHeader(activePeriod, response) {
    const isHistory = tkaTryoutScheduleShowHistory;
    const timezoneLabel = response?.timezone_label || '';

    return `
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EAF6FF] px-2.5 py-1 text-[10px] font-semibold text-[#0071BC] sm:text-xs">
                        <i class="fa-solid fa-clipboard-list text-[9px] sm:text-[10px]"></i>
                        ${isHistory ? 'Riwayat Tryout' : 'Tryout Aktif'}
                    </span>

                    <span class="text-[10px] text-slate-400 sm:text-xs">
                        ${isHistory ? `${response.periods.length} Periode` : `Periode ${activePeriod.period_number}`}
                    </span>
                </div>

                <h3 class="mt-2 text-base font-bold leading-tight text-slate-800 sm:text-lg">
                    ${isHistory ? 'Riwayat Jadwal Tryout' : escapeTkaTryoutScheduleHtml(activePeriod.title)}
                </h3>

                <p class="mt-1 text-[10px] leading-4 text-slate-400 sm:text-xs">
                    ${isHistory ? 'Lihat seluruh riwayat jadwal pelaksanaan tryout TKA anak Anda.' : 'Jadwal pelaksanaan tryout TKA anak Anda.'}
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:items-end">
                <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-3.5 py-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-[#0071BC] shadow-sm">
                        <i class="fa-regular fa-calendar text-xs"></i>
                    </div>

                    <div>
                        <p class="text-[9px] font-medium uppercase tracking-wide text-slate-400">
                            ${isHistory ? 'Periode Terbaru' : 'Periode'}
                        </p>

                        <p class="mt-0.5 text-xs font-semibold text-slate-700">
                            ${escapeTkaTryoutScheduleHtml(activePeriod.date_range)}
                        </p>

                        ${timezoneLabel ? `
                            <p class="mt-0.5 text-[9px] font-medium text-slate-400">
                                Waktu ${escapeTkaTryoutScheduleHtml(timezoneLabel)}
                            </p>
                        ` : ''}
                    </div>
                </div>

                <button type="button" id="tka-tryout-schedule-toggle" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border 
                    border-slate-200 bg-white px-3.5 py-2.5 text-[10px] font-semibold text-slate-600 shadow-sm transition-all duration-200 
                    hover:border-[#0071BC]/30 hover:bg-[#EAF6FF] hover:text-[#0071BC] sm:w-auto sm:text-xs cursor-pointer">
                    
                    <i class="fa-solid ${isHistory ? 'fa-arrow-left' : 'fa-clock-rotate-left'} text-[10px]"></i>
                    <span>${isHistory ? 'Kembali ke Periode Aktif' : 'Lihat Riwayat Jadwal'}</span>
                </button>
            </div>
        </div>
    `;
}

function renderTkaTryoutScheduleTimeline(periods) {
    if (!Array.isArray(periods) || periods.length === 0) {
        return `
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <p class="mt-3 text-xs font-medium text-slate-500">
                    Belum ada jadwal sesi tryout.
                </p>
            </div>
        `;
    }

    const items = [];

    periods.forEach(function (period, periodIndex) {
        items.push({
            type: 'period',
            period: period
        });

        if (Array.isArray(period.sessions) && period.sessions.length > 0) {
            const sortedSessions = [...period.sessions].sort(function (a, b) {
                const dateA = a.session_date || '';
                const dateB = b.session_date || '';

                if (dateA !== dateB) {
                    return dateB.localeCompare(dateA);
                }

                const timeA = a.start_time || '';
                const timeB = b.start_time || '';

                return timeB.localeCompare(timeA);
            });

            sortedSessions.forEach(function (session, sessionIndex) {
                items.push({
                    type: 'session',
                    period: period,
                    session: session,
                    isLast: periodIndex === periods.length - 1 && sessionIndex === sortedSessions.length - 1
                });
            });
        }
    });

    return `
        <div class="relative">
            <div class="absolute bottom-0 left-4 top-5 w-px bg-slate-200 sm:left-4.5"></div>

            <div class="relative">
                ${items.map(function (item) {
        if (item.type === 'period') {
            return renderTkaTryoutSchedulePeriodMarker(item.period);
        }

        return renderTkaTryoutScheduleSession(
            item.session,
            item.isLast
        );
    }).join('')}
            </div>
        </div>
    `;
}

function renderTkaTryoutSchedulePeriodMarker(period) {
    const isActive = period.status === 'active';
    const isUpcoming = period.status === 'upcoming';

    let statusText = 'Selesai';

    if (isActive) {
        statusText = 'Aktif';
    } else if (isUpcoming) {
        statusText = 'Akan Datang';
    }

    const markerClass = isActive ? 'bg-[#0071BC] text-white' : isUpcoming ? 'bg-amber-400 text-white' : 'bg-slate-300 text-white';
    const badgeClass = isActive ? 'bg-[#EAF6FF] text-[#0071BC]' : isUpcoming ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500';

    return `
        <div class="relative flex gap-3.5 pb-4 sm:gap-4">
            <div class="relative z-10 flex w-8 shrink-0 justify-center sm:w-9">
                <div class="flex h-8 w-8 items-center justify-center rounded-full border-4 border-white ${markerClass} shadow-sm sm:h-9 sm:w-9">
                    <i class="fa-solid fa-flag text-[9px] sm:text-[10px]"></i>
                </div>
            </div>

            <div class="min-w-0 flex-1 pt-0.5">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 flex-wrap items-center gap-2">
                        <h4 class="text-xs font-bold text-slate-800 sm:text-sm">
                            ${escapeTkaTryoutScheduleHtml(period.title)}
                        </h4>

                        <span class="rounded-full ${badgeClass} px-2 py-0.5 text-[9px] font-semibold sm:text-[10px]">
                            ${statusText}
                        </span>
                    </div>

                    <span class="shrink-0 text-[10px] font-medium text-slate-400 sm:text-xs">
                        ${escapeTkaTryoutScheduleHtml(period.date_range)}
                    </span>
                </div>
            </div>
        </div>
    `;
}

function renderTkaTryoutScheduleSession(session, isLast) {
    const config = getTkaTryoutScheduleStatusConfig(session.status);
    const isProgress = ['passed', 'ongoing'].includes(session.status);

    return `
        <div class="relative flex gap-3.5 sm:gap-4">
            <div class="relative z-10 flex w-8 shrink-0 flex-col items-center sm:w-9">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-4 border-white ${config.circle} sm:h-9 sm:w-9">
                    <i class="fa-solid ${config.icon} text-[11px] sm:text-xs"></i>
                </div>

                <div class="w-px ${isProgress ? 'bg-[#0071BC]' : 'bg-slate-200'} ${isLast ? 'h-full' : 'flex-1'}"></div>
            </div>

            <div class="min-w-0 flex-1 ${isLast ? 'pb-1' : 'pb-5'}">
                <div class="rounded-2xl border ${config.card} p-4 transition-all sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 sm:text-xs">
                                    ${escapeTkaTryoutScheduleHtml(session.date)}
                                </span>

                                <span class="rounded-full ${config.badge} px-2 py-0.5 text-[9px] font-semibold sm:text-[10px]">
                                    ${config.badgeText}
                                </span>
                            </div>

                            <h4 class="mt-1.5 text-sm font-bold text-slate-800 sm:text-base">
                                ${escapeTkaTryoutScheduleHtml(session.subject)}
                            </h4>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[10px] text-slate-500 sm:text-xs">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[10px] text-slate-400"></i>
                                    <span>
                                        ${escapeTkaTryoutScheduleHtml(session.start_time)}
                                        -
                                        ${escapeTkaTryoutScheduleHtml(session.end_time)}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-list-ol text-[10px] text-slate-400"></i>
                                    <span>
                                        ${Number(session.total_question || 0)} Soal
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-stopwatch text-[10px] text-slate-400"></i>
                                    <span>
                                        ${Number(session.duration || 0)} Menit
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1.5 text-[10px] font-semibold ${config.action} sm:text-xs">
                            ${config.actionIcon ? `<i class="fa-solid ${config.actionIcon}"></i>` : '<span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#0071BC]"></span>'}
                            ${config.actionText}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getTkaTryoutScheduleStatusConfig(status) {
    const configs = {
        ongoing: {
            icon: 'fa-play',
            circle: 'bg-[#0071BC] text-white shadow-md',
            card: 'border-[#0071BC]/20 bg-[#EAF6FF]/50 shadow-sm',
            badge: 'bg-[#0071BC]/10 text-[#0071BC]',
            badgeText: 'Sedang Berlangsung',
            action: 'text-[#0071BC]',
            actionIcon: null,
            actionText: 'Sedang Berlangsung'
        },
        upcoming: {
            icon: 'fa-hourglass-half',
            circle: 'border-2 border-amber-200 bg-amber-50 text-amber-500 shadow-sm',
            card: 'border-amber-100 bg-amber-50/40',
            badge: 'bg-amber-100 text-amber-600',
            badgeText: 'Akan Datang',
            action: 'text-amber-600',
            actionIcon: 'fa-hourglass-half',
            actionText: 'Belum Dimulai'
        },
        passed: {
            icon: 'fa-check',
            circle: 'bg-emerald-500 text-white shadow-sm',
            card: 'border-emerald-100 bg-emerald-50/50',
            badge: 'bg-emerald-100 text-emerald-600',
            badgeText: 'Selesai',
            action: 'text-emerald-600',
            actionIcon: 'fa-circle-check',
            actionText: 'Sesi Telah Selesai'
        }
    };

    return configs[status] || configs.upcoming;
}

function renderTkaTryoutScheduleFooter(response) {
    const timezoneLabel = response?.timezone_label || '';

    return `
        <div class="mt-6 border-t border-slate-100 pt-5">
            <div class="flex items-start gap-2.5">
                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[#0071BC]">
                    <i class="fa-solid fa-circle-info text-xs"></i>
                </div>

                <p class="text-[10px] leading-4 text-slate-500 sm:text-xs">
                    Jadwal mengikuti waktu yang telah ditentukan oleh penyelenggara tryout.
                    Pastikan anak mengikuti setiap sesi sesuai jadwal.
                    ${timezoneLabel ? `Waktu mengikuti zona ${escapeTkaTryoutScheduleHtml(timezoneLabel)} sekolah.` : ''}
                </p>
            </div>
        </div>
    `;
}

function renderTkaTryoutScheduleEmpty() {
    $('#tka-tryout-schedule-content').html(`
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col items-center justify-center px-5 py-10 text-center sm:px-6 sm:py-12">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <i class="fa-regular fa-calendar-xmark text-lg"></i>
                </div>

                <h3 class="mt-4 text-sm font-bold text-slate-800 sm:text-base">
                    Belum Ada Jadwal Tryout
                </h3>

                <p class="mt-1.5 max-w-md text-xs leading-5 text-slate-500 sm:text-sm">
                    Belum terdapat jadwal tryout TKA yang terdaftar untuk anak Anda.
                </p>
            </div>
        </div>
    `);
}

function escapeTkaTryoutScheduleHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;') .replace(/>/g, '&gt;') .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

$(document).on('click', '#tka-tryout-schedule-toggle', function () {
    if (!tkaTryoutScheduleData || !Array.isArray(tkaTryoutScheduleData.periods) || tkaTryoutScheduleData.periods.length === 0) {
        return;
    }

    tkaTryoutScheduleShowHistory = !tkaTryoutScheduleShowHistory;

    const content = $('#tka-tryout-schedule-content');
    content.addClass('opacity-0');

    setTimeout(function () {
        renderTkaTryoutSchedule(tkaTryoutScheduleData);

        content.removeClass('hidden');

        requestAnimationFrame(function () {
            content.removeClass('opacity-0');
        });
    }, 150);
});

$(document).ready(function () {
    $('#tka-tryout-schedule-content').addClass('transition-opacity duration-150');
    loadTkaTryoutSchedule();
});