let reflectionChartInstance = null;
let emotionChartInstance = null;

function loadReflectionDetailChart() {

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const reflectionQuestionId = container.dataset.reflectionQuestionId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!reflectionQuestionId) return;

    setLoadingState(true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/history-detail/${reflectionQuestionId}/load-chart`,
        method: 'GET',

        success: function (response) {

            setTimeout(() => {

                setLoadingState(false);

                const reflectionCtx = document.getElementById('studentReflectionChart');
                const emotionCtx = document.getElementById('emotionChart');

                if (!reflectionCtx || !emotionCtx) {
                    return;
                }

                // Destroy chart lama jika ada
                if (reflectionChartInstance) {
                    reflectionChartInstance.destroy();
                }

                if (emotionChartInstance) {
                    emotionChartInstance.destroy();
                }

                const hasReflection = response.reflection;
                const hasEmotionData = response.emotion_chart.data.some(item => item > 0);

                if (hasReflection) {
                    $('#reflection-chart-content').removeClass('hidden');
                    $('#empty-message-student-reflection-chart').addClass('hidden');

                    const isMobile = window.innerWidth < 768;

                    reflectionChartInstance = new Chart(reflectionCtx, {
                        type: 'bar',
                        data: {
                            labels: response.reflection_answered.labels,
                            datasets: [
                                {
                                    label: 'Sudah Menjawab',
                                    data: response.reflection_answered.answered,
                                    backgroundColor: '#22C55E'
                                },
                                {
                                    label: 'Belum Menjawab',
                                    data: response.reflection_answered.unanswered,
                                    backgroundColor: '#EF4444'
                                }
                            ]
                        },
                        options: {
                            indexAxis: isMobile ? 'y' : 'x',
                            responsive: true,
                            maintainAspectRatio: false,
                            resizeDelay: 200,
    
                            animation: {
                                duration: 1200,
                                easing: 'easeOutQuart'
                            },
    
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            }
                        }
                    });
                } else {
                    $('#reflection-chart-content').addClass('hidden');
                    $('#empty-message-student-reflection-chart').removeClass('hidden');
                }

                if (hasEmotionData) {
                    $('#emotion-chart-content').removeClass('hidden');
                    $('#empty-message-emotion-chart').addClass('hidden');

                    emotionChartInstance = new Chart(emotionCtx, {
                        type: 'doughnut',
                        data: {
                            labels: response.emotion_chart.labels,
                            datasets: [{
                                data: response.emotion_chart.data,
                                backgroundColor: response.emotion_chart.colors,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            resizeDelay: 200,
    
                            animation: {
                                duration: 1200,
                                easing: 'easeOutQuart'
                            },
    
                            cutout: '70%',
    
                            interaction: {
                                intersect: false,
                                mode: 'nearest'
                            },
    
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                } else { 
                    $('#emotion-chart-content').addClass('hidden');
                    $('#empty-message-emotion-chart').removeClass('hidden');
                }

            }, 150);

        },

        error: function (xhr) {
            setLoadingState(false);
            console.error(xhr);
        }
    });
}

$(document).ready(function () {
    loadReflectionDetailChart();
});

function setLoadingState(isLoading) {

    if (isLoading) {

        $('#emotion-chart-loading').removeClass('hidden');

        $('#emotion-chart-content').addClass('hidden');
        $('#empty-message-emotion-chart').addClass('hidden');

    } else {

        $('#reflection-chart-loading').addClass('hidden');
        $('#emotion-chart-loading').addClass('hidden');
    }
}

function updateReflectionChart(chartData) {
    console.log(chartData.labels);

    if (!reflectionChartInstance) {
        loadReflectionDetailChart();
        return;
    }

    reflectionChartInstance.data.labels =
        chartData.labels;

    reflectionChartInstance.data.datasets[0].data =
        chartData.answered;

    reflectionChartInstance.data.datasets[1].data =
        chartData.unanswered;

    reflectionChartInstance.update();
}

function updateEmotionChart(emotions) {

    const hasEmotionData = emotions.some(item => item.total > 0);

    if (!hasEmotionData) {
        return;
    }

    // chart belum pernah dibuat
    if (!emotionChartInstance) {

        $('#emotion-chart-content').removeClass('hidden');
        $('#empty-message-emotion-chart').addClass('hidden');

        createEmotionChart(emotions);

        return;
    }

    // chart sudah ada -> update saja
    emotionChartInstance.data.datasets[0].data =
        emotions.map(item => item.total);

    emotionChartInstance.update();
}

function createEmotionChart(emotions) {

    const emotionCtx = document.getElementById('emotionChart');

    emotionChartInstance = new Chart(emotionCtx, {
        type: 'doughnut',
        data: {
            labels: emotions.map(item => item.label),
            datasets: [{
                data: emotions.map(item => item.total),
                backgroundColor: [
                    '#22C55E',
                    '#3B82F6',
                    '#94A3B8',
                    '#F59E0B',
                    '#EF4444'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 200,

            animation: {
                duration: 1200,
                easing: 'easeOutQuart'
            },

            cutout: '70%',

            interaction: {
                intersect: false,
                mode: 'nearest'
            },

            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}