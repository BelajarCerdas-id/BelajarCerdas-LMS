function loadContractExpiring() {

    const container = document.getElementById('container');
    const role = container?.dataset?.role;

    if (!container || !role) return;

    $.ajax({
        url: `/lms/${role}/manage-contract/load-contract-expiring`,
        method: 'GET',

        beforeSend: function () {
            $('#contract-expiring-list').addClass('hidden').empty();
            $('#contract-expiring-empty').addClass('hidden');
            $('#contract-expiring-skeleton').removeClass('hidden');
        },

        success: function (res) {

            $('#contract-expiring-skeleton').addClass('hidden');

            const data = res?.data ?? [];

            if (!data.length) {
                $('#contract-expiring-empty').removeClass('hidden');
                return;
            }

            $('#contract-expiring-list').removeClass('hidden').empty();

            data.forEach((item) => {

                const days = Number(item.days_left ?? 0);

                let colorClass = 'bg-yellow-50 border-yellow-100';
                let badgeClass = 'bg-yellow-100 text-yellow-700';

                if (days <= 7) {
                    colorClass = 'bg-red-50 border-red-100';
                    badgeClass = 'bg-red-100 text-red-600';

                } else if (days <= 14) {
                    colorClass = 'bg-orange-50 border-orange-100';
                    badgeClass = 'bg-orange-100 text-orange-600';
                }

                const formatDate = (dateString) => {
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                    const date = new Date(dateString);
                    const day = date.getDate();
                    const monthName = months[date.getMonth()];
                    const year = date.getFullYear();

                    return `${day} ${monthName} ${year}`;
                };

                // Format tanggal berakhir
                const endDate = item.end_date ? formatDate(item.end_date) : 'Tanggal tidak tersedia';

                const html = `
                    <div class="p-4 rounded-2xl border ${colorClass} transition hover:shadow-sm">

                        <!-- TOP ROW -->
                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0 flex-1">
                                <h4 class="font-semibold text-sm md:text-base truncate">
                                    ${item.school_name ?? '-'}
                                </h4>

                                <p class="text-xs text-slate-500 mt-1">
                                    Berakhir: ${endDate}
                                </p>
                            </div>

                            <span class="px-3 py-1 rounded-full text-xs font-medium ${badgeClass}">
                                ${days} hari
                            </span>

                        </div>

                        <!-- DETAIL ROW -->
                        <div class="mt-3 flex flex-col gap-2">

                            <!-- PROGRESS BAR -->
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">

                                <div class="h-2 rounded-full ${days <= 7 ? 'bg-red-500' :
                                    days <= 14 ? 'bg-orange-400' :
                                        'bg-yellow-400'
                                }"
                                style="width: ${Math.max(0, 100 - (days * 3.3))}%">
                                </div>

                            </div>

                        </div>

                    </div>
                `;

                $('#contract-expiring-list').append(html);
            });
        },

        error: function (err) {
            console.log(err);

            $('#contract-expiring-skeleton').addClass('hidden');
            $('#contract-expiring-empty').removeClass('hidden');
        }
    });
}

$(document).ready(function () {
    loadContractExpiring();
});