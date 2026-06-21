function paginateRevenueManagementRanking(search_school = '', page = 1) {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/manage/revenue/paginate/load-ranking`,
        method: 'GET',
        data: {
            search_school: search_school,
            page: page
        },

        beforeSend: function () {
            $('#lifetime-revenue-ranking-skeleton').show();
            $('#table-lifetime-revenue-ranking').hide();
            $('.thead-table-lifetime-revenue-ranking').hide();
            $('#empty-message-lifetime-revenue-ranking').hide();
            $('.pagination-container-lifetime-revenue-ranking').empty();
        },

        success: function (response) {

            $('#lifetime-revenue-ranking-skeleton').hide();
            $('#tbody-lifetime-revenue-ranking').empty();
            $('.pagination-container-lifetime-revenue-ranking').empty();

            if (response.data && response.data.length > 0) {

                $('#table-lifetime-revenue-ranking').show();
                $('.thead-table-lifetime-revenue-ranking').show();

                $.each(response.data, function (index, item) {

                    let schoolLogo = '';

                    if (item.logo) {

                        schoolLogo = `
                            <img src="/${item.logo}" alt="${item.nama_sekolah}" class="w-10 h-10 md:w-14 md:h-14 rounded-full object-contain shrink-0">
                        `;

                    } else {

                        schoolLogo = `
                            <div class="w-10 h-10 md:w-14 md:h-14 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-school text-slate-500 text-sm md:text-lg"></i>
                            </div>
                        `;
                    }

                    const contribution = Number(item.contribution ?? 0);

                    $('#tbody-lifetime-revenue-ranking').append(`
                        <tr class="border-b border-slate-100">

                            <!-- Rank -->
                            <td class="px-4 md:px-6 py-4 align-middle">

                                <div class="flex items-center justify-center lg:justify-start">

                                    <div class="w-9 h-9 md:w-12 md:h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs md:text-sm">

                                        #${(response.current_page - 1) * response.per_page + index + 4}

                                    </div>

                                </div>

                            </td>

                            <!-- School -->
                            <td class="px-4 md:px-6 py-4">

                                <div class="flex items-center gap-3 md:gap-4 min-w-0">

                                    ${schoolLogo}

                                    <div class="min-w-0 flex-1">

                                        <h4 class="font-semibold text-sm md:text-base truncate">
                                            ${item.nama_sekolah ?? '-'}
                                        </h4>

                                        <p class="text-xs md:text-sm text-slate-500 flex flex-wrap gap-x-2 gap-y-1">

                                            <span class="whitespace-nowrap">
                                                ${item.student_count ?? 0} Siswa
                                            </span>

                                            <span class="text-slate-300">•</span>

                                            <span class="whitespace-nowrap">
                                                ${item.sch_contract_count ?? 0} Contract
                                            </span>

                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Revenue -->
                            <td class="px-4 md:px-6 py-4">

                                <div class="min-w-35">

                                    <h4 class="font-bold text-green-600 text-sm md:text-base">

                                        ${formatRupiah(item.lifetime_revenue)}

                                    </h4>

                                    <p class="text-xs text-slate-400">
                                        Lifetime Revenue
                                    </p>
                                </div>
                            </td>

                            <!-- Contribution -->
                            <td class="px-4 md:px-6 py-4 min-w-40">
                                <div>

                                    <div class="flex justify-between text-xs mb-2">

                                        <span class="text-slate-500">
                                            Contribution
                                        </span>

                                        <span class="font-semibold">
                                            ${contribution}%
                                        </span>

                                    </div>

                                    <div class="w-full bg-slate-100 rounded-full h-2">

                                        <div
                                            class="h-2 rounded-full bg-linear-to-r from-blue-500 to-cyan-400"
                                            style="width:${contribution}%">
                                        </div>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                if (response.links) {
                    $('.pagination-container-lifetime-revenue-ranking').html(response.links);
                    bindPaginationLinks();
                }

                $('#empty-message-lifetime-revenue-ranking').hide();

            } else {
                $('#table-lifetime-revenue-ranking').hide();
                $('.thead-table-lifetime-revenue-ranking').hide();
                $('#empty-message-lifetime-revenue-ranking').show();
            }
        },

        error: function (xhr) {
            $('#lifetime-revenue-ranking-skeleton').hide();
            $('#table-lifetime-revenue-ranking').hide();
            $('.thead-table-lifetime-revenue-ranking').hide();
            $('#empty-message-lifetime-revenue-ranking').show();
            console.log(xhr.responseText);
        }
    });
}

$(document).ready(function () {
    paginateRevenueManagementRanking();
});

$('#search_school').on('input', function () {
    paginateRevenueManagementRanking($(this).val(), 1);
});

function bindPaginationLinks() {
    $('.pagination-container-lifetime-revenue-ranking').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault();
        const search_school = $('#search_school').val();
        const page = new URL(this.href).searchParams.get('page');
        paginateRevenueManagementRanking(search_school, page);
    });
}

function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number || 0);
}