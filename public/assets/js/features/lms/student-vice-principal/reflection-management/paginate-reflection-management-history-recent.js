function paginateReflectionManagementHistoryRecent() {

    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/history-recent`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function (response) {

            const listContainer = $('#reflection-history-recent');

            // LOADING SCREEN
            listContainer.html(`
                <div class="relative">

                    <!-- GLOBAL LINE -->
                    <div class="absolute left-3 top-0 bottom-0 w-px 
                        bg-linear-to-b from-blue-100 via-sky-50 to-transparent">
                    </div>

                    ${Array.from({ length: 3 }).map(() => `
                        <div class="relative pl-8 pb-5">

                            <!-- DOT -->
                            <div class="absolute left-0 top-8 w-6 h-6 rounded-full 
                                bg-blue-200 border-4 border-white animate-pulse">
                            </div>

                            <!-- CARD -->
                            <div class="rounded-4xl border border-blue-100 
                                bg-linear-to-br from-white to-blue-50/40 
                                p-5 overflow-hidden">

                                <div class="flex flex-col gap-5 animate-pulse">

                                    <!-- HEADER -->
                                    <div class="flex flex-col gap-3">

                                        <!-- TOP -->
                                        <div class="flex items-center justify-between gap-3">

                                            <div class="h-7 w-36 rounded-full bg-blue-200"></div>

                                            <div class="h-4 w-28 rounded-lg bg-slate-200"></div>
                                        </div>

                                        <!-- TITLE -->
                                        <div class="space-y-2">

                                            <div class="h-6 w-2/3 rounded-xl bg-slate-300"></div>

                                            <div class="h-4 w-full rounded-lg bg-slate-200"></div>

                                            <div class="h-4 w-5/6 rounded-lg bg-slate-100"></div>
                                        </div>
                                    </div>

                                    <!-- META -->
                                    <div class="flex items-center gap-3">

                                        <div class="h-4 w-40 rounded-lg bg-slate-200"></div>

                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>

                                        <div class="h-4 w-24 rounded-lg bg-slate-200"></div>
                                    </div>

                                    <!-- FOOTER -->
                                    <div class="pt-4 border-t border-blue-100 
                                        flex flex-col sm:flex-row sm:items-center 
                                        sm:justify-between gap-4">

                                        <!-- STATS -->
                                        <div class="h-10 w-32 rounded-2xl bg-blue-200"></div>

                                        <!-- BUTTON -->
                                        <div class="h-11 w-full sm:w-36 rounded-2xl bg-slate-300"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `);

            setTimeout(() => {
                if (response.data.length > 0) {
                    let renderHTML = `
                        <div class="relative">

                            <!-- global line -->
                            <div class="absolute left-3 top-0 bottom-0 w-px 
                                bg-linear-to-b from-[#0071BC] via-[#0071BC] to-[#0071BC]">
                            </div>
                    `;

                    $.each(response.data, function (index, item) {

                        const formatDate = (dateString) => {
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                            const date = new Date(dateString);
                            const day = date.getDate();
                            const monthName = months[date.getMonth()];
                            const year = date.getFullYear();

                            return `${day}-${monthName}-${year}`;
                        };

                        // Format tanggal
                        const startDate = item.created_at ? `${formatDate(item.created_at)}` : 'Tanggal tidak tersedia';
    
                        const totalSiswa = item.sch_refl_target
                            ?.reduce((total, target) => {
    
                                const schoolClasses = Array.isArray(target.kelas?.school_class)
                                    ? target.kelas.school_class
                                    : (target.kelas?.school_class ? [target.kelas.school_class] : []);
    
                                const totalPerKelas = schoolClasses.reduce((sum, schoolClass) => {
                                    return sum + (schoolClass.student_school_class_count || 0);
                                }, 0);
    
                                return total + totalPerKelas;
    
                            }, 0);
                        
                        const reflectionHistoryDetail = response.reflectionHistoryDetail.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                            .replace(':reflectionQuestionId', item.id);
    
                        renderHTML += `
                            <div class="relative pl-8 pb-5" data-reflection-id="${item.id}">
    
                                <!-- DOT -->
                                <div class="absolute left-0 top-8 w-6 h-6 rounded-full bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] border-4
                                    border-white shadow-lg z-10">
                                </div>
    
                                <!-- CARD -->
                                <div class="rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50/40 p-5 hover:shadow-xl hover:-translate-y-0.5
                                    transition-all duration-300">
    
                                    <div class="flex flex-col gap-5">
    
                                        <!-- HEADER -->
                                        <div class="flex flex-col gap-3">
    
                                            <!-- TOP -->
                                            <div class="flex items-center justify-between gap-3 flex-wrap">
    
                                                <!-- BADGE -->
                                                <span class="w-fit text-[11px] font-black bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)]
                                                    text-white px-3 py-1 rounded-full shadow-sm">
                                                    REFLEKSI TERBARU
                                                </span>
    
                                                <!-- DATE -->
                                                <span class="text-xs text-slate-500 flex items-center gap-2">
    
                                                    <i class="far fa-calendar-alt text-[11px] text-amber-400"></i>
    
                                                    ${startDate}
                                                </span>
                                            </div>
    
                                            <!-- TITLE -->
                                            <h4 class="font-black text-slate-800 text-lg leading-snug">
                                                ${item.title ?? '-'}
                                            </h4>
    
                                            <!-- QUESTION -->
                                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-2">
                                                "${item.question ?? '-'}"
                                            </p>
                                        </div>
    
                                        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
    
                                            <!-- CLASS -->
                                            <span class="flex items-center gap-2">
    
                                                <i class="fas fa-layer-group text-blue-400 text-xs"></i>
    
                                                ${item.sch_refl_target?.map(target => target.kelas?.kelas).join(', ') ?? '-'}
                                            </span>
    
                                            <i class="fa-solid fa-circle text-[5px] text-slate-300"></i>
    
                                            <!-- STUDENT -->
                                            <span class="flex items-center gap-2">
    
                                                <i class="fas fa-user-graduate text-sky-400 text-xs"></i>
    
                                                ${totalSiswa} siswa
                                            </span>
                                        </div>
    
                                        <div class="pt-4 border-t border-blue-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    
                                            <!-- STATS -->
                                            <div class="flex flex-wrap items-center gap-3">
    
                                                <div class="px-4 py-2 rounded-2xl 
                                                    bg-blue-100 text-blue-700 text-xs 
                                                    font-black flex items-center gap-2">
    
                                                    <i class="fas fa-reply text-[11px]"></i>
    
                                                    <span class="total-responden">
                                                        ${item.total_responden ?? 0} Jawaban
                                                    </span>
                                                </div>
                                            </div>
    
                                            <!-- BUTTON -->
                                            <a href="${reflectionHistoryDetail}">
                                                <button class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-[#4189E0] text-white text-sm font-black hover:scale-[1.02] 
                                                    active:scale-[0.98] transition-all shadow-lg shadow-blue-100 cursor-pointer">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    Lihat Detail
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
    
                    renderHTML += `</div>`;
                    listContainer.html(renderHTML);
                    $('#empty-message-reflection-history').hide();

                } else {
                    listContainer.html('');
                    $('#empty-message-reflection-history').show();
                }

            }, 500);
        }
    });
}

$(document).ready(function () {
    paginateReflectionManagementHistoryRecent();
});

function updateHistoryRecentCount(reflectionId, totalResponden) {

    const card = $(`[data-reflection-id="${reflectionId}"]`);

    if (!card.length) return;

    card.find('.total-responden').text(`${totalResponden} Jawaban`);
}