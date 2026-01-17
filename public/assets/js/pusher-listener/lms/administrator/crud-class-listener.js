document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('lmsManagementClass')
        .listen('.lms.management.class', (event) => {
            managementClassSchoolSubscription($('#dropdown-filter-class').val(), $('#dropdown-filter-year').val());
        });
});
