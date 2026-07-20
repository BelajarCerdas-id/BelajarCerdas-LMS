function teacherDailyAgendaList() {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role || !schoolName || !schoolId) return;
    
    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/daily-agenda/paginate`,
        method: 'GET',
        beforeSend: function () {
            $('#daily-agenda-list').hide();
            $('#daily-agenda-skeleton').removeClass('hidden');
            $('#daily-agenda-empty').addClass('hidden');
        },
        success: function (response) {
            const dailyAgendaList = $('#daily-agenda-list');
            $('#daily-agenda-skeleton').addClass('hidden');
            $('#daily-agenda-list').show();
            dailyAgendaList.empty();

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {
                    $('#total-daily-agenda-header-info').text(response.totalDailyAgenda + ' Jadwal');

                    const teacherDailyAgendaForm = response.teacherDailyAgendaForm.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                        .replace(':dayOfWeek', item.day_of_week).replace(':classId', item.class_id).replace(':subjectId', item.mapel_id);

                    let statusBadge = '';

                    switch (item.status) {
                        case 'submitted':
                            statusBadge = `
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    Sudah Diisi
                                </span>
                            `;
                            break;

                        default:
                            statusBadge = `
                                <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                    Belum Diisi
                                </span>
                            `;
                    }

                    let actionButton = '';

                    if (item.status === 'submitted') {
                        actionButton = `
                            <a href="${teacherDailyAgendaForm}">
                                <button
                                    class="rounded-xl bg-slate-700 px-6 py-3 font-medium text-white transition hover:bg-slate-800 cursor-pointer">
                                    <i class="fa-solid fa-eye mr-2"></i>
                                    Lihat Agenda
                                </button>
                            </a>
                        `;
                    } else {
                        actionButton = `
                            <a href="${teacherDailyAgendaForm}">
                                <button
                                    class="rounded-xl bg-blue-600 px-6 py-3 font-medium text-white transition hover:bg-blue-700 cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square mr-2"></i>
                                    Isi Agenda
                                </button>
                            </a>
                        `;
                    }

                    const list = `
                        <div class="mt-8 space-y-5">
                            <div
                                class="rounded-2xl border border-slate-300 border-l-4 {{ $border }} bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

                                <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">

                                    <div class="flex gap-5">

                                        <!-- Time -->
                                        <div
                                            class="flex h-18 w-18 shrink-0 flex-col items-center justify-center rounded-2xl bg-blue-50">

                                            <span class="text-lg font-bold text-blue-700">
                                                ${item.start_time}
                                            </span>

                                            <span class="text-xs text-slate-500">
                                                ${item.end_time}
                                            </span>

                                        </div>

                                        <!-- Information -->
                                        <div>

                                            <div class="flex flex-wrap items-center gap-3">

                                                <h3 class="text-lg font-semibold text-slate-800">

                                                    ${item.subject_name}

                                                </h3>

                                                ${statusBadge}

                                            </div>

                                            <div class="mt-4 grid gap-3 md:grid-cols-3">

                                                <div class="flex items-center gap-2 text-sm text-slate-600">

                                                    <i class="fa-solid fa-users text-blue-500"></i>

                                                    ${item.rombel_class ?? '-'}

                                                </div>

                                                <div class="flex items-center gap-2 text-sm text-slate-600">

                                                    <i class="fa-solid fa-clock text-blue-500"></i>

                                                    ${item.total_session ?? 0} JP

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <!-- Actions -->
                                    <div>
                                        ${actionButton}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    dailyAgendaList.append(list);
                });
            } else {
                $('#daily-agenda-list').hide();
                $('#daily-agenda-empty').removeClass('hidden');
            }
        },
        error: function (xhr, status, error) {
            $('#daily-agenda-skeleton').addClass('hidden');
            $('#daily-agenda-list').show();
            console.log(error);
        }
    });
}

$(document).ready(function () {
    teacherDailyAgendaList();
});