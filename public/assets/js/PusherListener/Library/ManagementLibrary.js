document.addEventListener('DOMContentLoaded', () => {

    window.Echo.channel('managementLibrary')

        .listen('.management.library', (event) => {

            paginateLibrary();

        });

});