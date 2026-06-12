function loadReflectionDetailHeader() {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const reflectionQuestionId =  container.dataset.reflectionQuestionId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!reflectionQuestionId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/history-detail/${reflectionQuestionId}/load-header`,
        method: 'GET',
        success: function (response) {
            const item = response.data;

            if (!item) return;

            const formatDate = (dateString) => {
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                const date = new Date(dateString);
                const day = date.getDate();
                const monthName = months[date.getMonth()];
                const year = date.getFullYear();

                return `${day} ${monthName} ${year}`;
            };

            // Format tanggal
            const createdAt = item.created_at ? `${formatDate(item.created_at)}` : 'Tanggal tidak tersedia';

            $('#reflection-title').text(item.title ?? '-');

            $('#reflection-question').text(item.question ?? '-');

            const targetKelas = item.sch_refl_target?.length
                ? item.sch_refl_target
                    .map(target => target.kelas?.kelas)
                    .filter(Boolean)
                    .join(', ')
                : '-';

            $('#reflection-meta-wrapper').html(`
                <div class="px-4 py-2 rounded-2xl bg-white/10 border border-white/20 text-sm font-semibold">
                    <i class="fa-solid fa-calendar-days mr-2"></i>
                    ${createdAt}
                </div>

                <div data-reflection-id="${item.id}" class="px-4 py-2 rounded-2xl bg-white/10 border border-white/20 text-sm font-semibold">
                    <i class="fa-solid fa-users mr-2"></i>
                    <span class="total-responden"> ${item.total_responden ?? 0} Responden </span>
                </div>

                <div class="px-4 py-2 rounded-2xl bg-white/10 border border-white/20 text-sm font-semibold">
                    <i class="fa-solid fa-school mr-2"></i>
                    ${targetKelas}
                </div>
            `);
        },
        error: function (xhr, status, error) {
            console.error('Terjadi kesalahan:', status, error);
        }
    });
}

$(document).ready(function () {
    loadReflectionDetailHeader();
});

function updateHistoryDetailHeaderCount(reflectionId, totalResponden) {

    const card = $(`[data-reflection-id="${reflectionId}"]`);

    if (!card.length) return;

    card.find('.total-responden').text(`${totalResponden} Jawaban`);
}