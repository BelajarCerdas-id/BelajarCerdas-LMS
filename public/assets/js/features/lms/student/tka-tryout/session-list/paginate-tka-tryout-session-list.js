function paginateTkaTryoutSession() {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const periodId = container.dataset.periodId;

    if (!role || !schoolName || !schoolId || !periodId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/tryout-tka/period-list/${periodId}/session-list/paginate`,
        method: 'GET',
        beforeSend: function () {
            $('#header-skeleton').removeClass('hidden');
            $('#header-content').addClass('hidden');
            $('#session-list-skeleton').removeClass('hidden');
            $('#container-session-list').addClass('hidden');
            $('#empty-message-session-list').addClass('hidden');
            $('#session-list').empty();
        },
        success: function (response) {
            $('#session-list').empty();
            $('#header-skeleton').addClass('hidden');
            $('#header-content').removeClass('hidden');
            $('#session-list-skeleton').addClass('hidden');

            if (response.status && Array.isArray(response.data) && response.data.length > 0) {
                $('#container-session-list').removeClass('hidden');
                $('#empty-message-session-list').addClass('hidden');

                const data = response.data;
                let totalSession = 0;
                let totalSubject = 0;

                $.each(data, function (index, dateItem) {
                    const dateObject = new Date(`${dateItem.date}T00:00:00`);

                    const dayName = dateObject.toLocaleDateString('id-ID', {
                        weekday: 'long'
                    });

                    const formattedDate = dateObject.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    let sessionHtml = '';

                    $.each(dateItem.sessions || [], function (sessionIndex, session) {
                        totalSession++;

                        const isRegistered = session.is_registered === true || session.is_registered === 1;

                        $.each(session.subjects || [], function (subjectIndex, subject) {
                            totalSubject++;

                            const startDateTime = session.start_datetime;
                            const endDateTime = session.end_datetime;

                            const tkaTryoutTestUrl = `/lms/${role}/${schoolName}/${schoolId}/tryout-tka/period-list/${periodId}/session-list/${session.id}/subject/${subject.subject_id}/test`;

                            sessionHtml += `
                                <div class="session-card bg-white rounded-2xl border ${isRegistered ? 'border-gray-200' : 'border-gray-100'} overflow-hidden transition-all duration-300 hover:shadow-md"
                                    data-start="${startDateTime}"
                                    data-end="${endDateTime}"
                                    data-session-id="${session.id}"
                                    data-subject-id="${subject.id}"
                                    data-registered="${isRegistered ? 1 : 0}"
                                    data-test-url="${tkaTryoutTestUrl}">

                                    <div class="p-5">
                                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ${isRegistered ? 'bg-blue-50 text-[#0071BC]' : 'bg-gray-100 text-gray-400'} font-bold">
                                                    ${subject.subject_name ? subject.subject_name.substring(0, 2).toUpperCase() : '-'}
                                                </div>

                                                <div>
                                                    <h4 class="font-bold text-gray-800">
                                                        ${subject.subject_name ?? '-'}
                                                    </h4>

                                                    <p class="text-sm text-gray-500">
                                                        ${subject.total_question ?? 0} Soal
                                                    </p>
                                                </div>
                                            </div>

                                            <span class="session-badge inline-flex w-fit items-center gap-1.5 rounded-full ${isRegistered ? 'bg-blue-50 text-[#0071BC]' : 'bg-gray-100 text-gray-500'} px-3 py-1 text-xs font-semibold">
                                                <i class="fa-solid ${isRegistered ? 'fa-layer-group' : 'fa-lock'}"></i>
                                                Sesi ${session.session_number}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 mt-5">
                                            <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                                                <div class="flex items-center gap-2 text-gray-400 text-xs">
                                                    <i class="fa-regular fa-clock"></i>
                                                    <span>Waktu</span>
                                                </div>

                                                <div class="font-semibold text-gray-700 mt-1">
                                                    ${session.start_time} - ${session.end_time} ${response.timezone_label ?? ''}
                                                </div>
                                            </div>

                                            <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                                                <div class="flex items-center gap-2 text-gray-400 text-xs">
                                                    <i class="fa-solid fa-hourglass-half"></i>
                                                    <span>Durasi Pengerjaan</span>
                                                </div>

                                                <div class="font-semibold text-gray-700 mt-1">
                                                    ${subject.duration ?? 0} Menit
                                                </div>
                                            </div>
                                        </div>

                                        <div class="session-registration-status mt-4 rounded-xl ${isRegistered ? 'bg-green-50' : 'bg-gray-50'} px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <i class="fa-solid ${isRegistered ? 'fa-circle-check text-green-600' : 'fa-lock text-gray-400'}"></i>

                                                <div>
                                                    <div class="text-xs font-medium text-gray-500">
                                                        Akses Sesi
                                                    </div>

                                                    <div class="font-semibold ${isRegistered ? 'text-green-600' : 'text-gray-500'} mt-1">
                                                        ${isRegistered ? 'Kamu terdaftar di sesi ini' : 'Kamu tidak terdaftar di sesi ini'}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="session-status mt-4 rounded-xl bg-blue-50 px-4 py-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="session-status-label text-xs font-medium text-gray-500">
                                                        Sesi dimulai dalam
                                                    </div>

                                                    <div class="session-countdown font-bold text-[#0071BC] text-lg mt-1">
                                                        --:--:--
                                                    </div>
                                                </div>

                                                <i class="fa-solid fa-hourglass-half session-hourglass text-xl text-[#0071BC]"></i>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="session-action-btn w-full mt-4 rounded-xl bg-gray-200 text-gray-400 font-semibold py-3 cursor-default"
                                            data-test-url="${tkaTryoutTestUrl}"
                                            data-session-id="${session.id}"
                                            data-subject-id="${subject.id}"
                                            data-registered="${isRegistered ? 1 : 0}"
                                            disabled>
                                            <i class="fa-solid fa-lock mr-1"></i>
                                            ${isRegistered ? 'Belum Dimulai' : 'Tidak Terdaftar'}
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                    });

                    if (sessionHtml) {
                        $('#session-list').append(`
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-[#0071BC]">
                                        <i class="fa-solid fa-calendar-day text-xl"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800">
                                            ${formattedDate}
                                        </h3>

                                        <p class="text-sm text-gray-500">
                                            ${dayName}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                    ${sessionHtml}
                                </div>
                            </div>
                        `);
                    }
                });

                    $('#total-session-date').text(response.total_date ?? data.length);
                    $('#total-session-subject').text(response.total_subject ?? totalSubject);
                    $('#total-session').text(response.total_session ?? totalSession);

                    renderTkaTryoutPeriodHeader(response);
                    startTkaSessionCountdown();
                } else {
                    $('#container-session-list').addClass('hidden');
                    $('#empty-message-session-list').removeClass('hidden');

                    renderTkaTryoutPeriodHeader(response);

                    $('#total-session-date').text(response.total_date ?? 0);
                    $('#total-session-subject').text(response.total_subject ?? 0);
                    $('#total-session').text(response.total_session ?? 0);
                }
            },
            error: function (error) {
                console.log(error);

                $('#header-skeleton').addClass('hidden');
                $('#header-content').removeClass('hidden');
                $('#session-list-skeleton').addClass('hidden');
                $('#container-session-list').addClass('hidden');
                $('#empty-message-session-list').removeClass('hidden');
            }
                });
            }

function renderTkaTryoutPeriodHeader(response) {
    if (!response.period) return;

    $('#header-period-label').text(
        `Gelombang Tryout ${response.period.period_number ?? '-'}`
    );

    if (!response.period.start_date || !response.period.end_date) return;

    const startDate = new Date(`${response.period.start_date}T00:00:00`);
    const endDate = new Date(`${response.period.end_date}T00:00:00`);

    const startFormatted = startDate.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

    const endFormatted = endDate.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

    $('#period-date-label').text(
        `${startFormatted} - ${endFormatted}`
    );
}

let tkaSessionCountdownInterval = null;

function startTkaSessionCountdown() {
    if (tkaSessionCountdownInterval) {
        clearInterval(tkaSessionCountdownInterval);
    }

    updateTkaSessionCountdown();

    tkaSessionCountdownInterval = setInterval(function () {
        updateTkaSessionCountdown();
    }, 1000);
}

function updateTkaSessionCountdown() {
    $('.session-card').each(function () {
        const card = $(this);

        const startTime = new Date(card.attr('data-start')).getTime();
        const endTime = new Date(card.attr('data-end')).getTime();
        const isRegistered = card.attr('data-registered') === '1';
        const now = Date.now();

        const status = card.find('.session-status');
        const statusLabel = card.find('.session-status-label');
        const countdown = card.find('.session-countdown');
        const button = card.find('.session-action-btn');
        const badge = card.find('.session-badge');
        const hourglass = card.find('.session-hourglass');

        if (!isRegistered) {
            countdown.text('Akses tidak tersedia');

            status.removeClass('bg-blue-50 bg-green-50').addClass('bg-gray-50');

            countdown.removeClass('text-[#0071BC] text-green-600').addClass('text-gray-500');

            statusLabel.text('Sesi tidak ditujukan untuk kamu');

            hourglass.removeClass('text-[#0071BC] text-green-600 animate').addClass('text-gray-400');

            button.removeClass('bg-[#0071BC] text-white cursor-pointer').addClass('bg-gray-200 text-gray-400 cursor-default')
                .html('<i class="fa-solid fa-lock mr-1"></i> Tidak Terdaftar').removeAttr('data-action').prop('disabled', false);

            badge.removeClass('bg-green-100 text-green-700').addClass('bg-gray-100 text-gray-500');

            return;
        }

        if (now < startTime) {
            const difference = startTime - now;

            const day = Math.floor(difference / (1000 * 60 * 60 * 24));
            const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

            countdown.text(
                `${String(day).padStart(2, '0')} Hari ${String(hours).padStart(2, '0')} Jam ${String(minutes).padStart(2, '0')} Menit ${String(seconds).padStart(2, '0')} Detik`
            );

            status.removeClass('bg-green-50 bg-gray-50').addClass('bg-blue-50');

            countdown.removeClass('text-green-600 text-gray-500').addClass('text-[#0071BC]');

            statusLabel.text('Sesi dimulai dalam');

            hourglass.removeClass('text-green-600 text-gray-500').addClass('text-[#0071BC] animate');

            button.removeClass('bg-[#0071BC] text-white cursor-pointer bg-gray-100 text-gray-500').addClass('bg-gray-200 text-gray-400 cursor-default')
                .html('<i class="fa-solid fa-lock mr-1"></i> Belum Dimulai').removeAttr('data-action').prop('disabled', true);

            badge.removeClass('bg-green-100 text-green-700').addClass('bg-gray-100 text-gray-600');
        } else if (now >= startTime && now <= endTime) {
            const difference = endTime - now;

            const day = Math.floor(difference / (1000 * 60 * 60 * 24));
            const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

            countdown.text(
                `${String(day).padStart(2, '0')} Hari ${String(hours).padStart(2, '0')} Jam ${String(minutes).padStart(2, '0')} Menit ${String(seconds).padStart(2, '0')} Detik`
            );

            status.removeClass('bg-blue-50 bg-gray-50').addClass('bg-green-50');

            countdown.removeClass('text-[#0071BC] text-gray-500').addClass('text-green-600');

            statusLabel.text('Sesi sedang berlangsung');

            hourglass.removeClass('text-[#0071BC] text-gray-500').addClass('text-green-600 animate');

            button.removeClass('bg-gray-200 text-gray-400 cursor-default bg-gray-100 text-gray-500').addClass('bg-[#0071BC] text-white cursor-pointer')
                .html('<i class="fa-solid fa-play mr-1"></i> Mulai Tryout').attr('data-action', 'test').prop('disabled', false);

            badge.removeClass('bg-gray-100 text-gray-600').addClass('bg-green-100 text-green-700');
        } else {
            countdown.text('Sesi telah selesai');

            status.removeClass('bg-blue-50 bg-green-50').addClass('bg-gray-50');

            countdown.removeClass('text-[#0071BC] text-green-600').addClass('text-gray-500');

            statusLabel.text('Sesi selesai');

            hourglass.removeClass('text-[#0071BC] text-green-600 animate').addClass('text-gray-500');

            button.removeClass('bg-gray-200 text-gray-400 cursor-default bg-gray-100 text-gray-500').addClass('bg-[#0071BC] text-white cursor-pointer')
                .html('<i class="fa-solid fa-clipboard-check mr-1"></i> Lihat Jawaban').attr('data-action', 'review').prop('disabled', false);

            badge.removeClass('bg-green-100 text-green-700').addClass('bg-gray-100 text-gray-600');
        }
    });
}

$(document).off('click', '.session-action-btn').on('click', '.session-action-btn', function () {
    const button = $(this);

    if (button.prop('disabled')) return;

    const isRegistered = button.attr('data-registered') === '1';

    if (!isRegistered) {
        Swal.fire({
            icon: 'warning',
            title: 'Akses Tidak Diizinkan',
            text: 'Kamu tidak terdaftar pada sesi Tryout TKA ini.',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#0071BC'
        });
        return;
    }

    const action = button.attr('data-action');
    const testUrl = button.attr('data-test-url');

    if (!action || !testUrl) return;

    if (action === 'test' || action === 'review') {
        window.location.href = testUrl;
    }
});

$(document).ready(function () {
    paginateTkaTryoutSession();
});