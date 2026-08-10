function paginateSchoolReflectionSummary(search_year = null, page = 1) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!container || !role) return;

    // Tidak ada foundation
    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        $('#table-loading').addClass('hidden');
        $('#table-content').addClass('hidden');
        $('#empty-message-school-reflection-summary').removeClass('hidden');

        return;
    }

    $.ajax({
        url: `/lms/${role}/foundation/student-reflection/school-reflection-summary/paginate/${foundationId}`,
        method: 'GET',
        data: {
            search_year: search_year,
            page: page,
        },
        beforeSend: function () {
            $('#tbody-school-reflection-summary').empty();
            $('#table-loading').removeClass('hidden');
            $('#table-content').addClass('hidden');
            $('#empty-message-school-reflection-summary').addClass('hidden');
            $('.pagination-container-school-reflection-summary').empty();
        },
        success: function (response) {
            $('#table-loading').addClass('hidden');
            $('#tbody-school-reflection-summary').empty();

            const filter = $('#filter-tahun-ajaran');

            if (filter.children().length === 0) {
                filter.empty();

                response.academic_years.forEach((year) => {
                    filter.append(`<option value="${year}">${year}</option>`);
                });
            }

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {
                    $('#tbody-school-reflection-summary').append(`
                        <tr class="text-xs">
                            <td class="border border-gray-300 px-3 py-2 text-center w-2">
                                ${((response.current_page - 1) * response.per_page) + index + 1}
                            </td>

                            <td class="border border-gray-300 px-3 py-2 w-2">
                                ${item.nama_sekolah ?? '-'}
                            </td>

                            <td class="border border-gray-300 px-3 py-2 text-center w-2">
                                ${item.reflection_count ?? 0}
                            </td>

                            <td class="border border-gray-300 px-3 py-2 text-center w-2">
                                ${item.completion_percentage + '%' ?? 0}
                            </td>

                            <td class="border border-gray-300 px-3 py-2 text-center w-2">
                                ${item.positive_percentage + '%' ?? 0}
                            </td>
                        </tr>
                    `);
                });

                $('#table-content').removeClass('hidden');
                $('#empty-message-school-reflection-summary').addClass('hidden');
                $('.thead-table-school-reflection-summary').removeClass('hidden');
                $('.thead-table-school-reflection-summary').removeClass('hidden');

                $('.pagination-container-school-reflection-summary').html(response.links);
                bindPaginationLinks();
            } else {
                $('#table-content').addClass('hidden');
                $('.thead-table-school-reflection-summary').addClass('hidden');
                $('#empty-message-school-reflection-summary').removeClass('hidden');
            }
        },
        error: function (err) {
            $('#table-loading').addClass('hidden');
            $('#table-content').addClass('hidden');
            $('#tbody-school-reflection-summary').empty();
            $('.pagination-container-school-reflection-summary').empty();
            $('#empty-message-school-reflection-summary').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateSchoolReflectionSummary();
});

$('#filter-tahun-ajaran').on('change', function () {

    paginateSchoolReflectionSummary(
        $(this).val(),
        $('#search-school-reflection-summary').val()
    );

});

function bindPaginationLinks() {
    $('.pagination-container-school-reflection-summary').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const searchYear = $('#filter-tahun-ajaran').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSchoolReflectionSummary(searchYear, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}