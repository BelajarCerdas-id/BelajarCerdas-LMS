let teacherAgendaProgressChart = null;

function renderTeacherAgendaProgress(summary) {

    $('#teacher-agenda-progress-loading').addClass('hidden');
    $('#teacher-agenda-progress-content').removeClass('hidden');

    $('#teacher-agenda-progress-percentage')
        .text(summary.complianceRate + '%');

    $('#teacher-agenda-filled')
        .text(summary.totalSubmittedAgenda + ' Guru');

    $('#teacher-agenda-unfilled')
        .text(summary.totalPendingAgenda + ' Guru');

    const ctx = document
        .getElementById('teacherAgendaProgressChart')
        .getContext('2d');

    if (teacherAgendaProgressChart) {
        teacherAgendaProgressChart.destroy();
    }

    teacherAgendaProgressChart = new Chart(ctx, {

        type: 'doughnut',

        data: {

            labels: [
                'Sudah Mengisi',
                'Belum Mengisi'
            ],

            datasets: [{
                data: [
                    summary.totalSubmittedAgenda,
                    summary.totalPendingAgenda
                ],

                backgroundColor: [
                    '#22c55e',
                    '#ef4444'
                ],

                borderWidth: 0,
                hoverOffset: 8
            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '72%',

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {
                    enabled: false,
                    callbacks: {

                        label: function (context) {
                            return `${context.label}: ${context.raw} Guru`;
                        }

                    }

                }

            }

        }

    });

}