function initializeChartFilter() {
    const periodSelect = document.getElementById('chartPeriod');
    const yearSelect = document.getElementById('chartYear');
    const monthSelect = document.getElementById('chartMonth');

    function refresh() {

        const period = periodSelect.value;
        periodSelect.classList.remove('hidden');

        if (period === 'daily' || period === 'weekly') {
            monthSelect.classList.remove('hidden');
            yearSelect.classList.remove('hidden');

        }
        else if (period === 'monthly') {
            monthSelect.classList.add('hidden');
            yearSelect.classList.remove('hidden');

        }
        else {
            monthSelect.classList.add('hidden');
            yearSelect.classList.add('hidden');

        }
        loadStudentReflectionChart();
        loadEmotionTrendChart();
    }

    periodSelect.addEventListener('change', refresh);
    yearSelect.addEventListener('change', refresh);
    monthSelect.addEventListener('change', refresh);

    refresh();
}

function getChartFilter() {
    return {
        period: document.getElementById('chartPeriod').value,
        year: document.getElementById('chartYear').value,
        month: document.getElementById('chartMonth').value
    };
}

function loadReflectionYear() {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;

    if (!role) return;

    // Tidak ada foundation
    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        // Disable filter
        $('#chartPeriod').prop('disabled', true);
        $('#chartMonth').prop('disabled', true);
        $('#chartYear').prop('disabled', true);

        $('#filter-loading').addClass('hidden');
        $('#filter-content').addClass('hidden');
        $('#filter-empty').removeClass('hidden');

        loadKPI(null);
        loadEmotionOverview(null);
        loadStudentReflectionChart(true);
        loadEmotionTrendChart(true);

        return;
    }

    $.ajax({
        url: `/lms/${role}/foundation/student-reflection/load-reflection-years/${foundationId}`,
        method: 'GET',

        beforeSend: function () {
            $('#filter-loading').removeClass('hidden');
            $('#filter-content').addClass('hidden');
        },

        success: function (response) {

            const filter = $('#chartYear');

            filter.empty();

            // Tidak ada data tahun refleksi
            if (!Array.isArray(response.years) || response.years.length === 0) {
                $('#chartPeriod').prop('disabled', true);
                $('#chartMonth').prop('disabled', true);
                $('#chartYear').prop('disabled', true);

                $('#filter-loading').addClass('hidden');
                $('#filter-content').addClass('hidden');
                $('#filter-empty').removeClass('hidden');

                loadKPI(null);
                loadEmotionOverview(null);
                loadStudentReflectionChart(true);
                loadEmotionTrendChart(true);

                return;
            }

            $('#chartPeriod').prop('disabled', false);
            $('#chartMonth').prop('disabled', false);
            $('#chartYear').prop('disabled', false);

            response.years.forEach((year) => {
                filter.append(`
                    <option value="${year}" class="text-slate-700">
                        Tahun ${year}
                    </option>
                `);
            });

            // Ambil tahun terbaru
            const latestYear = Math.max(...response.years);

            filter.val(latestYear);

            $('#filter-loading').addClass('hidden');
            $('#filter-content').removeClass('hidden');

            initializeChartFilter();

            loadKPI(latestYear);
            loadEmotionOverview(latestYear);
        },

        error: function (err) {

            $('#filter-loading').addClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    loadReflectionYear();
});