function paginateLibrary(search = '', page = 1) {
    $.ajax({
        url: '/library/paginate',
        method: 'GET',
        data: {
            search: search,
            page: page
        },
        success: function (response) {

            $('#tbody-library-list').empty();
            $('.pagination-container-library-list').empty();

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {

                    $('#tbody-library-list').append(`
                        <tr>
                            <td class="border px-3 py-2 text-center">
                                ${(response.current_page - 1) * response.per_page + index + 1}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                <img src="/storage/${item.cover}" class="w-12 h-16 object-cover mx-auto">
                            </td>

                            <td class="border px-3 py-2 text-center">
                                ${item.title}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                ${item.book_type}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                ${item.class_level}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                ${item.category}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                ${item.access_type}
                            </td>

                            <td class="border px-3 py-2 text-center">
                                <button class="btn-edit-book text-blue-500"
                                    data-id="${item.id}">
                                    Edit
                                </button>

                                <button class="btn-delete-book text-red-500"
                                    data-id="${item.id}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-library-list').html(response.links);

                bindPaginationLinksLibrary();

                $('#empty-message-library-list').hide();

                $('.thead-table-library-list').show();

            } else {

                $('#tbody-library-list').empty();

                $('.thead-table-library-list').hide();

                $('#empty-message-library-list').show();
            }
        }
    });
}

$(document).ready(function () {
    paginateLibrary();
});

$('#search_library').on('input', function () {
    const search = $(this).val();
    paginateLibrary(search);
});

function bindPaginationLinksLibrary() {
    $('.pagination-container-library-list')
        .off('click', 'a')
        .on('click', 'a', function (event) {

            event.preventDefault();

            const page = new URL(this.href).searchParams.get('page');

            const search = $('#search_library').val();

            paginateLibrary(search, page);
        });
}