function paginateCurriculumManagement(page = 1) {
    const container = document.getElementById('container-curriculum-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    fetchDataCurriculum(schoolName, schoolId);
    
    function fetchDataCurriculum() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/kurikulum/paginate`,
            method: 'GET',
            data: { page: page },
            success: function (data) {
                $('#tbody-curriculum-management').empty();
                $('.pagination-container-curriculum-management').empty();
    
                if (data.data.length > 0) {
                    // Render rows
                    $.each(data.data, function (index, item) {
    
                        let faseDetail = data.faseDetail.replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumName', item.nama_kurikulum)
                            .replace(':curriculumId', item.id);
    
                        $('#tbody-curriculum-management').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">${item.nama_kurikulum ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${faseDetail}" class="btn-fase-detail" data-id="${item.id}" data-nama-kurikulum="${item.nama_kurikulum}">
                                        <div class="text-[#0071BC]">
                                            <span>Detail</span>
                                            <i class="fas fa-chevron-right text-xs"></i>
                                        </div>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
    
                    // Insert pagination HTML
                    $('.pagination-container-curriculum-management').html(data.links);
    
                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();
    
                    $('#empty-message-curriculum-management').hide();
                    $('.thead-table-curriculum-management').show();
                } else {
                    $('#empty-message-curriculum-management').show();
                    $('.thead-table-curriculum-management').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-curriculum-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateCurriculumManagement(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateCurriculumManagement();
});