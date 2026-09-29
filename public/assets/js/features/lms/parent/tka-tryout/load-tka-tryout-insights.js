let tkaTryoutInsightData = null;
let tkaTryoutInsightDetailOpen = false;

function loadTkaTryoutInsight() {
    const container = document.getElementById('container');
    const insightContainer = document.getElementById('tka-tryout-insight-container');

    if (!container || !insightContainer) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const studentId = container.dataset.studentId;

    if (!role || !schoolName || !schoolId || !studentId) {
        tkaTryoutInsightData = null;
        insightContainer.innerHTML = renderTkaTryoutInsightEmpty();
        return;
    }

    if (!tkaTryoutInsightData) {
        insightContainer.innerHTML = `
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                ${Array.from({ length: 3 }).map(() => `
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-200"></div>
                            <div class="min-w-0 flex-1">
                                <div class="h-3 w-24 animate-pulse rounded bg-slate-200"></div>
                                <div class="mt-2 h-4 w-40 animate-pulse rounded bg-slate-200"></div>
                                <div class="mt-3 h-3 w-full animate-pulse rounded bg-slate-100"></div>
                                <div class="mt-2 h-3 w-4/5 animate-pulse rounded bg-slate-100"></div>
                            </div>
                        </div>
                        <div class="mt-4 h-9 w-full animate-pulse rounded-xl bg-slate-100"></div>
                    </div>
                `).join('')}
            </div>
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
                    <div class="h-4 w-48 animate-pulse rounded bg-slate-200"></div>
                    <div class="mt-2 h-3 w-72 max-w-full animate-pulse rounded bg-slate-100"></div>
                </div>
                <div class="divide-y divide-slate-100">
                    ${Array.from({ length: 4 }).map(() => `
                        <div class="flex items-center gap-3 px-4 py-4 sm:px-5">
                            <div class="h-9 w-9 shrink-0 animate-pulse rounded-lg bg-slate-200"></div>
                            <div class="min-w-0 flex-1">
                                <div class="h-3 w-32 animate-pulse rounded bg-slate-200"></div>
                                <div class="mt-2 h-1.5 w-full animate-pulse rounded-full bg-slate-100"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    tkaTryoutInsightDetailOpen = false;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/parent/tka-tryout-monitoring/student/${studentId}/load-insight`,
        type: 'GET',
        dataType: 'json',
        headers: {
            'X-Timezone': Intl.DateTimeFormat().resolvedOptions().timeZone
        },
        success: function (response) {
            console.log('TKA TRYOUT INSIGHT RESPONSE:', response);

            if (!response || response.success === false || !response.data) {
                tkaTryoutInsightData = null;
                insightContainer.innerHTML = renderTkaTryoutInsightEmpty();
                return;
            }

            const data = buildTkaTryoutInsightData(response);

            if (!data || !data.sessions.length) {
                tkaTryoutInsightData = null;
                insightContainer.innerHTML = renderTkaTryoutInsightEmpty();
                return;
            }

            tkaTryoutInsightData = data;
            tkaTryoutInsightDetailOpen = false;
            renderTkaTryoutInsight();
        },
        error: function (xhr) {
            console.error('Failed to load TKA tryout insight:', xhr);
            console.error('STATUS:', xhr.status);
            console.error('RESPONSE:', xhr.responseText);
            console.error('JSON:', xhr.responseJSON);

            tkaTryoutInsightData = null;
            insightContainer.innerHTML = renderTkaTryoutInsightError();
        }
    });
}

function buildTkaTryoutInsightData(response) {
    const data = response && response.data ? response.data : null;

    if (!data) return null;

    const sessions = Array.isArray(data.sessions)
        ? data.sessions
            .slice()
            .sort(function (a, b) {
                const dateA = String(a.date_iso || '');
                const dateB = String(b.date_iso || '');

                if (dateA !== dateB) {
                    return dateB.localeCompare(dateA);
                }

                const sessionA = Number(a.session_number || 0);
                const sessionB = Number(b.session_number || 0);

                if (sessionA !== sessionB) {
                    return sessionB - sessionA;
                }

                return Number(b.id || 0) - Number(a.id || 0);
            })
        : [];

    if (!sessions.length) return null;

    const completedSessions = sessions
        .filter(function (session) {
            return getTkaTryoutSessionStatusMeta(session).key === 'completed' &&
                hasTkaTryoutSessionScore(session);
        })
        .sort(function (a, b) {
            const dateA = String(a.date_iso || '');
            const dateB = String(b.date_iso || '');

            if (dateA !== dateB) {
                return dateB.localeCompare(dateA);
            }

            const sessionA = Number(a.session_number || 0);
            const sessionB = Number(b.session_number || 0);

            if (sessionA !== sessionB) {
                return sessionB - sessionA;
            }

            return Number(b.id || 0) - Number(a.id || 0);
        });

    const latestSession = completedSessions.length
        ? completedSessions[0]
        : null;

    const previousSession = completedSessions.length > 1
        ? completedSessions[1]
        : null;

    const performance = data.performance || {};

    let latestAverage = null;
    let previousAverage = null;
    let performanceChange = null;

    if (
        performance.latest_average !== null &&
        typeof performance.latest_average !== 'undefined' &&
        Number.isFinite(Number(performance.latest_average))
    ) {
        latestAverage = Number(performance.latest_average);
    } else if (latestSession) {
        latestAverage = getTkaTryoutSessionScore(latestSession);
    }

    if (
        performance.previous_average !== null &&
        typeof performance.previous_average !== 'undefined' &&
        Number.isFinite(Number(performance.previous_average))
    ) {
        previousAverage = Number(performance.previous_average);
    } else if (previousSession) {
        previousAverage = getTkaTryoutSessionScore(previousSession);
    }

    if (
        previousAverage !== null &&
        latestAverage !== null &&
        Number.isFinite(latestAverage) &&
        Number.isFinite(previousAverage)
    ) {
        performanceChange =
            performance.difference !== null &&
                typeof performance.difference !== 'undefined'
                ? Number(performance.difference)
                : latestAverage - previousAverage;
    }

    const latestSubjects = Array.isArray(data.subjects)
        ? data.subjects
            .filter(function (subject) {
                return subject.latest_score !== null &&
                    typeof subject.latest_score !== 'undefined' &&
                    Number.isFinite(Number(subject.latest_score));
            })
            .map(function (subject) {
                return {
                    subjectId: subject.subject_id ?? null,
                    subjectName: subject.subject_name || 'Mata Pelajaran',
                    score: Number(subject.latest_score),
                    latestScore: Number(subject.latest_score),
                    latestDate: subject.latest_date || null,
                    latestDateIso: subject.latest_date_iso || null,
                    latestSessionId: subject.latest_session_id || null,
                    latestSessionNumber: subject.latest_session_number || null,
                    previousScore: subject.previous_score !== null &&
                        typeof subject.previous_score !== 'undefined'
                        ? Number(subject.previous_score)
                        : null,
                    previousDate: subject.previous_date || null,
                    previousDateIso: subject.previous_date_iso || null,
                    previousSessionId: subject.previous_session_id || null,
                    previousSessionNumber: subject.previous_session_number || null,
                    difference: subject.difference !== null &&
                        typeof subject.difference !== 'undefined'
                        ? Number(subject.difference)
                        : null,
                    status: subject.status || 'new',
                    history: Array.isArray(subject.history)
                        ? subject.history
                        : []
                };
            })
        : [];

    const comparisons = latestSubjects.map(function (subject) {
        const difference = subject.difference;

        if (difference === null || !Number.isFinite(difference)) {
            return {
                subjectId: subject.subjectId,
                subjectName: subject.subjectName,
                latestScore: subject.latestScore,
                previousScore: subject.previousScore,
                difference: null,
                status: 'new'
            };
        }

        let status = 'same';

        if (difference > 0) {
            status = 'up';
        } else if (difference < 0) {
            status = 'down';
        }

        return {
            subjectId: subject.subjectId,
            subjectName: subject.subjectName,
            latestScore: subject.latestScore,
            previousScore: subject.previousScore,
            difference: difference,
            status: status
        };
    });

    const positiveSubjects = comparisons.filter(function (item) {
        return item.status === 'up';
    });

    const attentionSubjects = comparisons.filter(function (item) {
        return item.status === 'down';
    });

    const bestSubject = latestSubjects.length
        ? [...latestSubjects].sort(function (a, b) {
            return b.score - a.score;
        })[0]
        : null;

    const weakestSubject = latestSubjects.length
        ? [...latestSubjects].sort(function (a, b) {
            return a.score - b.score;
        })[0]
        : null;

    return {
        student: response.student || null,
        school: response.school || null,
        timezone: response.timezone || null,
        sessions: sessions,
        completedSessions: completedSessions,
        periods: buildTkaTryoutInsightPeriods(sessions),
        latestSession: latestSession,
        previousSession: previousSession,
        latestPeriod: buildTkaTryoutInsightPeriodFromSession(latestSession),
        previousPeriod: previousSession
            ? buildTkaTryoutInsightPeriodFromSession(previousSession)
            : null,
        latestSubjects: latestSubjects,
        previousSubjects: previousSession
            ? buildTkaTryoutPreviousSubjects(sessions, previousSession)
            : [],
        latestAverage: latestAverage !== null &&
            Number.isFinite(latestAverage)
            ? latestAverage
            : 0,
        previousAverage: previousAverage !== null &&
            Number.isFinite(previousAverage)
            ? previousAverage
            : null,
        performanceChange: performanceChange !== null &&
            Number.isFinite(performanceChange)
            ? performanceChange
            : null,
        performanceStatus: performance.status || 'neutral',
        comparisons: comparisons,
        positiveSubjects: positiveSubjects,
        attentionSubjects: attentionSubjects,
        bestSubject: bestSubject,
        weakestSubject: weakestSubject
    };
}

function buildTkaTryoutInsightPeriods(sessions) {
    const periodMap = {};

    (Array.isArray(sessions) ? sessions : []).forEach(function (session) {
        const periodId = session.period_id ??
            `period-${session.period_number || 0}`;

        if (!periodMap[periodId]) {
            periodMap[periodId] = {
                id: session.period_id || null,
                period_number: session.period_number || null,
                title: session.period_title ||
                    `Tryout TKA Periode ${session.period_number || '-'}`,
                start_date: session.period_start_date ||
                    session.date_iso ||
                    null,
                end_date: session.period_end_date ||
                    session.date_iso ||
                    null,
                sessions: []
            };
        }

        periodMap[periodId].sessions.push(session);

        if (session.period_start_date) {
            if (
                !periodMap[periodId].start_date ||
                session.period_start_date < periodMap[periodId].start_date
            ) {
                periodMap[periodId].start_date = session.period_start_date;
            }
        } else if (session.date_iso) {
            if (
                !periodMap[periodId].start_date ||
                session.date_iso < periodMap[periodId].start_date
            ) {
                periodMap[periodId].start_date = session.date_iso;
            }
        }

        if (session.period_end_date) {
            if (
                !periodMap[periodId].end_date ||
                session.period_end_date > periodMap[periodId].end_date
            ) {
                periodMap[periodId].end_date = session.period_end_date;
            }
        } else if (session.date_iso) {
            if (
                !periodMap[periodId].end_date ||
                session.date_iso > periodMap[periodId].end_date
            ) {
                periodMap[periodId].end_date = session.date_iso;
            }
        }
    });

    return Object.values(periodMap).sort(function (a, b) {
        return Number(b.period_number || 0) -
            Number(a.period_number || 0);
    });
}

function buildTkaTryoutInsightPeriodFromSession(session) {
    if (!session) return null;

    return {
        id: session.period_id || null,
        period_number: session.period_number || null,
        title: session.period_title ||
            `Tryout TKA Periode ${session.period_number || '-'}`,
        start_date: session.period_start_date ||
            session.date_iso ||
            null,
        end_date: session.period_end_date ||
            session.date_iso ||
            null
    };
}

function buildTkaTryoutPreviousSubjects(sessions, previousSession) {
    if (!previousSession) return [];

    const periodId = previousSession.period_id;

    const previousSessions = (Array.isArray(sessions) ? sessions : [])
        .filter(function (session) {
            return hasTkaTryoutSessionScore(session) &&
                getTkaTryoutSessionStatusMeta(session).key === 'completed' &&
                (
                    periodId
                        ? Number(session.period_id) === Number(periodId)
                        : true
                );
        })
        .sort(function (a, b) {
            const dateA = String(a.date_iso || '');
            const dateB = String(b.date_iso || '');

            if (dateA !== dateB) {
                return dateB.localeCompare(dateA);
            }

            const sessionA = Number(a.session_number || 0);
            const sessionB = Number(b.session_number || 0);

            if (sessionA !== sessionB) {
                return sessionB - sessionA;
            }

            return Number(b.id || 0) - Number(a.id || 0);
        });

    const subjects = {};

    previousSessions.forEach(function (session) {
        const subjectId = session.subject_id ?? null;
        const subjectName = session.subject || 'Mata Pelajaran';
        const key = getTkaTryoutSubjectKey({
            subjectId: subjectId,
            subjectName: subjectName
        });

        if (!subjects[key]) {
            subjects[key] = {
                subjectId: subjectId,
                subjectName: subjectName,
                score: getTkaTryoutSessionScore(session)
            };
        }
    });

    return Object.values(subjects);
}

function renderTkaTryoutInsight() {
    const container = document.getElementById('tka-tryout-insight-container');

    if (!container || !tkaTryoutInsightData) return;

    container.innerHTML = tkaTryoutInsightDetailOpen
        ? renderTkaTryoutInsightDetail(tkaTryoutInsightData)
        : renderTkaTryoutInsightOverview(tkaTryoutInsightData);
}

function renderTkaTryoutInsightOverview(data) {
    const performance = getTkaTryoutInsightPerformance(data);
    const attention = getTkaTryoutAttentionSubject(data);
    const recommendation = getTkaTryoutRecommendation(data);

    return `
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            ${renderTkaTryoutInsightPerformanceCard(data, performance)}
            ${renderTkaTryoutInsightAttentionCard(data, attention)}
            ${renderTkaTryoutInsightRecommendationCard(data, recommendation)}
        </div>
        ${renderTkaTryoutInsightSubjectSummary(data)}
    `;
}

function renderTkaTryoutInsightPerformanceCard(data, performance) {
    const styles = {
        positive: {
            border: 'border-emerald-100',
            iconBg: 'bg-emerald-50',
            iconText: 'text-emerald-600',
            label: 'text-emerald-600',
            footerBg: 'bg-emerald-50',
            footerText: 'text-emerald-600',
            icon: 'fa-arrow-trend-up'
        },
        attention: {
            border: 'border-amber-100',
            iconBg: 'bg-amber-50',
            iconText: 'text-amber-600',
            label: 'text-amber-600',
            footerBg: 'bg-amber-50',
            footerText: 'text-amber-600',
            icon: 'fa-arrow-trend-down'
        },
        neutral: {
            border: 'border-slate-200',
            iconBg: 'bg-slate-100',
            iconText: 'text-slate-500',
            label: 'text-slate-500',
            footerBg: 'bg-slate-50',
            footerText: 'text-slate-600',
            icon: 'fa-chart-line'
        }
    };

    const style = styles[performance.type] || styles.neutral;
    const latestPeriodName = getTkaTryoutPeriodLabel(data.latestPeriod);

    let footerValue = 'Belum ada perbandingan';
    let footerIcon = 'fa-minus';

    if (data.performanceChange !== null) {
        footerValue = formatTkaTryoutDifference(data.performanceChange);

        if (data.performanceChange > 0) {
            footerIcon = 'fa-arrow-up';
        } else if (data.performanceChange < 0) {
            footerIcon = 'fa-arrow-down';
        }
    }

    return `
        <div class="rounded-2xl border ${style.border} bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconBg} ${style.iconText}">
                    <i class="fa-solid ${style.icon} text-sm"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold uppercase tracking-wider ${style.label} sm:text-[11px]">
                        ${escapeTkaTryoutResultsHtml(performance.label)}
                    </span>
                    <h3 class="mt-1 text-sm font-bold text-slate-800 sm:text-base">
                        ${escapeTkaTryoutResultsHtml(performance.title)}
                    </h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500 sm:text-sm">
                        ${performance.description}
                    </p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between rounded-xl ${style.footerBg} px-3 py-2.5">
                <span class="text-xs font-medium text-slate-600">
                    ${data.performanceChange !== null
            ? 'Perubahan nilai'
            : 'Tryout terakhir'}
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-bold ${style.footerText}">
                    ${data.performanceChange !== null
            ? `<i class="fa-solid ${footerIcon} text-[9px]"></i>`
            : ''}
                    ${escapeTkaTryoutResultsHtml(
                data.performanceChange !== null
                    ? footerValue
                    : latestPeriodName
            )}
                </span>
            </div>
        </div>
    `;
}

function renderTkaTryoutInsightAttentionCard(data, attention) {
    if (!attention) {
        return `
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 sm:text-[11px]">
                            Performa Baik
                        </span>
                        <h3 class="mt-1 text-sm font-bold text-slate-800 sm:text-base">
                            Tidak ada penurunan signifikan
                        </h3>
                        <p class="mt-2 text-xs leading-relaxed text-slate-500 sm:text-sm">
                            Belum ditemukan mata pelajaran yang mengalami penurunan nilai dibandingkan tryout sebelumnya.
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2.5">
                    <span class="text-xs font-medium text-slate-600">Status</span>
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                        <i class="fa-solid fa-check text-[9px]"></i>
                        Stabil
                    </span>
                </div>
            </div>
        `;
    }

    return `
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-amber-600 sm:text-[11px]">
                        Perlu Perhatian
                    </span>
                    <h3 class="mt-1 text-sm font-bold text-slate-800 sm:text-base">
                        ${escapeTkaTryoutResultsHtml(attention.subjectName)} perlu ditingkatkan
                    </h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500 sm:text-sm">
                        Nilai ${escapeTkaTryoutResultsHtml(attention.subjectName)}
                        mengalami penurunan dibandingkan tryout sebelumnya.
                    </p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between rounded-xl bg-amber-50 px-3 py-2.5">
                <span class="text-xs font-medium text-slate-600">Nilai terakhir</span>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-600">
                    ${formatTkaTryoutScore(attention.latestScore)}
                    <span class="font-normal text-slate-400">•</span>
                    <i class="fa-solid fa-arrow-down text-[9px]"></i>
                    ${formatTkaTryoutDifference(attention.difference)}
                </span>
            </div>
        </div>
    `;
}

function renderTkaTryoutInsightRecommendationCard(data, recommendation) {
    return `
        <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-bullseye text-sm"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-blue-600 sm:text-[11px]">
                        Rekomendasi
                    </span>
                    <h3 class="mt-1 text-sm font-bold text-slate-800 sm:text-base">
                        ${escapeTkaTryoutResultsHtml(recommendation.title)}
                    </h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500 sm:text-sm">
                        ${escapeTkaTryoutResultsHtml(recommendation.description)}
                    </p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between rounded-xl bg-blue-50 px-3 py-2.5">
                <span class="text-xs font-medium text-slate-600">
                    Prioritas belajar
                </span>
                <span class="text-xs font-bold text-blue-600">
                    ${escapeTkaTryoutResultsHtml(recommendation.priority)}
                </span>
            </div>
        </div>
    `;
}

function renderTkaTryoutInsightSubjectSummary(data) {
    const subjects = data.latestSubjects || [];

    const sortedSubjects = [...subjects].sort(function (a, b) {
        return b.score - a.score;
    });

    return `
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 sm:text-base">
                            Ringkasan Perkembangan
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Gambaran singkat performa setiap mata pelajaran pada tryout terakhir.
                        </p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600 sm:text-xs">
                        <i class="fa-solid fa-chart-simple text-[9px]"></i>
                        ${escapeTkaTryoutResultsHtml(
        getTkaTryoutPeriodLabel(data.latestPeriod)
    )}
                    </span>
                </div>
            </div>

            ${sortedSubjects.length
            ? `
                    <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                        ${sortedSubjects.map(function (subject) {
                return renderTkaTryoutInsightSubject(subject, data);
            }).join('')}
                    </div>
                `
            : `
                    <div class="px-4 py-8 text-center sm:px-5">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            <i class="fa-solid fa-chart-column text-sm"></i>
                        </div>
                        <p class="mt-3 text-xs font-medium text-slate-500">
                            Belum ada nilai mata pelajaran pada tryout terakhir.
                        </p>
                    </div>
                `
        }

            <div class="border-t border-slate-100 bg-slate-50/70 px-4 py-3.5 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-info mt-0.5 text-xs text-slate-400"></i>
                        <p class="text-[11px] leading-relaxed text-slate-500 sm:text-xs">
                            Insight dibuat berdasarkan perbandingan hasil tryout dan dapat menjadi acuan untuk menentukan fokus belajar anak.
                        </p>
                    </div>

                    ${data.sessions.length
            ? `
                            <button
                                type="button"
                                data-tka-tryout-insight-detail
                                class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#0071BC] px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-[#005f9e]"
                            >
                                Lihat Semua Data
                                <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </button>
                        `
            : ''
        }
                </div>
            </div>
        </div>
    `;
}

function renderTkaTryoutInsightSubject(subject, data) {
    const comparison = findTkaTryoutSubjectComparison(
        subject,
        data.comparisons
    );

    const score = Number(subject.score || 0);
    const percentage = Math.max(0, Math.min(100, score));

    const style = getTkaTryoutInsightSubjectStyle(
        comparison ? comparison.status : 'new'
    );

    let changeHtml = `
        <span class="shrink-0 text-[10px] font-semibold text-slate-400">
            Baru
        </span>
    `;

    if (comparison && comparison.status !== 'new') {
        changeHtml = `
            <span class="inline-flex shrink-0 items-center gap-1 text-[10px] font-semibold ${style.changeText}">
                <i class="fa-solid ${style.changeIcon} text-[8px]"></i>
                ${formatTkaTryoutDifference(comparison.difference)}
            </span>
        `;
    }

    return `
        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${style.iconBg} ${style.iconText}">
                <i class="fa-solid ${style.icon} text-xs"></i>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-3">
                    <span class="truncate text-xs font-semibold text-slate-700">
                        ${escapeTkaTryoutResultsHtml(subject.subjectName)}
                    </span>
                    <span class="shrink-0 text-xs font-bold text-slate-800">
                        ${formatTkaTryoutScore(score)}
                    </span>
                </div>

                <div class="mt-1.5 flex items-center justify-between gap-2">
                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full ${style.progress}"
                            style="width: ${percentage}%"
                        ></div>
                    </div>

                    ${changeHtml}
                </div>
            </div>
        </div>
    `;
}

function renderTkaTryoutInsightDetail(data) {
    const periods = buildTkaTryoutInsightPeriods(data.sessions)
        .sort(function (a, b) {
            return Number(a.period_number || 0) -
                Number(b.period_number || 0);
        });

    const comparisonMap = buildTkaTryoutSessionComparisonMap(
        data.sessions
    );

    return `
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 sm:text-base">
                            Detail Jadwal & Hasil Tryout
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Seluruh sesi tryout anak, termasuk sesi yang belum dikerjakan dan sesi yang tidak dikerjakan.
                        </p>
                    </div>

                    <button
                        type="button"
                        data-tka-tryout-insight-back
                        class="inline-flex w-fit shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:border-[#0071BC]/20 hover:bg-[#EAF6FF] hover:text-[#0071BC]"
                    >
                        <i class="fa-solid fa-arrow-left text-[9px]"></i>
                        Kembali
                    </button>
                </div>
            </div>

            <div class="max-h-150 overflow-y-auto p-4 sm:p-5">
                <div class="space-y-4">
                    ${periods.length
                        ? periods.map(function (period) {
                            return renderTkaTryoutDetailPeriod(period, comparisonMap);
                        }).join('')
                        : `
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-5 py-10 text-center">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark text-sm"></i>
                                </div>
                                <p class="mt-3 text-xs font-medium text-slate-500">
                                    Belum ada data sesi tryout.
                                </p>
                            </div>
                        `
                    }
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/70 px-4 py-3.5 sm:px-5">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[10px] font-medium text-slate-500 sm:text-xs">
                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-[9px] text-emerald-600"></i>
                        Telah dikerjakan
                    </span>

                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-hourglass-half text-[9px] text-blue-600"></i>
                        Belum dikerjakan
                    </span>

                    <span class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-xmark text-[9px] text-rose-500"></i>
                        Tidak dikerjakan
                    </span>
                </div>
            </div>
        </div>
    `;
}

function renderTkaTryoutDetailPeriod(period, comparisonMap) {
    const sessions = Array.isArray(period.sessions)
        ? [...period.sessions].sort(function (a, b) {
            const dateA = String(a.date_iso || '');
            const dateB = String(b.date_iso || '');

            if (dateA !== dateB) {
                return dateA.localeCompare(dateB);
            }

            const sessionA = Number(a.session_number || 0);
            const sessionB = Number(b.session_number || 0);

            if (sessionA !== sessionB) {
                return sessionA - sessionB;
            }

            return Number(a.id || 0) - Number(b.id || 0);
        })
        : [];

    return `
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-600 sm:text-xs">
                                <i class="fa-solid fa-layer-group text-[9px]"></i>
                                Periode ${escapeTkaTryoutResultsHtml(
        period.period_number || '-'
    )}
                            </span>

                            <span class="text-[10px] font-medium text-slate-400 sm:text-xs">
                                ${sessions.length} sesi
                            </span>
                        </div>

                        <h4 class="mt-2 text-sm font-bold text-slate-800 sm:text-base">
                            ${escapeTkaTryoutResultsHtml(
        period.title ||
        `Tryout TKA Periode ${period.period_number || '-'}`
    )}
                        </h4>

                        <p class="mt-1 text-xs text-slate-500">
                            ${escapeTkaTryoutResultsHtml(
        getTkaTryoutPeriodDateLabel(period)
    )}
                        </p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                ${sessions.length
            ? sessions.map(function (session) {
                return renderTkaTryoutDetailSession(
                    session,
                    comparisonMap[session.id] || null
                );
            }).join('')
            : `
                        <div class="px-4 py-8 text-center sm:px-5">
                            <p class="text-xs font-medium text-slate-400">
                                Belum ada sesi pada periode ini.
                            </p>
                        </div>
                    `
        }
            </div>
        </div>
    `;
}

function renderTkaTryoutDetailSession(session, comparison) {
    const status = getTkaTryoutSessionStatusMeta(session);
    const hasScore = status.key === 'completed' &&
        hasTkaTryoutSessionScore(session);

    const score = hasScore
        ? getTkaTryoutSessionScore(session)
        : null;

    let comparisonHtml = '';

    if (status.key === 'completed') {
        if (comparison && comparison.status === 'up') {
            comparisonHtml = `
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600">
                    <i class="fa-solid fa-arrow-up text-[8px]"></i>
                    ${escapeTkaTryoutResultsHtml(
                formatTkaTryoutDifference(comparison.difference)
            )}
                </span>
            `;
        } else if (comparison && comparison.status === 'down') {
            comparisonHtml = `
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-500">
                    <i class="fa-solid fa-arrow-down text-[8px]"></i>
                    ${escapeTkaTryoutResultsHtml(
                formatTkaTryoutDifference(comparison.difference)
            )}
                </span>
            `;
        } else if (comparison && comparison.status === 'same') {
            comparisonHtml = `
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-400">
                    <i class="fa-solid fa-minus text-[8px]"></i>
                    Tetap
                </span>
            `;
        } else {
            comparisonHtml = `
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-blue-500">
                    <i class="fa-solid fa-circle-plus text-[8px]"></i>
                    Baru
                </span>
            `;
        }
    }

    const timeLabel =
        session.start_time && session.end_time
            ? `${session.start_time} - ${session.end_time}`
            : session.start_time
                ? session.start_time
                : 'Waktu tidak tersedia';

    return `
        <div class="px-4 py-4 sm:px-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${status.iconBg} ${status.iconText}">
                        <i class="fa-solid ${status.icon} text-sm"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Sesi ${escapeTkaTryoutResultsHtml(
        session.session_number || '-'
    )}
                            </span>

                            <span class="text-[10px] text-slate-300">•</span>

                            <span class="text-[10px] font-medium text-slate-500">
                                ${escapeTkaTryoutResultsHtml(
        formatTkaTryoutDate(session.date_iso)
    )}
                            </span>
                        </div>

                        <h5 class="mt-1 truncate text-sm font-bold text-slate-800">
                            ${escapeTkaTryoutResultsHtml(
        session.subject || 'Mata Pelajaran'
    )}
                        </h5>

                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-slate-400">
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-regular fa-clock text-[9px]"></i>
                                ${escapeTkaTryoutResultsHtml(timeLabel)}
                            </span>

                            ${session.total_question
            ? `
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fa-regular fa-circle-question text-[9px]"></i>
                                        ${escapeTkaTryoutResultsHtml(
                session.total_question
            )} soal
                                    </span>
                                `
            : ''
        }
                        </div>

                        <div class="mt-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full ${status.badgeBg} px-2.5 py-1 text-[10px] font-semibold ${status.badgeText}">
                                <i class="fa-solid ${status.badgeIcon} text-[8px]"></i>
                                ${escapeTkaTryoutResultsHtml(status.label)}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center justify-between gap-6 rounded-xl bg-slate-50 px-3.5 py-2.5 sm:min-w-[150px] sm:justify-end">
                    <div class="text-left sm:text-right">
                        <span class="block text-[9px] font-semibold uppercase tracking-wider text-slate-400">
                            Nilai
                        </span>

                        <span class="mt-0.5 block text-lg font-bold ${hasScore
            ? 'text-slate-800'
            : 'text-slate-300'}">
                            ${hasScore
            ? formatTkaTryoutScore(score)
            : '—'}
                        </span>
                    </div>

                    ${comparisonHtml
            ? `
                            <div class="min-w-[75px] text-right">
                                <span class="block text-[9px] font-medium text-slate-400">
                                    Perbandingan
                                </span>

                                <div class="mt-1">
                                    ${comparisonHtml}
                                </div>
                            </div>
                        `
            : ''
        }
                </div>
            </div>
        </div>
    `;
}

function getTkaTryoutSessionStatusMeta(session) {
    const status = String(session?.status || '').toLowerCase();

    if (
        status === 'completed' ||
        status === 'done' ||
        status === 'finished' ||
        status === 'finish'
    ) {
        return {
            key: 'completed',
            label: session.status_label || 'Telah dikerjakan',
            icon: 'fa-circle-check',
            iconBg: 'bg-emerald-50',
            iconText: 'text-emerald-600',
            badgeBg: 'bg-emerald-50',
            badgeText: 'text-emerald-600',
            badgeIcon: 'fa-circle-check'
        };
    }

    if (
        status === 'missed' ||
        status === 'not_done' ||
        status === 'expired'
    ) {
        return {
            key: 'missed',
            label: session.status_label || 'Tidak dikerjakan',
            icon: 'fa-circle-xmark',
            iconBg: 'bg-rose-50',
            iconText: 'text-rose-500',
            badgeBg: 'bg-rose-50',
            badgeText: 'text-rose-500',
            badgeIcon: 'fa-circle-xmark'
        };
    }

    return {
        key: 'not_started',
        label: session.status_label || 'Belum dikerjakan',
        icon: 'fa-hourglass-half',
        iconBg: 'bg-blue-50',
        iconText: 'text-blue-600',
        badgeBg: 'bg-blue-50',
        badgeText: 'text-blue-600',
        badgeIcon: 'fa-hourglass-half'
    };
}

function buildTkaTryoutSessionComparisonMap(sessions) {
    const comparisonMap = {};
    const previousCompletedBySubject = {};

    const sortedSessions = [
        ...(Array.isArray(sessions) ? sessions : [])
    ].sort(function (a, b) {
        const dateA = String(a.date_iso || '');
        const dateB = String(b.date_iso || '');

        if (dateA !== dateB) {
            return dateA.localeCompare(dateB);
        }

        const sessionA = Number(a.session_number || 0);
        const sessionB = Number(b.session_number || 0);

        if (sessionA !== sessionB) {
            return sessionA - sessionB;
        }

        return Number(a.id || 0) - Number(b.id || 0);
    });

    sortedSessions.forEach(function (session) {
        const status = getTkaTryoutSessionStatusMeta(session);

        if (
            status.key !== 'completed' ||
            !hasTkaTryoutSessionScore(session)
        ) {
            comparisonMap[session.id] = null;
            return;
        }

        const key = getTkaTryoutSubjectKey({
            subjectId: session.subject_id ?? null,
            subjectName: session.subject || 'Mata Pelajaran'
        });

        const previous = previousCompletedBySubject[key] || null;
        const currentScore = getTkaTryoutSessionScore(session);

        if (!previous) {
            comparisonMap[session.id] = {
                status: 'new',
                difference: null,
                previousSession: null
            };
        } else {
            const difference = Number(
                currentScore -
                getTkaTryoutSessionScore(previous)
            );

            let comparisonStatus = 'same';

            if (difference > 0) {
                comparisonStatus = 'up';
            } else if (difference < 0) {
                comparisonStatus = 'down';
            }

            comparisonMap[session.id] = {
                status: comparisonStatus,
                difference: difference,
                previousSession: previous
            };
        }

        previousCompletedBySubject[key] = session;
    });

    return comparisonMap;
}

function getTkaTryoutInsightSubjects(period) {
    if (!period) return [];

    const sessions = Array.isArray(period.sessions)
        ? period.sessions
        : [];

    const subjects = {};

    sessions.forEach(function (session) {
        if (
            getTkaTryoutSessionStatusMeta(session).key !== 'completed' ||
            !hasTkaTryoutSessionScore(session)
        ) {
            return;
        }

        const subjectId = session.subject_id ?? null;
        const subjectName = session.subject || 'Mata Pelajaran';

        const key = getTkaTryoutSubjectKey({
            subjectId: subjectId,
            subjectName: subjectName
        });

        if (!subjects[key]) {
            subjects[key] = {
                subjectId: subjectId,
                subjectName: subjectName,
                score: getTkaTryoutSessionScore(session)
            };
        }
    });

    return Object.values(subjects);
}

function getTkaTryoutInsightAverage(subjects) {
    if (!Array.isArray(subjects) || !subjects.length) {
        return null;
    }

    const validScores = subjects
        .map(function (subject) {
            return Number(subject.score);
        })
        .filter(function (score) {
            return Number.isFinite(score);
        });

    if (!validScores.length) {
        return null;
    }

    return validScores.reduce(function (total, score) {
        return total + score;
    }, 0) / validScores.length;
}

function findTkaTryoutSubjectComparison(subject, comparisons) {
    if (!Array.isArray(comparisons)) return null;

    const key = getTkaTryoutSubjectKey(subject);

    return comparisons.find(function (item) {
        return getTkaTryoutSubjectKey({
            subjectId: item.subjectId,
            subjectName: item.subjectName
        }) === key;
    }) || null;
}

function getTkaTryoutSubjectKey(subject) {
    if (
        subject &&
        subject.subjectId !== null &&
        typeof subject.subjectId !== 'undefined'
    ) {
        return `id:${subject.subjectId}`;
    }

    return `name:${String(
        subject?.subjectName || ''
    ).toLowerCase().trim()}`;
}

function getTkaTryoutInsightPerformance(data) {
    if (!data || data.latestSession === null) {
        return {
            type: 'neutral',
            label: 'Performa',
            title: 'Belum ada hasil tryout',
            description: 'Belum ada hasil tryout yang dapat digunakan untuk menampilkan perkembangan nilai.'
        };
    }

    if (data.performanceChange === null) {
        return {
            type: 'neutral',
            label: 'Performa',
            title: 'Hasil tryout terakhir tersedia',
            description: 'Belum tersedia cukup data untuk membandingkan perkembangan nilai.'
        };
    }

    if (data.performanceChange > 0) {
        return {
            type: 'positive',
            label: 'Performa Positif',
            title: 'Nilai mengalami peningkatan',
            description: `Rata-rata nilai meningkat dari <span class="font-semibold text-slate-700">${formatTkaTryoutScore(data.previousAverage)}</span> menjadi <span class="font-semibold text-emerald-600">${formatTkaTryoutScore(data.latestAverage)}</span> pada tryout terakhir.`
        };
    }

    if (data.performanceChange < 0) {
        return {
            type: 'attention',
            label: 'Perlu Perhatian',
            title: 'Rata-rata nilai mengalami penurunan',
            description: `Rata-rata nilai turun dari <span class="font-semibold text-slate-700">${formatTkaTryoutScore(data.previousAverage)}</span> menjadi <span class="font-semibold text-amber-600">${formatTkaTryoutScore(data.latestAverage)}</span> pada tryout terakhir.`
        };
    }

    return {
        type: 'neutral',
        label: 'Performa Stabil',
        title: 'Nilai relatif stabil',
        description: `Rata-rata nilai tetap di <span class="font-semibold text-slate-700">${formatTkaTryoutScore(data.latestAverage)}</span> dibandingkan tryout sebelumnya.`
    };
}

function getTkaTryoutAttentionSubject(data) {
    if (
        !data ||
        !Array.isArray(data.attentionSubjects) ||
        !data.attentionSubjects.length
    ) {
        return null;
    }

    return [...data.attentionSubjects].sort(function (a, b) {
        return a.difference - b.difference;
    })[0];
}

function getTkaTryoutRecommendation(data) {
    const attentionSubject = getTkaTryoutAttentionSubject(data);

    if (!attentionSubject) {
        if (data && data.bestSubject) {
            return {
                title: `Pertahankan ${data.bestSubject.subjectName}`,
                description: `Pertahankan performa ${data.bestSubject.subjectName} dengan latihan rutin agar hasil belajar tetap konsisten.`,
                priority: 'Sedang'
            };
        }

        return {
            title: 'Pertahankan konsistensi belajar',
            description: 'Pertahankan hasil belajar dan lakukan latihan secara rutin sebelum tryout berikutnya.',
            priority: 'Sedang'
        };
    }

    const priority = attentionSubject.difference <= -5
        ? 'Tinggi'
        : 'Sedang';

    return {
        title: `Fokus pada latihan ${attentionSubject.subjectName}`,
        description: `Nilai ${attentionSubject.subjectName} mengalami penurunan ${formatTkaTryoutDifference(attentionSubject.difference)} dibandingkan tryout sebelumnya.`,
        priority: priority
    };
}

function getTkaTryoutInsightSubjectStyle(status) {
    if (status === 'up') {
        return {
            icon: 'fa-arrow-trend-up',
            iconBg: 'bg-emerald-50',
            iconText: 'text-emerald-600',
            progress: 'bg-emerald-500',
            changeText: 'text-emerald-600',
            changeIcon: 'fa-arrow-up'
        };
    }

    if (status === 'down') {
        return {
            icon: 'fa-arrow-trend-down',
            iconBg: 'bg-amber-50',
            iconText: 'text-amber-600',
            progress: 'bg-amber-500',
            changeText: 'text-amber-600',
            changeIcon: 'fa-arrow-down'
        };
    }

    if (status === 'same' || status === 'stable') {
        return {
            icon: 'fa-minus',
            iconBg: 'bg-slate-100',
            iconText: 'text-slate-500',
            progress: 'bg-slate-400',
            changeText: 'text-slate-500',
            changeIcon: 'fa-minus'
        };
    }

    return {
        icon: 'fa-book-open',
        iconBg: 'bg-blue-50',
        iconText: 'text-blue-600',
        progress: 'bg-blue-500',
        changeText: 'text-slate-400',
        changeIcon: 'fa-circle-plus'
    };
}

function getTkaTryoutPeriodLabel(period) {
    if (!period) return 'Tryout Terakhir';

    const periodNumber =
        period.period_number ||
        period.number ||
        null;

    if (periodNumber !== null) {
        return `Periode ${periodNumber}`;
    }

    return period.title || 'Tryout Terakhir';
}

function getTkaTryoutPeriodDateLabel(period) {
    if (!period) return '';

    if (period.date_range) {
        return period.date_range;
    }

    if (period.start_date && period.end_date) {
        if (period.start_date === period.end_date) {
            return formatTkaTryoutDate(period.start_date);
        }

        return `${formatTkaTryoutDate(period.start_date)} - ${formatTkaTryoutDate(period.end_date)}`;
    }

    if (period.start_date) {
        return formatTkaTryoutDate(period.start_date);
    }

    return '';
}

function formatTkaTryoutDate(value) {
    if (!value) return '';

    const date = new Date(`${value}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function formatTkaTryoutDifference(value) {
    if (value === null || typeof value === 'undefined') {
        return '-';
    }

    const number = Number(value);

    if (!Number.isFinite(number)) {
        return '-';
    }

    const formatted = Math.abs(number).toLocaleString('id-ID', {
        minimumFractionDigits: Number.isInteger(number) ? 0 : 1,
        maximumFractionDigits: 1
    });

    if (number > 0) {
        return `+${formatted} poin`;
    }

    if (number < 0) {
        return `-${formatted} poin`;
    }

    return 'Tetap';
}

function renderTkaTryoutInsightEmpty() {
    return `
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-10 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <i class="fa-solid fa-chart-line text-lg"></i>
            </div>

            <h3 class="mt-3 text-sm font-bold text-slate-700">
                Belum ada insight
            </h3>

            <p class="mx-auto mt-1.5 max-w-md text-xs leading-relaxed text-slate-500">
                Insight dan rekomendasi akan tersedia setelah anak memiliki hasil tryout TKA.
            </p>
        </div>
    `;
}

function renderTkaTryoutInsightError() {
    return `
        <div class="rounded-2xl border border-rose-100 bg-white px-5 py-10 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>

            <h3 class="mt-3 text-sm font-bold text-slate-700">
                Insight gagal dimuat
            </h3>

            <p class="mx-auto mt-1.5 max-w-md text-xs leading-relaxed text-slate-500">
                Terjadi kesalahan saat mengambil data insight dan rekomendasi.
            </p>

            <button
                type="button"
                data-tka-tryout-insight-retry
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#0071BC] px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-[#005f9e]"
            >
                <i class="fa-solid fa-rotate-right text-[9px]"></i>
                Coba Lagi
            </button>
        </div>
    `;
}

function hasTkaTryoutSessionScore(session) {
    if (!session) return false;

    const score =
        session.score ??
        session.nilai ??
        session.final_score ??
        session.total_score;

    return score !== null &&
        typeof score !== 'undefined' &&
        score !== '' &&
        Number.isFinite(Number(score));
}

function getTkaTryoutSessionScore(session) {
    if (!session) return 0;

    const score =
        session.score ??
        session.nilai ??
        session.final_score ??
        session.total_score;

    const number = Number(score);

    return Number.isFinite(number) ? number : 0;
}

function formatTkaTryoutScore(value) {
    if (value === null || typeof value === 'undefined') {
        return '-';
    }

    const number = Number(value);

    if (!Number.isFinite(number)) {
        return '-';
    }

    return number.toLocaleString('id-ID', {
        minimumFractionDigits: Number.isInteger(number) ? 0 : 1,
        maximumFractionDigits: 1
    });
}

function escapeTkaTryoutResultsHtml(value) {
    if (value === null || typeof value === 'undefined') {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

$(document).on('click', '[data-tka-tryout-insight-detail]', function () {
    if (!tkaTryoutInsightData) return;

    tkaTryoutInsightDetailOpen = true;
    renderTkaTryoutInsight();
});

$(document).on('click', '[data-tka-tryout-insight-back]', function () {
    tkaTryoutInsightDetailOpen = false;
    renderTkaTryoutInsight();
});

$(document).on('click', '[data-tka-tryout-insight-retry]', function () {
    tkaTryoutInsightData = null;
    loadTkaTryoutInsight();
});

$(document).ready(function () {
    loadTkaTryoutInsight();
});