function paginateContentForRelease(page = 1) {
    const container = document.getElementById('container-content-for-release-list');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/content-for-release/paginate`,
        method: 'GET',
        data: {
            page: page
        },
        success: function (response) {
            $('#tbody-content-for-release-list').empty();
            $('.pagination-container-content-for-release-list').empty();

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {

                    const teacherContentForReleaseReviewMeetings = response.teacherContentForReleaseReviewMeetings.replace(':role', role).replace(':schoolName', schoolName)
                        .replace(':schoolId', schoolId).replace(':schoolClassId', item.school_class_id).replace(':mapelId', item.mapel_id).replace(':semester', item.semester)
                        .replace(':serviceId', item.service_id);
                    
                    $('#tbody-content-for-release-list').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.class_name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.mapel?.mata_pelajaran?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.tahun_ajaran ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">Semester ${item.semester ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.total_meetings ?? 0} Pertemuan</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.service?.name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <div class="dropdown dropdown-left">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                        <li class="text-md">
                                            <a href="${teacherContentForReleaseReviewMeetings}">
                                                <i class="fa-solid fa-eye text-[#0071BC]"></i>
                                                Review Content
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-content-for-release-list').html(response.links);
                bindPaginationLinks();
                $('#empty-message-content-for-release-list').hide(); // sembunyikan pesan kosong
                $('.thead-table-content-for-release-list').show(); // Tampilkan tabel thead

            } else {
                $('#tbody-content-for-release-list').empty(); // Clear existing rows
                $('.thead-table-content-for-release-list').hide(); // Tampilkan tabel thead
                $('#empty-message-content-for-release-list').show();
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateContentForRelease();
});

function bindPaginationLinks() {
    $('.pagination-container-school-partner-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateContentForRelease(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}