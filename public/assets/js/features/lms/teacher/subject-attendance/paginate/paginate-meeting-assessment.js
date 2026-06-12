function paginateAssessmentList() {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const meetingNumber = container.dataset.meetingNumber;
    const semester = container.dataset.semester;

    const filter = document.getElementById('assessmentTypeFilter');

    if (!role || !schoolName || !schoolId || !subjectTeacherId || !meetingNumber || !semester) return;

    fetchData();

    // FILTER CHANGE
    filter.addEventListener('change', function () {

        fetchData(this.value);
    });

    function fetchData(assessmentTypeId = '') {

        $.ajax({

            url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/${meetingNumber}/semester/${semester}/meeting-management/assessment/paginate`,

            method: 'GET',

            data: {
                assessment_type_id: assessmentTypeId
            },

            success: function (response) {

                const assessmentList = $('#grid-assessment-list');

                assessmentList.empty();

                // RENDER FILTER
                renderAssessmentTypes(response.assessment_types);

                // LOOP DATA
                if (response.data.length > 0) {
                    $.each(response.data, function (index, item) {
    
                        const assessmentType = item.school_assessment_type?.name ?? 'Asesmen';
    
                        const title = item.title ?? 'Judul asesmen tidak tersedia';
    
                        const startDate = formatDate(item.start_date);
    
                        const endDate = formatDate(item.end_date);

                        const assessmentGradingStudentList = response.assessmentGradingStudentList.replace(':role', role).replace(':schoolName', schoolName)
                            .replace(':schoolId', schoolId).replace(':assessmentId', item.id).replace(':mode', item.assessment_category);
    
                        const card = `
                            <a href="${assessmentGradingStudentList}">
                                <div class="cursor-pointer flex items-center gap-4 p-4 rounded-2xl border border-gray-300 bg-slate-50 hover:bg-white hover:border-amber-300 hover:shadow-md transition-all shrink-0 group">

                                    <!-- ICON -->
                                    <div class="w-12 h-12 bg-white group-hover:bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 border border-slate-100 shadow-sm shrink-0 transition-colors">

                                        <i class="fas fa-clipboard-check text-lg"></i>

                                    </div>

                                    <!-- CONTENT -->
                                    <div class="flex-1 min-w-0">

                                        <div class="flex items-center gap-2 flex-wrap mb-1">

                                            <span class="bg-amber-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">
                                                ${assessmentType}
                                            </span>

                                        </div>

                                        <h4 class="font-bold text-slate-700 text-sm leading-relaxed wrap-break-word group-hover:text-amber-500 transition-colors">
                                            ${title}
                                        </h4>

                                        <div class="flex flex-wrap items-center gap-3 mt-2 text-[11px] text-slate-400">

                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-calendar-alt text-[#4189E0]"></i>
                                                Mulai: ${startDate}
                                            </span>

                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-clock text-[#4189E0]"></i>
                                                Deadline: ${endDate}
                                            </span>

                                        </div>

                                    </div>

                                    <div class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-amber-50 hover:text-amber-500 hover:border-amber-200 transition-all shadow-sm shrink-0">
                                        <i class="fas fa-chevron-right text-sm"></i>
                                    </div>

                                </div>
                            </a>
                        `;
    
                        assessmentList.append(card);
                    });
                        $('#empty-message-assessment-list').hide();
                } else {
                    $('#empty-message-assessment-list').show();
                }
            },

            error: function (xhr, status, error) {

                console.error('Terjadi kesalahan:', status, error);
            }
        });
    }

    // FORMAT DATE
    function formatDate(dateString) {

        if (!dateString) return '-';

        const date = new Date(dateString);

        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    }

    // FILTER OPTION
    function renderAssessmentTypes(types) {

        if (filter.dataset.loaded === 'true') return;

        $.each(types, function (index, item) {

            filter.innerHTML += `
                <option value="${item.id}">
                    ${item.name}
                </option>
            `;
        });

        filter.dataset.loaded = 'true';
    }
}

$(document).ready(function () {

    paginateAssessmentList();
});