function formContentForRelease(search_materi = null, search_year = null, search_class = null, kurikulum_id = null, service_id = null, kelas_id = null, mapel_id = null, bab_id = null) {
    const container = document.getElementById('container-form-content-for-release');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/content-for-release/form`,
        method: 'GET',
        data: {
            search_materi,
            search_year,
            search_class,
            kurikulum_id,
            service_id,
            kelas_id,
            mapel_id,
            bab_id
        },
        success: function (response) {
            // Dropdown Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Pilih Tahun Ajaran</label>
                    <select id="dropdown-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-6 cursor-pointer outline-none">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-class');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer outline-none">
                        <option value="" class="hidden">Filter Kelas</option>
                        ${response.className.map(item => `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Table Rombel
            const tbodyContent = document.getElementById('tbody-rombel-class-content-for-release');
            tbodyContent.innerHTML = '';

            if (response.rombel.length > 0) {
                (response.rombel || []).forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.classList.add('rombel-row');
                    row.dataset.id = item.school_class?.id ?? '';
                    row.dataset.tahun = item.school_class?.tahun_ajaran ?? '';
                    row.dataset.kelas = item.school_class?.kelas?.kelas ?? '';

                    const mapelName = item.mapel?.mata_pelajaran ?? '';
    
                    row.innerHTML = `
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <input type="checkbox" name="school_class_id[]" value="${item.id}" class="rombel-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.class_name ?? ''}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center">${mapelName}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center rombel-status text-gray-400">Belum dipilih</td>
                        <td class="border border-gray-300 px-3 py-2 align-middle">
                            <div class="relative w-full">
                                <input 
                                    type="text" name="meeting_date[${item.id}]"
                                    class="rombel-date w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm outline-none
                                        disabled:bg-gray-100 disabled:text-gray-400
                                        transition duration-200"
                                    placeholder="Pilih tanggal release"
                                    disabled
                                >
                                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                                    <i class="fa-regular fa-calendar text-sm"></i>
                                </span>
                            </div>
                                <span id="error-meeting_date-${item.id}"
                                    class="text-red-500 text-xs font-semibold">
                                </span>
                        </td>
                    `;
                    tbodyContent.appendChild(row);
                });
    
                initRombelCheckboxLogic();
                $('.thead-table-rombel-class-content-for-release').show();
                $('#empty-message-rombel-class-content-for-release-list').hide();
            } else {
                $('.thead-table-rombel-class-content-for-release').hide();
                $('#empty-message-rombel-class-content-for-release-list').show();
            }

            // Table Contents
            const contentContainer = document.getElementById('content-list-container');
            if (response.contents && response.contents.length > 0) {
                contentContainer.innerHTML = response.contents.map(item => {
                    const filename = item.lms_content_item?.[0]?.original_filename ?? '-';

                    return `
                        <label class="content-item flex gap-3 p-4 rounded-xl border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition cursor-pointer">

                            <input type="radio" name="lms_content_id" value="${item.id}" class="content-checkbox mt-1 h-4 w-4 shrink-0 rounded border-gray-300 cursor-pointer">
                            <input type="hidden" name="service_id" value="${item.service_id}">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 wrap-break-word">
                                    ${filename}
                                </p>

                                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                    
                                    <span class="truncate max-w-full">
                                        ${item.kurikulum?.nama_kurikulum ?? ''}
                                    </span>

                                    <span class="truncate max-w-full">
                                        ${item.kelas?.kelas ?? ''}
                                    </span>

                                    <i class="fa-solid fa-circle text-[4px]"></i>

                                    <span class="truncate max-w-full">
                                        ${item.mapel?.mata_pelajaran ?? '-'}
                                    </span>

                                    <i class="fa-solid fa-circle text-[4px]"></i>

                                    <span class="truncate max-w-full">
                                        ${item.bab?.nama_bab ?? '-'}
                                    </span>

                                    <i class="fa-solid fa-circle text-[4px]"></i>

                                    <span class="truncate max-w-full">
                                        ${item.service?.name ?? '-'}
                                    </span>

                                    <i class="fa-solid fa-circle text-[4px]"></i>

                                    <span class="truncate max-w-full">
                                        ${item.school_partner_id ? item.school_partner?.nama_sekolah : 'belajarcerdas.id'}
                                    </span>

                                </div>
                            </div>
                        </label>

                    `;
                }).join('');

                $('#content-list-container').show();
                $('#empty-message-content-list').hide();
            } else {
                $('#content-list-container').hide();
                $('#empty-message-content-list').show();
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    formContentForRelease();
});

$(document).on('input', '#search_materi', function () {
    formContentForRelease($(this).val(), $('#dropdown-tahun-ajaran').val(), $('#dropdown-filter-class').val() || null);
});

$(document).on('change', '#dropdown-tahun-ajaran', function () {
    formContentForRelease($('#search_materi').val(), $(this).val(), null); // null supaya auto pilih kelas paling rendah
});

$(document).on('change', '#dropdown-filter-class', function () {
    formContentForRelease($('#search_materi').val(), $('#dropdown-tahun-ajaran').val(), $(this).val());
});

$(document).on('change', '#id_kurikulum, #id_service, #id_kelas, #id_mapel, #id_bab', function () {
    formContentForRelease(
        $('#search_materi').val(),
        $('#dropdown-tahun-ajaran').val(),
        $('#dropdown-filter-class').val(),
        $('#id_kurikulum').val(),
        $('#id_service').val(),
        $('#id_kelas').val(),
        $('#id_mapel').val(),
        $('#id_bab').val(),
    );
});

function enableFlatpickr(el) {
    if (el._flatpickr) return;

    flatpickr(el, {
        dateFormat: "Y-m-d",
        minDate: "today",
        disableMobile: true,

        onChange: function (selectedDates, dateStr, instance) {
            const el = instance.input;

            el.classList.remove('border-red-400');

            // ambil ID dari name meeting_date[5]
            const nameAttr = el.getAttribute('name');
            const match = nameAttr.match(/\[(.*?)\]/);

            if (match) {
                const id = match[1];

                const errorMessage = document.querySelector(`#error-meeting_date-${id}`);
                if (errorMessage) {
                    errorMessage.textContent = '';
                }
            }
        }
    });
}

function disableFlatpickr(el) {
    if (el._flatpickr) {
        el._flatpickr.destroy();
    }
}

function initRombelCheckboxLogic() {
    const checkboxes = document.querySelectorAll('.rombel-checkbox');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {

            const row = this.closest('.rombel-row');
            if (!row) return;

            const dateInput = row.querySelector('.rombel-date');
            const status = row.querySelector('.rombel-status');

            if (this.checked) {
                dateInput.disabled = false;
                enableFlatpickr(dateInput);

                status.textContent = "Siap Dijadwalkan";
                status.classList.remove('text-gray-400');
                status.classList.add('text-green-600');
            } else {
                disableFlatpickr(dateInput);
                dateInput.disabled = true;
                dateInput.value = '';

                status.textContent = "Belum dipilih";
                status.classList.remove('text-green-600');
                status.classList.add('text-gray-400');
            }
        });
    });
}

document.addEventListener('change', function (e) {

    if (e.target.classList.contains('content-checkbox')) {
        updateSelectedCount();
    }

    if (e.target.classList.contains('rombel-checkbox')) {
        updateRombelSelectedCount();
    }

});

function updateSelectedCount() {

    const selected = document.querySelector('.content-checkbox:checked');
    const total = selected ? 1 : 0;

    document.getElementById('total-selected').innerText = `${total} Dipilih`;

    if (total > 0) {
        $('#error-lms_content_id').text('');
    }
}

function updateRombelSelectedCount() {

    const checkedItems = document.querySelectorAll('.rombel-checkbox:checked');
    const total = checkedItems.length;

    if (total > 0) {
        $('#total-rombel-selected').html(`
            <div class="flex items-center gap-1">
                <i class="fa-solid fa-circle text-[4px] text-white"></i>
                <span>${total} Rombel kelas dipilih</span>
            </div>
        `);

        $('#error-school_class_id').text('');
    } else {
        $('#total-rombel-selected').html('');
    }
}

$(document).on('change', '#container-dropdown-semester', function () {
    const selectedOption = $(this).find('option:selected').text();

    $('#text-semester').html(`
        <div class="flex items-center gap-1">
            <span>${selectedOption}</span>
        </div>
    `);
});

$(document).on('change', '#container-dropdown-pertemuan', function () {
    const selectedOption = $(this).find('option:selected').text();

    $('#text-pertemuan').html(`
        <div class="flex items-center gap-1">
            <i class="fa-solid fa-circle text-[4px] text-white"></i>
            <span>${selectedOption}</span>
        </div>
    `);
});

let isProcessing = false;

// Form Action content for release
$('#submit-button-publish-content-for-release, #submit-button-draft-content-for-release').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-form-content-for-release');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role || !schoolName || !schoolId) return;

    const status = $(this).data('status'); // draft / publish
    const isActive = status === 'publish' ? 1 : 0;

    const form = $('#content-for-release-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    formData.append('is_active', isActive);

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/content-for-release/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-content-for-release').html(`
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

            $('#id_kelas').html('<option disabled selected>Pilih Kelas</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_mapel').html('<option disabled selected>Pilih Mata Pelajaran</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_bab').html('<option disabled selected>Pilih Bab</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#id_sub_bab').html('<option disabled selected>Pilih Bab</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');

            // RESET SEMUA
            $('#content-for-release-form')[0].reset();

            isProcessing = false;
            btn.prop('disabled', false);

            formContentForRelease();
            paginateContentForRelease();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    if (field.includes('.')) {

                        let parts = field.split('.');
                        let fieldName = parts[0];
                        let index = parts[1];

                        // kasih border ke input sesuai index
                        let input = $(`[name="${fieldName}[${index}]"]`);
                        input.addClass('border-red-400 border');

                        // tampilkan error ke span sesuai index
                        $(`#error-${fieldName}-${index}`).text(messages[0]);

                    } else {

                        $(`#error-${field}`).text(messages[0]);
                        $(`[name="${field}"]`).addClass('border-red-400 border');
                    }
                });

                if (errors.school_class_id) {
                    $('#error-school_class_id')
                        .removeClass('hidden')
                        .text(errors.school_class_id[0]);
                }

                if (errors.lms_content_id) {
                    $('#error-lms_content_id')
                        .removeClass('hidden')
                        .text(errors.lms_content_id[0]);
                }
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});