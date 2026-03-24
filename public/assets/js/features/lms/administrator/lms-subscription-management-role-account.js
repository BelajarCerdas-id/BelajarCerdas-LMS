function managementRoleAccountSchoolSubscription() {
    const container = document.getElementById('container-role-account-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    fetchRoleAccount(schoolName, schoolId);

    function fetchRoleAccount() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/role-account/paginate`,
            method: 'GET',
            success: function (response) {
                const containerRoleAccountList = $('#grid-role-account-list');
                containerRoleAccountList.empty();

                function roleBadge(role) {
                    const map = {
                        'Kepala Sekolah': 'bg-purple-100 text-purple-700',
                        'Wakil Kepala Sekolah': 'bg-indigo-100 text-indigo-700',
                        'Admin Sekolah': 'bg-blue-100 text-blue-700',
                        'Guru': 'bg-green-100 text-green-700',
                        'Siswa': 'bg-orange-100 text-orange-700',
                    };
                    return map[role] || 'bg-gray-100 text-gray-700';
                }

                function roleIcon(role) {
                    const map = {
                        'Kepala Sekolah': 'fa-solid fa-user-tie',
                        'Wakil Kepala Sekolah': 'fa-solid fa-user-gear',
                        'Admin Sekolah': 'fa-solid fa-user-shield',
                        'Guru': 'fa-solid fa-chalkboard-user',
                        'Siswa': 'fa-solid fa-user-graduate',
                    };

                    return map[role] || 'fa-solid fa-user';
                }

                if (response.data.length > 0) {
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

                    $.each(response.data, function (index, group) {
                        const first = group[0];
                        const total = group.length;

                        let lmsLinkDetail = '';
                        let manageAccountLink = response.lmsManagementAccounts.replace(':schoolName', schoolIdentity.nama_sekolah).replace(':schoolId', schoolIdentity.id).replace(':role', first.role);

                        let containerLihatDetail = '';
                        let containerManageAccount = '';

                        if (first.role === 'Siswa') {
                            if (schoolIdentity.jenjang_sekolah === 'SMA' || schoolIdentity.jenjang_sekolah === 'SMK') {
                                lmsLinkDetail = response.lmsManagementMajors.replace(':schoolName', schoolIdentity.nama_sekolah).replace(':schoolId', schoolIdentity.id)
                                    .replace(':role', first.role);
                            } else {
                                lmsLinkDetail = response.lmsManagementClass.replace(':schoolName', schoolIdentity.nama_sekolah).replace(':schoolId', schoolIdentity.id)
                                    .replace(':role', first.role);
                            }

                            containerLihatDetail = `
                                <div class="text-sm text-[#0071BC] font-bold flex items-center gap-2">
                                    <a href="${lmsLinkDetail}">
                                        Lihat Detail
                                        <i class="fa solid fa-chevron-right"></i>
                                    </a>
                                </div>
                            `;
                        }
                        
                        containerManageAccount = `
                            <a href="${manageAccountLink}" class="${first.role === 'Siswa' ? 'text-xs' : 'text-sm'} text-[#0071BC] font-bold">
                                Manage Account
                                <i class="fa solid fa-chevron-right"></i>
                            </a>
                        `;

                        const card = `
                            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 flex flex-col justify-between h-full">
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold ${roleBadge(first.role)}">
                                            <i class="${roleIcon(first.role)}"></i>
                                            ${first.role}
                                        </span>

                                        ${first.role === 'Siswa' ? `${containerManageAccount}` : ''}
                                    </div>

                                    <div class="mt-4">
                                        <p class="text-3xl font-bold text-gray-800">
                                            ${total}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Total akun
                                        </p>
                                    </div>
                                </div>

                                ${first.role !== 'Siswa' ? `${containerManageAccount}` : ''}
                                ${first.role === 'Siswa' ? `${containerLihatDetail}` : ''}
                            </div>
                        `;
                        containerRoleAccountList.append(card);
                    });

                    $('#school-detail-card').show();
                    $('#empty-message-role-account-list').hide();
                } else {
                    $('#school-detail-card').hide();
                    $('#empty-message-role-account-list').show();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
}

$(document).ready(function () {
    managementRoleAccountSchoolSubscription();
});

let isProcessing = false;

// Form submit add users
$(document).ready(function () {
    $('#submit-button').on('click', function (e) {
        e.preventDefault();

        if (isProcessing) return; // abaikan jika sedang proses

        isProcessing = true; // tandai sedang proses

        const form = $('#school-partner-add-users-form')[0]; // ambil DOM Form-nya
        const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

        const btn = $(this);
        btn.prop('disabled', true); // Disable button UI

        $.ajax({
            url: '/school-subscription/add-users/store',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {
                const modal = document.getElementById('my_modal_1');

                if (modal) {
                    modal.close();

                    $('#alert-success-insert-add-users').html(
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

                // Reset form
                $('#school-partner-add-users-form')[0].reset();
                $('#excelPreviewContainer-bulkUpload-excel').addClass('hidden');
                $('#textPreview-bulkUpload-excel').text('');
                $('#textSize-bulkUpload-excel').text('');
                $('#textPages-bulkUpload-excel').text('');
                $('#textCircle-bulkUpload-excel').html('');
                $('#logo-bulkUpload-excel img').attr('src', '').hide();

                setTimeout(function () {
                    document.getElementById('alertSuccess').remove();
                }, 3000);

                document.getElementById('btnClose').addEventListener('click', function () {
                    document.getElementById('alertSuccess').remove();
                });

                managementRoleAccountSchoolSubscription();

                isProcessing = false;
                btn.prop('disabled', false);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const response = xhr.responseJSON;

                    // error validation form dan bulkUpload
                    const formErrors = response.errors.form_errors ?? {};
                    const excelErrors = response.errors.excel_validation_errors ?? [];

                    let errorList = '';

                    $.each(formErrors, function (field, messages) {
                        $(`#error-${field}`).text(messages[0]);
                        $(`[name="${field}"]`).addClass('border-red-400 border-2');
                    });

                    if (excelErrors.length > 0) {
                        excelErrors.forEach(err => {
                            errorList += `<li class="text-sm">${err}</li>`;
                        });

                        const html = `
                            <ul class="text-red-500 text-sm list-disc pl-5">
                                ${errorList}
                            </ul>
                        `;

                        const showError = `
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-2 h-max rounded">
                                <span class="font-bold text-sm">Terjadi Kesalahan :</span>
                                ${html}
                            </div>
                        `;

                        $('#error-bulkUpload').html(showError);
                        my_modal_1.showModal();
                    }
                } else {
                    alert('Terjadi kesalahan saat mengirim data.');
                }

                isProcessing = false;
                btn.prop('disabled', false);
            }
        });
    });
})