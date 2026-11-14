function renderTeacherAgendaKPI(summary) {

    $('#kpi-loading').addClass('hidden');
    $('#kpi-content').removeClass('hidden');

    $('#total-teaching-teachers').text(summary.totalTeachingTeachers);

    $('#total-submitted-agenda').text(summary.totalSubmittedAgenda);

    $('#total-pending-agenda').text(summary.totalPendingAgenda);

    $('#completion-rate').text(summary.complianceRate + '%');

}