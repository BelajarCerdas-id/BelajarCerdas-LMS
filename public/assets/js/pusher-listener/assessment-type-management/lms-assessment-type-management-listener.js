document.addEventListener('DOMContentLoaded', function () {
    window.Echo.channel('lmsAssessmentType')
        .listen('.lms.assessment.type', (event) => {
            assessmentTypeManagement();
        });
});
