function assessmentActivity() {
    const container = document.getElementById('container-paginate-student-learning');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumId = container.dataset.curriculumId;
    const mapelId = container.dataset.mapelId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumId) return;
    if (!mapelId) return;

    fetchService(role, schoolName, schoolId, curriculumId, mapelId);

    function fetchService() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/paginate`,
            method: 'GET',
            success: function (response) {
                const containerAssessmentActivity = $('#grid-list-student-learning');
                containerAssessmentActivity.empty();

                if (response.data.length > 0) {
                    $.each(response.data, function (index, item) {
                        
                        const previewMateri = response.previewMateri.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumId', curriculumId)
                            .replace(':mapelId', mapelId).replace(':serviceId', item.id);

                        const card = `
                            <div class="w-full flex justify-center">
                                <div
                                    class="relative w-full max-w-96 h-50 rounded-[20px] bg-[#F2811A]
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]
                                    transition-all duration-300 hover:-translate-y-1 overflow-hidden">

                                    <!-- Content -->
                                    <div class="p-4 h-full flex flex-col justify-center items-center text-white gap-3">
                                        <h2 class="text-lg font-semibold leading-tight text-center">
                                            ${item.name}
                                        </h2>

                                        <a href="${previewMateri}">
                                            <button class="bg-[#2E8B57] text-white text-[12px] font-bold px-8 py-2 rounded-lg shadow-md transition hover:scale-105 cursor-pointer">
                                                Lihat
                                            </button>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        `;

                        containerAssessmentActivity.append(card);
                    });

                    $.each(response.assessmentType, function (index, item) {

                        const previewAssessment = response.previewAssessment.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                            .replace(':curriculumId', curriculumId).replace(':mapelId', mapelId).replace(':assessmentTypeId', item.id);

                        const card = `
                            <div class="w-full flex justify-center">
                                <div
                                    class="relative w-full max-w-96 h-50 rounded-[20px] bg-[#F2811A]
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]
                                    transition-all duration-300 hover:-translate-y-1 overflow-hidden">

                                    <!-- Content -->
                                    <div class="p-4 h-full flex flex-col justify-center items-center text-white gap-3">
                                        <h2 class="text-lg font-semibold leading-tight text-center">
                                            ${item.name}
                                        </h2>

                                        <a href="${previewAssessment}">
                                            <button class="bg-[#2E8B57] text-white text-[12px] font-bold px-8 py-2 rounded-lg shadow-md transition hover:scale-105 cursor-pointer">
                                                Lihat
                                            </button>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        `;

                        containerAssessmentActivity.append(card);
                    });

                    $('#empty-message-list-student-learning').hide();
                } else {
                    $('#empty-message-list-student-learning').show();
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
}

$(document).ready(function () {
    assessmentActivity();
});