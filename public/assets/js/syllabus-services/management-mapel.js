function paginateSyllabusMapel(page = 1) {
    const container = document.getElementById('container-management-mapel');

    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;

    fetchFilteredDataSyllabusKelas(curriculumName, curriculumId, faseId, kelasId);
    
    function fetchFilteredDataSyllabusKelas() {
        $.ajax({
            url: `/paginate-syllabus-service-mapel/${curriculumName}/${curriculumId}/${faseId}/${kelasId}`,
            method: 'GET',
            data: { page: page },
            success: function (data) {
                $('#tableListSyllabusMapel').empty();
                $('.pagination-container-syllabus-mapel').empty();
    
                if (data.data.length > 0) {
                    // Render rows
                    $.each(data.data, function (index, item) {
    
                        const babDetail = data.babDetail.replace(':curriculumName', curriculumName).replace(':curriculumId', curriculumId).replace(':faseId', faseId)
                            .replace(':kelasId', kelasId).replace(':mapelId', item.id);
    
                        $('#tableListSyllabusMapel').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">${item.mata_pelajaran ?? '-'}</td>
                                <td class="border text-center border-gray-300">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="hidden peer toggle-mapel"
                                            data-id="${item.id}"
                                            ${item.status_mata_pelajaran === 'active' ? 'checked' : ''} />
                                        <div
                                            class="w-11 h-6 bg-gray-300 peer-checked:bg-green-500 rounded-full transition-colors duration-300 ease-in-out">
                                        </div>
                                            <div
                                            class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 ease-in-out peer-checked:translate-x-5">
                                        </div>
                                    </label>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${babDetail}">
                                        <div class="text-[#4189e0]">
                                            <span>Detail</span>
                                            <i class="fas fa-chevron-right text-xs"></i>
                                        </div>
                                    </a>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <div class="dropdown dropdown-left">
                                        <div tabindex="0" role="button">
                                            <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                        </div>
                                        <ul tabindex="0"
                                            class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                                <li class="text-xs">
                                                    <a href="#" class="btn-edit-mapel" data-curriculum-id="${curriculumId}" data-fase-id="${faseId}" data-kelas-id="${kelasId}"
                                                        data-mapel-id="${item.id}" data-mapel-name="${item.mata_pelajaran}">
                                                        <i class="fa-solid fa-pen text-[#4189e0]"></i>
                                                        Edit maata pelajaran
                                                    </a>
                                                </li>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
    
                    // Insert pagination HTML
                    $('.pagination-container-syllabus-mapel').html(data.links);
    
                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();
    
                    $('#emptyMessageSyllabusMapel').hide();
                    $('.thead-table-syllabus-mapel').show();
                } else {
                    $('#emptyMessageSyllabusMapel').show();
                    $('.thead-table-syllabus-mapel').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-syllabus-mapel').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSyllabusMapel(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateSyllabusMapel();
});

let isProcessing = false;

// Form Action create kelas
$('#submit-button-create-mapel').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-mapel').text('');

    const form = $('#create-mapel-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container-management-mapel');

    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    const kelasId = container.dataset.kelasId;
    if (!curriculumId) return;
    if (!faseId) return;
    if (!kelasId) return;

    $.ajax({
        url: `/syllabus/${curriculumId}/${faseId}/${kelasId}/mapel/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-mapel').html(
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

            $('#create-mapel-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateSyllabusMapel();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-mapel-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-mapel-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit mapel" (open modal)
$(document).off('click', '.btn-edit-mapel').on('click', '.btn-edit-mapel', function (e) {
    e.preventDefault();

    const curriculumId = $(this).data('curriculum-id');
    const faseId = $(this).data('fase-id');
    const kelasId = $(this).data('kelas-id');
    const mapelId = $(this).data('mapel-id');
    const mapelName = $(this).data('mapel-name');

    // set value ke form
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-fase-id').val(faseId);
    $('#edit-kelas-id').val(kelasId);
    $('#edit-mapel-id').val(mapelId);
    $('#edit-mapel').val(mapelName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit mapel
$('#submit-button-edit-mapel').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-mapel-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const curriculumId = $('#edit-curriculum-id').val();
    const faseId = $('#edit-fase-id').val();
    const kelasId = $('#edit-kelas-id').val();
    const mapelId = $('#edit-mapel-id').val();

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/syllabus/curriculum/mapel/edit/${curriculumId}/${faseId}/${kelasId}/${mapelId}`,
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

                $('#alert-success-edit-data-mapel').html(
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

                $('#edit-mapel-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateSyllabusMapel();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-mapel-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-mapel-form').find(`[name="${field}"]`).addClass('border-red-400 border');
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
    $(document).on('change', '.toggle-mapel', function () {
        let mapelId = $(this).data('id'); // Ambil ID mapel dari atribut data-id di checkbox
        let status = $(this).is(':checked') ? 'active' : 'inactive'; // Jika toggle ON maka active, kalau OFF maka inactive

        $.ajax({
            url: '/syllabus/curriculum/mapel/activate/' + mapelId, // Endpoint ke server
            method: 'PUT', // Method HTTP PUT untuk update data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status_mata_pelajaran: status // Kirim status baru (active/inactive)
            },
            success: function (response) {
                // Memanggil fungsi untuk memuat ulang data
                paginateSyllabusMapel();
            },
            error: function (xhr) {
                alert('Gagal mengubah status.');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
});
