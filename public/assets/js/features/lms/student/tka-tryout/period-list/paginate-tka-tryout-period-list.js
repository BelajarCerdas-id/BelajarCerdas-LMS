function paginateTkaTryoutPeriod() {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/tryout-tka/period-list/paginate`,
        method: 'GET',
        beforeSend: function () {
            $('#header-skeleton').removeClass('hidden');
            $('#header-content').addClass('hidden');
            $('#period-list-skeleton').removeClass('hidden');
            $('#container-period-list').addClass('hidden');
            $('#empty-message-period-list').addClass('hidden');
            $('#period-list').empty();

        },
        success: function (response) {
            $('#period-list').empty();
            $('#header-skeleton').addClass('hidden');
            $('#header-content').removeClass('hidden');
            $('#period-list-skeleton').addClass('hidden');

            const periods = Array.isArray(response?.data) ? response.data : [];
            const totalPeriod = periods.length;

            $('#total-period').text(totalPeriod);
            $('#total-period-list').text(totalPeriod);

            if (totalPeriod > 0) {

                $('#container-period-list').removeClass('hidden');
                $('#empty-message-period-list').addClass('hidden');

                $.each(periods, function (index, item) {

                    let statusText = 'Belum Dimulai';
                    let statusClass = 'bg-blue-50 text-[#0071BC]';
                    let statusIcon = 'fa-clock';

                    if (item.period_status === 'ongoing') {
                        statusText = 'Sedang Berlangsung';
                        statusClass = 'bg-green-50 text-green-600';
                        statusIcon = 'fa-circle-play';

                    } else if (item.period_status === 'finished') {
                        statusText = 'Selesai';
                        statusClass = 'bg-gray-100 text-gray-500';
                        statusIcon = 'fa-circle-check';
                    }

                    const startDate = item.start_date ? new Date(item.start_date) : null;
                    const endDate = item.end_date ? new Date(item.end_date) : null;
                    const formattedStartDate = startDate ? startDate.toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'}) : '-';
                    const formattedEndDate = endDate ? endDate.toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'}) : '-';
                    const sessionCount = Array.isArray(item.tka_tryout_session) ? item.tka_tryout_session.length : 0;
                    const subjectCount = Array.isArray(item.tka_tryout_subject) ? item.tka_tryout_subject.length : 0;

                    $('#period-list').append(`

                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="p-6 sm:p-7">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0071BC] flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-layer-group text-xl"></i>
                                        </div>

                                        <div>
                                            <div class="text-sm font-medium text-[#0071BC]">
                                                Gelombang ${item.period_number ?? '-'}
                                            </div>

                                            <h3 class="text-xl font-bold text-gray-800 mt-1">
                                                Tryout TKA Gelombang ${item.period_number ?? '-'}
                                            </h3>

                                            <p class="text-sm text-gray-500 mt-2">
                                                Tahun Ajaran ${item.tahun_ajaran ?? '-'}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full ${statusClass} text-sm font-medium w-fit">
                                        <i class="fa-solid ${statusIcon} text-xs"></i>
                                        <span>${statusText}</span>
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="flex flex-wrap items-center gap-3 mt-6">
                                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 text-gray-600 text-sm">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>${formattedStartDate}</span>
                                    </div>

                                    <i class="fa-solid fa-arrow-right text-gray-300 text-xs"></i>

                                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 text-gray-600 text-sm">
                                        <i class="fa-regular fa-calendar-check"></i>
                                        <span>${formattedEndDate}</span>
                                    </div>
                                </div>

                                <!-- Statistics -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5">
                                    <div class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-[#0071BC] flex items-center justify-center">
                                            <i class="fa-solid fa-list-ol"></i>
                                        </div>

                                        <div>
                                            <div class="text-xs text-gray-500">
                                                Jumlah Sesi
                                            </div>

                                            <div class="text-sm font-bold text-gray-800 mt-1">
                                                ${sessionCount} Sesi
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-50 text-[#0071BC] flex items-center justify-center">
                                            <i class="fa-solid fa-book"></i>
                                        </div>

                                        <div>
                                            <div class="text-xs text-gray-500">
                                                Mata Pelajaran
                                            </div>

                                            <div class="text-sm font-bold text-gray-800 mt-1">
                                                ${subjectCount} Mata Pelajaran
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-6 pt-5 border-t border-gray-100">
                                    <div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fa-regular fa-calendar-days text-[#0071BC]"></i>
                                            <span>
                                                ${item.period_status === 'ongoing' ? 'Gelombang sedang berlangsung.' : item.period_status === 'upcoming'
                                                    ? `Dimulai pada ${formattedStartDate}.` : `Berakhir pada ${formattedEndDate}.`
                                                }
                                            </span>
                                        </div>
                                    </div>

                                    <button type="button" data-period-id="${item.id}" data-period-status="${item.period_status}" data-is-review="${item.is_review ? 1 : 0}"
                                        class="btn-view-period w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#0071BC] 
                                            hover:bg-[#005B99] text-white font-medium transition-colors duration-200 cursor-pointer">

                                        <span>Lihat Gelombang</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                });

            } else {
                $('#container-period-list').addClass('hidden');
                $('#empty-message-period-list').removeClass('hidden');
            }
        },
        error: function (error) {

            console.log(error);

            $('#header-skeleton').addClass('hidden');
            $('#header-content').removeClass('hidden');

            $('#period-list-skeleton').addClass('hidden');
            $('#container-period-list').addClass('hidden');
            $('#empty-message-period-list').removeClass('hidden');

        },
    });
}

$(document).on('click', '.btn-view-period', function () {
    const button = $(this);
    const periodId = button.data('period-id');

    const container = document.getElementById('container');
    if (!container || !periodId) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/tryout-tka/period-list/${periodId}/check`,
        method: 'GET',
        beforeSend: function () {
            button.prop('disabled', true);
        },
        success: function (response) {

            if (!response.success || !response.data) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: response.message ?? 'Data gelombang tidak dapat diperiksa.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#0071BC'
                });

                return;
            }

            const period = response.data;

            if (period.period_status === 'upcoming') {
                Swal.fire({
                    icon: 'info',
                    title: 'Gelombang Belum Dimulai',
                    text: 'Kamu belum dapat mengikuti gelombang ini karena periode Tryout TKA belum dimulai.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#0071BC'
                });

                return;
            }

            if (period.period_status === 'finished') {
                Swal.fire({
                    icon: 'info',
                    title: 'Gelombang Telah Berakhir',
                    text: 'Gelombang Tryout TKA ini sudah berakhir dan tidak tersedia untuk diikuti lagi.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#0071BC'
                });

                return;
            }

            window.location.href = `/lms/${role}/${schoolName}/${schoolId}/tryout-tka/period-list/${periodId}/session-list`;
        },
        error: function (error) {
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Tidak Dapat Membuka Gelombang',
                text: error.responseJSON?.message ??
                    'Terjadi kesalahan saat memeriksa gelombang Tryout TKA.',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#0071BC'
            });

        },
        complete: function () {
            button.prop('disabled', false);
        }
    });
});

$(document).ready(function () {
    paginateTkaTryoutPeriod();
});