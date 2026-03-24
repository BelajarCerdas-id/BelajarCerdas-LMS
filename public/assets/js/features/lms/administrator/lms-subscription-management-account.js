function managementAccountUsersSchoolSubscription(search_user, page = 1) {
    const container = document.getElementById('container-management-staff-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const role = container.dataset.role;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!role) return;

    fetchAccountUsers(schoolName, schoolId, role);

    function fetchAccountUsers() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/management-role-account/${role}/management-accounts/paginate`,
            method: 'GET',
            data: {
                search_user: search_user,
                page: page,
            },
            success: function (response) {
                $('#tbody-management-staff-list').empty();
                $('.pagination-container-management-staff-list').empty();

                if (response.data.length > 0) {
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

                    $.each(response.data, function (index, item) {
                        $('#tbody-management-staff-list').append(`
                            <tr>
                                <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.student_profile?.nama_lengkap || item.school_staff_profile?.nama_lengkap}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.role}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.student_profile?.personal_email || item.school_staff_profile?.personal_email}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.email}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.no_hp}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.status_akun}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-activate-account"
                                            data-id="${item.id}" data-current-page="${response.current_page}" data-school-id="${schoolId}"
                                            ${item.status_akun == 'aktif' ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                        <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                            </tr>
                        `);
                    });

                    $('.pagination-container-management-staff-list').html(response.links);
                    bindPaginationLinks();
                    $('#empty-message-management-staff-list').hide(); // sembunyikan pesan kosong
                    $('.thead-table-management-staff-list').show(); // Tampilkan tabel thead
                    $('#school-detail-card').show();
                } else {
                    $('#tbody-management-staff-list').empty(); // Clear existing rows
                    $('.thead-table-management-staff-list').hide(); // Tampilkan tabel thead
                    $('#empty-message-management-staff-list').show();
                    $('#school-detail-card').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
}

$(document).ready(function () {
    managementAccountUsersSchoolSubscription();
});

// Fungsi untuk memfilter data berdasarkan search_user (pakai on input karena ketika data yang user cari akan munul tanpa di enter atau apapun by click)
$('#search_user').on('input', function () {
    const search_user = $(this).val();
    managementAccountUsersSchoolSubscription(search_user); // Call the function to fetch data based on search_user
});

function bindPaginationLinks() {
    $('.pagination-container-management-staff-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        const search_user = $('#search_user').val();
        managementAccountUsersSchoolSubscription(search_user, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

// activate account
$(document).on('change', '.toggle-activate-account', function () {
    let id = $(this).data('id'); // Ambil ID account dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'aktif' : 'non-aktif'; // Jika toggle ON maka aktif, kalau OFF maka non aktif
    let page = $(this).data('current-page'); // Ambil nomor halaman dari atribut data-page di checkbox
    let schoolId = $(this).data('school-id'); // Ambil ID sekolah dari atribut data-school-id di checkbox

    $.ajax({
        url: `/lms/school-subscription/${schoolId}/management-account/${id}/activate-account`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status_akun: status, // Kirim status baru aktif / non aktif
        },
        success: function (response) {
            const search_user = $('#search_user').val();
            managementAccountUsersSchoolSubscription(search_user, page);
        },
        error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.kepsekAccountIsActive) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Aksi tidak diizinkan',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });
            } else if (xhr.status === 422 && xhr.responseJSON?.cannotDeactivateLastKepsek) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Aksi tidak diizinkan',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti'
                });

                const search_user = $('#search_user').val();
                managementAccountUsersSchoolSubscription(search_user, page);
            } else {
                alert('Gagal mengubah status.');
            }
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});