document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('editor-container');
    if (!container) return;

    questionId = container.dataset.questionId;

    window.Echo.channel(`editQuestionBankPG.${questionId}`)
        .listen('.edit.question.bank.pg', (event) => {
            formQuestionBankEdit();
        });
});
