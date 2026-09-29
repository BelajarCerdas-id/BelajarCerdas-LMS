function loadFilter() {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container || !role) return;

    $('#filter-tka-tryout-skeleton').addClass('hidden');
    $('#filter-tka-tryout-content').removeClass('hidden');

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/load-filter`,
        method: 'GET',
        beforeSend: function () {
            $('#filter-tka-tryout-skeleton').removeClass('hidden');
            $('#filter-tka-tryout-content').addClass('hidden');
        },
        success: function (response) {
            $('#filter-tka-tryout-skeleton').addClass('hidden');
            $('#filter-tka-tryout-content').removeClass('hidden');

            const academicYearSelect = $('#search-academic-year');

            academicYearSelect.find('option:not(:first)').remove();

            $.each(response.academicYears, function (index, academicYear) {
                academicYearSelect.append(`
                    <option value="${academicYear}">
                        ${academicYear}
                    </option>
                `);
            });

            if (response.academicYears.length > 0) {
                academicYearSelect.val(response.academicYears[0]).trigger('change');
            }
        },
        error: function (err) {
            $('#filter-tka-tryout-skeleton').addClass('hidden');
            $('#filter-tka-tryout-content').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    loadFilter();
});