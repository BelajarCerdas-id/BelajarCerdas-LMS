let reflectionChart = null;
let reflectionChartRefreshTimer = null;

function loadStudentReflectionChart(showLoading = true) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!container) return;
    if (!role) return;

    const period = document.getElementById('chartPeriod').value;
    const year = document.getElementById('chartYear').value;
    const month = document.getElementById('chartMonth').value;
    const loadingEl = document.getElementById('reflection-chart-loading');
    const chartEl = document.getElementById('reflection-chart-content');
    const emptyEl = document.getElementById('empty-message-reflection-chart');

    if (!loadingEl || !chartEl || !emptyEl) return;

    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        loadingEl.classList.add('hidden');
        chartEl.classList.add('hidden');
        emptyEl.classList.remove('hidden');

        return;
    }

    loadingEl.classList.remove('hidden');
    chartEl.classList.add('hidden');
    emptyEl.classList.add('hidden');

    $.ajax({
        url: `/lms/${role}/foundation/student-reflection/load-reflection-trend/${foundationId}`,
        method: 'GET',
        data: {
            period,
            year,
            month
        },

        beforeSend() {
            if (!showLoading) return;

            loadingEl.classList.remove('hidden');
            chartEl.classList.add('hidden');
            emptyEl.classList.add('hidden');
        },

        success(response) {
            const chartData = Array.isArray(response.data) ? response.data : [];

            const totalData = chartData.reduce((sum, value) => {
                return sum + Number(value || 0);
            }, 0);

            if (totalData === 0) {
                loadingEl.classList.add('hidden');
                chartEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');

                return;
            }

            const dataset = {
                label: 'Jumlah Refleksi Terjawab',
                data: response.data,
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37,99,235,.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: '#2563EB',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            };

            const ctx = document.getElementById('studentReflectionChart');

            if (!ctx) return;

            if (reflectionChart) {
                reflectionChart.data.labels = response.labels;
                reflectionChart.data.datasets = [dataset];
                reflectionChart.options.plugins.title.text = response.title;
                reflectionChart.update();

            } else {
                reflectionChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.labels,
                        datasets: [
                            dataset
                        ]
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
                                },

                                padding: {
                                    bottom: 20
                                }
                            },

                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    boxWidth: 10,
                                    padding: 20,
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    }
                                }
                            },

                            tooltip: {
                                callbacks: {
                                    label(context) {
                                        return `${context.raw} Refleksi`;
                                    }
                                }
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },

                                grid: {
                                    color: '#E2E8F0'
                                }
                            },

                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },

                        elements: {
                            line: {
                                borderJoinStyle: 'round'
                            }
                        }
                    }
                });
            }

            loadingEl.classList.add('hidden');
            chartEl.classList.remove('hidden');
            emptyEl.classList.add('hidden');
        },
        error(xhr, status, error) {
            loadingEl.classList.add('hidden');
            chartEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');

            console.error(error);
        }
    });
}

function refreshReflectionChartRealtime() {
    clearTimeout(reflectionChartRefreshTimer);

    reflectionChartRefreshTimer = setTimeout(() => {
        loadStudentReflectionChart(false);
    }, 1000);
}