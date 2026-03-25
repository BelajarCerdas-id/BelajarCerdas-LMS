document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('lmsManagementMajors')
        .listen('.lms.management.majors', (event) => {
            managementMajorsSchoolSubscription();
        });
});
