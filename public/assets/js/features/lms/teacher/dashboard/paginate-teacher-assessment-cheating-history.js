function pagianteTeacherAssessmentHistoryCheating(search_year = null, search_class = null, subject_id = null, search_assessment_type = null) {
    const container = document.getElementById('container-teacher-assessment-cheating-history');
    if (!container) return;

    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/Guru/${schoolName}/${schoolId}/beranda/cheating-history`,
        method: 'GET',
        data: {
            search_year,
            search_class,
            subject_id,
            search_assessment_type,
        },
        success: function (response) {
            const containerCheatingHistory = $('#grid-list-teacher-assessment-cheating-history');
            containerCheatingHistory.empty();

            // Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-school-year-cheating-history');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tahun Ajaran</label>
                    <select id="dropdown-filter-school-year-cheating-history" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm outline-none cursor-pointer">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item =>
                            `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`
                        ).join('')}
                    </select>
                </div>
            `;

            // Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-rombel-class-cheating-history');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-filter-rombel-class-cheating-history" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm outline-none cursor-pointer">
                        <option value="" class="hidden">Filter Kelas</option>
                        ${response.className.map(item =>
                            `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`
                        ).join('')}
                    </select>
                </div>
            `;

            // Mapel
            const containerMapel = document.getElementById('container-dropdown-subject-teacher-cheating-history');
            containerMapel.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Mapel</label>
                    <select id="dropdown-filter-mapel-cheating-history" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm outline-none cursor-pointer">
                        <option value="" class="hidden">Filter Mata Pelajaran</option>
                        ${response.subject.map(item =>
                            `<option value="${item.id}" ${subject_id == item.id ? 'selected' : ''}>${item.name}</option>`
                        ).join('')}
                    </select>
                </div>
            `;

            // Tipe
            const containerAssessmentType = document.getElementById('container-dropdown-assessment-type-cheating-history');
            containerAssessmentType.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tipe Asesmen</label>
                    <select id="dropdown-filter-assessment-type-cheating-history" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm outline-none cursor-pointer">
                        <option value="" class="hidden">Filter Tipe Asesmen</option>
                        ${response.schoolAssessmentType.map(item =>
                            `<option value="${item.id}" ${search_assessment_type == item.id ? 'selected' : ''}>${item.name}</option>`
                        ).join('')}
                    </select>
                </div>
            `;

            if (response.data.length > 0) {

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
                        <div class="p-4 sm:p-5 rounded-2xl border border-red-100 bg-white hover:shadow-md transition-all flex flex-col gap-3">

                            <!-- HEADER -->
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">

                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm sm:text-base leading-tight">
                                        ${nama}
                                    </h4>
                                    <p class="text-[10px] sm:text-[11px] text-red-500 font-semibold">
                                        Cheating: ${tanggal_cheat}
                                    </p>
                                </div>

                                <span class="self-start sm:self-auto text-[10px] font-bold text-white bg-red-500 px-2 py-1 rounded-md uppercase">
                                    ${status}
                                </span>

                            </div>

                            <!-- CONTENT -->
                            <div class="text-xs text-gray-600 space-y-1">
                                <p class="font-semibold text-gray-800 wrap-break-word leading-snug text-xs sm:text-sm">
                                    ${judul}
                                </p>
                                <p class="text-[10px] sm:text-[11px] text-gray-400">
                                    ${tipe}
                                </p>
                                <p class="text-[10px] sm:text-[11px] text-gray-400">
                                    Waktu: ${startAssessment} - ${endAssessment}
                                </p>
                            </div>

                            <!-- TAG -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 text-xs">
                                <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md font-semibold w-fit">
                                    ${kelas} - ${tahun}
                                </span>
                                <span class="text-gray-500 text-[11px]">
                                    ${mapel}
                                </span>
                            </div>

                            <!-- FOOTER -->
                            <div class="flex items-center justify-between mt-2">
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

                $('#empty-message-teacher-assessment-cheating-history').addClass('hidden');

            } else {
                $('#empty-message-teacher-assessment-cheating-history').removeClass('hidden');
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}


// INIT
$(document).ready(function () {
    pagianteTeacherAssessmentHistoryCheating();
});


// FILTER EVENT
$(document).on('change', '#dropdown-filter-school-year-cheating-history', function () {
    pagianteTeacherAssessmentHistoryCheating($(this).val(), null, null, null);
});

$(document).on('change', '#dropdown-filter-rombel-class-cheating-history', function () {
    pagianteTeacherAssessmentHistoryCheating(
        $('#dropdown-filter-school-year-cheating-history').val(),
        $(this).val(),
        null,
        null
    );
});

$(document).on('change', '#dropdown-filter-mapel-cheating-history', function () {
    pagianteTeacherAssessmentHistoryCheating(
        $('#dropdown-filter-school-year-cheating-history').val(),
        $('#dropdown-filter-rombel-class-cheating-history').val(),
        $(this).val(),
        null
    );
});

$(document).on('change', '#dropdown-filter-assessment-type-cheating-history', function () {
    pagianteTeacherAssessmentHistoryCheating(
        $('#dropdown-filter-school-year-cheating-history').val(),
        $('#dropdown-filter-rombel-class-cheating-history').val(),
        $('#dropdown-filter-mapel-cheating-history').val(),
        $(this).val()
    );
});