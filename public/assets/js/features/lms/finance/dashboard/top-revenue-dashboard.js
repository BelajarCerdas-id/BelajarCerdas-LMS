function paginateTopRevenue() {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container || !role) return;

    $.ajax({
        url: `/lms/${role}/manage-contract/load-top-revenue`,
        method: 'GET',

        beforeSend: function () {
            $('#top-revenue-skeleton').removeClass('hidden');

            $('#grid-list-top-revenue')
                .addClass('hidden')
                .empty();

            $('#empty-message-top-revenue')
                .addClass('hidden');
        },

        success: function (response) {

            $('#top-revenue-skeleton').addClass('hidden');

            const data = response.data || [];

            if (data.length > 0) {

                $('#grid-list-top-revenue')
                    .removeClass('hidden');

                $.each(data, function (index, item) {

                    let schoolLogo = '';

                    if (item.logo) {
                        schoolLogo = `
                            <img src="/${item.logo}" class="w-10 h-10 md:w-12 md:h-12 rounded-full object-contain shrink-0">
                        `;
                    } else {
                        schoolLogo = `
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-school text-slate-500 text-sm"></i>
                            </div>
                        `;
                    }

                    const rank = index + 1;

                    $('#grid-list-top-revenue').append(`
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between md:flex-col md:items-start md:justify-start gap-3 p-4 
                            lg:flex-row lg:items-center lg:justify-between rounded-2xl hover:bg-slate-50 transition">

                            <!-- LEFT SIDE -->
                            <div class="flex items-center gap-3 md:gap-4 min-w-0 flex-1">

                                <!-- RANK -->
                                <div class="w-9 h-9 md:w-10 md:h-10 bg-indigo-100 rounded-full flex items-center justify-center shrink-0">
                                    <span class="font-bold text-indigo-600 text-xs md:text-sm">
                                        #${rank}
                                    </span>
                                </div>

                                <!-- LOGO -->
                                ${schoolLogo}

                                <!-- SCHOOL INFO -->
                                <div class="min-w-0 flex-1">

                                    <h4 class="font-semibold text-sm lg:text-base leading-tight wrap-break-word">
                                        ${item.nama_sekolah ?? '-'}
                                    </h4>

                                    <p class="text-xs md:text-sm text-slate-500 flex flex-wrap gap-x-2 gap-y-1 mt-1">

                                        <span class="whitespace-nowrap">
                                            ${item.student_count ?? 0} Siswa
                                        </span>

                                        <span class="text-slate-300">
                                            •
                                        </span>

                                        <span class="whitespace-nowrap">
                                            ${item.sch_contract_count ?? 0} Contract
                                        </span>

                                    </p>

                                </div>

                            </div>

                            <!-- RIGHT SIDE -->
                            <div class="sm:text-right">

                                <h4 class="font-bold text-green-600 text-sm md:text-base wrap-break-word">
                                    ${formatRupiah(item.lifetime_revenue)}
                                </h4>

                                <p class="text-[10px] md:text-xs text-slate-400">
                                    Lifetime Revenue
                                </p>

                            </div>

                        </div>
                    `);
                });

            } else {
                $('#empty-message-top-revenue')
                    .removeClass('hidden');
            }
        },

        error: function (xhr) {
            console.log(xhr.responseText);

            $('#top-revenue-skeleton').addClass('hidden');
            $('#empty-message-top-revenue').removeClass('hidden');
        }
    });
}

$(document).ready(function () {
    paginateTopRevenue();
});

function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number || 0);
}