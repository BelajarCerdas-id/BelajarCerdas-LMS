let isProcessing = false;
// Event listener tombol "create announcement" (open modal)
$(document).off('click', '#btn-create-announcement').on('click', '#btn-create-announcement', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('announcement-modal');
    if (modal) modal.showModal();
});

// Form Action create annoncement
$('#submit-btn-create-announcement').on('click', function (e) {
    e.preventDefault();

    const form = $('#create-announcemenet-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const meetingId = container.dataset.meetingId;
    const semester = container.dataset.semester;

    if (!container) return;
    if (!role || !schoolName || !schoolId || !subjectTeacherId || !meetingId || !semester) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/${meetingId}/semester/${semester}/meeting-management/announcement-store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('announcement-modal');
            if (modal) {
                modal.close();

                $('#alert-success-insert-data-announcement').html(
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
            }

            setTimeout(function () {
                document.getElementById('alertSuccess').remove();
            }, 3000);

            document.getElementById('btnClose').addEventListener('click', function () {
                document.getElementById('alertSuccess').remove();
            });

            // reset form
            $('#create-announcemenet-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateAnnouncementList();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const res = xhr.responseJSON;
                const errors = res.errors;

                $.each(errors, function (field, messages) {

                    const errorEl = $('#create-announcemenet-form').find(`#error-${field}`);

                    errorEl.text(messages[0]).removeClass('hidden');

                    $('#create-announcemenet-form').find(`[name="${field}"]`).removeClass('border-gray-300').addClass('border-red-400');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});