function teacherAcademicTranscript(selectedClass = null) {
    const container = document.getElementById('container-teacher-academic-transcript-management');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;

    if (!container || !role || !schoolName || !schoolId || !subjectTeacherId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/academic-transcript/classes/subject-teacher/${subjectTeacherId}/paginate`,
        method: 'GET',
        data: {
            class_level: selectedClass
        },

        success: function (response) {

            const ledgerGradeInfo = $('#header-academic-transcript-info');
            const students = response.students || [];
            const mapels = response.mapels || {};
            const summary = response.summary || { total_students: 0 };
            const teacherMapel = response.teacherMapel || {};

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-class-paginate-question-bank-for-release');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-filter-class-paginate-question-bank-for-release" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer
                        outline-none">
                        <option value="" class="hidden">Filter Kelas</option>
                        ${response.className.map(item => `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // HEADER INFO
            ledgerGradeInfo.html(`
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">

                    <div>
                        <h1 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-book-open"></i>
                            Transkrip Nilai
                        </h1>

                        <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2 text-sm text-blue-100">
                                <span>Transkrip Nilai</span>

                                <i class="fa-solid fa-circle text-[5px] hidden sm:inline"></i>
                                <span>${teacherMapel.school_class?.class_name}</span>

                            </div>
                        </div>
                    </div>

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

            const tbody = $('#tbody-teacher-academic-transcript-management');
            const thead = $('.thead-table-teacher-academic-transcript-management');

            // HEADER
            let thead1 = `<tr>
                <th rowspan="3" class="border px-32 py-2 bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white">
                    Siswa
                </th>
            `;

            let thead2 = `<tr>`;
            let thead3 = `<tr>`;

            const sortedMapels = Object.keys(mapels).sort();

            sortedMapels.forEach(mapel => {

                const classes = Object.keys(mapels[mapel]).sort((a, b) => a - b);

                let totalColMapel = 0;

                // hitung total kolom untuk mapel
                classes.forEach(cls => {
                    const years = Object.keys(mapels[mapel][cls]);

                    years.forEach(year => {
                        totalColMapel += Object.keys(mapels[mapel][cls][year]).length;
                    });
                });

                // ROW 1 -> MAPEL
                thead1 += `
                    <th colspan="${totalColMapel}" class="border px-3 py-2 text-center bg-[linear-gradient(to_bottom,#0071BC_65%,#003456_100%)] text-white">
                        ${mapel}
                    </th>
                `;

                // ROW 2 -> KELAS + TAHUN AJARAN
                classes.forEach(cls => {
                    const years = Object.keys(mapels[mapel][cls]).sort();

                    years.forEach(year => {
                        const semesters = Object.keys(mapels[mapel][cls][year]);

                        thead2 += `
                            <th colspan="${semesters.length}" class="border px-3 py-2 text-center bg-[linear-gradient(to_bottom,#0071BC_65%,#003456_100%)] text-white">
                                Kelas ${cls} (${year})
                            </th>
                        `;
                    });
                });

                // ROW 3 → SEMESTER
                classes.forEach(cls => {
                    const years = Object.keys(mapels[mapel][cls]);

                    years.forEach(year => {
                        const semesters = Object.keys(mapels[mapel][cls][year]);

                        semesters.forEach(sem => {
                            thead3 += `
                                <th class="border px-3 py-2 text-center bg-[linear-gradient(to_bottom,#0071BC_65%,#003456_100%)] text-white">
                                    Semester ${sem}
                                </th>
                            `;
                        });
                    });
                });

            });

            thead1 += `</tr>`;
            thead2 += `</tr>`;
            thead3 += `</tr>`;

            thead.html(thead1 + thead2 + thead3);

            // BODY
            tbody.empty();

            students.forEach(student => {

                let row = `<tr class="hover:bg-gray-50">
                    <td class="border border-gray-300 px-3 py-2">${student.name}</td>
                `;

                sortedMapels.forEach(mapel => {
                    const classes = Object.keys(mapels[mapel]).sort((a, b) => a - b);

                    classes.forEach(cls => {
                        const years = Object.keys(mapels[mapel][cls]).sort();

                        years.forEach(year => {
                            const semesters = Object.keys(mapels[mapel][cls][year]).sort();

                            semesters.forEach(sem => {
                                const score = student.mapels?.[mapel]?.[cls]?.[year]?.[sem] ?? '-';

                                row += `
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        ${score}
                                    </td>
                                `;
                            });
                        });
                    });
                });

                row += `</tr>`;
                tbody.append(row);
            });

        }
    });
}

// INIT
$(document).ready(function () {
    teacherAcademicTranscript();
});

// FILTER
$(document).on('change', '#dropdown-filter-class-paginate-question-bank-for-release', function () {
    const selectedClass = $(this).val();

    teacherAcademicTranscript(selectedClass);
});

// EXPORT
$(document).on('click', '#btn-export-grade-ledger', function () {
    const container = $('#container-teacher-academic-transcript-management');

    const role = container.data('role');
    const schoolName = container.data('schoolName');
    const schoolId = container.data('schoolId');
    const subjectTeacherId = container.data('subjectTeacherId');

    window.open(`/lms/${role}/${schoolName}/${schoolId}/academic-transcript/classes/subject-teacher/${subjectTeacherId}/export`);
});