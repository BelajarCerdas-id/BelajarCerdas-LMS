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
                    const palette = [
                        { bg: 'bg-[#0071BC]' },
                        { bg: 'bg-emerald-600' },
                        { bg: 'bg-violet-600' },
                        { bg: 'bg-amber-500' },
                        { bg: 'bg-rose-500' },
                        { bg: 'bg-teal-600' },
                        { bg: 'bg-indigo-600' },
                        { bg: 'bg-cyan-600' },
                    ];

                    const emojiFor = function (name) {
                        const n = (name || '').toLowerCase();
                        if (/matematika|global math/.test(n)) return '➗';
                        if (/fisika/.test(n)) return '🧲';
                        if (/kimia/.test(n)) return '⚗️';
                        if (/biologi/.test(n)) return '🧬';
                        if (/ipa/.test(n)) return '🔬';
                        if (/geografi/.test(n)) return '🗺️';
                        if (/sejarah/.test(n)) return '🏛️';
                        if (/ekonomi/.test(n)) return '💰';
                        if (/sosiologi|ips/.test(n)) return '🧭';
                        if (/informatika|koding/.test(n)) return '💻';
                        if (/seni/.test(n)) return '🎨';
                        if (/prakarya/.test(n)) return '🛠️';
                        if (/pancasila|kewirausahaan/.test(n)) return '🇮🇩';
                        if (/agama|pabp/.test(n)) return '🕊️';
                        if (/bahasa inggris/.test(n)) return '🔤';
                        if (/bahasa/.test(n)) return '📖';
                        return '📚';
                    };

                    $.each(response.data, function (index, item) {

                        const assessmentActivity = response.assessmentActivity.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                            .replace(':curriculumId', item.kurikulum_id).replace(':mapelId', item.id);

                        const teacher = item.teacher_mapel?.[0]?.user_account?.school_staff_profile?.nama_lengkap;
                        const color = palette[index % palette.length];
                        const emoji = emojiFor(item.mata_pelajaran);

                        const card = `
                            <a href="${assessmentActivity}" data-mapel="${item.mata_pelajaran}"
                                class="group bg-white border border-slate-200 rounded-3xl p-4 shadow-sm flex flex-col cursor-pointer
                                transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-[#0071BC]/40 active:scale-[0.98]">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl ${color.bg} flex items-center justify-center text-xl sm:text-2xl shrink-0">
                                        ${emoji}
                                    </div>
                                    <h2 class="font-bold text-slate-900 text-base leading-snug line-clamp-2 pt-0.5">
                                        ${item.mata_pelajaran}
                                    </h2>
                                </div>

                                <div class="mt-3 flex items-center gap-1.5 text-[11px] sm:text-xs text-slate-500 min-w-0">
                                    <i class="fa-solid fa-chalkboard-user ${color.bg.replace('bg-', 'text-')} shrink-0"></i>
                                    <span class="truncate">${teacher ? teacher : 'Belum ada guru pengajar'}</span>
                                </div>

                                <div class="mt-auto pt-3 border-t border-slate-100">
                                    <span class="flex items-center justify-center h-11 w-full bg-[#0071BC] group-hover:bg-[#005a96] text-white text-xs font-bold rounded-xl transition">
                                        Lihat Kelas
                                    </span>
                                </div>
                            </a>
                        `;

                        containerListMapel.append(card);
                    });

                    $('#mapel-search').off('input').on('input', function () {
                        const q = $(this).val().toLowerCase().trim();
                        $('#grid-list-mapel > a').each(function () {
                            $(this).toggle(($(this).data('mapel') || '').toLowerCase().includes(q));
                        });
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