function loadKPI() {
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
        url: `/lms/${role}/foundation/school-user-kpi/${foundationId}`,
        method: 'GET',
        beforeSend: function () {
            $('#kpi-loading').removeClass('hidden');
            $('#kpi-content').addClass('hidden');
        },
        success: function (response) {
            $('#kpi-loading').addClass('hidden');
            $('#kpi-content').removeClass('hidden');

            $('#total-user').text(response.total_user);
            $('#total-teacher').text(response.total_teacher);
            $('#total-student').text(response.total_student);
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
})