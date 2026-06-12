document.addEventListener('DOMContentLoaded', function () {

    fetchAttendanceChart();
});

function fetchAttendanceChart() {

    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const meetingNumber = container.dataset.meetingNumber;
    const semester = container.dataset.semester;

    $.ajax({

        url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/${meetingNumber}/semester/${semester}/chart`,

        method: 'GET',

        success: function (response) {

            // UPDATE CHART
            initOrUpdateChart(response);

            // UPDATE STATISTIK
            $('#totalSiswaCount').text(response.total_siswa);
            $('#hadirCount').text(response.hadir);
            $('#izinCount').text(response.izin);
            $('#sakitCount').text(response.sakit);
            $('#alpaCount').text(response.alpa);
        },

        error: function (xhr) {

            console.error(xhr);
        }
    });
}

function initOrUpdateChart(summary) {

    const allLabels = [
        'Hadir',
        'Izin',
        'Sakit',
        'Alpa'
    ];

    const allData = [
        summary.hadir,
        summary.izin,
        summary.sakit,
        summary.alpa
    ];

    const allColors = [
        '#10B981',
        '#3B82F6',
        '#F59E0B',
        '#EF4444'
    ];

    const activeLabels = [];
    const activeData = [];
    const activeColors = [];

    let totalSiswa = 0;

    for (let i = 0; i < allData.length; i++) {

        if (allData[i] > 0) {

            activeLabels.push(allLabels[i]);
            activeData.push(allData[i]);
            activeColors.push(allColors[i]);

            totalSiswa += allData[i];
        }
    }

    const isDataEmpty = totalSiswa === 0;

    const ctx = document
        .getElementById('absensiChart')
        .getContext('2d');

    // UPDATE
    if (myAbsensiChart) {

        myAbsensiChart.data.labels = isDataEmpty
            ? ['Belum ada data']
            : activeLabels;

        myAbsensiChart.data.datasets[0].data = isDataEmpty
            ? [1]
            : activeData;

        myAbsensiChart.data.datasets[0].backgroundColor = isDataEmpty
            ? ['#F1F5F9']
            : activeColors;

        myAbsensiChart.options.plugins.legend.display =
            !isDataEmpty;

        myAbsensiChart.update();

        return;
    }

    // CREATE
    myAbsensiChart = new Chart(ctx, {

        type: 'doughnut',

        data: {

            labels: isDataEmpty
                ? ['Belum ada data']
                : activeLabels,

            datasets: [{
                data: isDataEmpty
                    ? [1]
                    : activeData,

                backgroundColor: isDataEmpty
                    ? ['#F1F5F9']
                    : activeColors,

                borderWidth: 0
            }]
        },

        options: {

            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',

            interaction: {
                intersect: false,
                mode: 'nearest'
            },

            plugins: {

                legend: {
                    position: 'bottom',

                    display: !isDataEmpty,

                    labels: {
                        usePointStyle: true,

                        font: {
                            size: 11,
                            weight: 'bold'
                        }
                    }
                },

                tooltip: {

                    enabled: true,

                    backgroundColor: '#0F172A',

                    titleColor: '#FFFFFF',

                    bodyColor: '#FFFFFF',

                    padding: 12,

                    displayColors: true,

                    cornerRadius: 12,

                    callbacks: {

                        label: function (context) {

                            const label = context.label || '';
                            const value = context.raw || 0;

                            if (label === 'Belum ada data') {
                                return 'Belum ada data';
                            }

                            return `${label}: ${value} siswa`;
                        }
                    }
                }
            }
        }
    });
}