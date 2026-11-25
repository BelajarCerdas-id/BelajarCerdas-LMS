// save per-school anticheat settings on the management role account page
$(document).ready(function () {
    const container = $('#container-anticheat-settings');
    if (!container.length) return;

    $('#btn-save-anticheat-settings').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true);

        const pageData = $('#container-role-account-list').data();
        const role = pageData.role;
        const schoolName = pageData.schoolName;
        const schoolId = pageData.schoolId;

        $.ajax({
            url: `/lms/${role}/school-subscription/${schoolName}/${schoolId}/management-settings/anticheat`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                anticheat_max_strikes: $('#form-anticheat-settings [name="anticheat_max_strikes"]').val(),
                anticheat_auto_lock: $('#form-anticheat-settings [name="anticheat_auto_lock"]').is(':checked') ? 1 : 0,
                anticheat_warning_modal: $('#form-anticheat-settings [name="anticheat_warning_modal"]').is(':checked') ? 1 : 0,
                proctor_auto_admit: $('#form-anticheat-settings [name="proctor_auto_admit"]').is(':checked') ? 1 : 0,
                proctor_auto_start: $('#form-anticheat-settings [name="proctor_auto_start"]').is(':checked') ? 1 : 0,
            },
            success: function (response) {
                $('#alert-success-insert-add-users').html(`
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

                btn.prop('disabled', false);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        $(`#error-${field}`).text(messages[0]);
                    });
                } else {
                    alert('Terjadi kesalahan saat menyimpan pengaturan.');
                }

                btn.prop('disabled', false);
            }
        });
    });
});
