let kpiLoaded = false;

function paginateSchoolContract(search_school = '', page = 1, loadKpi = false) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;

    if (!role) return;

    $.ajax({
        url: `/lms/${role}/manage/contract/paginate`,
        method: 'GET',
        data: {
            search_school: search_school,
            page: page
        },

        beforeSend: function () {

            // KPI Skeleton
            if (loadKpi) {

                $('#kpi-skeleton').removeClass('hidden');
                $('#kpi-content').addClass('hidden');

            }

            // Table Skeleton
            $('#table-school-contract-list').addClass('hidden');
            $('#empty-message-school-contract-list').addClass('hidden');
            $('#school-contract-list-skeleton').removeClass('hidden');
        },

        success: function (response) {

            // Hide Skeleton
            $('#school-contract-list-skeleton').addClass('hidden');
            $('#table-school-contract-list').removeClass('hidden');

            // KPI
            if (loadKpi) {

                $('#kpi-skeleton').addClass('hidden');
                $('#kpi-content').removeClass('hidden');

                renderContractKpi(response.kpi);
            }

            renderSchoolContractList(
                response.data ?? [],
                response.links ?? '',
                response.manageContractDetail ?? '',
                response.current_page ?? 1
            );
        },

        error: function (xhr, status, error) {

            $('#school-contract-list-skeleton').addClass('hidden');

            $('#kpi-skeleton').addClass('hidden');

            console.log(error);
        }
    });
}

// KPI
function renderContractKpi(kpi) {

    $('#total-contract-value').text(
        formatRupiah(kpi.total_contract_value ?? 0)
    );

    $('#revenue-collected').text(
        formatRupiah(kpi.total_paid ?? 0)
    );

    $('#outstanding').text(
        formatRupiah(kpi.total_outstanding ?? 0)
    );

    $('#active-contracts').text(
        kpi.active_contracts ?? 0
    );

    const collectionRate = kpi.collection_rate ?? 0;

    $('#collection-rate').text(
        `${collectionRate}%`
    );

    $('#collection-progress-bar').css(
        'width',
        `${collectionRate}%`
    );

    $('#collected-summary').text(
        formatRupiah(kpi.total_paid ?? 0)
    );

    $('#outstanding-summary').text(
        formatRupiah(kpi.total_outstanding ?? 0)
    );
}

// CONTRACT STATUS
function renderContractStatus(status) {

    switch (status) {

        case 'active':
            return 'px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700';

        case 'expired':
            return 'px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700';

        case 'pending':
            return 'px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700';

        default:
            return 'px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600';
    }
}

// CONTRACT LIST
function renderSchoolContractList(
    data,
    links,
    manageContractRoute,
) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;

    $('#tbody-school-contract-list').empty();
    $('.pagination-container-school-contract-list').empty();

    // EMPTY STATE
    if (!data || data.length === 0) {

        $('.thead-table-school-contract-list').addClass('hidden');

        $('#table-school-contract-list').removeClass('hidden');

        $('#empty-message-school-contract-list').removeClass('hidden');

        return;
    }

    $('#empty-message-school-contract-list').addClass('hidden');

    $('.thead-table-school-contract-list').removeClass('hidden');

    // TABLE ROW
    $.each(data, function (index, item) {

        let manageContractDetail = '#';

        if (manageContractRoute) {

            manageContractDetail = manageContractRoute
                .replace(':role', role)
                .replace(':schoolId', item.id);
        }

        $('#tbody-school-contract-list').append(`

            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">

                <!-- SCHOOL -->
                <td class="p-4">

                    <div>

                        <h4 class="font-semibold text-slate-800">
                            ${item.nama_sekolah ?? '-'}
                        </h4>

                        <p class="text-xs text-slate-500 mt-1">
                            ${item.student_count ?? 0} siswa
                        </p>

                    </div>

                </td>

                <!-- ACTIVE CONTRACT -->
                <td class="p-4 text-center">

                    <span class="font-medium text-slate-700">
                        ${item.active_contract_count ?? 0}
                    </span>

                </td>

                <!-- CONTRACT VALUE -->
                <td class="p-4 text-center">

                    <span class="font-semibold text-violet-700">
                        ${formatRupiah(item.contract_value ?? 0)}
                    </span>

                </td>

                <!-- OUTSTANDING -->
                <td class="p-4 text-center">

                    <span class="${(item.outstanding ?? 0) > 0 ? 'text-red-600' : 'text-green-600'} font-semibold">

                        ${formatRupiah(item.outstanding ?? 0)}

                    </span>

                </td>

                <!-- OVERDUE -->
                <td class="p-4 text-center">

                    <span class="${(item.overdue_terms ?? 0) > 0 ? 'text-orange-600 font-semibold' : 'text-slate-500'}">

                        ${item.overdue_terms ?? 0}

                    </span>

                </td>

                <!-- STATUS -->
                <td class="p-4 text-center">

                    <span class="${renderContractStatus(item.status)}">

                        ${item.status_label ?? '-'}

                    </span>

                </td>

                <!-- ACTION -->
                <td class="p-4 text-center">

                    <a href="${manageContractDetail}"
                        class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition">

                        Lihat Kontrak

                    </a>

                </td>

            </tr>

        `);
    });

    // PAGINATION
    $('.pagination-container-school-contract-list').html(links);
    bindPaginationLinks();
}

$(document).ready(function () {
    paginateSchoolContract('', 1, true);
});

// Fungsi untuk memfilter data berdasarkan search_school (pakai on input karena ketika data yang user cari akan munul tanpa di enter atau apapun by click)
$('#search_school').on('input', function () {
    const search_school = $(this).val();
    paginateSchoolContract(search_school, 1, false);
});


function bindPaginationLinks() {
    $('.pagination-container-school-contract-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_school = $('#search_school').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSchoolContract(search_school, page, false); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

function formatRupiah(number) {

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

function renderContractStatus(status) {

    switch (status) {

        case 'active':
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700';

        case 'due-soon':
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700';

        case 'overdue':
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700';

        case 'expired':
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-200 text-slate-600';

        default:
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600';
    }
}