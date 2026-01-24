// untuk mendengarkan event broadcast ketika user mengedit soal dan terjadi perubahan di detail soal
document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('editQuestionBankPG')
        .listen('.edit.question.bank.pg', (event) => {
            paginateBankSoalDetail();
        });
    });

// untuk mendengarkan event broadcast ketika user insert soal dan update soal terbaru di detail soal
document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('bankSoalLmsUploaded')
        .listen('.bulk.upload.soal.lms', (event) => {
            paginateBankSoalDetail();
        });
});