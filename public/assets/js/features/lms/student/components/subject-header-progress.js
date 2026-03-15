function subjectHeaderProgress() {
    const container = document.getElementById('container');
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

    fetchProgress(role, schoolName, schoolId, curriculumId, mapelId);

    function fetchProgress() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/subject-progress/data`,
            method: 'GET',
            success: function (response) {
                if (response.mapel) {
                    const containerSubjectHeader = document.getElementById('container-subject-header');

                    containerSubjectHeader.innerHTML = `
                        <div class="w-full h-32">
                            <div class="relative flex items-center justify-between rounded-xl pr-8 h-full bg-[linear-gradient(to_left,#0071BC_30%,#29ABE2_85%)] text-white shadow-lg">
                                <div class="flex items-center gap-6">
                                    <div class="w-32 h-32 rounded-r-full bg-[#0B5FA5] flex items-center justify-center text-sm font-semibold tracking-wide shadow-md">
                                        Logo
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <h2 class="text-sm sm:text-lg md:text-xl font-semibold leading-tight">
                                            ${response.mapel?.mata_pelajaran ?? '-'}
                                        </h2>
                                        <p class="text-sm opacity-90">
                                            ${response.mapel?.teacher_mapel?.[0]?.user_account?.school_staff_profile?.nama_lengkap ?? 'tidak ada guru pengajar'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            },
        });
    }
}

$(document).ready(function () {
    subjectHeaderProgress();
});