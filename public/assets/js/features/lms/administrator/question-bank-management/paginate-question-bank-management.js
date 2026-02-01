function paginateBankSoal(page = 1) {
    const container = document.getElementById('container');
    if (!container) return;

    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

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
                    $.each(groups, function (_, questions) {
                        const first = questions[0];
                        
                        const formatDate = (dateString) => {
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                            const date = new Date(dateString);
                            const day = date.getDate();
                            const monthName = months[date.getMonth()];
                            const year = date.getFullYear();

                            return `${day}-${monthName}-${year}`;
                        };

                        const updatedAt = first.updated_at ? `${formatDate(first.updated_at)}` : 'Tanggal tidak tersedia';

                        let lmsReviewQuestion = '';

                        if (schoolId) {
                            lmsReviewQuestion = response.lmsReviewQuestionBySchool.replace(':source', first.question_source).replace(':subBabId', first.sub_bab_id)
                                .replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                        } else {
                            lmsReviewQuestion = response.lmsReviewQuestion.replace(':source', first.question_source).replace(':subBabId', first.sub_bab_id);
                        }

                        $('#tbody-bank-soal-list').append(`
                            <tr>
                                <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + 1 }</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.kurikulum?.nama_kurikulum}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.kelas?.kelas}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.mapel?.mata_pelajaran}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.bab?.nama_bab}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${first.sub_bab?.sub_bab}</td>
                                <td class="border text-center border-gray-300">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-activate-bank-soal"
                                            data-sub-bab-id="${first.sub_bab_id}" data-source="${first.question_source}"
                                            ${first.status_bank_soal === 'Publish' ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                            <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
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
                                                data-status="${first.user_account?.role ?? '-'}" data-updated_at="${updatedAt}" data-school_name="${first.school_partner?.nama_sekolah ?? ''}"
                                                data-is_default="${first.school_partner_id === null}" class="cursor-pointer">
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

    const namaLengkap = element.dataset.nama_lengkap;
    const status = element.dataset.status;
    const updatedAt = element.dataset.updated_at;
    const schoolName = element.dataset.school_name;
    const isDefault = element.dataset.is_default === "true";

    document.getElementById('text-nama_lengkap').innerText = namaLengkap;
    document.getElementById('text-status').innerText = status;
    document.getElementById('text-updated_at').innerText = 'Terakhir diperbarui: ' + `${updatedAt}`;

    const publisherEl = document.getElementById('text-publisher');

    if (isDefault) {
        publisherEl.innerHTML = '<i class="fa-solid fa-building-columns"></i> belajarcerdas.id';
        publisherEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-yellow-100 text-yellow-700';
    } else {
        publisherEl.innerHTML = `<i class="fa-solid fa-school-flag"></i> ${schoolName}`;
        publisherEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700';
    }

    document.getElementById('my_modal_2').showModal();
}

// function activate mapel
$(document).ready(function () {
    $(document).on('change', '.toggle-activate-bank-soal', function () {
        let subBabId = $(this).data('sub-bab-id'); // Ambil ID sub bab dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 'Publish' : 'Unpublish'; // Jika toggle ON maka publish, kalau OFF maka unpublish
        let source = $(this).data('source'); // Ambil source dari atribut data-source di checkbox

        $.ajax({
            url: `/lms/school-subscription/question-bank-management/${subBabId}/source/${source}/activate`, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status_bank_soal: status // Kirim status baru (publish / unpublish)
            },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                paginateBankSoal();
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});
