function renderLeaderboard() {

    const container = document.getElementById('container');
    const role = container.dataset.role;

    $.ajax({
        url: `/lms/${role}/manage/revenue/load-leaderboard`,
        method: 'GET',

        beforeSend: function () {

            $('#leaderboard-skeleton').removeClass('hidden');

            $('#leaderboard-content').addClass('hidden');

            $('#leaderboard-empty').addClass('hidden');
        },

        success: function (res) {

            const top3 = res?.data?.top3 ?? [];
            const summary = res?.data?.summary ?? {};

            // EMPTY STATE
            if (top3.length === 0 || (summary.top3_total ?? 0) <= 0) {

                $('#leaderboard-skeleton').addClass('hidden');

                $('#leaderboard-content').addClass('hidden');

                $('#leaderboard-empty').removeClass('hidden');

                return;
            }

            // SUMMARY
            $('#top-revenue').text(
                formatRupiah(summary.top_revenue ?? 0)
            );

            $('#top-3-total').text(
                formatRupiah(summary.top3_total ?? 0)
            );

            $('#avg-top-3-revenue').text(
                formatRupiah(summary.avg_top3_revenue ?? 0)
            );

            $('#top-3-contribution').text(
                (summary.top3_contribution ?? 0).toFixed(1) + '%'
            );

            // RESET PODIUM
            for (let rank = 1; rank <= 3; rank++) {

                $(`#rank-${rank}-name`).text('-');

                $(`#rank-${rank}-meta`).text('-');

                $(`#rank-${rank}-revenue`).text('Rp 0');

                $(`#rank-${rank}-contribution-text`).text('0%');

                $(`#rank-${rank}-contribution-bar`).css('width', '0%');

                $(`#rank-${rank}-logo`).addClass('hidden');

                $(`#rank-${rank}-fallback`)
                    .removeClass('hidden')
                    .html('<i class="fa-solid fa-school text-3xl"></i>');
            }

            // RENDER TOP 3
            top3.forEach((school, index) => {

                const rank = index + 1;

                $(`#rank-${rank}-name`).text(
                    school.nama_sekolah ?? '-'
                );

                $(`#rank-${rank}-meta`).text(
                    `${school.student_count ?? 0} Siswa • ${school.sch_contract_count ?? 0} Contract`
                );

                $(`#rank-${rank}-revenue`).text(
                    formatRupiah(school.lifetime_revenue ?? 0)
                );

                if (school.logo) {

                    $(`#rank-${rank}-logo`)
                        .attr('src', '/' + school.logo)
                        .removeClass('hidden');

                    $(`#rank-${rank}-fallback`)
                        .addClass('hidden');

                } else {

                    $(`#rank-${rank}-logo`)
                        .addClass('hidden');

                    $(`#rank-${rank}-fallback`)
                        .removeClass('hidden')
                        .html('<i class="fa-solid fa-school text-3xl"></i>');
                }

                $(`#rank-${rank}-contribution-text`).text(
                    (school.contribution ?? 0) + '%'
                );

                $(`#rank-${rank}-contribution-bar`).css(
                    'width',
                    (school.contribution ?? 0) + '%'
                );
            });

            $('#leaderboard-skeleton').addClass('hidden');

            $('#leaderboard-empty').addClass('hidden');

            $('#leaderboard-content').removeClass('hidden');
        },

        error: function (err) {

            console.log(err);

            $('#leaderboard-skeleton').addClass('hidden');

            $('#leaderboard-content').addClass('hidden');

            $('#leaderboard-empty').removeClass('hidden');
        }
    });
}

$(document).ready(function () {
    renderLeaderboard();
});

function formatRupiah(number) {

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}