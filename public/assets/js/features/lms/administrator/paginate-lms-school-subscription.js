function paginateLmsSchoolSubscription(search_school, page = 1) {
    const container = document.getElementById('container-school-partner-list');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

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
                    const lmsAcademicManagement = response.lmsAcademicManagement.replace(':role', role).replace(':schoolName', item.nama_sekolah).replace(':schoolId', item.id);

                    $('#tbody-school-partner-list').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.nama_sekolah}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.npsn}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.school_staff_profile?.nama_lengkap}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.school_staff_profile?.nik}</td>
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