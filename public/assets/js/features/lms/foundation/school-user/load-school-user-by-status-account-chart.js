let schoolUserByStatusChart = null;

function getSchoolUserStatusColor(label) {
    const normalized = String(label).toLowerCase().trim();

    if (normalized === 'aktif' || normalized === 'active') {
        return '#10b981';
    }

    if (normalized === 'nonaktif' || normalized === 'non-aktif' || normalized === 'inactive') {
        return '#ef4444';
    }

    return '#64748b';
}

function getSchoolUserSelectedSchoolId() {
    const schoolSelect = document.getElementById('school-user-by-school-select');

    if (!schoolSelect) {
        return '';
    }

    return schoolSelect.value || '';
}

function loadSchoolUserChartByStatus() {
    const config = getSchoolUserChartConfig();

    const {
        canvas,
        loading,
        empty
    } = getSchoolUserChartElements('status');

    if (!canvas || !loading || !empty) {
        return;
    }

    if (!config) {
        schoolUserByStatusChart = destroySchoolUserChart(schoolUserByStatusChart);

        showSchoolUserEmpty(canvas, empty);
        hideSchoolUserLoading(loading);

        return;
    }

    const schoolId = getSchoolUserSelectedSchoolId();

    resetSchoolUserChartState(canvas, loading, empty);
    schoolUserByStatusChart = destroySchoolUserChart(schoolUserByStatusChart);

    $.ajax({
        url: `/lms/${config.role}/foundation/school-user/chart-by-status/${config.foundationId}`,
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

            const labels = data.map(item => item.label);
            const values = data.map(item => Number(item.total || 0));
            const statusColors = labels.map(label => getSchoolUserStatusColor(label));
            const total = values.reduce((sum, value) => sum + value, 0);

            if (total === 0) {
                showSchoolUserEmpty(canvas, empty);
                return;
            }

            schoolUserByStatusChart = new Chart(
                canvas.getContext('2d'),
                {
                    type: 'bar',

                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Jumlah Akun',
                                data: values,
                                backgroundColor: statusColors,
                                borderColor: statusColors,
                                borderWidth: 1,
                                borderRadius: 8,
                                barPercentage: 0.55,
                                categoryPercentage: 0.6
                            }
                        ]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        animation: {
                            duration: 600,
                            easing: 'easeOutQuart'
                        },

                        scales: {
                            y: {
                                beginAtZero: true,

                                ticks: {
                                    precision: 0,
                                    color: '#64748b',
                                    font: {
                                        size: 11
                                    }
                                },

                                grid: {
                                    color: '#e2e8f0'
                                }
                            },

                            x: {
                                grid: {
                                    display: false
                                },

                                ticks: {
                                    color: '#475569',
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },

                        plugins: {
                            legend: {
                                display: false
                            },

                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `${context.raw} akun`;
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
            console.error('School User By Status Error:', xhr.status, xhr.responseText);
            showSchoolUserEmpty(canvas, empty);
        },

        complete: function () {
            hideSchoolUserLoading(loading);
        }
    });
}

$(document).on('change', '#school-user-by-school-select',
    function () {
        const schoolId = this.value || '';
        localStorage.setItem('school_user_selected_school', schoolId);
        loadSchoolUserChartByStatus();
    }
);

$(document).ready(function () {
    loadSchoolUserChartByStatus();
});