let selectedSemester = 1;
function changeSemester(semester) {
    selectedSemester = semester;

    const container = document.getElementById('container-review-meeting');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!subjectTeacherId) return;

    fetchService(role, schoolName, schoolId, subjectTeacherId);

    function fetchService() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/semester/${selectedSemester}/paginate`,
            method: 'GET',
            success: function (response) {
                const containerMeetingList = $('#grid-list-review-meeting');
                containerMeetingList.empty();

                if (response.data.length > 0) {
                    $.each(response.data, function (index, item) {
                        const formatDate = (dateString) => {
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                            const date = new Date(dateString);
                            const day = date.getDate();
                            const monthName = months[date.getMonth()];
                            const year = date.getFullYear();

                            return `${day} ${monthName} ${year}`;
                        };

                        // Format tanggal
                        const meetingDate = item.meeting_date ? formatDate(item.meeting_date) : 'Tanggal tidak tersedia';

                        const subjectAttendanceMeetingManagement = response.subjectAttendanceMeetingManagement.replace(':role', role).replace(':schoolName', schoolName)
                            .replace(':schoolId', schoolId).replace(':subjectTeacherId', subjectTeacherId).replace(':meetingNumber', item.meeting_number).replace(':semester', selectedSemester);

                        const card = `
                            <div class="bg-[#EFEFEF] rounded-xl border border-gray-300 px-6 py-5 flex items-center justify-between
                                shadow-[0_6px_14px_rgba(0,0,0,0.35),1px_1px_1px_1px_rgba(0,0,0,0.2)] hover:-translate-y-0.5 transition-all duration-300 ease-in-out">

                                <!-- LEFT -->
                                <div>
                                    <h3 class="font-bold text-gray-800 tracking-wide">
                                        PERTEMUAN ${item.meeting_number}
                                    </h3>

                                    <div class="flex items-center gap-2 text-sm text-gray-600 mt-2">
                                        <span class="text-[#0071BC] text-base">
                                            <i class="fa-solid fa-calendar-days font-bold"></i>
                                        </span>
                                        <i class="fa-solid fa-ellipsis-vertical text-xs font-bold"></i>
                                        <span class="font-bold">${meetingDate}</span>
                                    </div>
                                </div>

                                <!-- RIGHT BUTTONS -->
                                <div class="flex items-center gap-4">

                                    <!-- VIEW -->
                                    <a href="${subjectAttendanceMeetingManagement}" class="w-11.25 h-8.75 flex items-center justify-center rounded-md bg-gray-200 border 
                                        border-gray-300 cursor-pointer shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">

                                        <i class="fa-solid fa-eye text-gray-700 text-sm"></i>
                                    </a>

                                </div>
                            </div>

                        `;

                        containerMeetingList.append(card);
                    });

                    $('#empty-message-review-content').hide();
                } else {
                    $('#empty-message-review-content').show();
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    }
}

$(document).ready(function () {
    changeSemester(selectedSemester);
});