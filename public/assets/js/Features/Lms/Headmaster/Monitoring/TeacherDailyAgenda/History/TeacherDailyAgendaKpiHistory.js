function renderTeacherAgendaKPIHistory(summary) {

    $('#teacher-agenda-summary-skeleton').addClass('hidden');
    $('#teacher-agenda-summary').removeClass('hidden');

    $('#summary-total-agenda').text(summary.totalTeachingTeachers);

    $('#summary-filled').text(summary.totalSubmittedAgenda);

    $('#summary-unfilled').text(summary.totalPendingAgenda);

    $('#summary-compliance').text(summary.complianceRate + '%');

}