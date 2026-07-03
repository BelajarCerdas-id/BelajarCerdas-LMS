function paginateTKASubject() {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    $.ajax({
        url: `/lms/${role}/tka-simulation/subject-list/paginate`,
        method: 'GET',
        beforeSend: function () {

            $('#header-skeleton').removeClass('hidden');
            $('#header-content').addClass('hidden');

            $('#grid-subject-skeleton').removeClass('hidden');
            $('#container-subject-list').addClass('hidden');

            // Bersihkan data lama
            $('#grid-subject-list').empty();

        },
        success: function (response) {
            $('#grid-subject-list').empty();
            
            $('#header-skeleton').addClass('hidden');
            $('#grid-subject-skeleton').addClass('hidden');
            
            $('#header-content').removeClass('hidden');
            $('#container-subject-list').removeClass('hidden');
            
            if (response.data.length > 0) {
                $('#total-subject').text(response.subjectCount);

                $.each(response.data, function (index, item) {

                    const studentTkaPracticeTest = response.studentTkaPracticeTest.replace(':role', role).replace(':kelasId', item.kelas_id).replace(':mapelId', item.id);

                    $('#grid-subject-list').append(`
                        <div
                            class="group bg-white rounded-2xl border border-gray-200 hover:border-[#0071BC] hover:shadow-xl transition-all duration-300 overflow-hidden">

                            <div class="h-2 bg-[#0071BC]"></div>

                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-blue-100 text-[#0071BC] flex items-center justify-center">
                                        <i class="fa-solid fa-book-open text-2xl"></i>
                                    </div>
                                </div>

                                <h2 class="mt-6 text-xl font-bold text-gray-800">
                                    ${item.mata_pelajaran}
                                </h2>

                                <p class="mt-2 text-sm text-gray-500 leading-6">

                                    Kerjakan simulasi soal
                                    <b>${item.mata_pelajaran}</b>
                                    untuk mengukur pemahamanmu sebelum menghadapi TKA.

                                </p>

                                <div class="flex flex-wrap gap-2 mt-5">

                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-medium">

                                        <i class="fa-solid fa-database"></i>

                                        ${item.total_question ?? 0} Soal Tersedia

                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium">

                                        <i class="fa-solid fa-list-check"></i>

                                        15 Soal / Simulasi

                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-sm font-medium">

                                        <i class="fa-regular fa-clock"></i>

                                        30 Menit

                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">

                                        <i class="fa-solid fa-shuffle"></i>

                                        Soal Acak
                                    </span>

                                </div>

                                <div class="flex items-start gap-2 mt-5 text-xs text-gray-500 leading-5">
                                    <i class="fa-solid fa-circle-info text-[#0071BC] pt-1"></i>

                                    <p>
                                        Simulasi menggunakan <strong>15 soal acak</strong> dari
                                        <strong>Bank Soal BelajarCerdas</strong>.
                                        Soal ini merupakan materi latihan dan tidak memengaruhi penilaian sekolah.
                                    </p>
                                </div>

                                <a href="${studentTkaPracticeTest}"
                                    class="mt-6 w-full flex justify-center items-center gap-2 rounded-xl bg-[#0071BC] hover:bg-[#005E9D] 
                                    text-white py-3 font-semibold transition">

                                    <i class="fa-solid fa-play"></i>

                                    Mulai Simulasi
                                </a>
                            </div>
                        </div>
                    `);
                });

                $('#empty-message-subject-list').hide();
            } else {
                $('#empty-message-subject-list').show();
            }
        },
        error: function (error) {
            console.log(error);

            $('#header-skeleton').addClass('hidden');
            $('#grid-subject-skeleton').addClass('hidden');

            // kalau ingin tetap tampilkan header
            $('#header-content').removeClass('hidden');
            $('#container-subject-list').removeClass('hidden');
        },
    });
}

$(document).ready(function () {
    paginateTKASubject();
});