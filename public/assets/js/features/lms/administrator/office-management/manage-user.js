let currentPage = 1;
function paginateManageUser(page = 1, search_account) {
    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    currentPage = page;

    $.ajax({
        url: `/lms/${role}/office-management/manage-user/paginate`,
        method: 'GET',
        data: {
            page: page,
            search_account: search_account
        },
        beforeSend: function () {
            $('#loading-manage-user').show();
            $('#table-list-manage-user').empty();
            $('.pagination-container-manage-user').empty();
            $('#empty-message-manage-user').hide();
            $('.thead-table-manage-user').hide();
        },
        success: function (response) {
            $('#loading-manage-user').hide();
            $('#table-list-manage-user').empty();
            $('.pagination-container-manage-user').empty();

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

                    $('#table-list-manage-user').append(`
                        <tr class="text-xs bg-gray-100">
                            <td class="px-4 py-4 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>

                            <!-- USER -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-user text-blue-500"></i>
                                    </div>

                                    <div>
                                        <h2 class="font-semibold text-gray-800">
                                            ${item.nama_lengkap ?? '-'}
                                        </h2>

                                        <!-- EMAIL AKUN -->
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            ${item.user_account?.email ?? '-'}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <!-- PHONE -->
                            <td class="text-center px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-700">
                                        ${item.user_account?.no_hp ?? '-'}
                                    </span>
                                </div>
                            </td>

                            <!-- ROLE -->
                            <td class="text-center px-4 py-4">
                                <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-600 text-xs font-semibold">
                                    ${item.user_account?.role ?? '-'}
                                </span>
                            </td>

                            <!-- STATUS TOGGLE -->
                            <td class="text-center px-4 py-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="hidden peer toggle-account-status"
                                        data-id="${item.user_account?.id}"
                                        ${item.user_account?.status_akun === 'aktif' ? 'checked' : ''} />
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                    </div>
                                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out 
                                        peer-checked:translate-x-5">
                                    </div>
                                </label>
                            </td>

                            <!-- ACTION -->
                            <td class="px-4 py-4 rounded-r-2xl">
                                <div class="flex justify-center gap-2">
                                    <button data-user-account-id="${item.user_account?.id}" data-nama-lengkap="${item.nama_lengkap}" data-email="${item.user_account?.email}" 
                                        data-no-hp="${item.user_account?.no_hp}" data-role="${item.user_account?.role}"
                                        class="btn-edit-manage-user w-10 h-10 rounded-full bg-yellow-100 hover:bg-yellow-200 text-yellow-600 transition-all cursor-pointer">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-manage-user').html(response.links);
                bindPaginationLinks();
                $('#empty-message-manage-user').hide();
                $('.thead-table-manage-user').show();
            } else {
                $('#empty-message-manage-user').show();
                $('.thead-table-manage-user').hide();
            }
        },
        error: function (xhr, status, error) {
            $('#loading-manage-user').hide();
            console.error('Terjadi kesalahan:', status, error);
        }
    });
}

function bindPaginationLinks() {
    $('.pagination-container-manage-user').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateManageUser(page, $('#search_account').val()); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateManageUser();
});

// FILTERING SEARCH ACCOUNT
$(document).on('input', '#search_account', function () {
    paginateManageUser(1, $('#search_account').val());
});

let isProcessing = false;

// Form Action create manage user
$('#submit-button-create-manage-user').on('click', function (e) {
    e.preventDefault();

    const form = $('#create-manage-user-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/office-management/manage-user/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_create');

            if (modal) modal.close();

            $('#alert-success-insert-data-manage-user').html(
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
            $('#create-manage-user-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateManageUser(currentPage, $('#search_account').val());

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const res = xhr.responseJSON;

                const errors = res.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-manage-user-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-manage-user-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit manage user" (open modal)
$(document).off('click', '.btn-edit-manage-user').on('click', '.btn-edit-manage-user', function (e) {
    e.preventDefault();

    const userAccountId = $(this).data('user-account-id');
    const namaLengkap = $(this).data('nama-lengkap');
    const email = $(this).data('email');
    const noHp = $(this).data('noHp');
    const role = $(this).data('role');

    // set value ke form
    $('#edit-user_account_id').val(userAccountId);
    $('#edit-nama_lengkap').val(namaLengkap);
    $('#edit-email').val(email);
    $('#edit-no_hp').val(noHp);
    $('#edit-role').val(role);

    // buka modal
    const modal = document.getElementById('my_modal_edit');

    if (modal) modal.showModal();
});
// form edit manage user
$('#submit-button-edit-manage-user').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-manage-user-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const userAccountId = $('#edit-user_account_id').val();

    const container = document.getElementById('container');
    const role = container.dataset.role;

    if (!container) return;
    if (!role) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/office-management/manage-user/edit/${userAccountId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Menutup modal
            const modal = document.getElementById('my_modal_edit');
            if (modal) {
                modal.close();

                $('#alert-success-edit-data-manage-user').html(
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

                $('#edit-manage-user-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateManageUser(currentPage, $('#search_account').val());

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const res = xhr.responseJSON;
                const errors = res.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-manage-user-form').find(`#error-edit-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-manage-user-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// function activate user
$(document).ready(function () {
    $(document).on('change', '.toggle-account-status', function () {
        const container = document.getElementById('container');
        const role = container.dataset.role;

        if (!container) return;
        if (!role) return;

        let userAccountId = $(this).data('id'); // Ambil ID user account dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 'aktif' : 'non-aktif'; // Jika toggle ON maka aktif, kalau OFF maka non-aktif

        $.ajax({
            url: `/lms/${role}/office-management/manage-user/activate-account/${userAccountId}`, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status_akun: status // Kirim status baru (aktif/non-aktif)
            },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                paginateManageUser(currentPage);
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});