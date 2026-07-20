function paginateTeacherDailyAgendaManagement(search_date = null, search_teacher = null, search_status = null, loadSummary = false, page = 1) {
    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    $.ajax({

        url: `/${role}/${schoolName}/${schoolId}/headmaster/agenda-harian-guru/paginate`,

        method: 'GET',

        data: {
            search_date,
            search_teacher,
            search_status,
            page: page
        },

        beforeSend: function () {

            if (loadSummary) {
                $('#kpi-loading').removeClass('hidden');
                $('#kpi-content').addClass('hidden');

                $('#teacher-agenda-progress-loading').removeClass('hidden');
                $('#teacher-agenda-progress-content').addClass('hidden');
            }

            $('#teacher-agenda-list-loading').removeClass('hidden');
            $('#teacher-agenda-list-content').addClass('hidden');

        },

        success: function (response) {

            if (loadSummary) {
                renderTeacherAgendaKPI(response.summary);
                renderTeacherAgendaProgress(response.summary);
            }

            $('#teacher-agenda-list-loading').addClass('hidden');
            $('#teacher-agenda-list-content').removeClass('hidden');

            const grid = $('#grid-list-teacher-daily-agenda-management');

            grid.empty();

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

                    let uraian = '';

                    if (item.agenda) {

                        uraian = `
                            <p class="mt-2 text-sm leading-6 text-slate-700 whitespace-pre-line">
                                ${item.learning_activity}
                            </p>
                        `;

                    } else {

                        uraian = `
                            <p class="mt-2 text-sm italic text-red-600">
                                Guru belum mengisi agenda pembelajaran untuk sesi ini.
                            </p>
                        `;

                    }

                    let actions = '';

                    if (item.agenda) {

                        actions = `
                            <button
                                onclick="openTeacherAgendaDetail(this)"
                                data-teacher-agenda-id="${item.teacher_agenda_id}"
                                data-name="${item.teacher_name}"
                                data-school-class-name="${item.school_class_name}"
                                data-subject="${item.subject}"
                                data-learning-activity="${item.learning_activity}"
                                data-feedback="${item.feedback}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 cursor-pointer">

                                <i class="fa-solid fa-eye"></i>
                                Lihat Agenda

                            </button>
                        `;

                    }

                    const card = `
                        <div class="rounded-2xl border ${item.agenda ? 'border-gray-200' : 'border-red-200 bg-red-50/30'} p-6 transition hover:shadow-md">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between md:flex-col xl:flex-row xl:items-start xl:justify-between">
                                <div class="flex flex-1 min-w-0 items-start gap-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl
                                        ${item.agenda ? 'bg-blue-100 text-blue-600' : 'bg-red-100 text-red-600'}">
                                        <i class="fa-solid fa-chalkboard-user text-xl"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <h4 class="text-base sm:text-lg font-semibold text-slate-800">
                                            ${item.teacher_name}
                                        </h4>

                                        <p class="mt-1 text-sm leading-5 text-slate-500 wrap-break-word">
                                            ${item.subject}
                                        </p>

                                        <p class="text-sm text-slate-500">
                                            ${item.school_class_name}
                                        </p>
                                    </div>
                                </div>

                                <!-- Badge -->
                                <div class="flex justify-end md:self-end md:justify-end xl:self-auto">
                                    <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold whitespace-nowrap
                                        ${item.agenda ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">

                                        <span
                                            class="h-2.5 w-2.5 shrink-0 rounded-full
                                            ${item.agenda ? 'bg-green-500' : 'bg-red-500'}">
                                        </span>

                                        ${item.agenda ? 'Sudah Mengisi' : 'Belum Mengisi'}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-4">

                                <div class="rounded-xl bg-slate-50 p-4">

                                    <div class="flex items-center gap-2 text-slate-500">

                                        <i class="fa-solid fa-clock"></i>

                                        <span class="text-sm font-medium">
                                            Jam Mengajar
                                        </span>

                                    </div>

                                    <p class="mt-2 text-sm font-semibold text-slate-800">
                                        ${item.time}
                                    </p>

                                </div>

                                <div class="rounded-xl bg-slate-50 p-4">

                                    <div class="flex items-center gap-2 text-slate-500">

                                        <i class="fa-solid fa-clipboard-check"></i>

                                        <span class="text-sm font-medium">
                                            Absensi Siswa
                                        </span>

                                    </div>

                                    <p class="mt-2 text-sm font-semibold ${item.attendance ? 'text-green-600' : 'text-red-600'}">

                                        ${item.attendance ? 'Sudah Diisi' : 'Belum Diisi'}

                                    </p>

                                </div>

                            </div>

                            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">

                                <div class="flex items-center gap-2 border-b border-gray-300 pb-4 text-slate-500">

                                    <i class="fa-solid fa-book-open"></i>

                                    <span class="text-sm font-medium">
                                        Uraian Kegiatan Belajar Mengajar
                                    </span>

                                </div>

                                ${uraian}

                            </div>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">

                                ${actions}

                            </div>

                        </div>
                    `;

                    grid.append(card);

                });

                $('.pagination-container-teacher-daily-agenda-management').html(response.links);
                bindPaginationLinks();
                $('#empty-message-teacher-daily-agenda-management').hide();

            } else {

                $('#empty-message-teacher-daily-agenda-management').show();
                $('.pagination-container-teacher-daily-agenda-management').html('');

            }

        },

        error: function (error) {

            if (loadSummary) {
                $('#kpi-loading').addClass('hidden');
                $('#kpi-content').removeClass('hidden');

                $('#teacher-agenda-progress-loading').addClass('hidden');
                $('#teacher-agenda-progress-content').removeClass('hidden');
            }

            $('#teacher-agenda-list-loading').addClass('hidden');
            $('#teacher-agenda-list-content').removeClass('hidden');

            console.log(error);

        }

    });

}

$(document).ready(function () {

    paginateTeacherDailyAgendaManagement(null, null, null, true, 1);

    flatpickr("#search_date", {

        defaultDate: "today",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-m-Y",
        disableMobile: true,
        allowInput: false,
        clickOpens: true,
        monthSelectorType: "dropdown",

        onChange(selectedDates, dateStr) {
            paginateTeacherDailyAgendaManagement(dateStr, $('#search_teacher').val(), $('#search_status').val(), false, 1);
        }
    });
});


function bindPaginationLinks() {
    $('.pagination-container-teacher-daily-agenda-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_date = $('#search_date').val();
        const search_teacher = $('#search_teacher').val();
        const search_status = $('#search_status').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateTeacherDailyAgendaManagement(search_date, search_teacher, search_status, false, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$('#search_teacher').on('change', function () {
    paginateTeacherDailyAgendaManagement($('#search_date').val(), $(this).val(), $('#search_status').val(), false, 1);
});

$('#search_status').on('change', function () {
    paginateTeacherDailyAgendaManagement($('#search_date').val(), $('#search_teacher').val(), $(this).val(), false, 1);
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
    const learningActivity = $(element).data('learning-activity');
    const feedback = $(element).data('feedback');

    $('#detail_teacher_agenda_id').val(teacherAgendaId);
    $('#detail_teacher_name').text(name);
    $('#detail_subject').text(subject);
    $('#detail_class').text(schoolClassName);

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
                    paginateTeacherDailyAgendaManagement(
                        $('#search_date').val(),
                        $('#search_teacher').val(),
                        $('#search_status').val(),
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