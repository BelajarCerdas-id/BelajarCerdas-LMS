document.addEventListener('DOMContentLoaded', function () {
    window.Echo.channel('officeAccountManagement')
        .listen('.office.account.management', (event) => {
            paginateManageUser(1, $('#search_account').val());
        });
});
