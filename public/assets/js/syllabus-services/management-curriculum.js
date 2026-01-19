function fetchFilteredDataSyllabusCurriculum(page = 1) {
    $.ajax({
        url: '/paginate-syllabus-service-kurikulum',
        method: 'GET',
        data: { page: page },
        success: function (data) {
            $('#tableListSyllabusCurriculum').empty();
            $('.pagination-container-syllabus-curriculum').empty();

            if (data.data.length > 0) {
                // Render rows
                $.each(data.data, function (index, item) {
                    const formatDate = (dateString) => {
                        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const dayName = days[date.getDay()];
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${dayName}, ${day}-${monthName}-${year}`;
                    };

                    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    });

                    const updatedAt = item.updated_at ? `${formatDate(item.updated_at)}, ${timeFormatter.format(new Date(item.updated_at))}` : 'Tanggal tidak tersedia';

                    let faseDetail = data.faseDetail.replace(':curriculumName', item.nama_kurikulum).replace(':curriculumId', item.id);

                    $('#tableListSyllabusCurriculum').append(`
                        <tr class="text-xs">
                            <td class="border border-gray-300 px-3 py-2">${item.nama_kurikulum ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <a href="${faseDetail}" class="btn-fase-detail" data-id="${item.id}" data-nama-kurikulum="${item.nama_kurikulum}">
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
                                            <a href="#" class="btn-edit-curriculum" data-curriculum-id="${item.id}" data-curriculum-name="${item.nama_kurikulum}">
                                                <i class="fa-solid fa-pen text-[#4189e0]"></i>
                                                Edit Kurikulum
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
                $('.pagination-container-syllabus-curriculum').html(data.links);

                // Bind click event ke link pagination yang baru
                bindPaginationLinks();

                $('#emptyMessageSyllabusCurriculum').hide();
                $('.thead-table-syllabus-curriculum').show();
            } else {
                $('#emptyMessageSyllabusCurriculum').show();
                $('.thead-table-syllabus-curriculum').hide();
            }
        },
        error: function (xhr, status, error) {
            console.error('ajax error:', status, error);
        }
    });
}
function bindPaginationLinks() {
    $('.pagination-container-syllabus-curriculum').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        fetchFilteredDataSyllabusCurriculum(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    fetchFilteredDataSyllabusCurriculum();
});

let isProcessing = false;

// Form Action create curriculum
$('#submit-button-create-curriculum').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-nama-kurikulum').text('');

    const form = $('#create-curriculum-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/syllabus/curriculum/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-curriculum').html(
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

            $('#create-curriculum-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            fetchFilteredDataSyllabusCurriculum();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-curriculum-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-curriculum-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit kurikulum" (open modal)
$(document).off('click', '.btn-edit-curriculum').on('click', '.btn-edit-curriculum', function (e) {
    e.preventDefault();

    const curriculumId = $(this).data('curriculum-id');
    const curriculumName = $(this).data('curriculum-name');

    // set value ke form
    $('#edit-curriculum-id').val(curriculumId);
    $('#edit-curriculum-name').val(curriculumName);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit curriculum
$('#submit-button-edit-curriculum').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-curriculum-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const curriculumId = $('#edit-curriculum-id').val();

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/syllabus/curriculum/edit/${curriculumId}`,
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

                $('#alert-success-edit-data-curriculum').html(
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

                $('#edit-curriculum-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                fetchFilteredDataSyllabusCurriculum();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-curriculum-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-curriculum-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});