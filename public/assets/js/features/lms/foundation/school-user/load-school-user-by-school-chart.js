let schoolUserBySchoolChart = null;
let schoolUserBySchoolData = [];
let schoolUserBySchoolRoles = [];

function loadSchoolUserChartBySchool() {
    const { canvas, loading, empty } = getSchoolUserChartElements('school');
    const select = document.getElementById('school-user-by-school-select');

    if (!canvas || !loading || !empty || !select) {
        return;
    }

    const config = getSchoolUserChartConfig();

    if (!config) {
        schoolUserBySchoolChart = destroySchoolUserChart(schoolUserBySchoolChart);
        schoolUserBySchoolData = [];
        schoolUserBySchoolRoles = [];

        resetSchoolUserChartState(canvas, loading, empty);

        select.innerHTML = '';

        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Tidak ada data sekolah';
        select.appendChild(option);

        select.disabled = true;
        select.classList.add('opacity-60', 'cursor-default', 'bg-slate-50');
        select.classList.remove('cursor-pointer');

        localStorage.removeItem('school_user_selected_school');

        showSchoolUserEmpty(canvas, empty);
        hideSchoolUserLoading(loading);

        return;
    }

    resetSchoolUserChartState(canvas, loading, empty);
    schoolUserBySchoolChart = destroySchoolUserChart(schoolUserBySchoolChart);

    select.disabled = true;
    select.classList.add('opacity-60', 'cursor-default', 'bg-slate-50');
    select.classList.remove('cursor-pointer');

    select.innerHTML = '';

    const loadingOption = document.createElement('option');
    loadingOption.value = '';
    loadingOption.textContent = 'Memuat data sekolah...';
    select.appendChild(loadingOption);

    $.ajax({
        url: `/lms/${config.role}/foundation/school-user/chart-by-school/${config.foundationId}`,
        method: 'GET',

        success: function (response) {
            const data = Array.isArray(response.data) ? response.data : [];

            if (!data.length) {
                schoolUserBySchoolData = [];
                schoolUserBySchoolRoles = [];

                select.innerHTML = '';

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'Tidak ada data sekolah';
                select.appendChild(emptyOption);

                select.disabled = true;
                select.classList.add('opacity-60', 'cursor-default', 'bg-slate-50');
                select.classList.remove('cursor-pointer');

                localStorage.removeItem('school_user_selected_school');

                showSchoolUserEmpty(canvas, empty);
                return;
            }

            schoolUserBySchoolData = data;

            let roles = Array.isArray(response.roles) ? response.roles : [];

            if (!roles.length) {
                roles = extractSchoolUserRoles(data);
            }

            schoolUserBySchoolRoles = sortSchoolUserRoles(roles);

            select.innerHTML = '';

            const allOption = document.createElement('option');
            allOption.value = '';
            allOption.textContent = 'Semua Sekolah';
            select.appendChild(allOption);

            data.forEach(function (school) {
                const option = document.createElement('option');
                option.value = school.school_id;
                option.textContent = school.school_name;
                select.appendChild(option);
            });

            select.disabled = false;
            select.classList.remove('opacity-60', 'cursor-default', 'bg-slate-50');
            select.classList.add('cursor-pointer');

            const savedSchoolId = localStorage.getItem('school_user_selected_school');
            let selectedSchoolId = '';

            if (
                savedSchoolId &&
                data.some(function (school) {
                    return String(school.school_id) === String(savedSchoolId);
                })
            ) {
                selectedSchoolId = savedSchoolId;
            } else {
                localStorage.removeItem('school_user_selected_school');
            }

            select.value = selectedSchoolId;
            renderSchoolUserBySchoolChart(selectedSchoolId);
        },

        error: function (xhr) {
            console.error('School User By School Error:', xhr.responseText);

            schoolUserBySchoolData = [];
            schoolUserBySchoolRoles = [];

            select.innerHTML = '';

            const errorOption = document.createElement('option');
            errorOption.value = '';
            errorOption.textContent = 'Gagal memuat data sekolah';
            select.appendChild(errorOption);

            select.disabled = true;
            select.classList.add('opacity-60', 'cursor-default', 'bg-slate-50');
            select.classList.remove('cursor-pointer');

            showSchoolUserEmpty(canvas, empty);
        },

        complete: function () {
            hideSchoolUserLoading(loading);
        }
    });
}

function renderSchoolUserBySchoolChart(schoolId) {
    const { canvas, loading, empty } = getSchoolUserChartElements('school');

    if (!canvas || !loading || !empty) {
        return;
    }

    resetSchoolUserChartState(canvas, loading, empty);
    schoolUserBySchoolChart = destroySchoolUserChart(schoolUserBySchoolChart);

    const roles = schoolUserBySchoolRoles;

    if (!roles.length) {
        showSchoolUserEmpty(canvas, empty);
        hideSchoolUserLoading(loading);
        return;
    }

    let schoolName = '';
    let roleData = {};

    if (!schoolId) {
        schoolName = 'Semua Sekolah';

        roles.forEach(function (role) {
            roleData[role] = 0;
        });

        schoolUserBySchoolData.forEach(function (school) {
            roles.forEach(function (role) {
                roleData[role] += Number((school.roles && school.roles[role]) || 0);
            });
        });
    } else {
        const school = schoolUserBySchoolData.find(function (item) {
            return String(item.school_id) === String(schoolId);
        });

        if (!school) {
            showSchoolUserEmpty(canvas, empty);
            hideSchoolUserLoading(loading);
            return;
        }

        schoolName = school.school_name;

        roles.forEach(function (role) {
            roleData[role] = Number((school.roles && school.roles[role]) || 0);
        });
    }

    const labels = [];
    const values = [];
    const colors = [];

    roles.forEach(function (role) {
        labels.push(role);
        values.push(roleData[role] || 0);
        colors.push(getSchoolUserRoleColor(role));
    });

    const total = values.reduce(function (sum, value) {
        return sum + value;
    }, 0);

    if (total === 0) {
        showSchoolUserEmpty(canvas, empty);
        hideSchoolUserLoading(loading);
        return;
    }

    schoolUserBySchoolChart = new Chart(
        canvas.getContext('2d'),
        {
            type: 'bar',

            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Jumlah Pengguna',
                        data: values,
                        backgroundColor: colors,
                        borderColor: colors,
                        borderWidth: 1,
                        borderRadius: 7,
                        barPercentage: 0.65,
                        categoryPercentage: 0.75
                    }
                ]
            },

            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,

                animation: {
                    duration: 500,
                    easing: 'easeOutQuart'
                },

                interaction: {
                    mode: 'nearest',
                    intersect: true
                },

                scales: {
                    x: {
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

                    y: {
                        grid: {
                            display: false
                        },

                        ticks: {
                            color: '#475569',
                            font: {
                                size: 11
                            },
                            padding: 8
                        }
                    }
                },

                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        callbacks: {
                            title: function () {
                                return schoolName;
                            },

                            label: function (context) {
                                return `${context.label}: ${context.raw} pengguna`;
                            }
                        }
                    }
                }
            }
        }
    );

    empty.classList.add('hidden');
    empty.classList.remove('flex');
    showSchoolUserChart(canvas);
    hideSchoolUserLoading(loading);
}

$(document).on('change', '#school-user-by-school-select', function () {
    const schoolId = this.value || '';

    localStorage.setItem('school_user_selected_school', schoolId);

    renderSchoolUserBySchoolChart(schoolId);
    loadSchoolUserChartByRole();
    loadSchoolUserChartByStatus();
});

$(document).ready(function () {
    loadSchoolUserChartBySchool();
});