let kpiLoaded = false;

function paginateSchoolContractDetail(page = 1, loadKpi = false) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolId = container.dataset.schoolId;
    
    if (!container) return;
    if (!role) return;
    if (!schoolId) return;

    $.ajax({
        url: `/lms/${role}/manage/contract/schools/${schoolId}/paginate`,
        method: 'GET',
        data: {
            page: page
        },

        beforeSend: function () {

            if (loadKpi) {
                $('#kpi-skeleton').removeClass('hidden');
                $('#kpi-content').addClass('hidden');
            }
            
            $('#grid-contract-list').addClass('hidden');
            $('#contract-list-skeleton').removeClass('hidden');
        },

        success: function (response) {

            $('#contract-list-skeleton').addClass('hidden');
            
            $('#grid-contract-list').removeClass('hidden');
            
            if (loadKpi) {
                $('#kpi-skeleton').addClass('hidden');
                $('#kpi-content').removeClass('hidden');

                renderContractKpi(response.kpi);
            }

            renderContractList(
                response.data,
                response.links,
                response.contractPaymentDetail,
                response.current_page
            );
        },

        error: function (xhr, status, error) {
            $('#kpi-skeleton').addClass('hidden');
            $('#contract-list-skeleton').addClass('hidden');

            console.log(error);
        }
    });
}

// KPI
function renderContractKpi(kpi) {

    $('#total-contracts').text(
        kpi.total_contracts
    );

    $('#lifetime-contract-value').text(
        formatRupiah(kpi.lifetime_contract_value)
    );

    $('#total-paid').text(
        formatRupiah(kpi.total_paid)
    );

    $('#total-outstanding').text(
        formatRupiah(kpi.outstanding)
    );
}

// CONTRACT LIST
function renderContractList(data, links, contractPaymentRoute, currentPage) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolId = container.dataset.schoolId;

    if (!container || !role || !schoolId) return;

    $('#grid-contract-list').empty();
    $('.pagination-container-contract-list').empty();

    if (data.length === 0) {
        $('#empty-message-contract-list').removeClass('hidden');
        return;
    }

    $('#empty-message-contract-list').addClass('hidden');

    $.each(data, function (index, item) {

        const contractPaymentDetail = contractPaymentRoute.replace(':role', role).replace(':schoolId', schoolId).replace(':contractId', item.id);

        $('#grid-contract-list').append(`

            <!-- CARD -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-lg transition-all duration-200">

                <!-- HEADER -->
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                    <div class="flex items-start gap-4">

                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-file-contract text-white"></i>
                        </div>

                        <div>

                            <h3 class="font-bold text-lg text-slate-800">
                                ${item.contract_number}
                            </h3>

                            <p class="text-sm text-slate-500">
                                <i class="fa-regular fa-calendar mr-1"></i>
                                ${item.period}
                            </p>
                        </div>

                    </div>

                    <!-- ACTION -->
                    <div class="flex items-center justify-end gap-2 mt-2 md:mt-0">

                        <div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="hidden peer toggle-activate-contract" data-contract-id="${item.id}" data-current-page="${currentPage}"
                                    ${item.status === 'active' ? 'checked' : ''} />
                                <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out"></div>
                                <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out 
                                peer-checked:translate-x-5"></div>
                            </label>
                        </div>

                        <a href="${contractPaymentDetail}"
                            class="px-3 py-2 text-sm rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                            <i class="fa-solid fa-eye mr-1"></i> Detail
                        </a>

                    </div>

                </div>

                <!-- KPI GRID -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mt-5">

                    <!-- CONTRACT VALUE -->
                    <div class="bg-linear-to-br from-violet-50 to-violet-100 border border-violet-200 rounded-xl p-3">

                        <div class="flex items-center justify-between">
                            <p class="text-xs text-violet-600 font-medium">Nilai Kontrak</p>
                            <i class="fa-solid fa-wallet text-violet-600"></i>
                        </div>

                        <p class="font-bold text-violet-700 mt-2">
                            ${formatRupiah(item.contract_value)}
                        </p>

                    </div>

                    <!-- PAID -->
                    <div class="bg-linear-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-xl p-3">

                        <div class="flex items-center justify-between">
                            <p class="text-xs text-emerald-600 font-medium">Sudah Dibayar</p>
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        </div>

                        <p class="font-bold text-emerald-700 mt-2">
                            ${formatRupiah(item.paid_amount)}
                        </p>

                    </div>

                    <!-- OUTSTANDING -->
                    <div class="bg-linear-to-br from-red-50 to-red-100 border border-red-200 rounded-xl p-3">

                        <div class="flex items-center justify-between">
                            <p class="text-xs text-red-600 font-medium">Belum Dibayar</p>
                            <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                        </div>

                        <p class="font-bold text-red-700 mt-2">
                            ${formatRupiah(item.outstanding)}
                        </p>

                    </div>

                    <!-- PROGRESS -->
                    <div class="bg-linear-to-br from-sky-50 to-sky-100 border border-sky-200 rounded-xl p-3">

                        <div class="flex items-center justify-between">
                            <p class="text-xs text-sky-600 font-medium">Progress</p>
                            <i class="fa-solid fa-chart-line text-sky-600"></i>
                        </div>

                        <p class="font-bold text-sky-700 mt-2">
                            ${item.payment_progress}%
                        </p>
                    </div>
                </div>
            </div>
        `);
    });

    $('.pagination-container-contract-list').html(links);
    bindPaginationLinks();
}

$(document).ready(function () {
    paginateSchoolContractDetail(1, true);
});

function bindPaginationLinks() {
    $('.pagination-container-contract-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSchoolContractDetail(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

function formatRupiah(number) {

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

$(document).on('change', '.toggle-activate-contract', function () {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role) return;
    if (!schoolId) return;

    let contractId = $(this).data('contract-id'); // Ambil contract id dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive
    let currentPage = $(this).data('current-page'); // Ambil current page dari atribut data-current-page di checkbox

    $.ajax({
        url: `/lms/${role}/manage-contract/schools/${schoolId}/contract/${contractId}/activate`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status: status // Kirim status baru (aktif / non aktif)
        },
        success: function (response) {
            // inisialisasi update data terbaru setelah berhasil insert data
            const page = currentPage;
            paginateSchoolContractDetail(page, false);
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const message = xhr.responseJSON?.message ?? 'Tidak bisa mengubah data.';

                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Diizinkan',
                    text: message,
                    confirmButtonColor: '#d33'
                });

                paginateSchoolContractDetail(1, false);

            } else {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan sistem.',
                });
            }

            // rollback toggle state
            const checkbox = $(this);
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});