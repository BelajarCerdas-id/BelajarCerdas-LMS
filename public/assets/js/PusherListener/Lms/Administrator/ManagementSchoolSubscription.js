document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('managementSchoolSubscription')
        .listen('.management.school.subscription', (event) => {
            paginateLmsSchoolSubscription();
        });
});
