function paginateAnnouncementList() {
    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const meetingNumber = container.dataset.meetingNumber;
    const semester = container.dataset.semester;

    if (!role || !schoolName || !schoolId || !subjectTeacherId || !meetingNumber || !semester) return;

    fetchData();

    function fetchData() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/${meetingNumber}/semester/${semester}/meeting-management/announcement/paginate`,
            method: 'GET',

            success: function (response) {

                const announcementList = $('#grid-announcement-list');

                announcementList.empty();

                // format tanggal
                const formatDate = (dateString) => {

                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                    const date = new Date(dateString);

                    const day = date.getDate();
                    const monthName = months[date.getMonth()];
                    const year = date.getFullYear();

                    return `${day} ${monthName} ${year}`;
                };

                if (response.data.length > 0) {

                    $.each(response.data, function (index, item) {

                        const updatedAt = item.updated_at ? formatDate(item.updated_at) : '-';

                        // badge type
                        const badge = item.type === 'penting'
                            ? `
                                <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded-md border border-red-100 uppercase tracking-wider">
                                    Penting
                                </span>
                            `
                            : `
                                <span class="text-[10px] font-bold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200 uppercase tracking-wider">
                                    Info Biasa
                                </span>
                            `;

                        // views
                        const viewsCount = item.views_count ?? 0;

                        // percentage
                        const percentage = item.percentage ?? 0;

                        const card = `
                            <div class="border border-gray-300 rounded-2xl p-5 hover:shadow-md hover:border-blue-200 transition-all bg-slate-50/50 flex flex-col shrink-0">

                                <!-- HEADER -->
                                <div class="flex justify-between items-start mb-3">
                                    
                                    ${badge}

                                    <span class="text-[11px] font-bold text-slate-400">
                                        ${updatedAt}
                                    </span>
                                </div>

                                <!-- TITLE -->
                                <h4 class="font-bold text-slate-800 text-sm mb-2 truncate">
                                    ${item.title ?? 'Judul Pengumuman'}
                                </h4>

                                <!-- CONTENT -->
                                <p class="text-xs text-slate-500 mb-4 line-clamp-2">
                                    ${item.content ?? 'Isi pengumuman tidak tersedia.'}
                                </p>

                                <!-- FOOTER -->
                                <div class="mt-auto flex justify-between items-center">

                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-eye text-slate-400 text-sm"></i>

                                        <span class="text-xs font-bold text-slate-600">
                                            Dilihat:
                                            <span class="text-emerald-600">
                                                ${viewsCount}
                                            </span>
                                            Siswa
                                        </span>
                                    </div>

                                    <div class="w-20 bg-slate-200 rounded-full h-1.5">
                                        <div 
                                            class="bg-emerald-500 h-1.5 rounded-full"
                                            style="width: ${percentage}%">
                                        </div>
                                    </div>

                                </div>

                            </div>
                        `;

                        announcementList.append(card);
                    });

                    $('#empty-message-announcement-list').hide();

                } else {

                    $('#empty-message-announcement-list').show();
                }
            },

            error: function (xhr, status, error) {
                console.error('Terjadi kesalahan:', status, error);
            }
        });
    }
}

$(document).ready(function () {
    paginateAnnouncementList();
});