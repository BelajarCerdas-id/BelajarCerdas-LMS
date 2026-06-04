document.addEventListener('DOMContentLoaded', function () {

    window.Echo.channel('studentDailyReflectionForm')
        .listen('.student.daily.reflection.form', (event) => {
            studentDailyReflectionForm();
        });
});