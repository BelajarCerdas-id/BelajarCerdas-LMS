function renderKpi() {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    $.ajax({
        url: `/lms/${role}/manage/revenue/load-kpi`,
        method: 'GET',

        beforeSend: function () {
            $('#kpi-skeleton').removeClass('hidden');
            $('#kpi-content').addClass('hidden');
        },

        success: function (res) {

            // inject data
            $('#lifetime-revenue').text(formatRupiah(res.lifetime_revenue));
            $('#revenue-by-year').text(formatRupiah(res.revenue_by_year));
            $('#avg-revenue-by-school').text(formatRupiah(res.avg_revenue_by_school));
            $('#school-count').text(res.school_count + ' sekolah');

            $('#kpi-skeleton').addClass('hidden');
            $('#kpi-content').removeClass('hidden');
        },

        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    renderKpi();
});

function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(number);
}