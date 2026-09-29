function paginateTkaTryoutOverride(periodId) {
    const container = document.getElementById('container');
    if (!container || !periodId) return;

    const role = container.dataset.role;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/tka-tryout-management/period-school-override/${periodId}/paginate`,
        method: 'GET',
        beforeSend: function () {
            $('#tka-tryout-override-list-skeleton').removeClass('hidden');
            $('#tka-tryout-override-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-override-list').addClass('hidden');
        },
        success: function (response) {
            $('#tka-tryout-override-list-skeleton').addClass('hidden');
            $('#tka-tryout-override-list').empty().removeClass('hidden');
            $('#empty-message-tka-tryout-override-list').addClass('hidden');

            if (response.data.length > 0) {
                const totalSchoolOverride = $('#tka-tryout-override-total-school');
                totalSchoolOverride.text(response.total_school_override + ' sekolah' ?? '0 Sekolah');

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

                    $('#tka-tryout-override-list').append(`
                        <div class="border-b border-slate-200 px-4 py-4 last:border-b-0">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                                <!-- SCHOOL INFORMATION -->
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                        ${item.school_partner?.logo
                                            ? `<img src="/${item.school_partner.logo}" alt="Logo Sekolah" class="h-full w-full object-cover">`
                                            : `<i class="fa-solid fa-school text-slate-400"></i>`}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="truncate text-sm font-semibold text-slate-700">
                                                ${item.school_partner?.nama_sekolah ?? 'Sekolah tidak tersedia'}
                                            </p>

                                            <span class="inline-flex shrink-0 items-center rounded-full bg-purple-100 px-2.5 py-1 text-[11px] font-semibold text-purple-700">
                                                Periode Khusus
                                            </span>
                                        </div>

                                        <p class="mt-1 text-xs text-slate-400">
                                            ${startDate}
                                            <span class="mx-1">-</span>
                                            ${endDate}
                                        </p>
                                    </div>
                                </div>

                                <!-- SESSION & ACTION -->
                                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-end lg:w-auto lg:shrink-0">

                                    <button type="button" class="btn btn-sm w-full justify-between rounded-lg border-slate-200 bg-white px-3
                                        text-[#0071BC] hover:border-[#0071BC] hover:bg-blue-50 sm:w-auto sm:justify-center" data-role="${role}" data-override-id="${item.id}"
                                        onclick="openTkaTryoutOverrideSessionModal(this)"
                                        data-period-id="${item.id}" 
                                        data-school-name="${item.school_partner?.nama_sekolah ?? 'Sekolah tidak tersedia'}" 
                                        data-academic-year="${item.tka_tryout_period?.tahun_ajaran}" data-period-number="${item.tka_tryout_period?.period_number}">

                                        <span class="flex items-center gap-2">
                                            <i class="fa-regular fa-clock"></i>
                                            <span>${item.total_session ?? 0} Sesi</span>
                                        </span>

                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm w-full whitespace-nowrap rounded-lg border-emerald-200 bg-emerald-50 px-3
                                        text-emerald-700 hover:border-emerald-500 hover:bg-emerald-500 hover:text-white sm:w-auto"
                                        data-role="${role}" data-override-id="${item.id}" data-period-id="${item.id}" 
                                        data-school-name="${item.school_partner?.nama_sekolah ?? 'Sekolah tidak tersedia'}" 
                                        data-academic-year="${item.tka_tryout_period?.tahun_ajaran}" data-period-number="${item.tka_tryout_period?.period_number}"
                                        onclick="openTkaTryoutOverrideSubjectModal(this)">

                                        <i class="fa-solid fa-book-open"></i>
                                        <span>${item.total_subject ?? 0} Mapel</span>
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                    </button>

                                    <button type="button" id="btn-edit-tka-tryout-period-school-override"
                                        class="btn btn-sm w-full whitespace-nowrap rounded-lg border-[#0071BC]/20 bg-blue-50 px-3
                                        text-[#0071BC] hover:border-[#0071BC] hover:bg-[#0071BC] hover:text-white sm:w-auto"
                                        data-role="${role}"
                                        data-override-id="${item.id}"
                                        data-period-id="${periodId}"
                                        data-school-partner-id="${item.school_partner_id}"
                                        data-school-name="${item.school_partner?.nama_sekolah ?? 'Sekolah tidak tersedia'}"
                                        data-start-date="${item.start_date ?? ''}"
                                        data-end-date="${item.end_date ?? ''}"
                                        data-is-review="${item.is_review ?? 0}">

                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>Edit</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                });

            } else {
                $('#tka-tryout-override-list').addClass('hidden').empty();
                $('#empty-message-tka-tryout-override-list').removeClass('hidden');
            }
        },
        error: function (err) {
            $('#tka-tryout-override-list-skeleton').addClass('hidden');
            $('#tka-tryout-override-list').addClass('hidden').empty();
            $('#empty-message-tka-tryout-override-list').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).off('click', '#button-cancel-edit-tka-tryout-period-school-override').on('click', '#button-cancel-edit-tka-tryout-period-school-override', function (e) {
    e.preventDefault();

    const modal = document.getElementById('modal-edit-tka-tryout-period-school-override');

    if (!modal) {
        return;
    }

    modal.close();
});

function openTkaTryoutOverrideSessionModal(button) {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const periodOverrideId = button.dataset.periodId;
    const periodNumber = button.dataset.periodNumber;
    const academicYear = button.dataset.academicYear;
    const schoolName = button.dataset.schoolName;

    const tkaTryoutInformtion = $('#tka-tryout-override-session-school-information');
    tkaTryoutInformtion.html(`
        Periode ${periodNumber}
        <i class="fa-solid fa-circle text-[4px] align-middle"></i>

        Tahun Ajaran ${academicYear}
        <i class="fa-solid fa-circle text-[4px] align-middle"></i>

        ${schoolName}
    `);

    const schoolOverrideListModal = document.getElementById('modal-tka-tryout-override');
    const overrideSessionModal = document.getElementById('modal-tka-tryout-override-session');

    const addButton = overrideSessionModal.querySelector('#button-add-tka-tryout-override-session');

    if (addButton) {
        addButton.href = `/lms/${role}/tka-tryout-management/session-form/${periodOverrideId}/school-override`;
    }

    schoolOverrideListModal.close();
    overrideSessionModal.showModal();

    paginateTryoutTKAPeriodSessionOverride(periodOverrideId)
}

function openTkaTryoutOverrideSubjectModal(button) {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const periodOverrideId = button.dataset.overrideId;
    const periodNumber = button.dataset.periodNumber;
    const academicYear = button.dataset.academicYear;
    const schoolName = button.dataset.schoolName;

    if (!role || !periodOverrideId) return;

    const tkaTryoutInformtion = $('#tka-tryout-override-subject-information');

    tkaTryoutInformtion.html(`
        Periode ${periodNumber}
        <i class="fa-solid fa-circle text-[4px] align-middle"></i>

        Tahun Ajaran ${academicYear}
        <i class="fa-solid fa-circle text-[4px] align-middle"></i>

        ${schoolName}
    `);

    const schoolOverrideListModal = document.getElementById('modal-tka-tryout-override');
    const overrideSubjectModal = document.getElementById('modal-tka-tryout-override-subject');

    const addButton = overrideSubjectModal.querySelector(
        '#button-assign-tka-tryout-override-subject'
    );

    if (addButton) {
        addButton.href = `/lms/${role}/tka-tryout-management/assign-subject-form/${periodOverrideId}/school-override`;
    }

    schoolOverrideListModal.close();
    overrideSubjectModal.showModal();

    paginateTkaTryoutPeriodSubjectOverride(periodOverrideId);
}