let emotionTrendChart = null;

function loadEmotionTrendChart(showLoading = true) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    const period = document.getElementById('chartPeriod').value;
    const year = document.getElementById('chartYear').value;
    const month = document.getElementById('chartMonth').value;
    const loadingEl = document.getElementById('emotion-trend-loading');
    const chartEl = document.getElementById('emotion-trend-content');
    const emptyEl = document.getElementById('empty-message-emotion-trend');

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
        url: `/lms/${role}/foundation/student-reflection/load-emotion-trend/${foundationId}`,
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

            const datasets = Array.isArray(response.datasets) ? response.datasets : [];

            const totalData = datasets.reduce((sum, dataset) => {

                const totals = Array.isArray(dataset.totals) ? dataset.totals : [];

                return sum + totals.reduce((total, value) => {
                    return total + Number(value || 0);

                }, 0);

            }, 0);

            if (totalData === 0) {
                loadingEl.classList.add('hidden');
                chartEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');

                return;
            }

            const chartDatasets = datasets.map(dataset => ({
                ...dataset,
                borderWidth: 3,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: dataset.borderColor,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                fill: false
            }));

            const ctx = document.getElementById('emotionTrendChart');

            if (!ctx) return;

            if (emotionTrendChart) {
                emotionTrendChart.data.labels = response.labels;
                emotionTrendChart.data.datasets = chartDatasets;
                emotionTrendChart.options.plugins.title.text = response.title;
                emotionTrendChart.update();

            } else {
                emotionTrendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.labels,
                        datasets: chartDatasets
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
                                    label: function (context) {
                                        const percentage = Number(context.raw || 0);
                                        const total = context.dataset.totals?.[context.dataIndex] ?? 0;
                                        return `${context.dataset.label}: ${percentage}% (${total} siswa)`;
                                    }
                                }
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                min: 0,
                                max: 100,
                                ticks: {
                                    stepSize: 20,
                                    callback: function (value) {
                                        return `${value}%`;
                                    }
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
        error: function (xhr, status, error) {
            loadingEl.classList.add('hidden');
            chartEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');

            console.error(error);
        }
    });
}