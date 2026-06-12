function paginateDailyReflectionLivePreview(page = 1) {

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role || !schoolName || !schoolId) return;

    const previewContainer = $('#daily-reflection-live-preview');

    previewContainer.html(dailyReflectionLivePreviewLoading());

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/daily-reflection-live-preview`,
        method: 'GET',
        data: {
            page: page
        },
        headers: {
            'X-Timezone': Intl.DateTimeFormat().resolvedOptions().timeZone
        },

        success: function (response) {

            previewContainer.empty();

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {

                    let emotionHTML = '';

                    item.emotions.forEach((emotion, emotionIndex) => {

                        emotionHTML += `
                            <div 
                                id="emotion-${emotion.value}"
                                class="${emotionIndex === item.emotions.length - 1 ? 'col-span-2' : ''} 
                                rounded-2xl border border-gray-200 bg-${emotion.color}-50 p-4">

                                <div class="flex items-center justify-between">

                                    <i class="fas ${emotion.icon} text-${emotion.color}-600"></i>

                                    <span class="emotion-percentage text-xs font-black text-${emotion.color}-600">
                                        ${emotion.percentage}%
                                    </span>
                                </div>

                                <p class="text-sm font-black text-slate-800 mt-2">
                                    ${emotion.label}
                                </p>

                                <p class="emotion-total text-xs text-slate-500 mt-1">
                                    ${emotion.total} siswa
                                </p>
                            </div>
                        `;
                    });

                    const card = `
                        <!-- CARD -->
                        <div class="relative overflow-hidden rounded-4xl bg-linear-to-br from-[#F8FBFF] via-white to-blue-50 border border-blue-100 p-6">

                            <!-- DECORATION -->
                            <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full bg-blue-100/40 blur-3xl"></div>

                            <div class="relative z-10">

                                <!-- BADGE -->
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-linear-to-r from-[#0071BC] to-[#005B94] text-white text-xs font-black
                                    shadow-lg shadow-blue-100 mb-5">

                                    <i class="fas fa-book-open text-[11px]"></i>

                                    REFLEKSI HARI INI
                                </div>

                                <!-- TITLE -->
                                <div class="rounded-3xl border border-blue-100 bg-blue-50/70 backdrop-blur-sm p-5 mb-4">

                                    <div class="flex items-start gap-3">

                                        <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center shrink-0 border border-blue-100 shadow-sm">

                                            <i class="fas fa-heading text-[#0071BC] text-sm"></i>
                                        </div>

                                        <div>

                                            <p class="text-xs font-black uppercase tracking-wider text-blue-500 mb-2">
                                                Judul Refleksi
                                            </p>

                                            <h4 class="text-[15px] md:text-lg font-black text-slate-800 leading-relaxed">

                                                ${item.title ?? '-'}
                                            </h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- QUESTION -->
                                <div class="rounded-3xl border border-slate-200 bg-white/80 backdrop-blur-sm p-5">

                                    <div class="flex items-start gap-3">

                                        <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center shrink-0 border border-amber-100">

                                            <i class="fas fa-comment-dots text-amber-500 text-sm"></i>
                                        </div>

                                        <div>

                                            <p class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">
                                                Pertanyaan Refleksi
                                            </p>

                                            <p class="text-sm md:text-[15px] text-slate-600 leading-relaxed">

                                                ${item.question ?? '-'}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- EMOTION -->
                                <div id="emotion-list" class="grid grid-cols-2 gap-3 mt-6">
                                    ${emotionHTML}
                                </div>
                            </div>
                        </div>
                    `;

                    previewContainer.append(card);
                });

                $('.pagination-container-daily-reflection-live-preview').html(response.links);

                bindPaginationLinks();

                $('#empty-message-daily-reflection-live-preview').addClass('hidden');

            } else {

                $('#empty-message-daily-reflection-live-preview').removeClass('hidden');
            }
        }
    });
}

$(document).ready(function () {
    paginateDailyReflectionLivePreview();
});

function bindPaginationLinks() {
    $('.pagination-container-daily-reflection-live-preview').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateDailyReflectionLivePreview(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

function updateEmotionRealtime(emotions) {

    emotions.forEach((emotion) => {

        const card = $(`#emotion-${emotion.value}`);

        if (!card.length) return;

        card.find('.emotion-percentage')
            .text(`${emotion.percentage}%`);

        card.find('.emotion-total')
            .text(`${emotion.total} siswa`);
    });
}

function dailyReflectionLivePreviewLoading() {
    return `
        <div class="sticky bg-white rounded-4xl md:rounded-[2.5rem] p-6 md:p-8 border border-gray-300 shadow-sm animate-pulse">

            <div class="h-6 w-40 rounded-xl bg-slate-200 mb-8"></div>

            <div class="rounded-4xl bg-slate-50 border border-slate-200 p-6">

                <div class="space-y-4 mb-6">

                    <div class="h-8 w-40 rounded-2xl bg-slate-200"></div>

                    <div class="h-8 w-2/3 rounded-xl bg-slate-300"></div>

                    <div class="space-y-2">
                        <div class="h-4 w-full rounded bg-slate-200"></div>
                        <div class="h-4 w-5/6 rounded bg-slate-100"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">

                    ${Array.from({ length: 5 }).map(() => `
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="h-5 w-full rounded bg-slate-200 mb-3"></div>
                            <div class="h-4 w-20 rounded bg-slate-100 mb-2"></div>
                            <div class="h-3 w-16 rounded bg-slate-100"></div>
                        </div>
                    `).join('')}

                </div>
            </div>
        </div>
    `;
}