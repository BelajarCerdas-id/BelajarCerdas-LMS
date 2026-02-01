document.addEventListener('DOMContentLoaded', function () {
    window.Echo.channel('lmsContent')
        .listen('.lms.content', (event) => {
            paginateContentManagemet();
        });
});
