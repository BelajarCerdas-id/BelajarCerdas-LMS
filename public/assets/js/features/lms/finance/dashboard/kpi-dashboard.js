function renderKpi() {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/manage-contract/load-kpi`,
        method: 'GET',

        beforeSend: function () {
            $('#kpi-skeleton').removeClass('hidden');
            $('#kpi-content').addClass('hidden');
        },

        success: function (res) {

            $('#lifetime-revenue').text(formatRupiah(res.lifetime_revenue));
            $('#total-schools').text(res.school_count);
            $('#contract-active').text(res.contract_count);
            $('#total-students').text(formatNumber(res.student_count));

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

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(number);
}