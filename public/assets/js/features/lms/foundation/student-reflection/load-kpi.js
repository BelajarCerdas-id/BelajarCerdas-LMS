function loadKPI(search_year) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!container || !role) return;

    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        $('#kpi-loading').addClass('hidden');
        $('#kpi-content').removeClass('hidden');
        
        return;
    }

    $.ajax({
        url: `/lms/${role}/foundation/student-reflection-kpi/${foundationId}`,
        method: 'GET',
        data: {
            year: search_year,
        },
        beforeSend: function () {
            $('#kpi-loading').removeClass('hidden');
            $('#kpi-content').addClass('hidden');
        },

        success: function (response) {

            $('#kpi-loading').addClass('hidden');
            $('#kpi-content').removeClass('hidden');

            const emotionTheme = {
                positive: {
                    card: 'border-green-100 bg-green-50',
                    iconWrapper: 'bg-green-500/10',
                    icon: 'text-green-600',
                    percentage: 'text-green-600'
                },
                neutral: {
                    card: 'border-amber-100 bg-amber-50',
                    iconWrapper: 'bg-amber-500/10',
                    icon: 'text-amber-600',
                    percentage: 'text-amber-600'
                },
                attention: {
                    card: 'border-rose-100 bg-rose-50',
                    iconWrapper: 'bg-rose-500/10',
                    icon: 'text-rose-600',
                    percentage: 'text-rose-600'
                }
            };

            // KPI
            $('#total-reflection').text(response.total_reflection);
            $('#positive-condition').text(response.positive_percentage + '%');
            $('#positive-category').text(response.positive_emotions.join(' & '));
            $('#completion-percentage').text(response.completionPercentage_percentage + '%');
            $('#dominant-emotion-card').removeClass('border-green-100 bg-green-50 border-amber-100 bg-amber-50 border-rose-100 bg-rose-50 border-slate-200 bg-slate-50');
            $('#dominant-emotion-icon-wrapper').removeClass('bg-green-500/10 bg-amber-500/10 bg-rose-500/10 bg-slate-200');
            $('#dominant-percentage').removeClass('text-green-600 text-amber-600 text-rose-600 text-slate-600');

            // Ada emosi dominan
            if (response.dominant_emotion !== null) {

                const emotion = response.dominant_emotion;
                const theme = emotionTheme[emotion.category];

                $('#dominant-emotion').text(emotion.label);
                $('#dominant-percentage').text(response.dominant_percentage + '%');
                $('#dominant-emotion-card').addClass(theme.card);
                $('#dominant-emotion-icon-wrapper').addClass(theme.iconWrapper);
                $('#dominant-emotion-icon').attr('class', `fa-solid ${emotion.icon} text-xl ${theme.icon}`);
                $('#dominant-percentage').addClass(theme.percentage);
            }

            // Tidak ada emosi dominan
            else {

                $('#dominant-emotion').text('Tidak Ada Emosi Dominan');
                $('#dominant-percentage').text('-');
                $('#dominant-emotion-card').addClass('border-slate-200 bg-slate-50');
                $('#dominant-emotion-icon-wrapper').addClass('bg-slate-200');
                $('#dominant-emotion-icon').attr('class', 'fa-solid fa-scale-balanced text-xl text-slate-600');
                $('#dominant-percentage').addClass('text-slate-600');
            }

        },

        error: function (err) {

            $('#kpi-loading').addClass('hidden');
            $('#kpi-content').removeClass('hidden');

            console.log(err);

        }
    });
}

$('#chartYear').on('change', function () {
    loadKPI($(this).val());
});