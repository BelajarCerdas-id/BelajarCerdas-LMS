let schoolUserByRoleChart = null;

function loadSchoolUserChartByRole() {
    const config = getSchoolUserChartConfig();
    const { canvas, loading, empty } = getSchoolUserChartElements('role');

    if (!canvas || !loading || !empty) {
        return;
    }

    if (!config) {
        schoolUserByRoleChart = destroySchoolUserChart(schoolUserByRoleChart);
        showSchoolUserEmpty(canvas, empty);
        hideSchoolUserLoading(loading);
        return;
    }

    const schoolSelect = document.getElementById('school-user-by-school-select');
    const schoolId = schoolSelect ? schoolSelect.value : '';

    resetSchoolUserChartState(canvas, loading, empty);
    schoolUserByRoleChart = destroySchoolUserChart(schoolUserByRoleChart);

    $.ajax({
        url: `/lms/${config.role}/foundation/school-user/chart-by-role/${config.foundationId}`,
        method: 'GET',
        data: {
            school_id: schoolId || null
        },
        success: function (response) {
            const data = Array.isArray(response.data) ? response.data : [];

            if (!data.length) {
                showSchoolUserEmpty(canvas, empty);
                return;
            }

            const labels = data.map(function (item) {
                return item.role;
            });

            const values = data.map(function (item) {
                return Number(item.total || 0);
            });

            const colors = labels.map(function (role) {
                return getSchoolUserRoleColor(role);
            });

            const total = values.reduce(function (sum, value) {
                return sum + value;
            }, 0);

            if (total === 0) {
                showSchoolUserEmpty(canvas, empty);
                return;
            }

            schoolUserByRoleChart = new Chart(
                canvas.getContext('2d'),
                {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                data: values,
                                backgroundColor: colors,
                                borderWidth: 3,
                                borderColor: '#ffffff'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        animation: {
                            duration: 600,
                            easing: 'easeOutQuart'
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                align: 'center',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 14,
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const percentage = total > 0
                                            ? ((context.raw / total) * 100).toFixed(1)
                                            : 0;

                                        return `${context.label}: ${context.raw} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                }
            );

            showSchoolUserChart(canvas);
        },
        error: function (xhr) {
            console.error(
                'School User By Role Error:',
                xhr.status,
                xhr.responseText
            );

            showSchoolUserEmpty(canvas, empty);
        },
        complete: function () {
            hideSchoolUserLoading(loading);
        }
    });
}

$(document).on('change', '#school-user-by-school-select', function () {
    loadSchoolUserChartByRole();
});

$(document).ready(function () {
    loadSchoolUserChartByRole();
});