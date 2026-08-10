function renderSchoolFoundationKPI(summary) {

    $('#kpi-loading').addClass('hidden');
    $('#kpi-content').removeClass('hidden');

    $('#total-school-foundation').text(summary.total_school_foundation);

    $('#total-school').text(summary.total_school);

    $('#total-teacher').text(summary.total_teacher);

    $('#total-student').text(summary.total_student);

}