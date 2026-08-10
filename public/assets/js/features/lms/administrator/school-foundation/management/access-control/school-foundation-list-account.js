function paginateSchoolFoundationAccessControl(page = 1) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container) return;
    if (!role) return;
    if (!schoolFoundationId) return;

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/access-control/${schoolFoundationId}/paginate`,
        method: 'GET',
        data: {
            page: page
        },

        beforeSend: function () {
            $('#tbody-list-account').html('');
            $('#empty-message-list-account').addClass('hidden');
        },

        success: function (response) {

            let html = '';

            if (response.data.length > 0) {

                response.data.forEach(item => {
                    $('#tbody-list-account').append(`
                        <tr class="text-xs">
                            <td class="border border-gray-300 px-3 py-2">
                                <div class="font-semibold">
                                    ${item.nama_lengkap}
                                </div>

                                <div class="text-xs text-base-content/60">
                                    ${item.user_account.email}
                                </div>
                            </td>

                            <td class="border border-gray-300 px-3 py-2 text-center">
                                ${item.user_account.role}
                            </td>

                            <td class="border text-center border-gray-300">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="hidden peer toggle-status-access-control"
                                        data-id="${item.id}"
                                        ${item.school_foundation_id ? 'checked' : ''} />
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                    </div>
                                        <div
                                        class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                    </div>
                                </label>
                            </td>
                            
                            <td class="border text-center border-gray-300">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="hidden peer toggle-status-account"
                                        data-id="${item.user_id}"
                                        ${item.user_account.status_akun === 'aktif' ? 'checked' : ''} />
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                    </div>
                                        <div
                                        class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                    </div>
                                </label>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-list-account').html(response.links);
                bindPaginationLinks();

                $('#empty-message-list-account').addClass('hidden');
                $('.thead-table-list-account').removeClass('hidden');

            } else {
                $('#empty-message-list-account').removeClass('hidden');
                $('.thead-table-list-account').addClass('hidden');
            }
        },

        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateSchoolFoundationAccessControl();
});

function bindPaginationLinks() {
    $('.pagination-container-list-account').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSchoolFoundationAccessControl(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

// function activate status account
$(document).on('change', '.toggle-status-account', function () {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container) return;
    if (!role) return;
    if (!schoolFoundationId) return;

    const checkbox = $(this);
    let userId = $(this).data('id');
    let status = $(this).is(':checked') ? 'aktif' : 'non-aktif'; // Jika toggle ON maka aktif, kalau OFF maka non-aktif

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/access-control/${schoolFoundationId}/activate-account/${userId}`,
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status_akun: status
        },
        success: function (response) {

            paginateSchoolFoundationAccessControl();
        },
        error: function (xhr) {
            alert('Gagal mengubah status.');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});

// Toggle Status Access Control
$(document).on('change', '.toggle-status-access-control', function () {

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!container) return;
    if (!role) return;
    if (!schoolFoundationId) return;

    const checkbox = $(this);
    const profileId = checkbox.data('id');

    $.ajax({
        url: `/lms/${role}/school-foundation/manage/access-control/${schoolFoundationId}/toggle-access/${profileId}`,
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function (response) {
            paginateSchoolFoundationAccessControl();
        },

        error: function () {

            // Kembalikan posisi toggle jika gagal
            checkbox.prop('checked', !checkbox.prop('checked'));

            alert(response?.responseJSON?.message ?? 'Gagal mengubah status akses.');
        }
    });

});

$('#btn-open-create-foundation-access').on('click', function () {
    document.getElementById('modal-add-user-method').showModal();
});

$(document).on('click', '#btn-create-foundation-user', function () {
    $('#modal-add-user-method')[0].close();
    document.getElementById('modal-create-foundation-user').showModal();
});

$('#btn-assign-foundation-user').on('click', function () {
    $('#modal-add-user-method')[0].close();
    $('#modal-assign-foundation-user')[0].showModal();

    loadExistingAccounts();
});