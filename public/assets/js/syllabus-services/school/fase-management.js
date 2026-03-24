function paginateFaseManagement(page = 1) {
    const container = document.getElementById('container-fase-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumName) return;
    if (!curriculumId) return;

    fetchDataFase(schoolName, schoolId, curriculumName, curriculumId);

    function fetchDataFase() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/${curriculumName}/${curriculumId}/fase/paginate`,
            method: 'GET',
            data: { page: page },
            success: function (response) {
                $('#tbody-fase-management').empty();
                $('.pagination-container-fase-management').empty();

                if (response.data.length > 0) {
                    const schoolDetailCard = document.getElementById('school-detail-card');
                    const schoolIdentity = response.schoolIdentity;

                    schoolDetailCard.innerHTML = `
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

                            <!-- KIRI : ICON + NAMA SEKOLAH -->
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#EEF6FF] flex items-center justify-center text-[#0071BC] text-2xl shadow-sm">
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-gray-800 leading-tight">
                                        ${schoolIdentity.nama_sekolah}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Detail langganan LMS sekolah
                                    </p>
                                </div>
                            </div>

                            <!-- KANAN : INFO SEKOLAH -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full lg:w-auto">

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NPSN</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.npsn}</p>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NIK Kepala Sekolah</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.user_account?.school_staff_profile?.nik}</p>
                                </div>

                                <div class="bg-[#EEF6FF] rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-[#0071BC] mb-1">Total Pengguna</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${response.countUsers}</p>
                                </div>

                            </div>
                        </div>
                    `;

                    // Render rows
                    $.each(response.data, function (index, item) {

                        const kelasDetail = response.kelasDetail.replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumName', curriculumName)
                            .replace(':curriculumId', curriculumId).replace(':faseId', item.id);

                        $('#tbody-fase-management').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">${item.nama_fase ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${kelasDetail}" class="btn-kelas-detail">
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
                    $('.pagination-container-fase-management').html(response.links);

                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();

                    $('#empty-message-fase-management').hide();
                    $('.thead-table-fase-management').show();
                    $('#school-detail-card').show();
                } else {
                    $('#empty-message-fase-management').show();
                    $('.thead-table-fase-management').hide();
                    $('#school-detail-card').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-fase-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateFaseManagement(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateFaseManagement();
});