document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('managementAccount')
        .listen('.management.account', (event) => {
            managementAccountUsersSchoolSubscription();
        });
});
