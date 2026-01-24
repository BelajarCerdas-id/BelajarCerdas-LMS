function paginateSyllabusFase(page = 1) {
    const container = document.getElementById('container-management-fase');

    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    if (!curriculumName) return;
    if (!curriculumId) return;

    fetchFilteredDataSyllabusFase(curriculumName, curriculumId);
    
    function fetchFilteredDataSyllabusFase() {
        $.ajax({
            url: `/paginate-syllabus-service-fase/${curriculumName}/${curriculumId}`,
            method: 'GET',
            data: { page: page },
            success: function (data) {
                $('#tableListSyllabusFase').empty();
                $('.pagination-container-syllabus-fase').empty();
    
                if (data.data.length > 0) {
                    // Render rows
                    $.each(data.data, function (index, item) {
    
                        const kelasDetail = data.kelasDetail.replace(':curriculumName', curriculumName).replace(':curriculumId', curriculumId).replace(':faseId', item.id);
    
                        $('#tableListSyllabusFase').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">${item.nama_fase ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${kelasDetail}" class="btn-fase-detail">
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
                                            class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm  z-9999">
                                            <li class="text-xs">
                                                <a href="#" class="btn-edit-fase" data-curriculum-id="${curriculumId}" data-fase-id="${item.id}" data-fase-name="${item.nama_fase}">
                                                    <i class="fa-solid fa-pen text-[#4189e0]"></i>
                                                    Edit fase
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
                    $('.pagination-container-syllabus-fase').html(data.links);
    
                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();
    
                    $('#emptyMessageSyllabusFase').hide();
                    $('.thead-table-syllabus-fase').show();
                } else {
                    $('#emptyMessageSyllabusFase').show();
                    $('.thead-table-syllabus-fase').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-syllabus-fase').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSyllabusFase(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateSyllabusFase();
});

let isProcessing = false;

// Form Action create fase
$('#submit-button-create-fase').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-nama-fase').text('');

    const form = $('#create-fase-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container-management-fase');

    const curriculumId = container.dataset.curriculumId;
    if (!curriculumId) return;

    $.ajax({
        url: `/syllabus/${curriculumId}/fase/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-fase').html(
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

            $('#create-fase-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateSyllabusFase();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-fase-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-fase-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit fase" (open modal)
$(document).off('click', '.btn-edit-fase').on('click', '.btn-edit-fase', function (e) {
    e.preventDefault();

    const curriculumId = $(this).data('curriculum-id');
    const faseId = $(this).data('fase-id');
    const faseName = $(this).data('fase-name');

    // set value ke form
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-fase-id').val(faseId);
    $('#edit-fase-name').val(faseName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit curriculum
$('#submit-button-edit-fase').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-fase-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const curriculumId = $('#edit-curriculum-id').val();
    const faseId = $('#edit-fase-id').val();

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/syllabus/curriculum/fase/edit/${curriculumId}/${faseId}`,
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

                $('#alert-success-edit-data-fase').html(
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

                $('#edit-fase-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateSyllabusFase();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-fase-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-fase-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});