function paginateTryoutTKAPeriod(search_academic_year, page = 1) {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;

    if (!role) return;

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/paginate`,
        method: 'GET',
        data: {
            academic_year: search_academic_year,
            page: page
        },

        beforeSend: function () {
            $('#table-content').removeClass('hidden');
            $('#thead-tka-tryout-period-list-skeleton').removeClass('hidden');
            $('#thead-tka-tryout-period-list').addClass('hidden');
            $('#tbody-tka-tryout-period-list-skeleton').removeClass('hidden');
            $('#tbody-tka-tryout-period-list').addClass('hidden');
            $('#empty-message-tka-tryout-period-list').addClass('hidden');
            $('.pagination-container-tka-tryout-period-list').empty();
        },

        success: function (response) {
            $('#thead-tka-tryout-period-list-skeleton').addClass('hidden');
            $('#tbody-tka-tryout-period-list-skeleton').addClass('hidden');

            if (response.data.length > 0) {
                $('#tbody-tka-tryout-period-list').empty();

                $.each(response.data, function (index, item) {
                    const formatDate = (dateString) => {
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day} ${monthName} ${year}`;
                    };

                    const startDate = item.start_date ? formatDate(item.start_date) : 'Tanggal tidak tersedia';
                    const endDate = item.end_date ? formatDate(item.end_date) : 'Tanggal tidak tersedia';

                    $('#tbody-tka-tryout-period-list').append(`
                        <tr class="text-sm transition-colors duration-150 hover:bg-slate-50">

                            <!-- PERIODE -->
                            <td class="min-w-57.5 border border-slate-200 px-4 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="whitespace-nowrap text-sm font-semibold text-slate-800">
                                                Periode ${item.period_number ?? '-'}
                                            </p>

                                            <span class="inline-flex shrink-0 items-center rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                                                Default
                                            </span>
                                        </div>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Tahun Ajaran ${item.tahun_ajaran ?? '-'}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- PERIODE -->
                            <td class="min-w-65 border border-slate-200 px-4 py-3 align-middle">
                                <div class="flex items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium leading-relaxed text-slate-700">
                                            ${startDate}
                                            <span class="mx-1 text-slate-400">-</span>
                                            ${endDate}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Periode pelaksanaan
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- SESI -->
                            <td class="min-w-55 border border-slate-200 px-4 py-3 align-middle">
                                <button type="button" class="group flex w-full items-start text-left cursor-pointer" data-period-id="${item.id}" 
                                    data-period-number="${item.period_number}" data-academic-year="${item.tahun_ajaran}" onclick="openTkaTryoutSessionModal(this)">

                                    <div class="flex items-center gap-3">
                                        <div class="min-w-0">
                                            <p class="flex items-center gap-1.5 whitespace-nowrap text-sm font-semibold text-[#0071BC] underline decoration-transparent underline-offset-4 transition-all duration-150 group-hover:decoration-[#0071BC]">
                                                ${item.total_session ?? 0} Sesi

                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            </p>

                                            <p class="mt-1 text-xs text-slate-400">
                                                Lihat detail sesi
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </td>

                            <!-- MATA PELAJARAN -->
                            <td class="border border-slate-200 px-3 py-3 align-middle">
                                <button type="button" class="group text-left cursor-pointer" data-period-id="${item.id}" 
                                    data-period-number="${item.period_number}" data-academic-year="${item.tahun_ajaran}" onclick="openTkaTryoutSubjectModal(this)">

                                    <div class="flex items-center gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-[#0071BC] underline decoration-transparent underline-offset-2 transition-all duration-150 group-hover:decoration-[#0071BC]">
                                                ${item.total_subject ?? 0} Mata Pelajaran
                                            </p>

                                            <p class="mt-0.5 text-xs text-slate-400">
                                                Lihat detail mapel
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </td>

                            <!-- AKSI -->
                            <td class="min-w-37.5 border border-slate-200 px-4 py-3 align-middle">
                                <div class="flex items-center justify-center">
                                    <button type="button" id="btn-edit-tka-tryout-period"
                                        class="btn btn-sm whitespace-nowrap rounded-lg border-[#0071BC]/20 bg-blue-50 px-3 text-[#0071BC]
                                        hover:border-[#0071BC] hover:bg-[#0071BC] hover:text-white"
                                        data-period-id="${item.id}"
                                        data-period-number="${item.period_number ?? ''}"
                                        data-academic-year="${item.tahun_ajaran ?? ''}"
                                        data-start-date="${item.start_date ?? ''}"
                                        data-end-date="${item.end_date ?? ''}"
                                        data-is-review="${item.is_review ?? 0}">

                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>Edit Periode</span>
                                    </button>
                                </div>
                            </td>

                            <!-- OVERRIDE SEKOLAH -->
                            <td class="min-w-50 border border-slate-200 px-4 py-3 align-middle">
                                <button type="button" class="group flex min-w-max flex-col items-start text-left cursor-pointer" data-period-id="${item.id}" 
                                    data-period-number="${item.period_number}" data-academic-year="${item.tahun_ajaran}" onclick="openTkaTryoutOverrideModal(this)">

                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-sm font-semibold text-[#0071BC] underline 
                                        decoration-transparent underline-offset-4 transition-all duration-150 group-hover:decoration-[#0071BC]">

                                        ${item.total_school_override ?? 0} Sekolah
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </span>

                                    <span class="mt-1 whitespace-nowrap text-xs text-slate-400">
                                        Menggunakan periode custom
                                    </span>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                $('#thead-tka-tryout-period-list').removeClass('hidden');
                $('#tbody-tka-tryout-period-list').removeClass('hidden');
                $('#empty-message-tka-tryout-period-list').addClass('hidden');
                $('.pagination-container-tka-tryout-period-list').html(response.links);

                bindPaginationLinks();

            } else {
                $('#table-content').addClass('hidden');
                $('#empty-message-tka-tryout-period-list').removeClass('hidden');
                $('.pagination-container-tka-tryout-period-list').empty();
            }
        },

        error: function (err) {
            $('#thead-tka-tryout-period-list-skeleton').addClass('hidden');
            $('#tbody-tka-tryout-period-list-skeleton').addClass('hidden');
            $('#tbody-tka-tryout-period-list').empty();
            $('#table-content').addClass('hidden');
            $('.pagination-container-tka-tryout-period-list').empty();

            console.log(err);
        }
    });
}

function bindPaginationLinks() {
    $('.pagination-container-tka-tryout-period-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const searchYear = $('#search-academic-year').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateTryoutTKAPeriod(searchYear, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$('#search-academic-year').on('change', function () {
    paginateTryoutTKAPeriod($(this).val(), 1);
});

function openTkaTryoutOverrideModal(button) {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const periodId = button.dataset.periodId;
    const periodNumber = button.dataset.periodNumber;
    const academicYear = button.dataset.academicYear;

    const modal = document.getElementById('modal-tka-tryout-override');
    if (!modal) return;

    const periodInformation = modal.querySelector('#tka-tryout-override-period-information');

    if (periodInformation) {
        periodInformation.innerHTML = `
            Periode ${periodNumber}
            <i class="fa-solid fa-circle text-[4px] align-middle"></i>
            Tahun Ajaran ${academicYear}
        `;
    }

    const addButton = modal.querySelector('#button-add-tka-tryout-override');

    if (addButton) {
        addButton.href = `/lms/${role}/tka-tryout-management/period-school-override-form/${periodId}`;
    }

    modal.showModal();

    paginateTkaTryoutOverride(periodId);
}

function openTkaTryoutSessionModal(button) {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const periodId = button.dataset.periodId;
    const periodNumber = button.dataset.periodNumber;
    const academicYear = button.dataset.academicYear;

    const modal = document.getElementById('modal-tka-tryout-session');
    if (!modal) return;

    const periodInformation = modal.querySelector('#tka-tryout-session-period-information');

    if (periodInformation) {
        periodInformation.innerHTML = `
            Periode ${periodNumber}
            <i class="fa-solid fa-circle text-[4px] align-middle"></i>
            Tahun Ajaran ${academicYear}
        `;
    }

    const addButton = modal.querySelector('#button-add-tka-tryout-session');

    if (addButton) {
        addButton.href = `/lms/${role}/tka-tryout-management/session-form/${periodId}`;
    }

    modal.showModal();

    paginateTryoutTKAPeriodSession(periodId);
}

function openTkaTryoutSubjectModal(button) {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const periodId = button.dataset.periodId;
    const periodNumber = button.dataset.periodNumber;
    const academicYear = button.dataset.academicYear;

    const modal = document.getElementById('modal-tka-tryout-subject');
    if (!modal) return;

    const periodInformation = modal.querySelector('#tka-tryout-subject-information');

    if (periodInformation) {
        periodInformation.innerHTML = `
            Periode ${periodNumber}
            <i class="fa-solid fa-circle text-[4px] align-middle"></i>
            Tahun Ajaran ${academicYear}
        `;
    }

    const addButton = modal.querySelector('#button-assign-tka-tryout-subject');

    if (addButton) {
        addButton.href = `/lms/${role}/tka-tryout-management/assign-subject-form/${periodId}`;
    }

    modal.showModal();

    paginateTkaTryoutPeriodSubject(periodId);
}