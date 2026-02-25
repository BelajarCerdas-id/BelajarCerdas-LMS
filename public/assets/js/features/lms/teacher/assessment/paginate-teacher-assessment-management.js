function paginateTeacherAssessmentManagement(search_year = null, search_class = null, search_assessment_type = null, page = 1) {
    const container = document.getElementById('container-teacher-assessment-management-list');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/paginate`,
        method: 'GET',
        data: {
            search_year,
            search_class,
            search_assessment_type,
            page: page,
        },
        success: function (response) {
            enableFlatpickrCreate(); // inisialisasi flatpickr

            $('#tbody-teacher-assessment-management-list').empty();
            $('.pagination-container-teacher-assessment-management-list').empty();

            // Dropdown Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-assessment-management-tahun-ajaran');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tahun Ajaran</label>
                    <select id="dropdown-assessment-management-filter-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-6 cursor-pointer outline-none">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-assessment-management-class');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-assessment-management-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer outline-none">
                        <option value="" class="hidden">Filter Kelas</option>
                        ${response.className.map(item => `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Assessment Type
            const containerAssessmentType = document.getElementById('container-dropdown-assessment-type');
            containerAssessmentType.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tipe Asesmen</label>
                    <select id="dropdown-assessment-type" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer outline-none">
                        <option value="" class="hidden">Filter Tipe Asesmen</option>
                        ${response.schoolAssessmentType.map(item => `<option value="${item.id}" ${search_assessment_type == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </div>
            `;

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {
                    const formatDate = (dateString) => {
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day}-${monthName}-${year}`;
                    };

                    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    });

                    // Format tanggal mulai dan akhir
                    const startDate = item.start_date ? `${formatDate(item.start_date)}, ${timeFormatter.format(new Date(item.start_date))}` : 'Tanggal tidak tersedia';
                    const endDate = item.end_date ? `${formatDate(item.end_date)}, ${timeFormatter.format(new Date(item.end_date))}` : 'Tanggal tidak tersedia';
                    
                    const assessmentManagementEdit = response.assessmentManagementEdit.replace(':role', role).replace(':schoolId', schoolId).replace(':schoolName', schoolName)
                        .replace(':assessmentId', item.id)

                    $('#tbody-teacher-assessment-management-list').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.class_name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.tahun_ajaran ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.mapel?.mata_pelajaran ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_assessment_type?.name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.title ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.semester ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${startDate} - ${endDate}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                    class="hidden peer toggle-activate-assessment"
                                    data-assessment-id="${item.id}"
                                    data-current-page="${response.current_page}"
                                    ${item.status === 'published' ? 'checked' : ''}
                                />
                                    <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors"></div>
                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform peer-checked:translate-x-5"></div>
                                </label>
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <div class="dropdown dropdown-left">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                        <li class="text-md">
                                            <a href="${assessmentManagementEdit}">
                                                <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                                Edit Assessment
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-teacher-assessment-management-list').html(response.links);
                bindPaginationLinks();
                $('#empty-message-teacher-assessment-management-list').hide(); // sembunyikan pesan kosong
                $('.thead-table-teacher-assessment-management-list').show(); // Tampilkan tabel thead

            } else {
                $('#tbody-teacher-assessment-management-list').empty(); // Clear existing rows
                $('.thead-table-teacher-assessment-management-list').hide(); // Tampilkan tabel thead
                $('#empty-message-teacher-assessment-management-list').show();
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateTeacherAssessmentManagement();
});

$(document).on('change', '#dropdown-assessment-management-filter-tahun-ajaran', function () {
    paginateTeacherAssessmentManagement($(this).val(), null, $('#dropdown-assessment-type').val(), 1); // null supaya auto pilih kelas paling rendah
});

$(document).on('change', '#dropdown-assessment-management-filter-class', function () {
    paginateTeacherAssessmentManagement($('#dropdown-assessment-management-filter-tahun-ajaran').val(), $(this).val(), $('#dropdown-assessment-type').val(), 1);
});

$(document).on('change', '#dropdown-assessment-type', function () {
    paginateTeacherAssessmentManagement($('#dropdown-assessment-management-filter-tahun-ajaran').val(), null, $(this).val(), 1);
});

function bindPaginationLinks() {
    $('.pagination-container-teacher-assessment-management-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_year = $('#dropdown-assessment-management-filter-tahun-ajaran').val();
        const search_class = $('#dropdown-assessment-management-filter-class').val();
        const search_assessment_type = $('#dropdown-assessment-type').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateTeacherAssessmentManagement(search_year, search_class, search_assessment_type, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

// activate assessment
$(document).on('change', '.toggle-activate-assessment', function () {
    const checkbox = $(this);
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;

    let assessmentId = $(this).data('assessment-id'); // Ambil content id dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'published' : 'draft'; // Jika toggle ON maka publish, kalau OFF maka draft
    let currentPage = $(this).data('current-page'); // Ambil current page dari atribut data-current-page di checkbox

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/${assessmentId}/activate`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status: status
        },
        success: function (response) {
            paginateTeacherAssessmentManagement($('#dropdown-assessment-management-filter-tahun-ajaran').val(), $('#dropdown-assessment-management-filter-class').val(), currentPage);
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked')); // ← GUNAKAN INI
        }
    });
});