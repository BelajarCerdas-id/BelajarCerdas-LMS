function paginateContentForRelease() {
    const container = document.getElementById('container-content-for-release-review-meetings');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const schoolClassId = container.dataset.schoolClassId;
    const semester = container.dataset.semester;
    const serviceId = container.dataset.serviceId;

    if (!role || !schoolName || !schoolId || !schoolClassId || !semester || !serviceId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/content-for-release/rombel-kelas/${schoolClassId}/semester/${semester}/service/${serviceId}/review-meetings/paginate`,
        method: 'GET',
        success: function (response) {
            const headerContainer = $('#header-meetings');
            const listContainer = $('#grid-list-meeting-body');
            
            headerContainer.empty();
            listContainer.empty();

            const header = `
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                        <!-- Info -->
                        <div class="space-y-2">
                            <h1 class="text-xl font-semibold text-gray-800">
                                Review Meetings
                            </h1>

                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                <span>${response.schoolClass?.class_name ?? '-'}</span>
                                <i class="fa-solid fa-circle text-[4px]"></i>
                                <span>${response.schoolClass?.tahun_ajaran ?? '-'}</span>
                                <i class="fa-solid fa-circle text-[4px]"></i>
                                <span>Semester ${semester}</span>
                                <i class="fa-solid fa-circle text-[4px]"></i>
                                <span class="font-medium text-[#0071BC]">${response.service?.name ?? '-'}</span>
                            </div>
                        </div>

                    </div>
                </div>
            `;

            headerContainer.append(header);

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

                    // Format tanggal
                    const meetingDate = item.meeting_date ? formatDate(item.meeting_date) : 'Tanggal tidak tersedia';

                    const teacherContentForReleaseReviewContent = response.teacherContentForReleaseReviewContent.replace(':role', role).replace(':schoolName', schoolName)
                        .replace(':schoolId', schoolId).replace(':schoolClassId', schoolClassId).replace(':semester', semester)
                        .replace(':serviceId', serviceId).replace(':meetingContentId', item.id);

                    const MeetingList = `
                        <div class="p-5 hover:bg-gray-50 transition">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                                <div class="space-y-3">
                                    <p class="text-sm font-semibold text-gray-800">
                                        Pertemuan ${item.meeting_number} - ${item.lms_content?.lms_content_item?.[0]?.original_filename ?? '-'}
                                    </p>

                                    <div class="text-xs text-gray-500 flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-days text-[#0071BC] font-bold"></i>
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                        ${meetingDate ?? '-'}
                                    </div>
                                </div>

                            <div class="flex items-center gap-3 mt-2 md:mt-0">

                                <!-- Status Badge -->
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    ${item.is_active ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'} status-badge">
                                    ${item.is_active ? 'Active' : 'Draft'}
                                </span>

                                <!-- Toggle -->
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer toggle-activate-meeting-content" data-meeting-content-id="${item.id}" ${item.is_active ? 'checked' : ''}>
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:bg-blue-600 transition-all duration-300"></div>
                                    <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-all duration-300 peer-checked:translate-x-5"></div>
                                </label>

                                <!-- Edit -->
                                <a href="" data-meeting-content-id="${item.id}" data-semester="${semester}" data-meeting-number="${item.meeting_number}" data-meeting-date="${item.meeting_date}" 
                                    class="btn-edit-meeting text-sm text-yellow-600 font-semibold hover:underline preview-meeting-content">
                                    Edit
                                </a>

                                <!-- Preview -->
                                <a href="${teacherContentForReleaseReviewContent}" class="text-sm text-[#0071BC] font-semibold hover:underline preview-meeting-content">
                                    Preview
                                </a>

                            </div>

                            </div>
                        </div>
                    `;

                    listContainer.append(MeetingList);
                });

                $('#header-meetings').show();
                $('#empty-message-content-for-release-review-meetings').hide();
            } else {
                $('#header-meetings').show();
                $('#empty-message-content-for-release-review-meetings').show();
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateContentForRelease();
});

function enableFlatpickr(el) {
    if (el._flatpickr) {
        el._flatpickr.destroy();
    }

    flatpickr(el, {
        dateFormat: "Y-m-d",
        altInput: false,
        altFormat: "j F, Y",
        appendTo: document.querySelector(".modal-box"),
        position: "below",
        static: true,
        theme: "dark",
        minDate: 'today',
        clickOpens: true, // Mencegah focus otomatis pada input type date
        disableMobile: true, // untuk mencegah datepicker bawaan browser mobile, agar tetap menggunakan Flatpickr meskipun di HP

        onChange: function (selectedDates, dateStr, instance) {
            const el = instance.input;
            el.classList.remove('border-red-400');

            // langsung cari berdasarkan ID, bukan nextElementSibling
            const inputId = el.getAttribute('name');
            const errorMessage = document.querySelector(`#error-${inputId}`);
            if (errorMessage) {
                errorMessage.textContent = '';
            }
        }
    });
}

function disableFlatpickr(el) {
    if (el._flatpickr) {
        el._flatpickr.destroy();
    }
}

$(document).on('change', '.toggle-activate-meeting-content', function () {
    let meetingContentId = $(this).data('meeting-content-id'); // Ambil subscription id dari atribut data-id di checkbox
    let status = $(this).is(':checked') ? 1 : 0; // Jika toggle ON maka 1, kalau OFF maka 0

    const container = document.getElementById('container-content-for-release-review-meetings');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/content-for-release/${meetingContentId}/activate`, // Endpoint ke server
        method: 'PUT', // Method HTTP PUT untuk update data
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            is_active: status // Kirim status baru (true / false)
        },
        success: function (response) {
            // inisialisasi update data terbaru
            paginateContentForRelease();
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});

// Event listener tombol "edit meeting" (open modal)
$(document).off('click', '.btn-edit-meeting').on('click', '.btn-edit-meeting', function (e) {
    e.preventDefault();

    const meetingContentId = $(this).data('meeting-content-id');
    const semester = $(this).data('semester');
    const meetingNumber = $(this).data('meeting-number');
    const meetingDate = $(this).data('meeting-date');

    // set value ke form
    $('#edit-meeting-content-id').val(meetingContentId);
    $('#edit-semester').val(semester);
    $('#edit-meeting-number').val(meetingNumber);
    $('#edit-meeting-date').val(meetingDate);

    // set value ke input type
    $('#edit-semester-name').val(semester);
    $('#edit-pertemuan').val(meetingNumber);
    $('#edit-tanggal-pertemuan').val(meetingDate);

    const input = document.getElementById('edit-tanggal-pertemuan');

    disableFlatpickr(input);
    enableFlatpickr(input);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

let isProcessing = false;

// form edit meeting content
$('#submit-button-edit-meeting-content').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-meeting-content-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container-content-for-release-review-meetings');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const meetingContentId = $('#edit-meeting-content-id').val();

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!meetingContentId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/content-for-release/${meetingContentId}/edit`,
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

                $('#alert-success-edit-meeting-content').html(
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

                $('#edit-meeting-content-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateContentForRelease();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-meeting-content-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-meeting-content-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});
