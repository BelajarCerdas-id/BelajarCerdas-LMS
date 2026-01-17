document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('bulkUploadCreateAccount')
        .listen('.bulk.upload.create.account', (event) => {
            managementRoleAccountSchoolSubscription();
        });
});