let kpiLoaded = false;

function paginateStudentList(search_student = '', filter_status = '', page = 1, loadKpi = false) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolId = container.dataset.schoolId;
    const contractId = container.dataset.contractId;
    const termId = container.dataset.termId;
    
    if (!container || !role || !schoolId || !contractId || !termId) return;

    $.ajax({
        url: `/lms/${role}/manage-contract/schools/${schoolId}/contract/${contractId}/payment-detail/student-list/${termId}/paginate`,
        method: 'GET',
        data: {
            search_student: search_student,
            filter_status: filter_status,
            page: page
        },

        beforeSend: function () {

            $('#student-table-skeleton').removeClass('hidden');
            $('#student-table-content').addClass('hidden');
            
            if (loadKpi) {

                $('#kpi-skeleton').removeClass('hidden');
                $('#kpi-content').addClass('hidden');

            }
        },

        success: function (response) {

            $('#student-table-skeleton').addClass('hidden');
            $('#student-table-content').removeClass('hidden');

            if (loadKpi) {

                $('#kpi-skeleton').addClass('hidden');
                $('#kpi-content').removeClass('hidden');

                renderContractKpi(response.kpi);
            }

            $('.pagination-container-student-list').html(response.links);
            bindPaginationLinks();
            renderTermList(response.studentList, response.pagination);
        },

        error: function (xhr) {

            $('#kpi-skeleton').addClass('hidden');
            $('#student-table-skeleton').addClass('hidden');

            console.error(xhr.responseText);
        }
    });
}

// KPI
function renderContractKpi(kpi) {

    $('#total-students').text(kpi.total_students);

    $('#active-students').text(kpi.active_students);

    $('#inactive-students').text(kpi.inactive_students);

    $('#activation-rate').text(`${kpi.activation_rate}%`);

    $('#activation-progress')
        .css(
            'width',
            `${kpi.activation_rate}%`
        );

    $('#active-badge')
        .text(
            `${kpi.active_students} Active`
        );

    $('#inactive-badge')
        .text(
            `${kpi.inactive_students} Inactive`
        );
}

// TERM LIST
function renderTermList(studentList, pagination) {

    $('#student-empty-state').addClass('hidden');
    $('#student-table-body').empty();

    if (!studentList.length) {

        $('#student-empty-state').removeClass('hidden');

        return;
    }

    studentList.forEach((student, index) => {

        const profile = student.student_account?.student_profile;

        const name = profile?.nama_lengkap ?? '-';

        const row = `
            <tr class="hover">

                <td>
                    ${((pagination.current_page - 1) * pagination.per_page) + index + 1}
                </td>

                <td>

                    <div class="flex items-center gap-3">

                        <div>

                            <div class="font-semibold text-slate-800">
                                ${name}
                            </div>

                            <div class="text-xs text-slate-500">
                                ${student.student_account?.email ?? '-'}
                            </div>

                        </div>

                    </div>

                </td>

                <td>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="hidden peer toggle-activate-contract-student" data-term-student-id="${student.id}" data-current-page="${pagination.current_page}"
                            ${student.status === 'active' ? 'checked' : ''} />
                        <div class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out"></div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5"></div>
                    </label>
                </td>
            </tr>
        `;

        $('#student-table-body').append(row);
    });
}

$(document).ready(function () {
    paginateStudentList('', '', 1, true);
});

// Fungsi untuk memfilter data berdasarkan search_student (pakai on input karena ketika data yang user cari akan munul tanpa di enter atau apapun by click)
$('#search_student').on('input', function () {
    const search_student = $(this).val();
    const filter_status = $('#filter_status').val();
    paginateStudentList(search_student, filter_status, 1, false);
});

// Fungsi untuk memfilter data berdasarkan filter_status
$('#filter_status').on('change', function () {
    const search_student = $('#search_student').val();
    const filter_status = $(this).val();
    paginateStudentList(search_student, filter_status, 1, false);
});


function bindPaginationLinks() {
    $('.pagination-container-student-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_student = $('#search_student').val();
        const filter_status = $('#filter_status').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateStudentList(search_student, filter_status, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).on('change', '.toggle-activate-contract-student', function () {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolId = container.dataset.schoolId;
    const contractId = container.dataset.contractId;
    const termId = container.dataset.termId;

    if (!role || !schoolId || !contractId || !termId) return;
    
    let studentTermId = $(this).data('term-student-id'); // Ambil contract id dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive
    let currentPage = $(this).data('current-page'); // Ambil current page dari atribut data-current-page di checkbox

    $.ajax({
        url: `/lms/${role}/manage-contract/schools/${schoolId}/contract/${contractId}/payment-detail/student-list/${termId}/student/${studentTermId}/activate`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status: status // Kirim status baru (aktif / non aktif)
        },
        success: function (response) {
            // inisialisasi update data terbaru setelah berhasil insert data
            const search_student = $('#search_student').val();
            const filter_status = $('#filter_status').val();
            const page = currentPage;
            paginateStudentList(search_student, filter_status, page, true);
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const message = xhr.responseJSON?.message ?? 'Tidak bisa mengubah data.';

                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Diizinkan',
                    text: message,
                    confirmButtonColor: '#d33'
                });

                const search_student = $('#search_student').val();
                const filter_status = $('#filter_status').val();
                paginateStudentList(search_student, filter_status, 1, false);

            } else {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan sistem.',
                });
            }

            // rollback toggle state
            const checkbox = $(this);
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});