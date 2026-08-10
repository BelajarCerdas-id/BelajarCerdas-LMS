function loadKPI(search_academic_year) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!container || !role) return;

    if (!foundationId) {
        $('#kpi-loading').addClass('hidden');
        $('#kpi-content').removeClass('hidden');
        
        return;
    }

    $.ajax({
        url: `/lms/${role}/foundation}/school-teacher-performance-kpi/${foundationId}`,
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

            // assessments
            $('#assessment-published-percentage').text(response.assessments.percentage + '%');
            $('#assessment-published-count').text(response.assessments.published + ' / ' + response.assessments.total);
            $('#assessment-published-progress').css('width', `${response.assessments.percentage}%`);

            // contents
            $('#content-published-percentage').text(response.contents.percentage + '%');
            $('#content-published-count').text(response.contents.published + ' / ' + response.contents.total);
            $('#content-active-progress').css('width', `${response.contents.percentage}%`);

            // average publishhed
            $('#average-published-percentage').text((response.assessments.percentage + response.contents.percentage) / 2 + '%');
            $('#average-published-progress').css('width', `${(response.assessments.percentage + response.contents.percentage) / 2}%`);
        },
        error: function (err) {
            $('#kpi-loading').addClass('hidden');
            $('#kpi-content').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    loadKPI();
});

$('#filter-tahun-ajaran').on('change', function () {
    loadKPI($(this).val());
});