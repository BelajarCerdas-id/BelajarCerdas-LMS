function loadFilterAcademicYear() {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!role) return;

    // Tidak ada foundation
    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        $('#filter-loading').addClass('hidden');
        $('#filter-content').addClass('hidden');
        $('#filter-empty').removeClass('hidden');

        return;
    }

    $.ajax({
        url: `/lms/${role}/foundation/school-teacher-performance/load-academic-years/${foundationId}`,
        method: 'GET',

        beforeSend: function () {
            $('#filter-loading').removeClass('hidden');
            $('#filter-content').addClass('hidden');
            $('#filter-empty').addClass('hidden');
        },

        success: function (response) {
            const filter = $('#filter-tahun-ajaran');

            filter.empty();

            if (!response.academic_years || !response.academic_years.length) {
                $('#filter-loading').addClass('hidden');
                $('#filter-content').addClass('hidden');
                $('#filter-empty').removeClass('hidden');

                return;
            }

            response.academic_years.forEach((year) => {
                filter.append(`
                    <option value="${year}" class="text-slate-700">
                        ${year}
                    </option>
                `);
            });

            $('#filter-loading').addClass('hidden');
            $('#filter-content').removeClass('hidden');
            $('#filter-empty').addClass('hidden');

            const academicYear = filter.val();

            if (academicYear) {
                filter.trigger('change');
            }
        },

        error: function (err) {
            $('#filter-loading').addClass('hidden');
            $('#filter-content').addClass('hidden');
            $('#filter-empty').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    loadFilterAcademicYear();
});