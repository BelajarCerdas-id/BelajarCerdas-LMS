function paginateSchoolTeacherPerformance(search_academic_year, page = 1) {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!role) return;

    $.ajax({
        url: `/lms/${role}/foundation/school-teacher-performance/paginate/${foundationId}`,
        method: 'GET',
        data: {
            academic_year: search_academic_year,
            page: page
        },

        beforeSend: function () {
            $('#table-content').removeClass('hidden');
            $('#thead-school-teacher-performance-skeleton').removeClass('hidden');
            $('#thead-school-teacher-performance').addClass('hidden');
            $('#tbody-school-teacher-performance-skeleton').removeClass('hidden');
            $('#tbody-school-teacher-performance').addClass('hidden');
            $('#empty-message-school-teacher-performance').addClass('hidden');
            $('.pagination-container-school-teacher-performance').empty();
        },

        success: function (response) {
            $('#thead-school-teacher-performance-skeleton').addClass('hidden');
            $('#tbody-school-teacher-performance-skeleton').addClass('hidden');

            if (response.data.length > 0) {
                $('#tbody-school-teacher-performance').empty();

                $.each(response.data, function (index, item) {

                    $('#tbody-school-teacher-performance').append(`
                        <tr class="text-xs">

                            <!-- No -->
                            <td class="border border-slate-200 px-3 py-2 text-center w-2">
                                ${((response.current_page - 1) * response.per_page) + index + 1}
                            </td>

                            <!-- Sekolah -->
                            <td class="border border-slate-200 px-4 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                        <i class="fa-solid fa-school text-blue-600"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">
                                            ${item.school_name}
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-500">
                                            NPSN: ${item.npsn}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Assessment -->
                            <td class="border border-slate-200 px-4 py-4 text-center">
                                <span class="text-sm font-semibold text-slate-700">
                                    ${item.published_assessments} / ${item.total_assessments}
                                </span>
                            </td>

                            <!-- Assessment Progress -->
                            <td class="border border-slate-200 px-4 py-4">
                                <div class="min-w-32.5">
                                    <div class="mb-1.5 flex items-center justify-end">
                                        <span class="text-xs font-semibold text-slate-700">
                                            ${item.assessment_percentage}%
                                        </span>
                                    </div>

                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-blue-500"
                                            style="width: ${item.assessment_percentage}%">
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Materi -->
                            <td class="border border-slate-200 px-4 py-4 text-center">
                                <span class="text-sm font-semibold text-slate-700">
                                    ${item.published_contents} / ${item.total_contents}
                                </span>
                            </td>

                            <!-- Materi Progress -->
                            <td class="border border-slate-200 px-4 py-4">
                                <div class="min-w-32.5">

                                    <div class="mb-1.5 flex items-center justify-end">
                                        <span class="text-xs font-semibold text-slate-700">
                                            ${item.content_percentage}%
                                        </span>
                                    </div>

                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-emerald-500"
                                            style="width: ${item.content_percentage}%">
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                $('#thead-school-teacher-performance').removeClass('hidden');
                $('#tbody-school-teacher-performance').removeClass('hidden');
                $('#empty-message-school-teacher-performance').addClass('hidden');
                $('.pagination-container-school-teacher-performance').html(response.links);

                bindPaginationLinks();

            } else {
                $('#table-content').addClass('hidden');
                $('#empty-message-school-teacher-performance').removeClass('hidden');
                $('.pagination-container-school-teacher-performance').empty();
            }
        },

        error: function (err) {
            $('#thead-school-teacher-performance-skeleton').addClass('hidden');
            $('#tbody-school-teacher-performance-skeleton').addClass('hidden');
            $('#tbody-school-teacher-performance').empty();
            $('#table-content').addClass('hidden');
            $('.pagination-container-school-teacher-performance').empty();

            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateSchoolTeacherPerformance();
});

function bindPaginationLinks() {
    $('.pagination-container-school-teacher-performance').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const searchYear = $('#filter-tahun-ajaran').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSchoolTeacherPerformance(searchYear, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$('#filter-tahun-ajaran').on('change', function () {
    paginateSchoolTeacherPerformance($(this).val(), 1);
});