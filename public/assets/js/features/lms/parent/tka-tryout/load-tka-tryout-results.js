let tkaTryoutResultsData = null;
let tkaTryoutResultsSelectedPeriodId = null;

function loadTkaTryoutResults() {
    const container = document.getElementById('container');
    const resultsContainer = document.getElementById('tka-tryout-results-container');

    if (!container || !resultsContainer) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const studentId = container.dataset.studentId;

    if (!role || !schoolName || !schoolId || !studentId) {
        resultsContainer.innerHTML = renderTkaTryoutResultsEmpty();
        return;
    }

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/parent/tka-tryout-monitoring/student/${studentId}/load-result`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (!response || response.success === false) {
                tkaTryoutResultsData = null;
                tkaTryoutResultsSelectedPeriodId = null;
                resultsContainer.innerHTML = renderTkaTryoutResultsEmpty();
                return;
            }

            tkaTryoutResultsData = response;

            const periods = getTkaTryoutResultPeriods(response);

            if (!periods.length) {
                tkaTryoutResultsSelectedPeriodId = null;
                resultsContainer.innerHTML = renderTkaTryoutResultsEmpty();
                return;
            }

            const sortedPeriods = sortTkaTryoutPeriods(periods);

            const selectedExists = sortedPeriods.some(function (period) {
                return String(period.id) === String(tkaTryoutResultsSelectedPeriodId);
            });

            if (!selectedExists) {
                tkaTryoutResultsSelectedPeriodId = sortedPeriods[0].id;
            }

            renderTkaTryoutResults();
        },
        error: function (xhr) {
            console.error('Failed to load TKA tryout results:', xhr);
            console.error('STATUS:', xhr.status);
            console.error('RESPONSE:', xhr.responseText);
            console.error('JSON:', xhr.responseJSON);

            tkaTryoutResultsData = null;
            tkaTryoutResultsSelectedPeriodId = null;

            resultsContainer.innerHTML = renderTkaTryoutResultsError();
        }
    });
}

function renderTkaTryoutResults() {
    const container = document.getElementById('tka-tryout-results-container');

    if (!container) return;

    const periods = getTkaTryoutResultPeriods(tkaTryoutResultsData);

    if (!periods.length) {
        container.innerHTML = renderTkaTryoutResultsEmpty();
        return;
    }

    let selectedPeriod = periods.find(function (period) {
        return String(period.id) === String(tkaTryoutResultsSelectedPeriodId);
    });

    if (!selectedPeriod) {
        selectedPeriod = sortTkaTryoutPeriods(periods)[0];
        tkaTryoutResultsSelectedPeriodId = selectedPeriod.id;
    }

    container.innerHTML = renderTkaTryoutResultsCard(
        selectedPeriod,
        periods
    );
}

function getTkaTryoutResultPeriods(response) {
    if (!response) return [];

    if (Array.isArray(response.periods)) {
        return response.periods;
    }

    if (response.data && Array.isArray(response.data.periods)) {
        return response.data.periods;
    }

    if (Array.isArray(response.data)) {
        return response.data;
    }

    return [];
}

function sortTkaTryoutPeriods(periods) {
    return [...periods].sort(function (a, b) {
        const periodA = Number(a.period_number || a.number || 0);
        const periodB = Number(b.period_number || b.number || 0);

        if (periodA !== periodB) {
            return periodB - periodA;
        }

        return String(b.start_date || '').localeCompare(
            String(a.start_date || '')
        );
    });
}

function getTkaTryoutResultSessions(period) {
    if (!period) return [];

    let sessions = [];

    if (Array.isArray(period.sessions)) {
        sessions = [...period.sessions];
    } else if (Array.isArray(period.results)) {
        sessions = [...period.results];
    } else if (Array.isArray(period.subjects)) {
        sessions = [...period.subjects];
    }

    return sessions.sort(function (a, b) {
        const dayA = Number(a.day_number || a.day || a.sequence || 0);
        const dayB = Number(b.day_number || b.day || b.sequence || 0);

        if (dayA !== dayB) {
            return dayA - dayB;
        }

        const dateA = String(
            a.date_iso ||
            a.session_date ||
            a.exam_date ||
            a.date ||
            ''
        );

        const dateB = String(
            b.date_iso ||
            b.session_date ||
            b.exam_date ||
            b.date ||
            ''
        );

        return dateA.localeCompare(dateB);
    });
}

function renderTkaTryoutResultsCard(period, periods) {
    const sessions = getTkaTryoutResultSessions(period);
    const periodNumber = period.period_number || period.number || '-';
    const title = period.title || `Tryout TKA Periode ${periodNumber}`;
    const dateRange = period.date_range || period.date || '-';

    const totalSubjects = Number(
        period.total_subjects ||
        period.subject_count ||
        sessions.length ||
        0
    );

    const completedSubjects = getTkaTryoutCompletedSubjects(sessions);
    const averageScore = getTkaTryoutAverageScore(period, sessions);
    const highestScore = getTkaTryoutHighestScore(period, sessions);
    const bestSubject = getTkaTryoutBestSubject(period, sessions);

    return `
        <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <i class="fa-solid fa-chart-column pointer-events-none absolute -right-6 -top-8 rotate-12 text-[110px] text-[#0071BC]/5 sm:text-[140px]"></i>
            <div class="relative z-10 p-5 sm:p-6 lg:p-7">
                ${renderTkaTryoutResultsPeriodSelector(periods)}

                <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EAF6FF] px-2.5 py-1 text-[10px] font-semibold text-[#0071BC] sm:text-xs">
                                <i class="fa-solid fa-chart-column text-[9px] sm:text-[10px]"></i>
                                Tersedia
                            </span>

                            <span class="text-[10px] text-slate-400 sm:text-xs">
                                Periode ${escapeTkaTryoutResultsHtml(periodNumber)}
                            </span>
                        </div>

                        <h3 class="mt-2 text-base font-bold leading-tight text-slate-800 sm:text-lg">
                            ${escapeTkaTryoutResultsHtml(title)}
                        </h3>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[10px] text-slate-500 sm:text-xs">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-[10px] text-slate-400"></i>
                                <span>${escapeTkaTryoutResultsHtml(dateRange)}</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group text-[10px] text-slate-400"></i>
                                <span>${totalSubjects} Mata Pelajaran</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 rounded-2xl border border-[#0071BC]/10 bg-[#EAF6FF]/60 px-4 py-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0071BC] text-white shadow-sm">
                            <i class="fa-solid fa-star text-sm"></i>
                        </div>

                        <div>
                            <p class="text-[10px] font-medium text-slate-400 sm:text-xs">
                                Nilai Rata-rata
                            </p>

                            <div class="mt-0.5 flex items-end gap-1.5">
                                <span class="text-2xl font-bold leading-none text-slate-800 sm:text-3xl">
                                    ${formatTkaTryoutScore(averageScore)}
                                </span>

                                <span class="mb-0.5 text-[10px] text-slate-400 sm:text-xs">
                                    / 100
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                ${renderTkaTryoutResultsOverview(
        averageScore,
        highestScore,
        bestSubject,
        completedSubjects,
        totalSubjects
    )}

                ${renderTkaTryoutResultsSessions(sessions)}

                <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-blue-100 bg-blue-50/60 p-3.5">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-[#0071BC] shadow-sm">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-slate-700 sm:text-xs">
                            Hasil tryout diperbarui berdasarkan pengerjaan anak
                        </p>

                        <p class="mt-0.5 text-[10px] leading-4 text-slate-500 sm:text-xs">
                            Semua mata pelajaran yang telah di-assign ke anak akan tampil di sini.
                            Nilai akan muncul setelah anak menyelesaikan pengerjaan pada mata pelajaran tersebut.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderTkaTryoutResultsPeriodSelector(periods) {
    if (!Array.isArray(periods) || !periods.length) {
        return '';
    }

    const sortedPeriods = [...periods].sort(function (a, b) {
        const periodA = Number(a.period_number || a.number || 0);
        const periodB = Number(b.period_number || b.number || 0);

        if (periodA !== periodB) {
            return periodA - periodB;
        }

        return String(a.start_date || '').localeCompare(
            String(b.start_date || '')
        );
    });

    return `
        <div class="mb-5 overflow-x-auto pb-1">
            <div class="flex min-w-max items-center gap-2">
                ${sortedPeriods.map(function (period) {
        const isActive =
            String(period.id) === String(tkaTryoutResultsSelectedPeriodId);

        const periodNumber =
            period.period_number ||
            period.number ||
            '-';

        const sessions = getTkaTryoutResultSessions(period);
        const completedSubjects = getTkaTryoutCompletedSubjects(sessions);
        const totalSubjects = Number(
            period.total_subjects ||
            period.subject_count ||
            sessions.length ||
            0
        );

        const isComplete =
            totalSubjects > 0 &&
            completedSubjects >= totalSubjects;

        return `
                        <button
                            type="button"
                            data-tka-results-period="${escapeTkaTryoutResultsHtml(period.id)}"
                            class="inline-flex shrink-0 items-center gap-2 rounded-xl px-3.5 py-2 text-[10px] font-semibold transition-all duration-200 sm:text-xs cursor-pointer ${isActive
                ? 'bg-[#0071BC] text-white shadow-sm'
                : 'border border-slate-200 bg-white text-slate-500 hover:border-[#0071BC]/20 hover:bg-[#EAF6FF] hover:text-[#0071BC]'
            }"
                        >
                            <i class="fa-solid ${isComplete
                ? 'fa-circle-check'
                : 'fa-layer-group'
            } text-[9px]"></i>
                            <span>
                                Periode ${escapeTkaTryoutResultsHtml(periodNumber)}
                            </span>
                        </button>
                    `;
    }).join('')}
            </div>
        </div>
    `;
}

function renderTkaTryoutResultsOverview(
    averageScore,
    highestScore,
    bestSubject,
    completedSubjects,
    totalSubjects
) {
    return `
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5 sm:p-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EAF6FF] text-[#0071BC]">
                        <i class="fa-solid fa-chart-line text-xs"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] text-slate-400 sm:text-xs">
                            Rata-rata
                        </p>

                        <p class="mt-0.5 text-sm font-bold text-slate-800 sm:text-base">
                            ${formatTkaTryoutScore(averageScore)}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5 sm:p-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i class="fa-solid fa-arrow-up text-xs"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] text-slate-400 sm:text-xs">
                            Nilai Tertinggi
                        </p>

                        <p class="mt-0.5 text-sm font-bold text-slate-800 sm:text-base">
                            ${formatTkaTryoutScore(highestScore)}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-span-2 rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5 sm:col-span-1 sm:p-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i class="fa-solid fa-book-open text-xs"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] text-slate-400 sm:text-xs">
                            Mata Pelajaran Terbaik
                        </p>

                        <p class="mt-0.5 truncate text-sm font-bold text-slate-800 sm:text-base">
                            ${escapeTkaTryoutResultsHtml(bestSubject || '-')}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderTkaTryoutResultsSessions(sessions) {
    if (!sessions.length) {
        return `
            <div class="mt-6 rounded-2xl border border-slate-100 bg-slate-50/60 p-5 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    <i class="fa-regular fa-file-circle-xmark"></i>
                </div>

                <p class="mt-2 text-xs font-medium text-slate-500">
                    Belum ada sesi tryout yang di-assign.
                </p>
            </div>
        `;
    }

    return `
        <div class="mt-6">
            <div>
                <h4 class="text-xs font-bold text-slate-700 sm:text-sm">
                    Nilai Per Hari
                </h4>

                <p class="mt-0.5 text-[10px] text-slate-400 sm:text-xs">
                    Setiap hari terdiri dari satu mata pelajaran.
                </p>
            </div>

            <div class="mt-4 space-y-3">
                ${sessions.map(function (session, index) {
        return renderTkaTryoutResultSession(session, index);
    }).join('')}
            </div>
        </div>
    `;
}

function renderTkaTryoutResultSession(session, index) {
    const hasScore = hasTkaTryoutSessionScore(session);
    const score = getTkaTryoutSessionScore(session);

    const subject =
        session.subject ||
        session.subject_name ||
        session.mata_pelajaran ||
        '-';

    const date =
        session.date ||
        session.session_date ||
        session.exam_date ||
        '';

    const dayNumber =
        session.day_number ||
        session.day ||
        session.sequence ||
        index + 1;

    const subjectStyle = getTkaTryoutSubjectStyle(subject);

    const percentage = hasScore
        ? Math.max(0, Math.min(score, 100))
        : 0;

    const scoreLabel = hasScore
        ? formatTkaTryoutScore(score)
        : 0;

    const scoreColor = hasScore
        ? 'text-slate-800'
        : 'text-slate-400';

    const progressClass = hasScore
        ? subjectStyle.progress
        : 'bg-slate-200';

    const statusText = hasScore
        ? 'Sudah dikerjakan'
        : 'Belum dikerjakan';

    const statusClass = hasScore
        ? 'text-emerald-600'
        : 'text-slate-400';

    return `
        <div class="rounded-2xl border border-slate-100 bg-white p-3.5 transition-all duration-200 hover:border-[#0071BC]/10 hover:shadow-sm sm:p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${subjectStyle.iconBg} ${subjectStyle.iconColor}">
                    <i class="fa-solid ${subjectStyle.icon} text-xs"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400 sm:text-[10px]">
                                Hari ${escapeTkaTryoutResultsHtml(dayNumber)}
                                ${date ? ` • ${escapeTkaTryoutResultsHtml(date)}` : ''}
                            </p>

                            <p class="mt-0.5 truncate text-xs font-semibold text-slate-700 sm:text-sm">
                                ${escapeTkaTryoutResultsHtml(subject)}
                            </p>

                            <p class="mt-0.5 text-[9px] font-medium ${statusClass} sm:text-[10px]">
                                ${statusText}
                            </p>
                        </div>

                        <span class="shrink-0 text-sm font-bold ${scoreColor} sm:text-base">
                            ${scoreLabel}
                        </span>
                    </div>

                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full ${progressClass}"
                            style="width: ${percentage}%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getTkaTryoutCompletedSubjects(sessions) {
    if (!Array.isArray(sessions) || !sessions.length) {
        return 0;
    }

    return sessions.filter(function (session) {
        return hasTkaTryoutSessionScore(session);
    }).length;
}

function hasTkaTryoutSessionScore(session) {
    if (!session) return false;

    const value =
        session.score ??
        session.nilai ??
        session.final_score ??
        session.result;

    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return false;
    }

    const score = Number(value);

    return Number.isFinite(score);
}

function getTkaTryoutSessionScore(session) {
    if (!hasTkaTryoutSessionScore(session)) {
        return null;
    }

    const value =
        session.score ??
        session.nilai ??
        session.final_score ??
        session.result;

    const score = Number(value);

    return Number.isFinite(score) ? score : null;
}

function getTkaTryoutAverageScore(period, sessions) {
    const directValue =
        period?.average_score ??
        period?.average ??
        period?.avg_score ??
        period?.rata_rata;

    if (
        directValue !== undefined &&
        directValue !== null &&
        directValue !== ''
    ) {
        const score = Number(directValue);

        if (Number.isFinite(score)) {
            return score;
        }
    }

    const scoredSessions = (sessions || [])
        .filter(function (session) {
            return hasTkaTryoutSessionScore(session);
        })
        .map(function (session) {
            return getTkaTryoutSessionScore(session);
        })
        .filter(function (score) {
            return Number.isFinite(score);
        });

    if (!scoredSessions.length) {
        return null;
    }

    return scoredSessions.reduce(function (total, score) {
        return total + score;
    }, 0) / scoredSessions.length;
}

function getTkaTryoutHighestScore(period, sessions) {
    const directValue =
        period?.highest_score ??
        period?.highest ??
        period?.max_score ??
        period?.nilai_tertinggi;

    if (
        directValue !== undefined &&
        directValue !== null &&
        directValue !== ''
    ) {
        const score = Number(directValue);

        if (Number.isFinite(score)) {
            return score;
        }
    }

    const scoredSessions = (sessions || [])
        .filter(function (session) {
            return hasTkaTryoutSessionScore(session);
        })
        .map(function (session) {
            return getTkaTryoutSessionScore(session);
        })
        .filter(function (score) {
            return Number.isFinite(score);
        });

    if (!scoredSessions.length) {
        return null;
    }

    return Math.max.apply(null, scoredSessions);
}

function getTkaTryoutBestSubject(period, sessions) {
    const directSubject =
        period?.best_subject ??
        period?.best_subject_name ??
        period?.subject_best ??
        period?.mata_pelajaran_terbaik;

    if (directSubject) {
        if (typeof directSubject === 'object') {
            return (
                directSubject.name ||
                directSubject.subject_name ||
                directSubject.mata_pelajaran ||
                '-'
            );
        }

        return String(directSubject);
    }

    const scoredSessions = (sessions || []).filter(function (session) {
        return hasTkaTryoutSessionScore(session);
    });

    if (!scoredSessions.length) {
        return '-';
    }

    const bestSession = [...scoredSessions].sort(function (a, b) {
        return (
            getTkaTryoutSessionScore(b) -
            getTkaTryoutSessionScore(a)
        );
    })[0];

    return (
        bestSession.subject ||
        bestSession.subject_name ||
        bestSession.mata_pelajaran ||
        '-'
    );
}

function getTkaTryoutSubjectStyle(subject) {
    const name = String(subject || '').toLowerCase();

    if (name.includes('indonesia')) {
        return {
            icon: 'fa-book',
            iconBg: 'bg-emerald-50',
            iconColor: 'text-emerald-600',
            progress: 'bg-emerald-500'
        };
    }

    if (name.includes('matematika') || name.includes('math')) {
        return {
            icon: 'fa-calculator',
            iconBg: 'bg-[#EAF6FF]',
            iconColor: 'text-[#0071BC]',
            progress: 'bg-[#0071BC]'
        };
    }

    if (name.includes('inggris') || name.includes('english')) {
        return {
            icon: 'fa-language',
            iconBg: 'bg-indigo-50',
            iconColor: 'text-indigo-600',
            progress: 'bg-indigo-500'
        };
    }

    if (
        name.includes('ipa') ||
        name.includes('fisika') ||
        name.includes('kimia') ||
        name.includes('biologi')
    ) {
        return {
            icon: 'fa-flask',
            iconBg: 'bg-amber-50',
            iconColor: 'text-amber-600',
            progress: 'bg-amber-500'
        };
    }

    if (
        name.includes('ips') ||
        name.includes('ekonomi') ||
        name.includes('sosiologi') ||
        name.includes('geografi') ||
        name.includes('sejarah')
    ) {
        return {
            icon: 'fa-earth-asia',
            iconBg: 'bg-orange-50',
            iconColor: 'text-orange-600',
            progress: 'bg-orange-500'
        };
    }

    return {
        icon: 'fa-book-open',
        iconBg: 'bg-slate-100',
        iconColor: 'text-slate-500',
        progress: 'bg-slate-500'
    };
}

function formatTkaTryoutScore(score) {
    if (
        score === null ||
        score === undefined ||
        score === ''
    ) {
        return 0;
    }

    const value = Number(score);

    if (!Number.isFinite(value)) {
        return 0;
    }

    if (Number.isInteger(value)) {
        return String(value);
    }

    return value.toFixed(1);
}

function renderTkaTryoutResultsEmpty() {
    return `
        <div class="flex flex-col items-center justify-center rounded-3xl border border-slate-200 bg-white px-5 py-10 text-center shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <i class="fa-solid fa-chart-column"></i>
            </div>

            <p class="mt-3 text-sm font-semibold text-slate-700">
                Belum ada hasil tryout
            </p>

            <p class="mt-1 max-w-sm text-xs leading-5 text-slate-400">
                Jadwal dan hasil tryout TKA akan tampil di sini setelah anak Anda mendapatkan assignment sesi tryout.
            </p>
        </div>
    `;
}

function renderTkaTryoutResultsError() {
    return `
        <div class="flex flex-col items-center justify-center rounded-3xl border border-red-100 bg-white px-5 py-10 text-center shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-400">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <p class="mt-3 text-sm font-semibold text-slate-700">
                Gagal memuat hasil tryout
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Silakan coba muat ulang halaman.
            </p>

            <button
                type="button"
                id="tka-tryout-results-retry"
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#0071BC] px-3.5 py-2 text-[10px] font-semibold text-white shadow-sm transition-all hover:bg-[#005f9e] sm:text-xs"
            >
                <i class="fa-solid fa-rotate-right text-[9px]"></i>
                Coba Lagi
            </button>
        </div>
    `;
}

function escapeTkaTryoutResultsHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

$(document).on('click', '[data-tka-results-period]', function () {
    const periodId = $(this).attr('data-tka-results-period');

    if (
        !periodId ||
        String(periodId) === String(tkaTryoutResultsSelectedPeriodId)
    ) {
        return;
    }

    tkaTryoutResultsSelectedPeriodId = periodId;

    const container = document.getElementById(
        'tka-tryout-results-container'
    );

    if (!container) return;

    container.classList.add('opacity-50');

    setTimeout(function () {
        renderTkaTryoutResults();
        container.classList.remove('opacity-50');
    }, 120);
});

$(document).on('click', '#tka-tryout-results-retry', function () {
    loadTkaTryoutResults();
});

$(document).ready(function () {
    loadTkaTryoutResults();
});