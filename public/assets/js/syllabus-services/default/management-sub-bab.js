function paginateSyllabusSubBab(page = 1) {
    const container = document.getElementById('container-management-sub-bab');

    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;
    const babId = container.dataset.babId;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;
    if (!babId) return;

    fetchFilteredDataSyllabusSubBab(curriculumName, curriculumId, faseId, kelasId, mapelId, babId);
    
    function fetchFilteredDataSyllabusSubBab() {
        $.ajax({
            url: `/paginate-syllabus-service-sub-bab/${curriculumName}/${curriculumId}/${faseId}/${kelasId}/${mapelId}/${babId}`,
            method: 'GET',
            data: { page: page },
            success: function (data) {
                $('#tableListSyllabusSubBab').empty();
                $('.pagination-container-syllabus-sub-bab').empty();
    
                if (data.data.length > 0) {
                    // Render rows
                    $.each(data.data, function (index, item) {
                        $('#tableListSyllabusSubBab').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">${item.sub_bab ?? '-'}</td>
                                <td class="border text-center border-gray-300">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-sub-bab"
                                            data-id="${item.id}"
                                            ${item.status_sub_bab === 'active' ? 'checked' : ''} />
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
                                            <li class="text-xs">
                                                <a href="#" class="btn-edit-sub-bab" data-curriculum-id="${curriculumId}" data-fase-id="${faseId}" data-kelas-id="${kelasId}"
                                                    data-mapel-id="${mapelId}" data-bab-id="${babId}" data-sub-bab-id="${item.id}" data-sub-bab-name="${item.sub_bab}">
                                                    <i class="fa-solid fa-pen text-[#4189e0]"></i>
                                                    Edit sub bab
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
    
                    // Insert pagination HTML
                    $('.pagination-container-syllabus-sub-bab').html(data.links);
    
                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();
    
                    $('#emptyMessageSyllabusSubBab').hide();
                    $('.thead-table-syllabus-sub-bab').show();
                } else {
                    $('#emptyMessageSyllabusSubBab').show();
                    $('.thead-table-syllabus-sub-bab').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-syllabus-sub-bab').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSyllabusSubBab(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateSyllabusSubBab();
});

let isProcessing = false;

// Form Action create bab
$('#submit-button-create-sub-bab').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-sub-bab').text('');

    const form = $('#create-sub-bab-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container-management-sub-bab');

    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    const mapelId = container.dataset.mapelId;
    const babId = container.dataset.babId;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;
    if (!mapelId) return;
    if (!babId) return;

    $.ajax({
        url: `/syllabus/${curriculumId}/${faseId}/${kelasId}/${mapelId}/${babId}/sub-bab/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-sub-bab').html(
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

            $('#create-sub-bab-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateSyllabusSubBab();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-sub-bab-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-sub-bab-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit sub bab" (open modal)
$(document).off('click', '.btn-edit-sub-bab').on('click', '.btn-edit-sub-bab', function (e) {
    e.preventDefault();

    const curriculumId = $(this).data('curriculum-id');
    const faseId = $(this).data('fase-id');
    const kelasId = $(this).data('kelas-id');
    const mapelId = $(this).data('mapel-id');
    const babId = $(this).data('bab-id');
    const subBabId = $(this).data('sub-bab-id');
    const subBabName = $(this).data('sub-bab-name');

    // set value ke form
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-fase-id').val(faseId);
    $('#edit-kelas-id').val(kelasId);
    $('#edit-mapel-id').val(mapelId);
    $('#edit-bab-id').val(babId);
    $('#edit-sub-bab-id').val(subBabId);
    $('#edit-sub_bab').val(subBabName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit sub bab
$('#submit-button-edit-sub-bab').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-sub-bab-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const curriculumId = $('#edit-curriculum-id').val();
    const faseId = $('#edit-fase-id').val();
    const kelasId = $('#edit-kelas-id').val();
    const mapelId = $('#edit-mapel-id').val();
    const babId = $('#edit-bab-id').val();
    const subBabId = $('#edit-sub-bab-id').val();

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/syllabus/curriculum/sub-bab/edit/${curriculumId}/${faseId}/${kelasId}/${mapelId}/${babId}/${subBabId}`,
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

                $('#alert-success-edit-data-sub-bab').html(
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

                $('#edit-sub-bab-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateSyllabusSubBab();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-sub-bab-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-sub-bab-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// function activate bab
$(document).ready(function () {
    $(document).on('change', '.toggle-sub-bab', function () {
        let subBabId = $(this).data('id'); // Ambil ID bab dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive

        $.ajax({
            url: '/syllabus/curriculum/sub-bab/activate/' + subBabId, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status_sub_bab: status // Kirim status baru (active/inactive)
            },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                paginateSyllabusSubBab();
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});
