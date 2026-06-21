function paginatePaymentDetail() {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolId = container.dataset.schoolId;
    const contractId = container.dataset.contractId;

    if (!role || !schoolId || !contractId) return;

    $.ajax({

        url: `/lms/${role}/manage-contract/schools/${schoolId}/contract/${contractId}/payment-detail/paginate`,
        method: 'GET',

        beforeSend: function () {

            $('#kpi-skeleton').removeClass('hidden');
            $('#contract-list-skeleton').removeClass('hidden');
            $('#progress-skeleton').removeClass('hidden');

            $('#kpi-content').addClass('hidden');
            $('#grid-term-list').addClass('hidden');
            $('#progress-content').addClass('hidden');
        },

        success: function (response) {

            $('#kpi-skeleton').addClass('hidden');
            $('#contract-list-skeleton').addClass('hidden');
            $('#progress-skeleton').addClass('hidden');

            $('#kpi-content').removeClass('hidden');
            $('#grid-term-list').removeClass('hidden');
            $('#progress-content').removeClass('hidden');

            renderContractKpi(response.kpi);

            renderTermList(response.terms, response.uploadContractStudent, response.studentList);
        },

        error: function (xhr) {

            console.error(xhr.responseText);
        }
    });
}

// KPI
function renderContractKpi(kpi) {

    $('#total-contract-value').text(
        formatRupiah(kpi.total_contract_value)
    );

    $('#total-paid').text(
        formatRupiah(kpi.total_paid)
    );

    $('#outstanding-amount').text(
        formatRupiah(kpi.outstanding_amount)
    );

    animateProgressBar(
        kpi.progress
    );

    // Summary bawah progress bar
    $('#contract-value-summary').text(
        formatRupiah(kpi.total_contract_value)
    );

    $('#paid-summary').text(
        formatRupiah(kpi.total_paid)
    );

    $('#remaining-summary').text(
        formatRupiah(kpi.outstanding_amount)
    );
}

// TERM LIST
function renderTermList(terms, uploadContractStudent, studentList) {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolId = container.dataset.schoolId;
    const contractId = container.dataset.contractId;

    if (!role || !schoolId || !contractId) return;

    $('#grid-term-list').empty();

    if (!terms || terms.length === 0) {

        $('#empty-message-contract-list')
            .removeClass('hidden');

        return;
    }

    $('#empty-message-contract-list')
        .addClass('hidden');

    const formatDate = (dateString) => {

        if (!dateString) return '-';

        const months = [
            'Jan', 'Feb', 'Mar', 'Apr',
            'Mei', 'Jun', 'Jul', 'Agu',
            'Sep', 'Okt', 'Nov', 'Des'
        ];

        const date = new Date(dateString);

        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    };

    $.each(terms, function (index, term) {

        let badge = '';
        let border = '';
        let bgColor = '';
        let icon = '';
        let actionButton = '';

        const periodStart = formatDate(term.period_start);
        const periodEnd = formatDate(term.period_end);
        const paidAt = formatDate(term.paid_at);

        const linkStudentList = studentList.replace(':role', role).replace(':schoolId', schoolId).replace(':contractId', contractId).replace(':termId', term.id); 

        if (term.status === 'paid') {

            badge = `
                <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-700">
                    PAID
                </span>
            `;

            bgColor = 'bg-green-50';
            border = 'border-green-200';

            icon = `
                <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-green-600"></i>
                </div>
            `;

            actionButton = `
                <div class="dropdown dropdown-end w-full lg:w-auto">

                    <label tabindex="0"
                        class="w-full lg:w-44 h-12 lg:h-11 rounded-2xl bg-green-600 hover:bg-green-700 border border-green-200 text-white flex items-center justify-center gap-2
                        cursor-pointer font-semibold text-sm transition">

                        <i class="fa-solid fa-ellipsis"></i>
                        Aksi

                    </label>

                    <ul tabindex="0"
                        class="dropdown-content menu bg-white rounded-2xl shadow-xl border border-slate-200 w-64 p-2 z-50">
                        <li>
                            <a href="${linkStudentList}">
                                <i class="fa-solid fa-users"></i>
                                Lihat Daftar Siswa
                            </a>
                        </li>
                    </ul>

                </div>
            `;

        } else if (term.status === 'overdue') {

            badge = `
                <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-700">
                    OVERDUE
                </span>
            `;

            bgColor = 'bg-red-50';
            border = 'border-red-200';

            icon = `
                <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                </div>
            `;

            actionButton = `
                <div class="dropdown dropdown-end w-full lg:w-auto">

                    <label tabindex="0"
                        class="w-full lg:w-44 h-12 lg:h-11 rounded-2xl bg-red-600 hover:bg-red-700 text-white flex items-center justify-center gap-2
                        cursor-pointer font-semibold text-sm transition">

                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Aksi

                    </label>

                    <ul tabindex="0"
                        class="dropdown-content menu bg-white rounded-2xl shadow-xl border border-slate-200 w-64 p-2 z-50">

                        <li>
                            <a href="${linkStudentList}">
                                <i class="fa-solid fa-users"></i>
                                Lihat Daftar Siswa
                            </a>
                        </li>

                        <div class="divider my-1"></div>

                        <li>
                            <a
                                onclick="confirmMarkPaid(${term.id})"
                                class="text-green-600 font-semibold">

                                <i class="fa-solid fa-circle-check"></i>
                                Tandai Sebagai Lunas

                            </a>
                        </li>

                    </ul>

                </div>
            `;

        } else {

            badge = `
                <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700">
                    UNPAID
                </span>
            `;

            bgColor = 'bg-amber-50';
            border = 'border-amber-200';

            icon = `
                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-amber-600"></i>
                </div>
            `;

            actionButton = `
                <div class="dropdown dropdown-end w-full lg:w-auto">

                    <label tabindex="0"
                        class="w-full lg:w-44 h-12 lg:h-11 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center gap-2
                        cursor-pointer font-semibold text-sm transition">

                        <i class="fa-solid fa-gear"></i>
                        Aksi

                    </label>

                    <ul tabindex="0"
                        class="dropdown-content menu bg-white rounded-2xl shadow-xl border border-slate-200 w-64 p-2 z-50">

                        <li>
                            <a href="${linkStudentList}">
                                <i class="fa-solid fa-users"></i>
                                Lihat Daftar Siswa
                            </a>
                        </li>

                        <div class="divider my-1"></div>

                        <li>
                            <a
                                onclick="confirmMarkOverdue(${term.id})"
                                class="text-red-600 font-semibold">

                                <i class="fa-solid fa-calendar-xmark"></i>
                                Tandai Sebagai Terlambat

                            </a>
                        </li>

                        <li>
                            <a
                                onclick="confirmMarkPaid(${term.id})"
                                class="text-green-600 font-semibold">

                                <i class="fa-solid fa-circle-check"></i>
                                Tandai Sebagai Lunas

                            </a>
                        </li>

                    </ul>

                </div>
            `;
        }

        $('#grid-term-list').append(`

            <div class="border ${border} ${bgColor} rounded-3xl p-5 lg:p-6 mb-5">

                <!-- HEADER -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <div class="flex items-start gap-3">

                        ${icon}

                        <div>

                            <div class="flex items-center gap-2 flex-wrap">

                                <h3 class="font-bold text-lg lg:text-xl">
                                    Termin ${term.term_number}
                                </h3>

                                ${badge}

                            </div>

                            <p class="text-slate-500 text-sm mt-1">
                                ${periodStart} - ${periodEnd}
                            </p>

                        </div>

                    </div>

                    <div class="w-full lg:w-auto">
                        ${actionButton}
                    </div>

                </div>

                <!-- DATA -->
                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">

                    <div class="relative overflow-hidden bg-linear-to-br from-white to-slate-50 border border-slate-200 rounded-2xl p-4 shadow-sm 
                        hover:shadow-md transition">

                        <div class="absolute top-0 left-0 w-full h-1 bg-blue-500"></div>

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Siswa Aktif
                                </p>

                                <h4 class="font-black text-2xl mt-2 text-slate-800">
                                    ${term.active_students ?? 0} / ${term.students_count ?? 0}
                                </h4>

                            </div>

                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">

                                <i class="fa-solid fa-users text-blue-600"></i>

                            </div>

                        </div>

                    </div>

                    <div class="relative overflow-hidden bg-linear-to-br from-white to-emerald-50 border border-emerald-200 rounded-2xl p-4 shadow-sm 
                        hover:shadow-md transition">

                        <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Harga Per Siswa
                                </p>

                                <h4 class="font-bold text-lg mt-2 wrap-break-word">
                                    ${formatRupiah(term.price_per_student ?? 0)}
                                </h4>

                            </div>

                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">

                                <i class="fa-solid fa-user-tag text-emerald-600"></i>

                            </div>

                        </div>

                    </div>

                    <div class="relative overflow-hidden bg-linear-to-br from-white to-bg-amber-200 border border-blue-200 rounded-2xl p-4 shadow-sm 
                        hover:shadow-md transition">

                        <div class="absolute top-0 left-0 w-full h-1 bg-amber-200"></div>

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Nilai per Bulan
                                </p>

                                <h4 class="font-bold text-lg mt-2 wrap-break-word text-amber-700">
                                    ${formatRupiah(term.monthly_amount ?? 0)}
                                </h4>

                            </div>

                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">

                                <i class="fa-solid fa-calendar-days text-amber-700"></i>

                            </div>

                        </div>

                    </div>

                    <div class="relative overflow-hidden bg-linear-to-br from-white to-violet-50 border border-violet-200 rounded-2xl p-4 shadow-sm 
                        hover:shadow-md transition">

                        <div class="absolute top-0 left-0 w-full h-1 bg-violet-500"></div>

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Nilai Termin
                                </p>

                                <h4 class="font-black text-lg mt-2 text-violet-700 wrap-break-word">
                                    ${formatRupiah(term.term_amount ?? 0)}
                                </h4>

                            </div>

                            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">

                                <i class="fa-solid fa-wallet text-violet-600"></i>

                            </div>

                        </div>

                    </div>

                    <div class="relative overflow-hidden bg-linear-to-br from-white to-green-50 border border-green-200 rounded-2xl p-4 shadow-sm 
                        hover:shadow-md transition">

                        <div class="absolute top-0 left-0 w-full h-1 bg-green-500"></div>

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Tanggal Pelunasan
                                </p>

                                <h4 class="font-bold mt-2 ${term.status === 'paid' ? 'text-green-700' : 'text-slate-500'}">
                                    ${term.status === 'paid' ? paidAt : '-'}
                                </h4>

                            </div>

                            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="fa-solid fa-circle-check text-green-600"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    });
}

$(document).ready(function () {
    paginatePaymentDetail();
});

function formatRupiah(number) {

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number ?? 0);
}

function animateProgressBar(progress) {

    if (progress <= 0) {

        $('#payment-progress-label').text('0%');

        $('#payment-progress-bar').css('width', '0%');

        return;
    }

    $('#payment-progress-bar')
        .css({
            width: '0%',
            background: 'linear-gradient(90deg,#22c55e,#15803d)'
        });

    let current = 0;

    const interval = setInterval(() => {

        current++;

        $('#payment-progress-label')
            .text(`${current}%`);

        $('#payment-progress-bar')
            .css('width', `${current}%`);

        if (current >= progress) {
            clearInterval(interval);
        }

    }, 12);
}

function confirmMarkOverdue(termId) {

    Swal.fire({
        title: 'Tandai Termin Terlambat?',
        text: 'Termin ini akan ditandai sebagai terlambat (Overdue).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Tandai Terlambat',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {

        if (result.isConfirmed) {

            const container = document.getElementById('container');

            if (!container) return;

            const role = container.dataset.role;
            const schoolId = container.dataset.schoolId;
            const contractId = container.dataset.contractId;

            if (!role || !schoolId || !contractId) return;

            let status = 'overdue';

            $.ajax({
                url: `/lms/${role}/manage-contract/schools/${schoolId}/contract/${contractId}/payment-detail/term/${termId}/mark-status`,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    status: status // Kirim status baru (aktif / non aktif)
                },

                success: function (response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Termin berhasil ditandai sebagai terlambat.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    paginatePaymentDetail();
                },

                error: function () {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat memperbarui status.'
                    });
                }
            });

        }
    });
}

function confirmMarkPaid(termId) {

    Swal.fire({
        title: 'Tandai Termin Lunas?',
        text: 'Termin ini akan ditandai sebagai sudah lunas.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Tandai Lunas',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {

        if (result.isConfirmed) {

            const container = document.getElementById('container');

            if (!container) return;

            const role = container.dataset.role;
            const schoolId = container.dataset.schoolId;
            const contractId = container.dataset.contractId;

            if (!role || !schoolId || !contractId) return;

            let status = 'paid';

            $.ajax({
                url: `/lms/${role}/manage-contract/schools/${schoolId}/contract/${contractId}/payment-detail/term/${termId}/mark-status`,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    status: status // Kirim status baru (aktif / non aktif)
                },

                success: function (response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Termin berhasil ditandai sebagai lunas.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    paginatePaymentDetail();
                },

                error: function () {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat memperbarui status.'
                    });
                }
            });

        }
    });
}