function paginateSchoolReflectionAttention(search_year = null) {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const foundationId = container.dataset.foundationId;
    if (!role) return;

    if (!foundationId || foundationId === 'undefined' || foundationId === 'null') {
        $('#academic-year-loading').addClass('hidden');
        $('#filter-tahun-ajaran-wrapper').addClass('hidden');
        $('#filter-tahun-ajaran-empty').removeClass('hidden');
        $('#school-attention-loading').addClass('hidden');
        $('#school-attention-content').addClass('hidden');
        $('#school-attention-empty').removeClass('hidden');
        $('#school-attention-list').empty();
        $('#school-attention-total').text('Tidak ada data sekolah.');

        return;
    }

    $('#academic-year-loading').removeClass('hidden');
    $('#filter-tahun-ajaran-wrapper').addClass('hidden');
    $('#filter-tahun-ajaran-empty').addClass('hidden');
    $('#school-attention-loading').removeClass('hidden');
    $('#school-attention-content').addClass('hidden');
    $('#school-attention-empty').addClass('hidden');
    $('#school-attention-list').empty();

    $.ajax({
        url: `/lms/${role}/foundation/student-reflection/school-reflection-attention/paginate/${foundationId}`,
        method: 'GET',
        data: {
            search_year: search_year
        },

        beforeSend: function () {
            $('#academic-year-loading').removeClass('hidden');
            $('#filter-tahun-ajaran-wrapper').addClass('hidden');
            $('#filter-tahun-ajaran-empty').addClass('hidden');
            $('#school-attention-loading').removeClass('hidden');
            $('#school-attention-content').addClass('hidden');
            $('#school-attention-empty').addClass('hidden');
            $('#school-attention-list').empty();
        },
        success: function (response) {
            $('#academic-year-loading').addClass('hidden');
            $('#filter-tahun-ajaran-empty').addClass('hidden');
            $('#filter-tahun-ajaran-wrapper').removeClass('hidden');
            $('#school-attention-loading').addClass('hidden');
            $('#school-attention-list').empty();

            if (response.data.length > 0) {
                $('#school-attention-content').removeClass('hidden');
                $('#school-attention-empty').addClass('hidden');
                $('#school-attention-total').text(`${response.total_attention} sekolah memerlukan pendampingan.`);

                $.each(response.data, function (_, item) {
                    const badgeClass = item.level === 'high' ? 'badge-error badge-outline' : 'badge-warning badge-outline';
                    const cardClass = item.level === 'high' ? 'border-red-200 bg-red-50' : 'border-orange-200 bg-orange-50';
                    const progressBg = item.level === 'high' ? 'bg-red-100' : 'bg-orange-100';
                    const progressFill = item.level === 'high' ? 'bg-red-500' : 'bg-orange-500';
                    const positiveText = item.level === 'high' ? 'text-red-700' : 'text-orange-700';

                    $('#school-attention-list').append(`
                        <div class="rounded-xl border ${cardClass} p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800">
                                        ${item.nama_sekolah}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        ${item.total_students} siswa •
                                        ${item.reflection_count} refleksi
                                    </p>
                                </div>

                                <span class="badge ${badgeClass}">
                                    ${item.badge}
                                </span>
                            </div>

                            <div class="mt-4 h-2 rounded-full ${progressBg}">
                                <div
                                    class="h-2 rounded-full ${progressFill}"
                                    style="width:${item.positive_percentage}%">
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
                                <span class="font-medium ${positiveText}">
                                    Positif ${item.positive_percentage}%
                                </span>

                                <span class="text-slate-500">
                                    Pengisian ${item.completion_percentage}%
                                </span>
                            </div>
                        </div>
                    `);
                });
            } else {
                $('#school-attention-content').addClass('hidden');
                $('#school-attention-empty').removeClass('hidden');
                $('#school-attention-total').text('Tidak ada sekolah yang memerlukan perhatian.');
            }
        },
        error: function (xhr, status, error) {
            $('#academic-year-loading').addClass('hidden');
            $('#filter-tahun-ajaran-wrapper').addClass('hidden');
            $('#filter-tahun-ajaran-empty').removeClass('hidden');
            $('#school-attention-loading').addClass('hidden');
            $('#school-attention-content').addClass('hidden');
            $('#school-attention-empty').removeClass('hidden');
            $('#school-attention-list').empty();
            $('#school-attention-total').text('Gagal memuat data.');

            console.error(error);
        }
    });
}

$(document).ready(function () {
    paginateSchoolReflectionAttention();
});

$('#filter-tahun-ajaran').on('change', function () {
    paginateSchoolReflectionAttention($(this).val());
});