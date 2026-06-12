let reflectionChart = null;

function loadStudentReflectionChart(showLoading = true) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    const period = document.getElementById('chartPeriod').value;
    const year = document.getElementById('chartYear').value;
    const month = document.getElementById('chartMonth').value;

    const loadingEl = document.getElementById('reflection-chart-loading');
    const chartEl = document.getElementById('reflection-chart-content');
    const emptyEl = document.getElementById('empty-message-reflection-chart');
    const chartPeriod = document.getElementById('chartPeriod');
    const chartMonth = document.getElementById('chartMonth');
    const chartYear = document.getElementById('chartYear');

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/dashboard/load-student-reflection-chart`,
        method: 'GET',
        data: {
            period,
            year,
            month
        },

        beforeSend: function () {

            if (showLoading) {

                loadingEl.classList.remove('hidden');
                chartEl.classList.add('hidden');
                emptyEl.classList.add('hidden');

            }
        },
        success: function (response) {
            const chartData = Array.isArray(response.data) ? response.data : [];

            const totalData = chartData.reduce((sum, value) => sum + value, 0);
            
            if (totalData === 0) {

                loadingEl.classList.add('hidden');

                chartEl.classList.add('hidden');

                emptyEl.classList.remove('hidden');

                return;
            }

            const yearSelect = document.getElementById('chartYear');

            if (!yearSelect.dataset.loaded) {
                yearSelect.innerHTML = '';

                response.years.forEach(year => {
                    yearSelect.innerHTML += `<option value="${year}">Tahun ${year}</option>`;
                });

                yearSelect.dataset.loaded = 'true';
            }

            const ctx = document.getElementById('studentReflectionChart');

            if (reflectionChart) {
                reflectionChart.data.labels =
                    response.labels;

                reflectionChart.data.datasets[0].data =
                    response.data;

                reflectionChart.options.plugins.title.text =
                    response.title;

                reflectionChart.update();
            } else {
                reflectionChart = new Chart(ctx, {
    
                    type: 'line',
    
                    data: {
    
                        labels: response.labels,
    
                        datasets: [{
    
                            label: 'Jumlah Jawaban',
    
                            data: response.data,
    
                            borderColor: '#2563EB',
    
                            backgroundColor:
                                'rgba(37,99,235,0.08)',
    
                            fill: true,
    
                            tension: 0.4,
    
                            pointRadius: 5,
    
                            pointHoverRadius: 7,
    
                            pointBackgroundColor: '#2563EB',
    
                            borderWidth: 3
                        }]
                    },
    
                    options: {
    
                        responsive: true,
    
                        maintainAspectRatio: false,
    
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
    
                        plugins: {
    
                            title: {
    
                                display: true,
    
                                text: response.title,
    
                                font: {
                                    size: 18,
                                    weight: 'bold'
                                }
                            },
    
                            legend: {
                                display: false
                            },
    
                            tooltip: {
    
                                callbacks: {
    
                                    label: function (context) {
    
                                        return (
                                            context.dataset.label +
                                            ': ' +
                                            context.raw
                                        );
                                    }
                                }
                            }
                        },
    
                        scales: {
    
                            y: {
    
                                beginAtZero: true,
    
                                grid: {
                                    color: '#E2E8F0'
                                }
                            },
    
                            x: {
    
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }


            loadingEl.classList.add('hidden');
            chartEl.classList.remove('hidden');
            emptyEl.classList.add('hidden');

        },

        error: function (xhr, status, error) {
            loadingEl.classList.add('hidden');
            chartEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');

            chartPeriod.classList.add('hidden');
            chartMonth.classList.add('hidden');
            chartYear.classList.add('hidden');
            console.error(error);
        }
    });
}

$(document).ready(function () {

    initializeReflectionChartFilter();

});

function initializeReflectionChartFilter() {

    const periodSelect = document.getElementById('chartPeriod');

    const yearSelect = document.getElementById('chartYear');

    const monthSelect = document.getElementById('chartMonth');

    function refresh() {

        const period = periodSelect.value;
        periodSelect.classList.remove('hidden');

        if (period === 'daily' || period === 'weekly') {

            monthSelect.classList.remove('hidden');
            yearSelect.classList.remove('hidden');

        } else if(period === 'monthly') {
            
            monthSelect.classList.add('hidden');
            yearSelect.classList.remove('hidden');

        } else {

            monthSelect.classList.add('hidden');
            yearSelect.classList.add('hidden');
        }

        loadStudentReflectionChart();
    }

    periodSelect.addEventListener('change', refresh);

    yearSelect.addEventListener('change', refresh);

    monthSelect.addEventListener('change', refresh);

    refresh();
}

let reflectionChartRefreshTimer = null;

function refreshReflectionChartRealtime() {

    clearTimeout(reflectionChartRefreshTimer);

    reflectionChartRefreshTimer =
        setTimeout(() => {

            loadStudentReflectionChart(false);

        }, 1000);
}