document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('lmsManagementStudentInClass')
        .listen('.lms.management.student.in.class', (event) => {
            if (event.action === 'activate') {
                window.__shouldResetSelection = false; // tidak reset selection
            } else {
                window.__shouldResetSelection = true; // reset selection
            }

            managementStudentSchoolSubscription();
        });
});
