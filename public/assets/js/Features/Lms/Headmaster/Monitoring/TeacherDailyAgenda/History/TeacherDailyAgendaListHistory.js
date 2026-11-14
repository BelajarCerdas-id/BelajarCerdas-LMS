function paginateTeacherDailyAgendaManagementHistory(search_date = null, search_teacher = null, search_status = null, search_feedback = null, loadSummary = false, page = 1) {
    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    $.ajax({

        url: `/${role}/${schoolName}/${schoolId}/headmaster/agenda-harian-guru/histori/paginate`,

        method: 'GET',

        data: {
            search_date,
            search_teacher,
            search_status,
            search_feedback,
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

            const teacherSelect = $('#search_teacher');

            const selectedTeacher = teacherSelect.val();

            teacherSelect.empty();

            teacherSelect.append(`
                <option value="">
                    Semua Guru
                </option>
            `);

            $.each(response.teachers, function (index, teacher) {

                teacherSelect.append(`
                    <option value="${teacher.id}"
                        ${selectedTeacher == teacher.id ? 'selected' : ''}>
                        ${teacher.name}
                    </option>
                `);

            });

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
                                    data-feedback="${item.feedback}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 
                                    transition hover:bg-slate-100 cursor-pointer">

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

    paginateTeacherDailyAgendaManagementHistory(null, null, null, null, true, 1);

    flatpickr("#search_date", {

        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-m-Y",
        disableMobile: true,
        allowInput: false,
        clickOpens: true,
        monthSelectorType: "dropdown",

        onChange(selectedDates, dateStr) {
            paginateTeacherDailyAgendaManagementHistory(dateStr, $('#search_teacher').val(), $('#search_status').val(), $('#search_feedback').val(), false, 1);
        }
    });
});


function bindPaginationLinks() {
    $('.pagination-container-teacher-daily-agenda-history').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_date = $('#search_date').val();
        const search_teacher = $('#search_teacher').val();
        const search_status = $('#search_status').val();
        const search_feedback = $('#search_feedback').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateTeacherDailyAgendaManagementHistory(search_date, search_teacher, search_status, search_feedback, false, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$('#search_teacher').on('change', function () {
    paginateTeacherDailyAgendaManagementHistory($('#search_date').val(), $(this).val(), $('#search_status').val(), $('#search_feedback').val(), false, 1);
});

$('#search_status').on('change', function () {
    paginateTeacherDailyAgendaManagementHistory($('#search_date').val(), $('#search_teacher').val(), $(this).val(), $('#search_feedback').val(), false, 1);
});

$('#search_feedback').on('change', function () {
    paginateTeacherDailyAgendaManagementHistory($('#search_date').val(), $('#search_teacher').val(), $('#search_status').val(), $(this).val(), false, 1);
});

function openTeacherAgendaDetail(element) {
    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    const teacherAgendaId = $(element).data('teacher-agenda-id');
    const name = $(element).data('name');
    const schoolClassName = $(element).data('school-class-name');
    const subject = $(element).data('subject');
    const date = $(element).data('date');
    const learningActivity = $(element).data('learning-activity');
    const feedback = $(element).data('feedback');

    $('#detail_teacher_agenda_id').val(teacherAgendaId);
    $('#detail_teacher_name').text(name);
    $('#detail_subject').text(subject);
    $('#detail_class').text(schoolClassName);
    $('#detail_date').text(date);

    if (learningActivity) {
        $('#detail_learning_activity').html(learningActivity);
    } else {
        $('#detail_learning_activity').html(
            '<span class="text-slate-400 italic">Belum ada uraian kegiatan.</span>'
        );
    }

    if (feedback) {
        $('#feedback').val(feedback);
    } else {
        $('#feedback').val('');
    }

    document.getElementById('my_modal_1').showModal();
}

let isProcessing = false;

// form feedback store
$('#btn-submit-feedback').on('click', function (e) {
    e.preventDefault();

    const form = $('#create-feedback-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return;
    isProcessing = true;

    const teacherAgendaId = $('#detail_teacher_agenda_id').val();

    const btn = $(this);
    btn.prop('disabled', true);

    if (!teacherAgendaId) {
        const modal = document.getElementById('my_modal_1');
        if (modal) modal.close();

        Swal.fire({
            icon: 'info',
            title: 'Belum Bisa Memberikan Feedback',
            text: 'Feedback hanya dapat diberikan setelah guru mengisi agenda.',
        });

        return;
    }

    $.ajax({
        url: `/${role}/${schoolName}/${schoolId}/headmaster/agenda-harian-guru/histori/feedback-store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Menutup modal
            const modal = document.getElementById('my_modal_1');
            if (modal) {
                modal.close();

                $('#alert-success-create-feedback').html(
                    `
                    <div class=" w-full flex justify-center">
                        <div class="fixed z-9999">
                            <div id="alertSuccess"
                                class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-green-600 text-sm">${response.message}</span>
                                <i class="fas fa-times cursor-pointer text-green-600" id="btnClose"></i>
                            </div>
                        </div>
                    </div>
                    `
                );

                setTimeout(function () {
                    document.getElementById('alertSuccess').remove();
                }, 3000);

                document.getElementById('btnClose').addEventListener('click', function () {
                    document.getElementById('alertSuccess').remove();
                });

                setTimeout(() => {
                    paginateTeacherDailyAgendaManagementHistory(
                        $('#search_date').val(),
                        $('#search_teacher').val(),
                        $('#search_status').val(),
                        $('#search_feedback').val(),
                        true,
                        1
                    );
                }, 50);

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const flag = xhr.responseJSON.flag;

                if (flag === 'AGENDA_NOT_FOUND') {
                    const modal = document.getElementById('my_modal_1');
                    if (modal) modal.close();
                    
                    Swal.fire({
                        icon: 'info',
                        title: 'Belum Bisa Memberikan Feedback',
                        text: xhr.responseJSON.message,
                    });
                    return;
                }
                
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-feedback-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-feedback-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});