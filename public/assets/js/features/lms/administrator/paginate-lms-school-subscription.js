function paginateLmsSchoolSubscription(search_school, page = 1) {
    $.ajax({
        url: '/lms/school-subscription/paginate',
        method: 'GET',
        data: {
            search_school: search_school,
            page: page
        },
        success: function (response) {
            $('#tbody-school-partner-list').empty();
            $('.pagination-container-school-partner-list').empty();

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {

                    const formatDate = (dateString) => {
                        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day}-${monthName}-${year}`;
                    };

                    // Format tanggal mulai dan akhir
                    const startDate = item.school_lms_subscription?.start_date ? formatDate(item.school_lms_subscription?.start_date) : 'Tanggal tidak tersedia';
                    const endDate = item.school_lms_subscription?.end_date ? formatDate(item.school_lms_subscription?.end_date) : 'Tanggal tidak tersedia';

                    const lmsAcademicManagement = response.lmsAcademicManagement.replace(':schoolName', item.nama_sekolah).replace(':schoolId', item.id);

                    $('#tbody-school-partner-list').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.nama_sekolah}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.npsn}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.school_staff_profile?.nama_lengkap}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.school_staff_profile?.nik}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${startDate} - ${endDate}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center w-[15%]">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="hidden peer toggle-activate-subscription" data-subscription-id="${item.school_lms_subscription?.id}" 
                                        data-current-page="${response.current_page}"
                                        ${item.school_lms_subscription?.subscription_status === 'active' ? 'checked' : ''} />
                                    <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out"></div>
                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5"></div>
                                </label>
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <a href="${lmsAcademicManagement}" class="flex items-center justify-center gap-2 text-xs text-[#4189E0] font-bold">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    `);
                })
                $('.pagination-container-school-partner-list').html(response.links);
                bindPaginationLinks();
                $('#empty-message-school-partner-list').hide(); // sembunyikan pesan kosong
                $('.thead-table-school-partner-list').show(); // Tampilkan tabel thead
            } else {
                $('#tbody-school-partner-list').empty(); // Clear existing rows
                $('.thead-table-school-partner-list').hide(); // Tampilkan tabel thead
                $('#empty-message-school-partner-list').show();
            }
        }
    });
}

$(document).ready(function () {
    paginateLmsSchoolSubscription();
})

// Fungsi untuk memfilter data berdasarkan search_school (pakai on input karena ketika data yang user cari akan munul tanpa di enter atau apapun by click)
$('#search_school').on('input', function () {
    const search_school = $(this).val();
    paginateLmsSchoolSubscription(search_school); // Call the function to fetch data based on search_school
});


function bindPaginationLinks() {
    $('.pagination-container-school-partner-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        const search_school = $('#search_school').val();
        paginateLmsSchoolSubscription(search_school, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).on('change', '.toggle-activate-subscription', function () {
    let subscriptionId = $(this).data('subscription-id'); // Ambil subscription id dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive
    let currentPage = $(this).data('current-page'); // Ambil current page dari atribut data-current-page di checkbox

    $.ajax({
        url: `/lms/school-subscription/${subscriptionId}/activate`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            subscription_status: status // Kirim status baru (aktif / non aktif)
        },
        success: function (response) {
            // inisialisasi update data terbaru setelah berhasil insert data
            const search_school = $('#search_school').val();
            const page = currentPage;
            paginateLmsSchoolSubscription(search_school, page);
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked')); // ← GUNAKAN INI
        }
    });
});