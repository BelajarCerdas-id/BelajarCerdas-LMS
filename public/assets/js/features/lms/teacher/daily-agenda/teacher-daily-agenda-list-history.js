function paginateTeacherDailyAgendaManagementHistory(search_date = null, search_status = null, loadSummary = false, page = 1) {
    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    $.ajax({

        url: `/lms/${role}/${schoolName}/${schoolId}/daily-agenda/history/paginate`,

        method: 'GET',

        data: {
            search_date,
            search_status,
            page: page
        },

        beforeSend: function () {

            if (loadSummary) {
                $('#teacher-agenda-summary-skeleton').removeClass('hidden');
                $('#teacher-agenda-summary').addClass('hidden');
            }

            $('#teacher-daily-agenda-history-skeleton').removeClass('hidden');
            $('#table-teacher-daily-agenda-history').addClass('hidden');
            $('.pagination-teacher-agenda-history').addClass('hidden');
            $('#empty-message-teacher-daily-agenda-history').addClass('hidden');

        },

        success: function (response) {

            if (loadSummary) {
                renderTeacherAgendaKPIHistory(response.summary);
            }

            $('#teacher-daily-agenda-history-skeleton').addClass('hidden');
            $('#table-teacher-daily-agenda-history').removeClass('hidden');

            $('#tbody-teacher-daily-agenda-history').empty();
            $('.pagination-container-teacher-daily-agenda-history').empty();

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {

                    let attendance_status = '';

                    if (item.attendance) {
                        attendance_status = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Sudah Absen
                            </span>
                        `;
                    } else {
                        attendance_status = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                Belum Absen
                            </span>
                        `;
                    }

                    let agenda_status = '';

                    if (item.agenda) {
                        agenda_status = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Sudah Mengisi
                            </span>
                        `;
                    } else {
                        agenda_status = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                Belum Mengisi
                            </span>
                        `;
                    }

                    let learning_activity = '';

                    if (item.learning_activity) {
                        learning_activity = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Sudah Mengisi
                            </span>
                        `;
                    } else {
                        learning_activity = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                Belum Mengisi
                            </span>
                        `;
                    }

                    let feedback_status = '';

                    if (item.feedback) {
                        feedback_status = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Sudah Diberikan
                            </span>
                        `;
                    } else {
                        feedback_status = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                Belum Diberikan
                            </span>
                        `;
                    }

                    const formatDate = (dateString) => {
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        const dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${dayName[date.getDay()]}, ${day}-${monthName}-${year}`;
                    };

                    // Format tanggal mulai dan akhir
                    const meetingDate = item.meeting_date ? `${formatDate(item.meeting_date)}` : 'Tanggal tidak tersedia';

                    $('#tbody-teacher-daily-agenda-history').append(`
                        <tr>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${meetingDate ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${item.teacher_name ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${item.subject ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${item.school_class_name ?? '-'} - ${item.school_year ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${item.time ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${attendance_status ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${agenda_status ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${learning_activity ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                ${feedback_status ?? '-'}
                            </td>
                            <td class="px-2 py-2 text-sm font-medium text-slate-700">
                                <button
                                    onclick="openTeacherAgendaDetail(this)"
                                    data-teacher-agenda-id="${item.teacher_agenda_id}"
                                    data-name="${item.teacher_name}"
                                    data-school-class-name="${item.school_class_name}"
                                    data-subject="${item.subject}"
                                    data-date="${meetingDate}"
                                    data-learning-activity="${item.learning_activity}"
                                    data-feedback="${item.feedback ?? ''}"
                                    data-feedback-status="${item.feedback ? 'reviewed' : 'pending'}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition 
                                        hover:bg-slate-100 cursor-pointer">

                                    <i class="fa-solid fa-eye"></i>
                                    Detail

                                </button>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-teacher-daily-agenda-history').html(response.links);
                bindPaginationLinks();
                $('#empty-message-teacher-daily-agenda-history').hide(); // sembunyikan pesan kosong
                $('.thead-table-teacher-daily-agenda-history').show(); // Tampilkan tabel thead

            } else {
                $('#tbody-teacher-daily-agenda-history').empty(); // Clear existing rows
                $('.thead-table-teacher-daily-agenda-history').hide(); // Tampilkan tabel thead
                $('#empty-message-teacher-daily-agenda-history').show();
            }
        },

        error: function (error) {

            if (loadSummary) {
                $('#teacher-agenda-summary-skeleton').addClass('hidden');
                $('#teacher-agenda-summary').removeClass('hidden');
            }

            $('#teacher-daily-agenda-history-skeleton').addClass('hidden');
            $('#table-teacher-daily-agenda-history').removeClass('hidden');

            console.log(error);
        }
    });
}

$(document).ready(function () {

    paginateTeacherDailyAgendaManagementHistory(null, null, true, 1);

    flatpickr("#search_date", {

        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-m-Y",
        disableMobile: true,
        allowInput: false,
        clickOpens: true,
        monthSelectorType: "dropdown",

        onChange(selectedDates, dateStr) {
            paginateTeacherDailyAgendaManagementHistory(dateStr, $('#search_status').val(), false, 1);
        }
    });
});


function bindPaginationLinks() {
    $('.pagination-container-teacher-daily-agenda-history').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_date = $('#search_date').val();
        const search_teacher = $('#search_teacher').val();
        const search_status = $('#search_status').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateTeacherDailyAgendaManagementHistory(search_date, search_teacher, search_status, false, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$('#search_status').on('change', function () {
    paginateTeacherDailyAgendaManagementHistory($('#search_date').val(), $(this).val(), false, 1);
});

function openTeacherAgendaDetail(element) {

    const teacherAgendaId = $(element).data('teacher-agenda-id');
    const name = $(element).data('name');
    const schoolClassName = $(element).data('school-class-name');
    const subject = $(element).data('subject');
    const date = $(element).data('date');
    const learningActivity = $(element).data('learning-activity');
    const feedback = $(element).data('feedback');
    const feedbackStatus = $(element).data('feedback-status');

    $('#detail_teacher_agenda_id').val(teacherAgendaId);

    $('#detail_teacher_name').text(name || '-');
    $('#detail_subject').text(subject || '-');
    $('#detail_class').text(schoolClassName || '-');
    $('#detail_date').text(date || '-');

    // Learning Activity
    if (learningActivity) {

        $('#detail_learning_activity').html(learningActivity);

    } else {

        $('#detail_learning_activity').html(`
            <span class="italic text-slate-400">
                Belum ada uraian kegiatan pembelajaran.
            </span>
        `);

    }

    // Status Review
    if (feedbackStatus === 'reviewed') {

        $('#detail_status_badge')
            .removeClass()
            .addClass('inline-flex items-center gap-2 rounded-full bg-green-100 px-4 py-2 text-sm font-medium text-green-700')
            .html(`
                <i class="fa-solid fa-circle-check"></i>
                Sudah Direview
            `);

    } else {

        $('#detail_status_badge')
            .removeClass()
            .addClass('inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-medium text-amber-700')
            .html(`
                <i class="fa-solid fa-clock"></i>
                Menunggu Review
            `);

    }

    // Feedback
    if (feedback) {

        $('#detail_feedback_card')
            .removeClass('border-slate-200 bg-slate-50')
            .addClass('border-amber-200 bg-amber-50');

        $('#detail_feedback').html(feedback);

    } else {

        $('#detail_feedback_card')
            .removeClass('border-amber-200 bg-amber-50')
            .addClass('border-slate-200 bg-slate-50');

        $('#detail_feedback').html(`
            <span class="italic text-slate-400">
                Kepala sekolah belum memberikan review untuk agenda pembelajaran ini.
            </span>
        `);

    }

    document.getElementById('my_modal_1').showModal();
}