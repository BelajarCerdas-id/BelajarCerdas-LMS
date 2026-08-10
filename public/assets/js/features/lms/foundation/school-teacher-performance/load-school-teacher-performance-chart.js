let schoolTeacherPerformanceChart = null;
function schoolTeacherPerformanceChartLoad(search_academic_year) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!role) return;
    
    const loadingEl = document.getElementById('school-teacher-performance-chart-loading');
    const canvasEl = document.getElementById('school-teacher-performance-chart');
    const emptyEl = document.getElementById('empty-message-school-teacher-performance-chart');

    if (!loadingEl || !canvasEl || !emptyEl) return;

    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        loadingEl.classList.add('hidden');
        canvasEl.classList.add('hidden');
        emptyEl.classList.remove('hidden');

        return;
    }

    loadingEl.classList.remove('hidden');
    canvasEl.classList.add('hidden');
    emptyEl.classList.add('hidden');

    destroySchoolTeacherPerformanceChart();

    $.ajax({
        url: `/lms/${role}/foundation/school-teacher-performance/load-chart/${foundationId}`,
        method: 'GET',
        data: {
            academic_year: search_academic_year
        },

        success: function (response) {
            const chartData = Array.isArray(response.data) ? response.data : [];

            if (chartData.length === 0) {
                emptyEl.classList.remove('hidden');
                emptyEl.classList.add('flex');

                return;
            }

            const labels = chartData.map(function (item) {
                return item.school_name;
            });

            const assessmentData = chartData.map(function (item) {
                return Number(item.assessment_percentage || 0);
            });

            const contentData = chartData.map(function (item) {
                return Number(item.content_percentage || 0);
            });

            const hasData = assessmentData.some(function (value) {
                return value > 0;
            }) || contentData.some(function (value) {
                return value > 0;
            });


            if (!hasData) {
                emptyEl.classList.remove('hidden');
                emptyEl.classList.add('flex');

                return;
            }

            renderSchoolTeacherPerformanceChart(canvasEl, labels, assessmentData, contentData);

            canvasEl.classList.remove('hidden');
        },
        error: function (xhr, status, error) {
            console.error('School Teacher Performance Chart Error:', error);
            console.error('Response:', xhr.responseText);

            destroySchoolTeacherPerformanceChart();
            emptyEl.classList.remove('hidden');
            emptyEl.classList.add('flex');
        },

        complete: function () {
            loadingEl.classList.add('hidden');
        }
    });
}

// render chart
function renderSchoolTeacherPerformanceChart(canvasEl, labels, assessmentData, contentData) {

    const ctx = canvasEl.getContext('2d');
    if (!ctx) return;

    destroySchoolTeacherPerformanceChart();

    schoolTeacherPerformanceChart = new Chart(ctx, {
        type: 'bar',
        data: {

            // nama sekolah
            labels: labels,
            datasets: [
                {
                    label: 'Assessmen',
                    data: assessmentData,
                    borderWidth: 1,
                    borderRadius: 6
                },

                {
                    label: 'Materi',
                    data: contentData,
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            },

            scales: {
                // Y Axis = Percentage
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function (value) {
                            return value + '%';
                        }
                    },

                    grid: {
                        drawBorder: false
                    }
                },

                // X Axis = Nama Sekolah
                x: {
                    grid: {
                        display: false
                    },

                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            },

            plugins: {
                legend: {
                    position: 'top',
                    align: 'center',

                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },

                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${ context.dataset.label }: ${ context.raw }% `;
                        }
                    }
                }
            }
        }
    });
}

// destroy chart
function destroySchoolTeacherPerformanceChart() {

    if (!schoolTeacherPerformanceChart) {
        return;
    }

    schoolTeacherPerformanceChart.destroy();
    schoolTeacherPerformanceChart = null;
}

$(document).ready(function () {
    const container = document.getElementById('container');
    if (!container) return;

    const foundationId = container.dataset.foundationId;

    // Tidak ada foundation
    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        const loadingEl = document.getElementById('school-teacher-performance-chart-loading');
        const canvasEl = document.getElementById('school-teacher-performance-chart');
        const emptyEl = document.getElementById('empty-message-school-teacher-performance-chart');

        if (!loadingEl || !canvasEl || !emptyEl) {
            return;
        }

        loadingEl.classList.add('hidden');
        canvasEl.classList.add('hidden');
        emptyEl.classList.remove('hidden');
        emptyEl.classList.add('flex');

        return;
    }

    const filter = $('#filter-tahun-ajaran');

    filter.on('change', function () {
        const selectedYear = $(this).val();
        if (!selectedYear) {
            return;
        }

        schoolTeacherPerformanceChartLoad(selectedYear);
    });

});