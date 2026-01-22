document.addEventListener('DOMContentLoaded', () => {
    window.Echo.channel('activateQuestionBankPG')
        .listen('.activate.question.bank.pg', (event) => {
            paginateBankSoal();
        });
});