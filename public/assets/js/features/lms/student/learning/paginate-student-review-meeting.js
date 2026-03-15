function assessmentActivity() {
    const container = document.getElementById('container-review-meeting');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumId = container.dataset.curriculumId;
    const mapelId = container.dataset.mapelId;
    const serviceId = container.dataset.serviceId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumId) return;
    if (!mapelId) return;
    if (!serviceId) return;

    fetchService(role, schoolName, schoolId, curriculumId, mapelId, serviceId);

    function fetchService() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/service/${serviceId}/paginate`,
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
                                    <button onclick="openReviewModal(${item.id})"
                                        class="w-11.25 h-8.75 flex items-center justify-center rounded-md bg-gray-200 border border-gray-300 cursor-pointer
                                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">

                                        <i class="fa-solid fa-eye text-gray-700 text-sm"></i>
                                    </button>

                                    <!-- DOWNLOAD -->
                                    <a href="/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/service/${serviceId}/download-content/${item.id}"
                                    class="w-11.25 h-8.75 flex items-center justify-center rounded-md bg-[#43AB3C]
                                    shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">

                                        <i class="fa-solid fa-download text-white text-sm"></i>
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
    assessmentActivity();
});

function openReviewModal(meetingId) {
    const container = document.getElementById('container-review-meeting');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const curriculumId = container.dataset.curriculumId;
    const mapelId = container.dataset.mapelId;
    const serviceId = container.dataset.serviceId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!curriculumId) return;
    if (!mapelId) return;
    if (!serviceId) return;
    
    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/service/${serviceId}/show-content/${meetingId}`,
        method: 'GET',
        success: function (response) {

            const container = $('#modal-content-container');
            container.empty();

            if (response.type === 'text') {
                container.append(`
                    <div class="bg-gray-50 border rounded-xl p-5 shadow-sm">
                        <p class="text-sm font-semibold text-blue-600 mb-3 uppercase">
                            ${response.service_name ?? ''}
                        </p>
                        <div class="text-gray-700 leading-relaxed whitespace-pre-line">
                            ${response.value_text ?? 'Tidak ada konten'}
                        </div>
                    </div>
                `);
            }

            if (response.type === 'file') {

                if (response.mime.startsWith('video/')) {
                    container.append(`
                        <div class="rounded-xl overflow-hidden shadow h-[70vh]">
                            <video id="video-frame" src="${response.file_url}" controls class="w-full rounded-lg h-[70vh]"></video>
                        </div>
                    `);
                }

                else if (response.mime === 'application/pdf') {
                    container.append(`
                        <iframe src="${response.file_url}"
                            class="w-full h-[70vh] rounded-xl border"></iframe>
                    `);
                }

                else {
                    container.append(`
                        <div class="text-center">
                            <a href="${response.file_url}"
                                class="bg-green-500 text-white px-5 py-2 rounded-lg shadow">
                                Download File
                            </a>
                        </div>
                    `);
                }
            }

            document.getElementById('reviewModal').showModal();
        }
    });
}

function closeModal() {
    const iframe = document.getElementById('video-frame');
    if (iframe) {
        iframe.src = ''; // remove the video after close modal
    }
}