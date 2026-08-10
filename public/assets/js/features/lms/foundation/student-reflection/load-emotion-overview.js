let emotionChart = null;
function loadEmotionOverview(search_year) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!container || !role) return;

    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        $('#emotion-overview-loading').addClass('hidden');
        $('#emotion-overview-content').removeClass('hidden');
        $('#emotion-chart-content').addClass('hidden');
        $('#emotion-chart-empty').removeClass('hidden').addClass('block');
        $('#insight-list').addClass('hidden');
        $('#insight-empty').removeClass('hidden');

        if (emotionChart) {
            emotionChart.destroy();
            emotionChart = null;
        }

        return;
    }

    $.ajax({
        url: `/lms/${role}/foundation/student-reflection/load-emotion-overview/${foundationId}`,
        method: 'GET',
        data: {
            year: search_year,
        },
        beforeSend: function () {
            $('#emotion-overview-loading').removeClass('hidden');
            $('#emotion-overview-content').addClass('hidden');
            $('#emotion-chart-content').addClass('hidden');
            $('#emotion-chart-empty').addClass('hidden').removeClass('block');
            $('#insight-empty').addClass('hidden');
            $('#insight-list').addClass('hidden');

            if (emotionChart) {
                emotionChart.destroy();
                emotionChart = null;
            }
        },
        success: function (response) {
            const emotionList = $('#emotion-list');
            emotionList.empty();

            if (!response.has_data) {
                $('#emotion-overview-loading').addClass('hidden');
                $('#emotion-overview-content').removeClass('hidden');
                $('#emotion-chart-content').addClass('hidden');
                $('#emotion-chart-empty').removeClass('hidden').addClass('block');
                $('#insight-list').addClass('hidden');
                $('#insight-empty').removeClass('hidden');

                if (emotionChart) {
                    emotionChart.destroy();
                    emotionChart = null;
                }

                return;
            }

            $('#emotion-overview-loading').addClass('hidden');
            $('#emotion-overview-content').removeClass('hidden');
            $('#emotion-chart-content').removeClass('hidden');
            $('#emotion-chart-empty').addClass('hidden').removeClass('block');

            const theme = {
                positive: {
                    card: 'border-green-100 bg-green-50',
                    icon: 'bg-green-100 text-green-600',
                    description: 'Kondisi positif'
                },
                neutral: {
                    card: 'border-amber-100 bg-amber-50',
                    icon: 'bg-amber-100 text-amber-600',
                    description: 'Kondisi netral'
                },
                attention: {
                    card: 'border-rose-100 bg-rose-50',
                    icon: 'bg-rose-100 text-rose-600',
                    description: 'Perlu perhatian'
                }
            };

            const insightList = $('#insight-list');
            insightList.empty();

            $('#insight-empty').addClass('hidden');
            $('#insight-list').removeClass('hidden');

            $.each(response.labels, function (_, emotion) {
                const color = theme[emotion.category];

                emotionList.append(`
                    <div class="flex items-center justify-between rounded-xl border p-4" style="
                        border-color: ${emotion.chart_color}40; background-color: ${emotion.chart_color}15;">

                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color:${emotion.chart_color}20;">
                                <i class="fa-solid ${emotion.icon}" style="color:${emotion.chart_color};"></i>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-slate-700">
                                    ${emotion.label}
                                </p>

                                <p class="text-xs text-slate-500">
                                    ${color.description}
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-lg font-bold text-slate-800">
                                ${emotion.percentage}%
                            </p>

                            <p class="text-xs text-slate-500">
                                ${emotion.total} jawaban
                            </p>
                        </div>
                    </div>
                `);
            });

            const labels = response.labels.map(item => item.label);
            const totals = response.labels.map(item => item.total);
            const colors = response.labels.map(item => item.chart_color);

            renderEmotionChart(labels, totals, colors);

            const insightTheme = {
                success: {
                    card: '#ECFDF5',
                    border: '#A7F3D0',
                    color: '#059669'
                },
                info: {
                    card: '#EFF6FF',
                    border: '#BFDBFE',
                    color: '#2563EB'
                },
                warning: {
                    card: '#FFF7ED',
                    border: '#FED7AA',
                    color: '#EA580C'
                },
                positive: {
                    card: '#ECFDF5',
                    border: '#A7F3D0',
                    color: '#059669'
                },
                neutral: {
                    card: '#FEFCE8',
                    border: '#FDE68A',
                    color: '#D97706'
                },
                attention: {
                    card: '#FFF1F2',
                    border: '#FECDD3',
                    color: '#E11D48'
                }
            };

            $.each(response.insights, function (_, insight) {

                const theme = insightTheme[insight.type];

                insightList.append(`
                    <div class="rounded-xl border p-4" style="background:${theme.card}; border-color:${theme.border};">
                        <div class="flex items-start gap-3">
                            <div class="mt-1">
                                <i class="fa-solid ${insight.icon}"
                                    style="color:${theme.color};">
                                </i>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-slate-700">
                                    ${insight.title}
                                </p>

                                <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                    ${insight.message}
                                </p>
                            </div>
                        </div>
                    </div>
                `);
            });
        },
        error: function (err) {
            $('#emotion-overview-loading').addClass('hidden');
            $('#emotion-overview-content').removeClass('hidden');
            $('#emotion-chart-content').addClass('hidden');
            $('#emotion-chart-empty').removeClass('hidden').addClass('block');
            $('#insight-empty').removeClass('hidden');
            $('#insight-list').addClass('hidden');

            // Destroy chart
            if (emotionChart) {
                emotionChart.destroy();
                emotionChart = null;
            }

            console.log(err);
        }
    });
}

$('#chartYear').on('change', function () {
    loadEmotionOverview($(this).val());
});

function renderEmotionChart(labels, data, colors) {

    const ctx = document.getElementById('emotion-chart');

    if (emotionChart) {
        emotionChart.destroy();
    }

    emotionChart = new Chart(ctx, {
        type: 'doughnut',

        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '68%',

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {
                    callbacks: {
                        label: function (context) {

                            const total = context.dataset.data.reduce((a, b) => a + b, 0);

                            const percentage = total
                                ? ((context.raw / total) * 100).toFixed(1)
                                : 0;

                            return `${context.label}: ${context.raw} jawaban (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}