function loadKPI(search_academic_year) {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container || !role) return;

    $('#kpi-loading').addClass('hidden');
    $('#kpi-content').removeClass('hidden');

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/load-kpi`,
        method: 'GET',
        data: {
            academic_year: search_academic_year
        },
        beforeSend: function () {
            $('#kpi-loading').removeClass('hidden');
            $('#kpi-content').addClass('hidden');
        },
        success: function (response) {
            $('#kpi-loading').addClass('hidden');
            $('#kpi-content').removeClass('hidden');

            $('#total-tka-tryout-period').text(response.total_tryout_tka_period);
            $('#total-tka-tryout-period-default').text(response.total_tryout_tka_period_default);
            $('#total-tka-tryout-period-override').text(response.total_tryout_tka_period_override);
            $('#total-tka-tryout-school-override').text(response.total_tryout_tka_school_override);
        },
        error: function (err) {
            $('#kpi-loading').addClass('hidden');
            $('#kpi-content').removeClass('hidden');

            console.log(err);
        }
    });
}

$('#search-academic-year').on('change', function () {
    loadKPI($(this).val());
});