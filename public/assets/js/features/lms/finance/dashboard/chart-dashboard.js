let revenueChart = null;

function renderRevenueTrendChart(showLoading = true) {

    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;

    const period = document.getElementById('chartPeriod').value;
    const year = document.getElementById('chartYear').value || new Date().getFullYear();

    const loadingEl = document.getElementById('revenue-chart-loading');
    const emptyEl = document.getElementById('empty-message-revenue-chart');
    const chartWrap = document.getElementById('revenue-chart-container');

    $.ajax({
        url: `/lms/${role}/manage-contract/load-chart`,
        method: 'GET',
        data: { period, year },

        beforeSend: function () {
            if (showLoading) {
                loadingEl.classList.remove('hidden');
                emptyEl.classList.add('hidden');
                chartWrap.classList.add('hidden');
            }
        },

        success: function (res) {

            const data = res.data || [];
            const total = data.reduce((a, b) => a + b, 0);

            loadingEl.classList.add('hidden');

            if (total === 0) {
                emptyEl.classList.remove('hidden');
                chartWrap.classList.add('hidden');
                return;
            }

            emptyEl.classList.add('hidden');
            chartWrap.classList.remove('hidden');

            // YEAR OPTIONS
            const yearSelect = document.getElementById('chartYear');

            if (!yearSelect.dataset.loaded) {
                yearSelect.innerHTML = '';

                res.years.forEach(y => {
                    yearSelect.innerHTML += `<option value="${y}">Tahun ${y}</option>`;
                });

                yearSelect.dataset.loaded = "true";
            }

            const ctx = document.getElementById('revenueTrendChart');

            if (revenueChart) {
                revenueChart.destroy();
                revenueChart = null;
            }

            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: res.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: res.data,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37,99,235,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
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
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#E2E8F0' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        },

        error: function () {
            loadingEl.classList.add('hidden');
            chartWrap.classList.add('hidden');
            emptyEl.classList.remove('hidden');
        }
    });
}

function initializeRevenueChartFilter() {

    const periodSelect = document.getElementById('chartPeriod');
    const yearSelect = document.getElementById('chartYear');

    periodSelect.classList.remove('hidden');

    function refresh() {

        const period = periodSelect.value;

        if (period === 'monthly') {
            yearSelect.classList.remove('hidden');
        }

        if (period === 'yearly') {
            yearSelect.classList.remove('hidden');
        }

        renderRevenueTrendChart();
    }

    periodSelect.addEventListener('change', refresh);
    yearSelect.addEventListener('change', refresh);

    refresh();
}

$(document).ready(function () {
    initializeRevenueChartFilter();
});