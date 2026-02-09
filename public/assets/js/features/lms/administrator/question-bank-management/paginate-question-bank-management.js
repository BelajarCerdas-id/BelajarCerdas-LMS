function paginateBankSoal(page = 1) {
    const container = document.getElementById('container');
    if (!container) return;

    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const isSchoolMode = !!schoolId;

    fetchBankSoal(schoolName, schoolId);

    function fetchBankSoal() {
        $.ajax({
            url: schoolId
                ? `/lms/school-subscription/${schoolName}/${schoolId}/question-bank-management/paginate`
                : `/lms/question-bank-management/paginate`,
            method: 'GET',
            data: {
                page: page
            },
            success: function (response) {
                $('#tbody-bank-soal-list').empty();
                $('.pagination-container-bank-soal-list').empty();

                const groups = response.data;

                if (schoolId) {
                    const schoolDetailCard = document.getElementById('school-detail-card');
                    const schoolIdentity = response.schoolIdentity;

                    schoolDetailCard.innerHTML = `
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

                            <!-- KIRI : ICON + NAMA SEKOLAH -->
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#EEF6FF] flex items-center justify-center text-[#0071BC] text-2xl shadow-sm">
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-gray-800 leading-tight">
                                        ${schoolIdentity.nama_sekolah}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Detail langganan LMS sekolah
                                    </p>
                                </div>
                            </div>

                            <!-- KANAN : INFO SEKOLAH -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full lg:w-auto">

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NPSN</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.npsn}</p>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NIK Kepala Sekolah</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.user_account?.school_staff_profile?.nik}</p>
                                </div>

                                <div class="bg-[#EEF6FF] rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-[#0071BC] mb-1">Total Pengguna</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${response.countUsers}</p>
                                </div>

                            </div>
                        </div>
                    `;
                }

                if (groups && Object.keys(groups).length > 0) {
                    $.each(groups, function (index, questions) {
                        const first = questions[0];
                        
                        const formatDate = (dateString) => {
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                            const date = new Date(dateString);
                            const day = date.getDate();
                            const monthName = months[date.getMonth()];
                            const year = date.getFullYear();

                            return `${day}-${monthName}-${year}`;
                        };

                        const createdAt = first.created_at ? `${formatDate(first.created_at)}` : 'Tanggal tidak tersedia';
                        const updatedAt = first.updated_at ? `${formatDate(first.updated_at)}` : 'Tanggal tidak tersedia';

                        let lmsReviewQuestion = '';

                        if (schoolId) {
                            lmsReviewQuestion = response.lmsReviewQuestionBySchool.replace(':source', first.question_source).replace(':questionType', first.tipe_soal)
                                .replace(':subBabId', first.sub_bab_id).replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                        } else {
                            lmsReviewQuestion = response.lmsReviewQuestion.replace(':source', first.question_source).replace(':questionType', first.tipe_soal).replace(':subBabId', first.sub_bab_id);
                        }

                        const isGlobalActive = first.status_bank_soal === 'Publish';
                        const hasSchoolOverride = first.school_question_bank?.length > 0;

                        // EFFECTIVE STATUS (dipakai / tidak)
                        const isChecked = isSchoolMode
                            ? (
                                hasSchoolOverride
                                    ? !!first.school_question_bank[0].is_active
                                    : isGlobalActive
                            )
                            : isGlobalActive; // ADMIN MODE → PURE GLOBAL

                        toggleActivateQuestionBank = `
                            <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                class="hidden peer toggle-activate-bank-soal"
                                data-sub-bab-id="${first.sub_bab_id}"
                                data-question-id="${first.id}"
                                data-source="${first.question_source}"
                                data-question-type="${first.tipe_soal}"
                                data-global-active="${isGlobalActive ? 1 : 0}"
                                ${isChecked ? 'checked' : ''}
                            />
                                <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors"></div>
                                <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform peer-checked:translate-x-5"></div>
                            </label>
                        `;

                        $('#tbody-bank-soal-list').append(`
                            <tr>
                                <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1 }</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.kurikulum?.nama_kurikulum}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.kelas?.kelas}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.mapel?.mata_pelajaran}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.bab?.nama_bab}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.sub_bab?.sub_bab}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.tipe_soal}</td>
                                <td class="border text-center border-gray-300">
                                    ${toggleActivateQuestionBank}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    ${first.question_source === 'school' ? first.school_partner?.nama_sekolah : 'belajarcerdas.id'}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <div class="dropdown dropdown-left">
                                        <div tabindex="0" role="button">
                                            <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                        </div>
                                        <ul tabindex="0"
                                            class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                            <li class="text-md">
                                                <a href="${lmsReviewQuestion}" class="btn-review-content">
                                                    <i class="fa-solid fa-eye text-[#0071BC]"></i>
                                                    Review Question
                                                </a>
                                            </li>
                                            <li onclick="historyQuestion(this)"
                                                data-nama_lengkap="${first.user_account?.office_profile?.nama_lengkap || first.user_account?.school_staff_profile?.nama_lengkap}"
                                                data-status="${first.user_account?.role ?? '-'}" 
                                                data-created_at="${createdAt}" 
                                                data-updated_at="${updatedAt}" 
                                                data-global_status="${first.status_bank_soal}" 
                                                data-school_status="${first.school_question_bank?.[0]?.is_active ? 'true' : 'false'}"
                                                data-has-school-override="${first.school_question_bank?.length ? 'true' : 'false'}"
                                                data-school_name="${first.school_partner?.nama_sekolah ?? ''}" 
                                                data-is_default="${first.school_partner_id ? 'false' : 'true'}" 
                                                class="cursor-pointer">
                                                <span>
                                                    <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                                                    History Question
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });

                    $('.pagination-container-bank-soal-list').html(response.links);
                    bindPaginationLinks();
                    $('.thead-table-bank-soal-list').show(); // Tampilkan tabel thead
                    $('#empty-message-bank-soal-list').hide(); // sembunyikan pesan kosong
                } else {
                    $('#tbody-bank-soal-list').empty(); // Clear existing rows
                    $('.thead-table-bank-soal-list').hide(); // Tampilkan tabel thead
                    $('#empty-message-bank-soal-list').show();
                }

                if (schoolId) {
                    $('#school-detail-card').show();
                }

            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
}

$(document).ready(function () {
    paginateBankSoal();
});

function bindPaginationLinks() {
    $('.pagination-container-bank-soal-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateBankSoal(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

// open modal history question
function historyQuestion(element) {
    const container = document.getElementById('container');
    if (!container) return;

    const schoolId = container.dataset.schoolId;

    const namaLengkap = element.dataset.nama_lengkap;
    const status = element.dataset.status;
    const createdAt = element.dataset.created_at;
    const updatedAt = element.dataset.updated_at;

    const globalStatus = element.dataset.global_status === 'Publish';
    const hasSchoolOverride = element.dataset.hasSchoolOverride === 'true';
    const schoolStatusRaw = element.dataset.school_status === 'true';

    const schoolName = element.dataset.school_name;
    const isDefault = element.dataset.is_default === "true";

    document.getElementById('text-nama_lengkap').innerText = namaLengkap;
    document.getElementById('text-status').innerText = status;
    document.getElementById('text-created_at').innerText = 'Tanggal dibuat: ' + `${createdAt}`;
    document.getElementById('text-updated_at').innerText = 'Terakhir diperbarui: ' + `${updatedAt}`;

    const publisherEl = document.getElementById('text-publisher');

    if (isDefault) {
        publisherEl.innerHTML = '<i class="fa-solid fa-building-columns"></i> belajarcerdas.id';
        publisherEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-yellow-100 text-yellow-700';
    } else {
        publisherEl.innerHTML = `<i class="fa-solid fa-school-flag"></i> ${schoolName}`;
        publisherEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700';
    }

    // BADGE GLOBAL
    const badgeGlobal = document.getElementById('badge-global');
    if (globalStatus) {
        badgeGlobal.innerText = 'AKTIF';
        badgeGlobal.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700';
    } else {
        badgeGlobal.innerText = 'NONAKTIF';
        badgeGlobal.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700';
    }

    if (schoolId) {
        // BADGE SCHOOL
        document.getElementById('text-badge-school').classList.replace('hidden', 'block');
        const badgeSchool = document.getElementById('badge-school');
        if (!hasSchoolOverride) {
            badgeSchool.innerText = '-';
            badgeSchool.className = '';
        } else if (schoolStatusRaw) {
            badgeSchool.innerText = 'AKTIF';
            badgeSchool.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700';
        } else {
            badgeSchool.innerText = 'NONAKTIF';
            badgeSchool.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-gray-200 text-gray-600';
        }
    
        // INFO MESSAGE
        const infoEl = document.getElementById('text-info');
        if (!globalStatus) {
            infoEl.innerHTML =
                '<i class="fa-solid fa-triangle-exclamation text-red-500"></i> Bank soal ini dinonaktifkan oleh platform dan tidak dapat digunakan oleh sekolah.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-red-50 text-red-700';
    
        } else if (!hasSchoolOverride) {
            infoEl.innerHTML =
                '<i class="fa-solid fa-circle-check text-green-500"></i> Bank soal mengikuti status global dan dapat digunakan.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-green-50 text-green-700';
    
        } else if (schoolStatusRaw) {
            infoEl.innerHTML =
                '<i class="fa-solid fa-circle-check text-green-500"></i> Bank soal aktif dan dapat digunakan oleh guru dan siswa.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-green-50 text-green-700';
    
        } else {
            infoEl.innerHTML =
                '<i class="fa-solid fa-triangle-exclamation text-yellow-500"></i> Bank soal ini dinonaktifkan oleh sekolah.';
            infoEl.className = 'mt-5 text-sm px-4 py-3 rounded-lg bg-yellow-50 text-yellow-700';
        }
    }

    document.getElementById('my_modal_2').showModal();
}

// function activate bank soal
$(document).on('change', '.toggle-activate-bank-soal', function () {
    const checkbox = $(this);
    const container = document.getElementById('container');
    if (!container) return;

    const schoolId = container.dataset.schoolId;
    const schoolName = container.dataset.schoolName;

    const subBabId = checkbox.data('sub-bab-id');
    const source = checkbox.data('source');
    const questionType = checkbox.data('question-type');
    const isGlobalActive = Number(checkbox.data('global-active')) === 1;

    const action = checkbox.is(':checked') ? 'enable' : 'disable';

    $.ajax({
        url: schoolId
            ? `/lms/school-subscription/question-bank-management/${subBabId}/source/${source}/question-type/${questionType}/${schoolName}/${schoolId}/activate`
            : `/lms/question-bank-management/${subBabId}/source/${source}/question-type/${questionType}/activate`,
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { action },
        success: function () {
            // REFRESH DATA LIST
            paginateQuestionBank(response.current_page);
        },
        error: function () {
            checkbox.prop('checked', !checkbox.is(':checked'));
            alert('Gagal mengubah status');
        }
    });
});

