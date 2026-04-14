function teacherGradeLedger(semester = 1) {
    const container = document.getElementById('container-teacher-grade-ledger-management');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!subjectTeacherId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/management/teacher-class-list/grade-ledger/subject-teacher/${subjectTeacherId}/paginate`,
        method: 'GET',
        data: {
            semester: semester
        },
        success: function (response) {

            const teacherMapel = response.teacherMapel;
            const ledgerGradeInfo = $('#header-ledger-grade-info');
            const { students, subjects, summary, classInfo } = response;

            // HEADER
            ledgerGradeInfo.html(`
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">

                    <!-- TITLE -->
                    <div>
                        <h1 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-book-open"></i>
                            Leger Nilai
                        </h1>

                        <!-- WRAPPER -->
                        <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                            <!-- INFO -->
                            <div class="flex flex-wrap items-center gap-2 text-sm text-blue-100">
                                <span>Leger Nilai</span>

                                <i class="fa-solid fa-circle text-[5px] hidden sm:inline"></i>
                                <span>${teacherMapel.school_class?.class_name}</span>

                                <i class="fa-solid fa-circle text-[5px] hidden sm:inline"></i>
                                <span>${teacherMapel.school_class?.tahun_ajaran ?? '-'}</span>
                            </div>

                            <!-- FILTER -->
                            <div class="flex justify-end">
                                <select id="filter-semester" 
                                    class="bg-white border text-gray-700 text-xs rounded-md px-6.5 h-8 outline-none cursor-pointer w-auto">
                                    <option value="1" ${semester == 1 ? 'selected' : ''}>Semester 1</option>
                                    <option value="2" ${semester == 2 ? 'selected' : ''}>Semester 2</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- ACTION -->
                    <div class="flex flex-col xl:flex-row gap-3 items-end lg:items-center">

                        <button id="btn-export-grade-ledger" class="bg-white text-[#4189E0] font-bold px-4 py-2 rounded-lg text-sm hover:bg-gray-100 cursor-pointer">
                            <i class="fa-solid fa-download"></i>
                            Export Excel
                        </button>

                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-xs text-gray-500">Total Siswa</p>
                        <h2 class="text-xl font-bold text-blue-600">
                            ${summary.total_students}
                        </h2>
                    </div>

                </div>
            `);

            ledgerGradeInfo.show();

            // HEADER TABLE
            let thead = `
                <tr>
                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                        Nama Siswa
                    </th>
            `;

            subjects.forEach(sub => {
                thead += `<th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">${sub}</th>`;
            });

            $('.thead-table-teacher-grade-ledger-management').html(thead);

            // BODY
            $('#tbody-teacher-grade-ledger-management').empty();

            students.forEach(student => {

                let row = `
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-3 py-2">
                            ${student.name}
                        </td>
                `;

                subjects.forEach(sub => {
                    const score = student.subjects[sub] ?? 0;

                    row += `
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            ${score}
                        </td>
                    `;
                });

                $('#tbody-teacher-grade-ledger-management').append(row);
            });
        }
    });
}

$(document).ready(function () {
    teacherGradeLedger();
});

$(document).on('change', '#filter-semester', function () {
    teacherGradeLedger($(this).val());
});

$(document).on('click', '#btn-export-grade-ledger', function () {
    const role = $('#container-teacher-grade-ledger-management').data('role');
    const schoolName = $('#container-teacher-grade-ledger-management').data('schoolName');
    const schoolId = $('#container-teacher-grade-ledger-management').data('schoolId');
    const subjectTeacherId = $('#container-teacher-grade-ledger-management').data('subjectTeacherId');

    const semester = $('#filter-semester').val();

    window.open(`/lms/${role}/${schoolName}/${schoolId}/management/teacher-class-list/grade-ledger/subject-teacher/${subjectTeacherId}/semester/${semester}/export`, '_blank');
});