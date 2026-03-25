document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('bankSoalLmsUploaded')
        .listen('.bulk.upload.soal.lms', (event) => {
            paginateBankSoal();
        });
});