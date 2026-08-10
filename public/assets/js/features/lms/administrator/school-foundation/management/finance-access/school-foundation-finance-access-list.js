function paginateSchoolFoundationFinanceAccessLink() {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container || !role || !schoolFoundationId) return;

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/finance-access-control/${schoolFoundationId}/paginate`,
        method: 'GET',
        beforeSend: function () {
            $('#tbody-school-foundation-finance-access-link').empty();
            $('#table-loading').removeClass('hidden');
            $('#table-content').addClass('hidden');
            $('#empty-message-school-foundation-finance-access-link').addClass('hidden');
            $('.pagination-container-school-foundation-finance-access-link').empty();
        },
        success: function (response) {
            $('#table-loading').addClass('hidden');
            $('#tbody-school-foundation-finance-access-link').empty();

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {
                    $('#tbody-school-foundation-finance-access-link').append(`
                        <tr class="text-xs">
                            <td class="border border-gray-300 px-3 py-2 text-center w-2">
                                ${index + 1}
                            </td>

                            <td class="border border-gray-300 px-3 py-2 w-2">
                                ${item.school_partner?.nama_sekolah ?? '-'}
                            </td>

                            <td class="border border-gray-300 px-4 py-2 text-center">
                                ${item.link ? `
                                    <a href="${item.link}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-medium 
                                        text-blue-600 hover:text-blue-700 hover:underline">
                                        <i class="fa-brands fa-google-drive"></i>
                                        <span>Buka File</span>
                                    </a>
                                    ` : `
                                        <span class="text-xs text-slate-400">
                                            Belum tersedia
                                        </span>
                                    `
                                }
                            </td>

                            <td class="border border-gray-300 px-3 py-2 text-center w-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="hidden peer toggle-status-access-control"
                                        data-id="${item.id}"
                                        ${item.status_access ? 'checked' : ''} />
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                    </div>
                                        <div
                                        class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out 
                                        peer-checked:translate-x-5">
                                    </div>
                                </label>
                            </td>

                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <button type="button" class="edit-finance-access-link  inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-sm font-medium text-blue-600
                                    hover:text-blue-700 hover:bg-blue-50 transition-colors cursor-pointer" data-id="${item.id}" data-school-partner-id="${item.school_partner_id}"
                                    data-school="${item.school_partner?.nama_sekolah ?? '-'}"
                                    data-link="${item.link ?? ''}">
                                    
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                $('#table-content').removeClass('hidden');
                $('#empty-message-school-foundation-finance-access-link').addClass('hidden');
                $('.thead-table-school-foundation-finance-access-link').removeClass('hidden');
                $('.thead-table-school-foundation-finance-access-link').removeClass('hidden');

            } else {
                $('#table-content').addClass('hidden');
                $('.thead-table-school-foundation-finance-access-link').addClass('hidden');
                $('#empty-message-school-foundation-finance-access-link').removeClass('hidden');
            }
        },
        error: function (err) {
            $('#table-loading').addClass('hidden');
            $('#table-content').addClass('hidden');
            $('#tbody-school-foundation-finance-access-link').empty();
            $('.pagination-container-school-foundation-finance-access-link').empty();
            $('#empty-message-school-foundation-finance-access-link').removeClass('hidden');

            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateSchoolFoundationFinanceAccessLink();
});

// function activate status access control
$(document).on('change', '.toggle-status-access-control', function () {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container) return;
    if (!role) return;
    if (!schoolFoundationId) return;

    const checkbox = $(this);
    let linkId = $(this).data('id');
    let status = $(this).is(':checked') ? 1 : 0; // Jika toggle ON maka 1, kalau OFF maka 0

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/finance-access-control/${schoolFoundationId}/status-access-control/activate/${linkId}`,
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status_access: status
        },
        success: function (response) {
            paginateSchoolFoundationFinanceAccessLink();
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});

// Event listener tombol "edit link" (open modal)
$(document).off('click', '.edit-finance-access-link').on('click', '.edit-finance-access-link', function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const schoolPartnerId = $(this).data('school-partner-id');
        const schoolName = $(this).data('school');
        const link = $(this).data('link');

        $('#edit-finance-access-link-id').val(id);
        $('#edit-finance-school-partner-id').val(schoolPartnerId);
        $('#edit-finance-school-name').text(schoolName);
        $('#edit-finance-link').val(link || '');

        const modal = document.getElementById('my_modal_2');

        if (modal) {
            modal.showModal();
        }
    });

$('#submit-button-edit-finance-access-link').on('click', function (e) {
    e.preventDefault();
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container) return;
    if (!role) return;
    if (!schoolFoundationId) return;

    const form = $('#edit-finance-access-link-form')[0];
    const formData = new FormData(form);

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const linkId = $('#edit-finance-access-link-id').val();

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/finance-access-control/${schoolFoundationId}/status-access-control/edit/${linkId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,

        success: function (response) {

            // Tutup modal
            const modal = document.getElementById('my_modal_2');

            if (modal) {
                modal.close();
            }

            $('#alert-success-edit-finance-access-link').html(`
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
            `);

            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function () {
                $('#alertSuccess').remove();
            });

            // Reset form
            $('#edit-finance-access-link-form')[0].reset();

            // Reload data
            paginateSchoolFoundationFinanceAccessLink();

            isProcessing = false;
            btn.prop('disabled', false);
        },

        error: function (xhr) {

            isProcessing = false;
            btn.prop('disabled', false);

            if (xhr.status === 422) {

                const response = xhr.responseJSON;
                $.each(response.errors, function (field, messages) {

                    const errorText = $(`#error-${field}`);

                    errorText.text(messages[0]);

                    $(`[name="${field}"]`).addClass('!border-red-400');
                });

                return;
            }

            alert('Terjadi kesalahan saat mengirim data.');
        }
    });
});