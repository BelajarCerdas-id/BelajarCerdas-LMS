function formContentForRelease(search_materi = null, search_year = null, search_class = null, kurikulum_id = null, service_id = null, kelas_id = null,
    mapel_id = null, bab_id = null, preserveSelection = true) {
    
    const container = document.getElementById('container-form-content-for-release');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    const selectedSchoolClassId = preserveSelection ? ($('#dropdown-school-class').val() || '') : '';
    const selectedMeetingData = preserveSelection ? getSelectedMeetings() : [];
    const selectedContentId = preserveSelection ? ($('input[name="lms_content_id"]:checked').val() || '') : '';

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
            const dropdownTahunAjaran = document.getElementById('dropdown-tahun-ajaran');
            const dropdownClass = document.getElementById('dropdown-filter-class');

            // render tahun ajaran
            if (dropdownTahunAjaran) {
                const tahunAjaranOptions = (response.tahunAjaran || []).map(item => `
                    <option value="${item}"
                        ${response.selectedYear == item ? 'selected' : ''}>
                        Tahun Ajaran ${item}
                    </option>
                `).join('');

                dropdownTahunAjaran.insertAdjacentHTML('beforeend', tahunAjaranOptions);
            }

            // render rombel kelas
            if (dropdownClass) {
                const classOptions = (response.className || []).map(item => `
                    <option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>
                        Kelas ${item}
                    </option>
                `).join('');

                dropdownClass.insertAdjacentHTML('beforeend', classOptions);
            }

            // Dropdown Rombel
            const dropdownSchoolClass = document.getElementById('dropdown-school-class');
            if (dropdownSchoolClass) {
                dropdownSchoolClass.innerHTML = `
                    <option value="" class="hidden">Pilih Rombel Kelas</option>
                    ${(response.rombel || []).map(item => {
                        const classId = item.school_class?.id ?? '';
                        const className = item.school_class?.class_name ?? '';
                        const mapelId = item.mapel?.id ?? '';
                        const mapelName = item.mapel?.mata_pelajaran ?? '';
                    
                    return `
                        <option value="${classId}" data-mapel="${mapelId}" data-class-name="${className}" data-mapel-name="${mapelName}"
                            ${selectedSchoolClassId == classId ? 'selected' : ''}>
                            ${className} - ${mapelName}
                        </option>
                    `;
                }).join('')}
                `;
            }

            // Update Informasi Rombel
            if (selectedSchoolClassId && $('#dropdown-school-class').val() == selectedSchoolClassId) {
                updateSelectedRombelInformation();
            } else {
                $('#selected-rombel-information').addClass('hidden');
                $('#selected-rombel-name').text('');
                $('#selected-rombel-mapel').text('');
                $('#dynamic_mapel_id').remove();
            }

            // Table Contents
            const contentContainer = document.getElementById('content-list-container');
            if (response.contents && response.contents.length > 0) {
                contentContainer.innerHTML = response.contents.map(item => {
                    const filename = item.lms_content_item?.[0]?.original_filename ?? '-';
                    let subBabDisplay = '';

                    if (item.sub_bab_id) {
                        subBabDisplay = `
                            <i class="fa-solid fa-circle text-[4px]"></i>
                            <span class="truncate max-w-full">
                                ${item.sub_bab?.sub_bab ?? '-'}
                            </span>
                        `;
                    }

                    return `
                        <label class="content-item flex gap-3 p-4 rounded-xl border ${selectedContentId == item.id ? 'border-blue-400 bg-blue-50' : 'border-gray-200'} hover:border-blue-400 hover:bg-blue-50 transition cursor-pointer"
                            data-service="${item.service_id}">
                            <input type="radio" name="lms_content_id" value="${item.id}"
                                ${selectedContentId == item.id ? 'checked' : ''}
                                class="content-checkbox mt-1 h-4 w-4 shrink-0 rounded border-gray-300 cursor-pointer">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 wrap-break-word">
                                    ${filename}
                                </p>

                                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                    <span class="truncate max-w-full">
                                        ${item.kurikulum?.nama_kurikulum ?? ''}
                                    </span>

                                    <i class="fa-solid fa-circle text-[4px]"></i>
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

                                    ${subBabDisplay}

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

            restoreSelectedMeetings(selectedMeetingData);
            initMeetingLogic();
            updateSelectedCount();
            updateMeetingSelectedCount();
            updateSummary();
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
    formContentForRelease($(this).val(), $('#dropdown-tahun-ajaran').val(), $('#dropdown-filter-class').val() || null, $('#id_kurikulum').val(), $('#id_service').val(),
        $('#id_kelas').val(), $('#id_mapel').val(), $('#id_bab').val());
});

$(document).on('change', '#dropdown-tahun-ajaran', function () {
    $('#dropdown-filter-class').val('');
    formContentForRelease($('#search_materi').val(), $(this).val(), null, $('#id_kurikulum').val(), $('#id_service').val(), $('#id_kelas').val(), $('#id_mapel').val(),
        $('#id_bab').val());
});

$(document).on('change', '#dropdown-filter-class', function () {
    formContentForRelease($('#search_materi').val(), $('#dropdown-tahun-ajaran').val(), $(this).val(), $('#id_kurikulum').val(), $('#id_service').val(), $('#id_kelas').val(),
        $('#id_mapel').val(), $('#id_bab').val());
});

$(document).on('change', '#id_kurikulum, #id_service, #id_kelas, #id_mapel, #id_bab', function () {
    formContentForRelease($('#search_materi').val(), $('#dropdown-tahun-ajaran').val(), $('#dropdown-filter-class').val(), $('#id_kurikulum').val(), $('#id_service').val(),
        $('#id_kelas').val(), $('#id_mapel').val(), $('#id_bab').val());
});

function getSelectedMeetings() {
    const meetings = [];

    $('.meeting-checkbox:checked').each(function () {
        const pertemuan = $(this).val();
        const dateInput = $(`.meeting-release-date[data-meeting="${pertemuan}"]`);

        meetings.push({pertemuan: pertemuan, release_date: dateInput.val() || ''});
    });

    return meetings;
}

function restoreSelectedMeetings(meetings = []) {
    $('.meeting-checkbox').prop('checked', false);

    $('.meeting-release-date').each(function () {
        const input = this;

        input.disabled = true;

        if (input._flatpickr) {
            input._flatpickr.clear();
        }

        $(input).removeClass('border-red-400 bg-white border-gray-300').addClass('bg-gray-100 border-gray-200');
    });

    $('.meeting-row').removeClass('bg-blue-50');

    meetings.forEach(item => {
        const checkbox = $(`.meeting-checkbox[value="${item.pertemuan}"]`);

        const dateInput = $(`.meeting-release-date[data-meeting="${item.pertemuan}"]`);

        const input = dateInput[0];

        if (!checkbox.length || !input) {
            return;
        }

        checkbox.prop('checked', true);

        input.disabled = false;

        $(input).removeClass('bg-gray-100 border-gray-200').addClass('bg-white border-gray-300');

        if (input._flatpickr) {
            input._flatpickr.setDate(item.release_date || null, false);
        }

        checkbox.closest('.meeting-row').addClass('bg-blue-50');
    });
}

function initMeetingDatePickers() {
    $('.meeting-release-date').each(function () {
        const input = this;

        if (input._flatpickr) {
            return;
        }

        flatpickr(input, {
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
            time_24hr: true,
            minDate: 'today',
            disableMobile: true,

            onChange: function (selectedDates, dateStr, instance) {
                const input = instance.input;

                input.classList.remove('border-red-400');

                const row = input.closest('.meeting-row');
                const errorSpan = row?.querySelector('.meeting-error-date');

                if (errorSpan) {
                    errorSpan.textContent = '';
                }

                updateSummary();
            }
        });
    });
}

function initMeetingLogic() {

    initMeetingDatePickers();

    $(document).off('change', '.meeting-checkbox').on('change', '.meeting-checkbox', function () {
        
        const meeting = $(this).val();
        const dateInput = $(`.meeting-release-date[data-meeting="${meeting}"]`);

        const row = $(this).closest('.meeting-row');
        const errorSpan = row.find('.meeting-error-date');

        const input = dateInput[0];

        if (!input) {
            return;
        }

        if ($(this).is(':checked')) {
            input.disabled = false;

            dateInput.removeClass('bg-gray-100 border-gray-200').addClass('bg-white border-gray-300');

            row.addClass('bg-blue-50');

            errorSpan.text('');

        } else {
            if (input._flatpickr) {
                input._flatpickr.clear();
            }

            input.disabled = true;

            dateInput.removeClass('border-red-400 bg-white border-gray-300').addClass('bg-gray-100 border-gray-200');

            row.removeClass('bg-blue-50');

            errorSpan.text('');
        }

        updateMeetingSelectedCount();
        updateSummary();

        if ($('.meeting-checkbox:checked').length > 0) {
            $('#error-meetings').text('');
        }
    });
}

function updateSelectedRombelInformation() {
    const selectedOption = $('#dropdown-school-class option:selected');
    const classId = selectedOption.val();
    const className = selectedOption.data('class-name') || '';
    const mapelId = selectedOption.data('mapel') || '';
    const mapelName = selectedOption.data('mapel-name') || '';

    if (!classId) {
        $('#selected-rombel-information').addClass('hidden');
        $('#selected-rombel-name').text('');
        $('#selected-rombel-mapel').text('');
        $('#dynamic_mapel_id').remove();
        return;
    }

    $('#selected-rombel-name').text(className);
    $('#selected-rombel-mapel').text(mapelName);
    $('#selected-rombel-information').removeClass('hidden');

    $('#dynamic_mapel_id').remove();

    $('#content-for-release-form').append(
        `<input type="hidden" id="dynamic_mapel_id" name="mapel_id" value="${mapelId}">`
    );

    $('#error-school_class_id').text('');
    updateSummary();
}

$(document).on('change', '#dropdown-school-class', function () {
    updateSelectedRombelInformation();
});

$(document).on('change', 'input[name="lms_content_id"]', function () {
    const selected = $(this).closest('.content-item');
    const serviceId = selected.data('service');

    $('.content-item').removeClass('border-blue-400 bg-blue-50').addClass('border-gray-200');

    selected.removeClass('border-gray-200').addClass('border-blue-400 bg-blue-50');

    $('#hidden-service-id').remove();

    $('#content-for-release-form').append(
        `<input type="hidden" id="hidden-service-id" name="service_id" value="${serviceId}">`
    );

    $('#error-lms_content_id').text('');
    updateSelectedCount();
});

function updateSelectedCount() {
    const selected = document.querySelector('.content-checkbox:checked');
    const total = selected ? 1 : 0;

    $('#total-selected').text(`${total} Dipilih`);

    if (total > 0) {
        $('#error-lms_content_id').text('');
    }
}

function updateMeetingSelectedCount() {
    const total = $('.meeting-checkbox:checked').length;

    $('#total-meeting-selected').text(`${total} Pertemuan`);
}

function updateSummary() {
    const semester = $('#dropdown-semester').val();
    const selectedOption = $('#dropdown-school-class option:selected');
    const className = selectedOption.data('class-name') || '';
    const mapelName = selectedOption.data('mapel-name') || '';
    const totalMeeting = $('.meeting-checkbox:checked').length;

    $('#text-semester').text(semester ? `Semester ${semester}` : 'Belum memilih semester');
    $('#text-rombel').text(selectedOption.val() ? `${className}${mapelName ? ` - ${mapelName}` : ''}` : 'Belum memilih rombel');
    $('#text-meeting').text(`${totalMeeting} Pertemuan`);
}

$(document).on('change', '#dropdown-semester', function () {
    if ($(this).val()) {
        $('#error-semester').text('');
    }

    updateSummary();
});

$(document).on('change', '.meeting-release-date', function () {
    updateSummary();
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

    const status = $(this).data('status');
    const isActive = status === 'publish' ? 1 : 0;

    const form = $('#content-for-release-form')[0];
    const formData = new FormData(form);

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

            // RESET ALL
            $('#content-for-release-form')[0].reset();

            isProcessing = false;
            btn.prop('disabled', false);

            formContentForRelease(null, null, null, null, null, null, null, null, false);
            paginateContentForRelease();
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors || {};

                // Reset semua error
                $('.border-red-400').removeClass('border-red-400');
                $('.meeting-error-date').text('');
                $('.text-error').text('');

                $.each(errors, function (field, messages) {

                    // error meeting date
                    if (field.startsWith('meeting_date.')) {

                        const index = field.split('.')[1];

                        const checkbox = $(`.meeting-checkbox`).eq(index);
                        const meeting = checkbox.val();

                        const dateInput = $(`.meeting-release-date[data-meeting="${meeting}"]`);

                        const row = checkbox.closest('.meeting-row');
                        const errorSpan = row.find('.meeting-error-date');

                        dateInput.addClass('border-red-400');

                        errorSpan.text(messages[0]);

                        return;
                    }

                    // Error field biasa
                    $(`#error-${field}`).removeClass('hidden').text(messages[0]);
                    $(`[name="${field}"]`).addClass('border-red-400');
                });

                // Error khusus rombel
                if (errors.school_class_id) {
                    $('#error-school_class_id').removeClass('hidden').text(errors.school_class_id[0]);
                }

                // Error khusus content
                if (errors.lms_content_id) {
                    $('#error-lms_content_id').removeClass('hidden').text(errors.lms_content_id[0]);
                }

                // Error meeting secara global
                if (errors.pertemuan) {
                    $('#error-meetings').removeClass('hidden').text(errors.pertemuan[0]);
                }

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});