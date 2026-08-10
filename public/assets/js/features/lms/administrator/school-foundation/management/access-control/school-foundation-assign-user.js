function existingAccountCard(item) {
    return `
        <div class="rounded-xl border border-gray-300 p-3 sm:p-4 transition-all hover:border-primary hover:shadow-sm">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <!-- User Information -->
                <div class="flex min-w-0 items-start gap-3 flex-1">

                    <!-- Avatar -->
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                        <i class="fa-solid fa-user text-primary text-sm"></i>
                    </div>

                    <!-- Detail -->
                    <div class="min-w-0 flex-1">

                        <h3 class="text-sm font-semibold wrap-break-word sm:truncate">
                            ${item.nama_lengkap}
                        </h3>

                        <p class="mt-0.5 truncate text-xs text-base-content/60">
                            ${item.user_account.email}
                        </p>

                        <span class="badge badge-outline badge-xs mt-2">
                            ${item.user_account.role}
                        </span>

                    </div>

                </div>

                <!-- Button Desktop -->
                <div class="hidden sm:block shrink-0">
                    <button type="button" class="btn bg-[#0071BC] text-white font-bold btn-sm btn-assign-user" data-id="${item.user_id}">
                        <i class="fa-solid fa-plus"></i>
                        Tambahkan
                    </button>
                </div>

                <!-- Button Mobile -->
                <div class="w-full flex justify-end sm:hidden">
                    <button type="button" class="btn bg-[#0071BC] text-white font-bold btn-xs btn-assign-user w-max" data-id="${item.user_id}">
                        <i class="fa-solid fa-plus"></i>
                        Tambahkan
                    </button>
                </div>
            </div>
        </div>
    `;
}
function loadExistingAccounts(page = 1, keyword = '') {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;

    if (!role || !schoolFoundationId) return;

    $.ajax({

        url: `/lms/${role}/school-foundation/manage/access-control/${schoolFoundationId}/existing-account`,

        method: 'GET',

        data: {
            page: page,
            search: keyword
        },

        beforeSend: function () {

            $('#existing-account-list').html(`
                <div class="flex justify-center py-10">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                </div>
            `);

            $('#empty-existing-account').addClass('hidden');

        },

        success: function (response) {

            $('#existing-account-list').empty();

            if (response.length > 0) {
                response.forEach(item => {

                    $('#existing-account-list').append(
                        existingAccountCard(item)
                    );
                });

                $('#empty-existing-account').addClass('hidden');

            } else {
                $('#empty-existing-account').removeClass('hidden');
            }

            if (response.links) {

                if ($('#existing-account-pagination').length === 0) {

                    $('#existing-account-list').after(`
                        <div
                            id="existing-account-pagination"
                            class="border-t border-gray-200 px-6 py-4">
                        </div>
                    `);

                }

                $('#existing-account-pagination').html(response.links);

                bindExistingAccountPagination();
            }
        },

        error: function (xhr) {
            console.log(xhr);

            $('#existing-account-list').html('');
            $('#empty-existing-account').removeClass('hidden');
        }
    });
}

$(document).on('click', '.btn-assign-user', function () {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolFoundationId = container.dataset.schoolFoundationId;
    const userId = $(this).data('id');

    const button = $(this);

    $.ajax({

        url: `/lms/${role}/school-foundation/manage/access-control/${schoolFoundationId}/assign-user/${userId}`,

        method: 'PUT',

        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        beforeSend: function () {

            button.prop('disabled', true);

            button.html(`
                <span class="loading loading-spinner loading-sm"></span>
                Menambahkan...
            `);

        },

        success: function (response) {
            loadExistingAccounts(1, $('#search-existing-account').val());
            paginateSchoolFoundationAccessControl();
        },

        error: function (xhr) {

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.'
            });

        },

        complete: function () {
            button.prop('disabled', false);
            button.html(`
                <i class="fa-solid fa-plus"></i>
                Tambahkan
            `);
        }
    });
});