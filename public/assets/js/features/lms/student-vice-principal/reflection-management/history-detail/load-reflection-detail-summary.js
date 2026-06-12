function loadReflectionDetailSummary() {

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const reflectionQuestionId = container.dataset.reflectionQuestionId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!reflectionQuestionId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/history-detail/${reflectionQuestionId}/load-summary`,
        method: 'GET',

        success: function (response) {

            const data = response.data;

            const totalSiswa = response.kelas.reduce((sum, item) => {
                return sum + item.total_siswa;
            }, 0);

            $('#summary-total-responden').html(`
                <span id="text-total-responden">${data.total_responden}</span>
                <span class="text-lg text-slate-400"> / ${totalSiswa} </span>
            `);

            $('#summary-participation').text(
                `${response.participation_percentage ?? 0}% Partisipasi`
            );

            $('#summary-positive').text(response.positive);
            $('#summary-neutral').text(response.neutral);
            $('#summary-attention').text(response.attention);

            $('#summary-skeleton-total').hide();
            $('#summary-skeleton-positive').hide();
            $('#summary-skeleton-neutral').hide();
            $('#summary-skeleton-attention').hide();

            $('#summary-total-content').removeClass('hidden');
            $('#summary-positive-content').removeClass('hidden');
            $('#summary-neutral-content').removeClass('hidden');
            $('#summary-attention-content').removeClass('hidden');
        },

        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

$(document).ready(function () {
    loadReflectionDetailSummary();
});

function updateHistoryDetailSummaryCount(data) {
    $('#text-total-responden').text(data.total_responden);
    $('#summary-participation').text(`${data.participation_percentage ?? 0}% Partisipasi`);
    $('#summary-positive').text(data.positive);
    $('#summary-neutral').text(data.neutral);
    $('#summary-attention').text(data.attention);
}