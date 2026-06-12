let currentPage = 1;
function paginateReflectionManagementHistory(page = 1) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    currentPage = page;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/history/paginate`,
        method: 'GET',
        data: {
            page: page,
        },
        beforeSend: function () {
            $('#loading-reflection-history').show();
            $('#table-list-reflection-history').empty();
            $('.pagination-container-reflection-history').empty();
            $('#empty-message-reflection-history').hide();
            $('.thead-table-reflection-history').hide();
        },
        success: function (response) {
            $('#loading-reflection-history').hide();
            $('#table-list-reflection-history').empty();
            $('.pagination-container-reflection-history').empty();

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {
                    const historyDetail = response.historyDetail.replace(':role', role).replace(':schoolName', schoolName)
                        .replace(':schoolId', schoolId).replace(':reflectionQuestionId', item.id);
                    
                    $('#table-list-reflection-history').append(`
                        <tr data-reflection-id="${item.id}">
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2">${item.title ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2">${item.question ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center total-responden">${item.total_responden ?? '0'} Jawaban</td>
                            <td class="border border-gray-300 px-3 py-2">
                                <div class="flex justify-center gap-1">
                                    ${
                                        item.sch_refl_target?.map(target => `
                                            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium">
                                                ${target.kelas?.kelas ?? '-'}
                                            </span>
                                        `).join('') || '-'
                                    }
                                </div>
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <a href="${historyDetail}">
                                    <button class="inline-flex items-center gap-2 px-3 py-2 rounded-xlbg-blue-50 text-[#2563EB] hover:bg-blue-100
                                        font-semibold text-sm transition-all cursor-pointer rounded-lg">
                                        <i class="fas fa-eye"></i>
                                        Lihat
                                    </button>
                                </a>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-reflection-history').html(response.links);
                bindPaginationLinks();
                $('#empty-message-reflection-history').hide();
                $('.thead-table-reflection-history').show();
            } else {
                $('#empty-message-reflection-history').show();
                $('.thead-table-reflection-history').hide();
            }
        },
        error: function (xhr, status, error) {
            $('#loading-reflection-history').hide();
            console.error('Terjadi kesalahan:', status, error);
        }
    });
}

function bindPaginationLinks() {
    $('.pagination-container-reflection-history').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateReflectionManagementHistory(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateReflectionManagementHistory();
});

function updateHistoryCount(reflectionId, totalResponden) {

    const card = $(`[data-reflection-id="${reflectionId}"]`);

    if (!card.length) return;

    card.find('.total-responden').text(`${totalResponden} Jawaban`);
}