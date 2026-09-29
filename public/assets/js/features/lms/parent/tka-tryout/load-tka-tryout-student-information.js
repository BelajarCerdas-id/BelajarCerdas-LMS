function loadTkaTryoutStudentInformation() {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const studentId = container.dataset.studentId;

    if (!container || !role || !schoolName || !schoolId) return;

    $('#student-information-skeleton').addClass('hidden');
    $('#student-information-content').removeClass('hidden');

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/parent/tka-tryout-monitoring/student/${studentId}/load-student-information`,
        method: 'GET',
        beforeSend: function () {
            $('#student-information-skeleton').removeClass('hidden');
            $('#student-information-content').addClass('hidden');
        },
        success: function (response) {
            $('#student-information-skeleton').addClass('hidden');
            $('#student-information-content').removeClass('hidden');

            $('#student-information-name').text(response.student?.name ?? '-');
            $('#student-information-class-value').text(response.student?.class ?? '-');
            $('#student-information-school-year-value').text(response.student?.school_year ?? '-');
        },
        error: function (err) {
            $('#student-information-skeleton').addClass('hidden');
            $('#student-information-content').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    loadTkaTryoutStudentInformation();
});