function teacherDailyAgendaForm() {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const dayOfWeek = container.dataset.dayOfWeek;
    const classId = container.dataset.classId;
    const subjectId = container.dataset.subjectId;

    if (!role || !schoolName || !schoolId || !dayOfWeek || !classId || !subjectId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/daily-agenda/${dayOfWeek}/class/${classId}/subject-teacher/${subjectId}/form-detail`,
        method: 'GET',
        beforeSend: function () {
            $('#daily-agenda-header').addClass('hidden');
            $('#daily-agenda-header-skeleton').removeClass('hidden');
        },
        success: function (response) {
            $('#daily-agenda-header-skeleton').addClass('hidden');

            $('#daily-agenda-header').removeClass('hidden');

            const data = response.data;

            const formatDate = (dateString) => {
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                const date = new Date(dateString);

                const dayName = days[date.getDay()];
                const day = String(date.getDate()).padStart(2, '0');
                const monthName = months[date.getMonth()];
                const year = date.getFullYear();

                return `${dayName}, ${day} ${monthName} ${year}`;
            };

            // Format date
            const formatedDate = data.date ? `${formatDate(data.date)}` : 'Tanggal tidak tersedia';

            $('#agenda-date').text(formatedDate);

            $('#agenda-time').text(`${data.start_time} - ${data.end_time} (${data.total_session} JP)`);

            $('#agenda-class').text(data.rombel_class);

            $('#agenda-subject').text(data.subject_name);

            $('#learning_activity').text(data.learning_activity);

            if (data.status === 'submitted') {

                $('#agenda-status').removeClass('bg-amber-400/20 text-amber-200').addClass('bg-green-500/20 text-green-200').html(`
                    <i class="fa-solid fa-circle-check"></i>
                    Agenda sudah disimpan
                `);

            } else {

                $('#agenda-status').removeClass('bg-green-500/20 text-green-200').addClass('bg-amber-400/20 text-amber-200').html(`
                    <i class="fa-solid fa-circle-exclamation"></i>
                    Agenda belum disimpan
                `);

            }

        },
        error: function (xhr, status, error) {
            $('#daily-agenda-skeleton').addClass('hidden');
            $('#daily-agenda-list').show();
            console.log(error);
        }
    });
}

$(document).ready(function () {
    teacherDailyAgendaForm();
});

let isProcessing = false;

// Form Action daily agenda
$('#submit-button-create-daily-agenda').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-learning-activity').text('');

    const form = $('#create-daily-agenda-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const dayOfWeek = container.dataset.dayOfWeek;
    const classId = container.dataset.classId;
    const subjectId = container.dataset.subjectId;

    if (!role || !schoolName || !schoolId || !dayOfWeek || !classId || !subjectId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/daily-agenda/${dayOfWeek}/class/${classId}/subject-teacher/${subjectId}/form-detail/submit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-daily-agenda').html(
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

            teacherDailyAgendaForm();

            setTimeout(function () {
                document.getElementById('alertSuccess').remove();
            }, 3000);

            document.getElementById('btnClose').addEventListener('click', function () {
                document.getElementById('alertSuccess').remove();
            });

            $('#create-daily-agenda-form')[0].reset();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-daily-agenda-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input
                    $('#create-daily-agenda-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else if (xhr.status === 409) {

                $('#error-learning_activity').text(xhr.responseJSON.message);

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});