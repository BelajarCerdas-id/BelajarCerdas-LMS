function paginateLmsStudent() {
    const container = document.getElementById('container-paginate-lms-student');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    fetchMapel(role, schoolName, schoolId);

    function fetchMapel() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/paginate`,
            method: 'GET',
            success: function (response) {
                const containerListMapel = $('#grid-list-mapel');
                containerListMapel.empty();

                if (response.data.length > 0) {
                    $.each(response.data, function (index, item) {

                        const assessmentActivity = response.assessmentActivity.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                            .replace(':curriculumId', item.kurikulum_id).replace(':mapelId', item.id);

                        const card = `
                            <div class="w-full flex justify-center">
                                <div
                                    class="relative w-full max-w-96 h-50 rounded-[15px] bg-linear-to-br from-[#FF6D1F] to-[#E58C40] shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]
                                    transition-all duration-200 hover:-translate-y-1 overflow-hidden">
                                    
                                    <!-- image kanan atas -->


                                    <!-- image kiri bawah -->


                                    <div class="p-4 h-full flex flex-col justify-between text-white font-bold">
                                        <span class="text-xs opacity-90">
                                            ${item.teacher_mapel?.[0]?.user_account?.school_staff_profile?.nama_lengkap ?? 'tidak ada guru pengajar'}
                                        </span>

                                        <h2 class="text-lg font-semibold leading-tight text-center">
                                            ${item.mata_pelajaran}
                                        </h2>

                                        <div class="flex justify-end z-20">
                                            <a href="${assessmentActivity}">
                                                <button class="bg-[#2E8B57] text-white text-xs font-bold px-8 py-2 rounded-lg shadow-md transition hover:scale-105 cursor-pointer">
                                                    Lihat Kelas
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        containerListMapel.append(card);
                    });

                    $('#empty-message-list-mapel').hide();
                } else {
                    $('#empty-message-list-mapel').show();
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
}

$(document).ready(function () {
    paginateLmsStudent();
});