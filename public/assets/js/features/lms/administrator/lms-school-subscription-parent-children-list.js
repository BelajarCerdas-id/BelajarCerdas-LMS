function pagnateParentChildrenList() {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const managedRole = container.dataset.managedRole;
    const parentId = container.dataset.parentId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!managedRole) return;
    if (!parentId) return;

    $.ajax({
        url: `/lms/${role}/school-subscription/${schoolName}/${schoolId}/management-role-account/${managedRole}/management-accounts/${parentId}/parent-children-list/paginate`,
        method: 'GET',

        beforeSend: function () {
            $('#skeleton-children-list').show();
            $('#container-children-list').hide();
            $('#empty-message-children-list').hide();
        },

        success: function (response) {
            $('#grid-children-list').empty();

            $('#skeleton-children-list').hide();

            if (response.data.length > 0) {

                $('#container-children-list').show();
                $('#empty-message-children-list').hide();

                $.each(response.data, function (index, item) {
                    $('#grid-children-list').append(`
                        <div class="bg-white border border-gray-200 rounded-2xl p-4 md:p-5 shadow-sm hover:shadow-md transition">

                            <div class="flex items-start gap-3 md:gap-4">

                                <div class="w-12 h-12 md:w-14 md:h-14 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-user-graduate text-[#005B94] text-lg md:text-xl"></i>
                                </div>

                                <div class="flex-1 min-w-0">

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                                        <h3 class="font-bold text-gray-800 text-sm md:text-base break-words">
                                            ${item.student_profile?.nama_lengkap ?? '-'}
                                        </h3>

                                        <span class="self-start sm:self-auto px-2 md:px-3 py-1 text-[10px] md:text-xs font-bold rounded-full whitespace-nowrap
                                            ${item.status_akun === 'aktif'
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700'
                                            }">
                                            ${item.status_akun ?? '-'}
                                        </span>

                                    </div>

                                    <div class="mt-2 space-y-1">

                                        <p class="text-xs md:text-sm text-gray-600 flex items-center gap-2">
                                            <i class="fa-solid fa-school text-[#005B94] w-4"></i>
                                            <span>
                                                ${item.student_school_class?.[0]?.school_class?.kelas?.kelas ?? '-'}
                                            </span>
                                        </p>

                                        <p class="text-xs md:text-sm text-gray-600 flex items-center gap-2">
                                            <i class="fa-solid fa-building-columns text-[#005B94] w-4"></i>
                                            <span class="break-words">
                                                ${item.student_profile?.school_partner?.nama_sekolah ?? '-'}
                                            </span>
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>
                    `);
                });

            } else {

                $('#container-children-list').hide();
                $('#empty-message-children-list').show();

            }
        },

        error: function (xhr, status, error) {
            $('#skeleton-children-list').hide();
            $('#container-children-list').hide();
            $('#empty-message-children-list').show();

            console.log(error);
        }
    });
}

$(document).ready(function () {
    pagnateParentChildrenList();
});