function assessmentTypeManagement(page = 1) {
    const container = document.getElementById('container-assessment-type-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    fetchData(schoolName, schoolId);

    function fetchData() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/assessment-type-management/paginate`,
            method: 'GET',
            data: {
                page: page
            },
            success: function (response) {
                $('#table-list-assessment-type-management').empty();
                $('.pagination-container-assessment-type-management').empty();

                const schoolDetailCard = document.getElementById('school-detail-card');
                const schoolIdentity = response.schoolIdentity;

                schoolDetailCard.innerHTML = `
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

                            <!-- KIRI : ICON + NAMA SEKOLAH -->
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#EEF6FF] flex items-center justify-center text-[#0071BC] text-2xl shadow-sm">
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-gray-800 leading-tight">
                                        ${schoolIdentity.nama_sekolah}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Detail langganan LMS sekolah
                                    </p>
                                </div>
                            </div>

                            <!-- KANAN : INFO SEKOLAH -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full lg:w-auto">

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NPSN</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.npsn}</p>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NIK Kepala Sekolah</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.user_account?.school_staff_profile?.nik}</p>
                                </div>

                                <div class="bg-[#EEF6FF] rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-[#0071BC] mb-1">Total Pengguna</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${response.countUsers}</p>
                                </div>

                            </div>
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

                        const updatedAt = item.updated_at ? `${formatDate(item.updated_at)}` : '-';

                        $('#table-list-assessment-type-management').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.name ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.assessment_mode ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.is_remedial_allowed == true ? 'Ya' : 'Tidak' ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.max_remedial_attempt ? item.max_remedial_attempt + ` Kesempatan` : '-'}</td>
                                <td class="border text-center border-gray-300">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-activate-assessment-type"
                                            data-id="${item.id}"
                                            ${item.is_active == true ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                            <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <div class="dropdown dropdown-left">
                                        <div tabindex="0" role="button">
                                            <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                        </div>
                                        <ul tabindex="0"
                                            class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                            <li>
                                                <a href="#" class="btn-edit-assessment-type" data-assessment-type-id="${item.id}" data-assessment-type-name="${item.name}"
                                                    data-assessment-mode="${item.assessment_mode}" data-is-remedial-allowed="${item.is_remedial_allowed}" 
                                                    data-max-remedial-attempt="${item.max_remedial_attempt}">
                                                    <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                                    Edit Assessment Type
                                                </a>
                                            </li>
                                            <li onclick="historyAssessmentType(this)" class="btn-history-assessment-type"
                                                data-assessment-type-id="${item.id}"
                                                data-nama_lengkap="${item.user_account?.office_profile?.nama_lengkap || item.user_account?.school_staff_profile?.nama_lengkap}"
                                                data-role="${item.user_account?.role ?? '-'}"
                                                data-updated_at="${updatedAt}">
                                                <span>
                                                    <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                                                    History Assessment Type
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });

                    $('#school-detail-card').show();
                    $('.pagination-container-assessment-type-management').html(response.links);
                    bindPaginationLinks();
                    $('#empty-message-assessment-type-management').hide();
                    $('.thead-table-assessment-type-management').show();
                } else {
                    $('#school-detail-card').show();
                    $('#empty-message-assessment-type-management').show();
                    $('.thead-table-assessment-type-management').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('Terjadi kesalahan:', status, error);
            }
        });
    }
}

function bindPaginationLinks() {
    $('.pagination-container-assessment-type-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        assessmentTypeManagement(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    assessmentTypeManagement();
    
    bindRemedialToggle({
        selectId: 'is_remedial_allowed',
        wrapperId: 'max-remedial-wrapper',
        inputId: 'max_remedial_attempt',
        mode: 'create'
    });
});

// open modal history assessment type
function historyAssessmentType(element) {
    const namaLengkap = element.dataset.nama_lengkap;
    const role = element.dataset.role;
    const updatedAt = element.dataset.updated_at;

    // BASIC INFO
    document.getElementById('text-nama_lengkap').innerText = namaLengkap;
    document.getElementById('text-role').innerText = role;
    document.getElementById('text-updated_at').innerText =
        updatedAt ? `Terakhir diperbarui: ${updatedAt}` : '';

    document.getElementById('my_modal_2').showModal();
}

let isProcessing = false;

// Form Action create assessment type
$('#submit-button-create-assessment-type').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-name').text('');

    const form = $('#create-assessment-type-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container-assessment-type-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/assessment-type-management/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-assessment-type').html(
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

            // reset form
            $('#create-assessment-type-form')[0].reset();

            // pastikan select kembali ke default
            $('#is_remedial_allowed').val('');

            // sembunyikan wrapper
            $('#max-remedial-wrapper').addClass('hidden');

            // Memanggil fungsi untuk memuat ulang data
            assessmentTypeManagement();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-assessment-type-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-assessment-type-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit assessment type" (open modal)
$(document).off('click', '.btn-edit-assessment-type').on('click', '.btn-edit-assessment-type', function (e) {
    e.preventDefault();

    const assessmentTypeId = $(this).data('assessment-type-id');
    const assessmentTypeName = $(this).data('assessment-type-name');
    const assessmentMode = $(this).data('assessment-mode');
    const isRemedialAllowed = $(this).data('is-remedial-allowed');
    const maxRemedialAttempt = $(this).data('max-remedial-attempt');

    // set value ke form
    $('#edit-assessment-type-id').val(assessmentTypeId);
    $('#edit-assessment-type-name').val(assessmentTypeName);
    $('#edit-assessment-mode').val(assessmentMode);
    $('#edit-is-remedial-allowed').val(isRemedialAllowed);

    const input = $('#edit-max-remedial-attempt');

    if (isRemedialAllowed == 1) {
        if (input) {
            input.val(maxRemedialAttempt);
            input.data('saved', maxRemedialAttempt); // SIMPAN NILAI DB
        }
    } else {
        if (input) {
            input.val(1);
            input.data('saved', 1); // SIMPAN NILAI DB
        }
    }

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();

    bindRemedialToggle({
        selectId: 'edit-is-remedial-allowed',
        wrapperId: 'edit-max-remedial-wrapper',
        inputId: 'edit-max-remedial-attempt',
        mode: 'edit'
    });
});

// form edit assessment type
$('#submit-button-edit-assessment-type').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-assessment-type-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const assessmentTypeId = $('#edit-assessment-type-id').val();

    const container = document.getElementById('container-assessment-type-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/assessment-type-management/${assessmentTypeId}/edit`,
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

                $('#alert-success-edit-data-assessment-type').html(
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

                $('#edit-assessment-type-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                assessmentTypeManagement();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-assessment-type-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-assessment-type-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// function activate mapel
$(document).ready(function () {
    $(document).on('change', '.toggle-activate-assessment-type', function () {
        let assessmentTypeId = $(this).data('id'); // Ambil ID mapel dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 1 : 0; // Jika toggle ON maka 1, kalau OFF maka 0

        const container = document.getElementById('container-assessment-type-management');
        const schoolName = container.dataset.schoolName;
        const schoolId = container.dataset.schoolId;

        if (!container) return;
        if (!schoolName) return;
        if (!schoolId) return;

        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/assessment-type-management/${assessmentTypeId}/activate`, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                is_active: status // Kirim status baru (true / false)
            },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                assessmentTypeManagement();
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});

// function show or hide max remedial attempt
function bindRemedialToggle({ selectId, wrapperId, inputId, mode }) {
    const select = document.getElementById(selectId);
    const wrapper = document.getElementById(wrapperId);
    const input = document.getElementById(inputId);

    if (!select || !wrapper || !input) return;

    function toggle() {
        if (select.value === '1') {
            wrapper.classList.remove('hidden');
            input.required = true;

            if (mode === 'edit') {
                input.value = $(input).data('saved') ?? 1;
            }
        } else {
            wrapper.classList.add('hidden');
            input.required = false;
        }
    }

    select.addEventListener('change', toggle);
    toggle();
}