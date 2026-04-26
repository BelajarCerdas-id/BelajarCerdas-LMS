function pagianteStudentAssessmentHistoryCheating() {
    const container = document.getElementById('container-student-assessment-cheating-history');
    if (!container) return;

    $.ajax({
        url: `/lms/student/dashboard/cheating-history/data-paginate`,
        method: 'GET',
        success: function (response) {
            const containerCheatingHistory = $('#grid-list-student-assessment-cheating-history');
            containerCheatingHistory.empty();

            if (response.data && response.data.length > 0) {

                $.each(response.data, function (index, item) {

                    const formatDate = (dateString) => {
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day}-${monthName}-${year}`;
                    };

                    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                    });

                    const nama = item.user_account?.student_profile?.nama_lengkap ?? '-';
                    const kelas = item.school_assessment?.school_class?.class_name ?? '-';
                    const tahun = item.school_assessment?.school_class?.tahun_ajaran ?? '-';
                    const mapel = item.school_assessment?.mapel?.mata_pelajaran ?? '-';
                    const tipe = item.school_assessment?.school_assessment_type?.name ?? '-';

                    const judul = item.school_assessment?.title ?? '-';
                    const status = (item.status ?? '').toUpperCase();

                    const jumlah = item.tab_switch_count ?? 0;

                    const tanggal_cheat = item.updated_at ? `${formatDate(item.updated_at)}, ${timeFormatter.format(new Date(item.updated_at))}` : '-';

                    const startAssessment = item.school_assessment?.start_date ? `${formatDate(item.school_assessment.start_date)}, ${timeFormatter.format(new Date(item.school_assessment.start_date))}` : '-';

                    const endAssessment = item.school_assessment?.end_date ? `${formatDate(item.school_assessment.end_date)}, ${timeFormatter.format(new Date(item.school_assessment.end_date))}` : '-';

                    const card = `
                        <div class="p-4 sm:p-5 lg:p-6 rounded-2xl border border-red-100 bg-white hover:shadow-md transition-all flex flex-col gap-4">

                            <!-- HEADER -->
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">

                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-800 text-sm sm:text-base leading-tight truncate">
                                        ${nama}
                                    </h4>
                                    <p class="text-[11px] text-red-500 font-semibold mt-0.5">
                                        Cheating: ${tanggal_cheat}
                                    </p>
                                </div>

                                <span class="w-fit text-[10px] font-bold text-white bg-red-500 px-2.5 py-1 rounded-md uppercase">
                                    ${status}
                                </span>

                            </div>

                            <!-- CONTENT -->
                            <div class="text-xs sm:text-sm text-gray-600 space-y-1.5">
                                <p class="font-semibold text-gray-800 leading-snug wrap-break-word">
                                    ${judul}
                                </p>
                                <p class="text-[11px] text-gray-400">
                                    ${tipe}
                                </p>
                                <p class="text-[11px] text-gray-400">
                                    Waktu: ${startAssessment} - ${endAssessment}
                                </p>
                            </div>

                            <!-- TAG -->
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md font-semibold">
                                    ${kelas} - ${tahun}
                                </span>
                                <span class="text-gray-500 text-[11px] wrap-break-word">
                                    ${mapel}
                                </span>
                            </div>

                            <!-- FOOTER -->
                            <div class="flex items-center justify-between pt-2 border-t border-red-50">
                                <span class="text-xs font-semibold text-red-600">
                                    Pelanggaran
                                </span>

                                <span class="text-xs font-bold text-white bg-red-500 px-3 py-1 rounded-lg">
                                    ${jumlah} <i class="fas fa-xmark"></i>
                                </span>
                            </div>

                        </div>
                    `;

                    containerCheatingHistory.append(card);
                });

                $('#empty-message-student-assessment-cheating-history').addClass('hidden');

            } else {
                $('#empty-message-student-assessment-cheating-history').removeClass('hidden');
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}


// INIT
$(document).ready(function () {
    pagianteStudentAssessmentHistoryCheating();
});
