function paginateSyllabusKelas(page = 1) {
    const container = document.getElementById('container-management-kelas');

    const curriculumName = container.dataset.curriculumName;
    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    if (!curriculumName) return;
    if (!curriculumId) return;
    if (!faseId) return;

    fetchFilteredDataSyllabusKelas(curriculumName, curriculumId, faseId);
    
    function fetchFilteredDataSyllabusKelas() {
        $.ajax({
            url: `/paginate-syllabus-service-kelas/${curriculumName}/${curriculumId}/${faseId}`,
            method: 'GET',
            data: { page: page },
            success: function (data) {
                $('#tableListSyllabusKelas').empty();
                $('.pagination-container-syllabus-kelas').empty();
    
                if (data.data.length > 0) {
                    // Render rows
                    $.each(data.data, function (index, item) {
    
                        const mapelDetail = data.mapelDetail.replace(':curriculumName', curriculumName).replace(':curriculumId', curriculumId).replace(':faseId', faseId)
                            .replace(':kelasId', item.id);
    
                        $('#tableListSyllabusKelas').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2">${item.kelas ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${mapelDetail}">
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
                                                    <a href="#" class="btn-edit-kelas" data-curriculum-id="${curriculumId}" data-fase-id="${faseId}" data-kelas-id="${item.id}"
                                                        data-kelas-name="${item.kelas}">
                                                        <i class="fa-solid fa-pen text-[#4189e0]"></i>
                                                        Edit kelas
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
                    $('.pagination-container-syllabus-kelas').html(data.links);
    
                    // Bind click event ke link pagination yang baru
                    bindPaginationLinks();
    
                    $('#emptyMessageSyllabusKelas').hide();
                    $('.thead-table-syllabus-kelas').show();
                } else {
                    $('#emptyMessageSyllabusKelas').show();
                    $('.thead-table-syllabus-kelas').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('ajax error:', status, error);
            }
        });
    }
}
function bindPaginationLinks() {
    $('.pagination-container-syllabus-kelas').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateSyllabusKelas(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    paginateSyllabusKelas();
});

let isProcessing = false;

// Form Action create kelas
$('#submit-button-create-kelas').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-kelas').text('');

    const form = $('#create-kelas-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    const container = document.getElementById('container-management-kelas');

    const curriculumId = container.dataset.curriculumId;
    const faseId = container.dataset.faseId;
    if (!curriculumId) return;
    if (!faseId) return;

    $.ajax({
        url: `/syllabus/${curriculumId}/${faseId}/kelas/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-kelas').html(
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

            $('#create-kelas-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            paginateSyllabusKelas();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-kelas-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-kelas-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit kelas" (open modal)
$(document).off('click', '.btn-edit-kelas').on('click', '.btn-edit-kelas', function (e) {
    e.preventDefault();

    const curriculumId = $(this).data('curriculum-id');
    const faseId = $(this).data('fase-id');
    const kelasId = $(this).data('kelas-id');
    const kelasName = $(this).data('kelas-name');

    // set value ke form
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-fase-id').val(faseId);
    $('#edit-kelas-id').val(kelasId);
    $('#edit-kelas').val(kelasName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit kelas
$('#submit-button-edit-kelas').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-kelas-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const curriculumId = $('#edit-curriculum-id').val();
    const faseId = $('#edit-fase-id').val();
    const kelasId = $('#edit-kelas-id').val();

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/syllabus/curriculum/kelas/edit/${curriculumId}/${faseId}/${kelasId}`,
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

                $('#alert-success-edit-data-kelas').html(
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

                $('#edit-kelas-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                paginateSyllabusKelas();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-kelas-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-kelas-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});