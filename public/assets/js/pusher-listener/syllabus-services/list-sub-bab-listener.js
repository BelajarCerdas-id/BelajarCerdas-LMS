document.addEventListener('DOMContentLoaded', function() {
    window.Echo.channel('syllabusCrud')
    .listen('.syllabus.crud', (event) => {
        paginateSyllabusSubBab();
    });
});
