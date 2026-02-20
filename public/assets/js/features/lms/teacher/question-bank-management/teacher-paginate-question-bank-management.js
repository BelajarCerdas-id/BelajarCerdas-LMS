function paginateBankSoal(search_class = null, search_year = null, page = 1) {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const isSchoolMode = !!schoolId;

    fetchBankSoal(schoolName, schoolId);

    function fetchBankSoal() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-management/paginate`,
            method: 'GET',
            data: {
                search_class: search_class,
                search_year: search_year,
                page: page,
            },
            success: function (response) {
                $('#tbody-bank-soal-list').empty();
                $('.pagination-container-bank-soal-list').empty();

                // Dropdown Tahun Ajaran
                const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran');
                containerDropdownTahunAjaran.innerHTML = `
                    <div class="flex flex-col w-full mb-2">
                        <label class="text-sm font-medium text-gray-600 mb-1">Pilih Tahun Ajaran</label>
                        <select id="dropdown-filter-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-6 cursor-pointer outline-none">
                            <option value="" class="hidden">Pilih Tahun Ajaran</option>
                            ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                        </select>
                    </div>
                `;

                // Dropdown Kelas
                const containerDropdownClass = document.getElementById('container-dropdown-class');
                containerDropdownClass.innerHTML = `
                    <div class="flex flex-col w-full mb-2">
                        <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                        <select id="dropdown-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer outline-none">
                            <option value="" class="hidden">Filter Kelas</option>
                            ${response.className.map(item => `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`).join('')}
                        </select>
                    </div>
                `;

                const groups = response.data;
                
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

                        lmsReviewQuestion = response.lmsReviewQuestion.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                            .replace(':source', first.question_source).replace(':questionType', first.tipe_soal).replace(':subBabId', first.sub_bab_id);

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
                                <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
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

$(document).on('change', '#dropdown-filter-class', function () {
    paginateBankSoal($(this).val(), $('#dropdown-filter-tahun-ajaran').val(), 1);
});

$(document).on('change', '#dropdown-filter-tahun-ajaran', function () {
    paginateBankSoal(null, $(this).val(), 1);
});

function bindPaginationLinks() {
    $('.pagination-container-bank-soal-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_class = $('#dropdown-filter-class').val();
        const search_year = $('#dropdown-filter-tahun-ajaran').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateBankSoal(search_class, search_year, page); // Ambil data yang difilter untuk halaman yang ditentukan
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

