let selectedSemester = 1;

$(document).ready(function () {
    changeSemester(selectedSemester);
});

function changeSemester(semester) {

    selectedSemester = semester;

    const container = $('#container-load-assessment-schedule');

    const role = container.data('role');
    const schoolName = container.data('school-name');
    const schoolId = container.data('school-id');
    const curriculumId = container.data('curriculum-id');
    const mapelId = container.data('mapel-id');
    const assessmentTypeId = container.data('assessment-type-id');

    if (!container) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${selectedSemester}`,
        type: 'GET',
        dataType: 'json',

        success: function (response) {

            const loadAssessmentSchedule = $('#load-content-assessment-schedule');
            loadAssessmentSchedule.empty();

            if (!response.data || response.data.length === 0) {
                $('#empty-message-load-assessment-schedule').show();
                return;
            }

            $('#empty-message-load-assessment-schedule').hide();

            const assessments = response.data;

            // container grid untuk mode selain exam
            let gridContainer = `
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-12 w-full" id="assessment-grid"></div>
            `;

            loadAssessmentSchedule.append(gridContainer);

            assessments.forEach(assessment => {

                const startDate = assessment.start_date ? formatDate(assessment.start_date) : '-';
                const endDate = assessment.end_date ? formatDate(assessment.end_date) : '-';

                const now = new Date();
                const start = parseLocalDateTime(assessment.start_date);
                const end = parseLocalDateTime(assessment.end_date);

                const total_questions = assessment.total_questions;
                const total_answers = assessment.total_answers;

                const resultTestHref = assessment.resultTestHref.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumId', curriculumId)
                    .replace(':mapelId', mapelId).replace(':assessmentTypeId', assessmentTypeId).replace(':semester', selectedSemester).replace(':assessmentId', assessment.id);

                const isBefore = now < start;
                const isAfter = now > end;

                const mode = assessment.assessment_mode;

                let btnStartExam = '';

                if (isAfter || (total_questions > 0 && total_questions === total_answers)) {

                    btnStartExam = `
                        <a href="${resultTestHref}">
                            <button class="mt-6 bg-[#43AB3C] w-full ${mode === 'exam' ? 'sm:w-65' : 'text-white font-bold'} py-2 text-sm rounded-md font-medium shadow-md cursor-pointer">
                                Lihat Hasil Asesmen
                            </button>
                        </a>
                    `;

                } else {

                    btnStartExam = `
                        <button
                            onclick="startExamLocalTime(${assessment.id})"
                            class="mt-6 bg-[#43AB3C] w-full ${mode === 'exam' ? 'sm:w-65' : 'text-white font-bold'} py-2 text-sm rounded-md font-medium shadow-md cursor-pointer">
                            Mulai Asesmen
                        </button>
                    `;
                }

                // MODE EXAM
                if (mode === 'exam') {

                    let lockOverlay = '';

                    if (isBefore) {

                        lockOverlay = `
                            <div class="absolute inset-0 backdrop-blur-[3px] z-20"></div>

                            <div class="absolute inset-0 z-30 flex flex-col items-center justify-center gap-6 pointer-events-none">

                                <div class="relative w-39.25 h-39.25 flex items-center justify-center">
                                    <img src="/assets/images/assessment-asset/lock-bg.svg" class="absolute inset-0 w-full h-full object-contain">
                                    <img src="/assets/images/assessment-asset/padlock.svg" class="relative w-20 h-20 object-contain">
                                </div>

                                <div class="bg-[#0A5EA8] px-20 py-3 rounded-md text-white text-sm font-bold text-center">
                                    Kamu Belum Memiliki Akses Untuk Ini <br>
                                    <span class="text-xs">${startDate}</span>
                                </div>

                            </div>
                        `;
                    }

                    const card = `
                        <div 
                            class="relative z-10 text-white px-6 sm:px-8 pt-8 sm:pt-10 w-full min-h-120 sm:min-h-130 rounded-xl overflow-hidden shadow-xl bg-cover bg-center"
                            style="background-image: url('/assets/images/components/background-bc.svg'); background-color:#0071BC;">

                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-2xl font-semibold mt-1">
                                        ${assessment.school_assessment_type.name}
                                    </h2>
                                    <p class="text-sm opacity-80 font-medium">
                                        ${assessment.school_class?.class_name ?? ''}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-10">
                                <p class="text-lg mt-4 opacity-80">
                                    ${assessment.title}
                                </p>

                                <div class="mt-4 flex flex-col sm:flex-row sm:justify-between text-sm bg-white/10 backdrop-blur-md rounded-lg px-4 py-3 w-full sm:w-max gap-3 sm:gap-5">
                                    <div class="flex flex-col items-center">
                                        <span class="opacity-70 text-xs">Mulai Asesmen</span>
                                        <span class="font-medium">${startDate}</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="opacity-70 text-xs">Berakhir</span>
                                        <span class="font-medium">${endDate}</span>
                                    </div>
                                </div>

                                ${btnStartExam}
                            </div>

                            <div class="absolute bottom-0 left-0 w-full pointer-events-none">
                                <img src="/assets/images/assessment-asset/bg-white-wave.svg" class="w-full">
                            </div>

                            ${lockOverlay}
                        </div>
                    `;

                    loadAssessmentSchedule.append(card);
                }

                // MODE NON EXAM
                else {
                    
                    const projectResultTestHref = assessment.projectResultTestHref.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                        .replace(':curriculumId', curriculumId).replace(':mapelId', mapelId).replace(':assessmentTypeId', assessmentTypeId).replace(':semester', selectedSemester)
                        .replace(':assessmentId', assessment.id);
                    let btnSubmitProject = '';

                    if (!assessment.student_submitted) {

                        btnSubmitProject = `
                            <button type="button" id="btn-submit-project-${assessment.id}"
                                class="mt-6 bg-[#43AB3C] w-full ${mode === 'exam' ? 'sm:w-65' : 'text-white font-bold'} py-2 text-sm rounded-md font-medium shadow-md cursor-pointer 
                                disabled:cursor-pointer">
                                Kirim Project
                            </button>
                        `;
                    } else {

                        btnSubmitProject = `
                            <a href="${projectResultTestHref}">
                                <button class="mt-6 bg-[#43AB3C] w-full py-2 text-sm rounded-md shadow-md text-white font-bold cursor-pointer">
                                    Lihat Hasil Asesmen
                                </button>
                            </a>
                        `;
                    }

                    const gridCard = `
                        <div class="group bg-white border border-gray-200 rounded-2xl transition-all duration-200 p-6 flex flex-col justify-between
                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.5)]">

                            <div>

                                <div class="w-full flex items-center justify-between gap-4">
                                    <span class="text-[11px] font-medium px-3 py-1 rounded-full bg-blue-100 text-blue-600 mb-4">
                                        ${assessment.assessment_mode}
                                    </span>

                                    <span class="text-[11px] font-medium px-3 py-1 rounded-full bg-blue-100 text-blue-600 mb-4">
                                        ${assessment.mapel}
                                    </span>
                                </div>

                                <h3 class="text-base font-semibold text-gray-800 transition">
                                    ${assessment.title}
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    ${assessment.school_class?.class_name ?? ''}
                                </p>

                                <div class="mt-6 space-y-3 text-sm">

                                    <div class="flex justify-between items-center font-bold opacity-70">
                                        <span>Mulai</span>
                                        <span>${startDate}</span>
                                    </div>

                                    <div class="flex justify-between items-center font-bold opacity-70">
                                        <span>Berakhir</span>
                                        <span>${endDate}</span>
                                    </div>

                                </div>

                                ${assessment.description ? `
                                    <div class="mt-5 bg-gray-50 border border-gray-200 rounded-lg p-3">
                                        <p class="text-xs font-semibold text-gray-400 mb-1">
                                            Deskripsi Guru
                                        </p>
                                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">
                                            ${assessment.description}
                                        </p>
                                    </div>
                                ` : ''}

                                ${assessment.assessment_mode === 'project' ? `
                                    <!-- Preview file guru -->
                                    <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <p class="text-xs font-semibold text-gray-400 mb-1">
                                            File Project
                                        </p>
                                        ${assessment.assessment_value_file ? `
                                            ${assessment.assessment_value_file.endsWith('.mp4') ? `
                                                <video controls class="w-full h-75 rounded-md">
                                                    <source src="/assessment/assessment-file/${assessment.assessment_value_file}" type="video/mp4">
                                                    Browsermu tidak mendukung video.
                                                </video>
                                            ` : `
                                                <iframe src="/assessment/assessment-file/${assessment.assessment_value_file}" class="w-full h-75 rounded-md"></iframe>

                                            `}
                                        ` : `<p class="text-gray-400 text-sm">Tidak ada file referensi.</p>`}
                                    </div>

                                    ${assessment.student_submitted ? `

                                        <!-- PREVIEW SUBMISSION SISWA -->
                                        <div class="mt-6">

                                            <p class="text-xs font-semibold text-green-600 mb-2">
                                                Jawaban Kamu
                                            </p>

                                            <div class="bg-green-50 border border-green-200 rounded-xl p-4">

                                                ${assessment.student_submission_file ? `

                                                    <div class="flex items-center justify-between gap-4 bg-white border border-gray-200 rounded-lg p-3">

                                                        <div class="flex items-center gap-3 min-w-0">

                                                            <i class="fas fa-file-alt text-[#0071BC] text-lg"></i>

                                                            <div class="flex flex-col min-w-0">

                                                                <span class="text-sm font-medium text-gray-700 wrap-break-word truncate max-w-50">
                                                                    ${assessment.student_submission_filename ?? 'File Project'}
                                                                </span>

                                                                <span class="text-xs text-gray-400">
                                                                    File yang kamu upload
                                                                </span>

                                                            </div>

                                                        </div>

                                                        <button type="button"
                                                            onclick="previewStudentFile('${assessment.student_submission_file}')"
                                                            class="text-xs bg-[#0071BC] text-white px-3 py-1 rounded-md cursor-pointer">
                                                            Lihat
                                                        </button>

                                                    </div>

                                                ` : ''}

                                                ${assessment.student_submission_text ? `
                                                    <div class="bg-white rounded-lg text-sm text-gray-700">
                                                        <textarea id="project-text-${assessment.id}" rows="6" class="w-full border border-gray-300 rounded-xl p-3 text-sm outline-none resize-none"
                                                            disabled>${assessment.student_submission_text}</textarea>
                                                    </div>

                                                ` : ''}

                                            </div>

                                        </div>

                                    ` : `

                                        <!-- Upload file siswa -->
                                        <div class="mt-6">

                                            <p class="text-xs font-semibold text-gray-400 mb-2">
                                                Metode Pengumpulan
                                            </p>

                                            <!-- Toggle -->
                                            <div class="flex bg-gray-100 p-1 rounded-lg w-full">
                                                <button onclick="setProjectMode(${assessment.id}, 'file')" id="btn-file-${assessment.id}"
                                                    class="flex-1 py-2 text-xs font-semibold bg-[#0071BC] text-white rounded-md cursor-pointer">
                                                    Upload File
                                                </button>

                                                <button onclick="setProjectMode(${assessment.id}, 'text')" 
                                                    id="btn-text-${assessment.id}"
                                                    class="flex-1 py-2 text-xs font-semibold text-gray-500 rounded-md cursor-pointer">
                                                    Tulis Jawaban
                                                </button>
                                            </div>

                                            <form id="project-submission-form-${assessment.id}" enctype="multipart/form-data">

                                                <input type="hidden" id="submission-type-${assessment.id}" name="submission_type" value="file">

                                                <!-- FILE MODE -->
                                                <div id="project-file-container-${assessment.id}" class="mt-4">

                                                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition">

                                                        <p class="text-sm text-gray-500 mb-2">
                                                            Klik button untuk upload file
                                                        </p>

                                                        <button type="button"
                                                            onclick="document.getElementById('project-file-${assessment.id}').click()"
                                                            class="text-xs bg-[#0071BC] text-white px-4 py-2 rounded-md cursor-pointer">
                                                            Pilih File
                                                        </button>

                                                        <p class="text-xs text-gray-400 mt-3 flex items-center justify-center gap-1">
                                                            Format file: <b>PDF</b>
                                                            <i class="fas fa-circle text-[5px]"></i>
                                                            Maksimal <b>100 MB</b>
                                                        </p>

                                                    </div>
                                                </div>

                                                <!-- FILE PREVIEW -->
                                                <div id="file-preview-${assessment.id}" class="hidden mt-4">

                                                    <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-lg">

                                                        <div class="flex items-center gap-3">

                                                            <i class="fas fa-file-alt text-[#0071BC] text-lg"></i>

                                                            <div>
                                                                <p id="file-name-${assessment.id}" class="text-sm font-medium text-gray-700"></p>
                                                                <p id="file-size-${assessment.id}" class="text-xs text-gray-400"></p>
                                                            </div>

                                                        </div>

                                                        <button type="button" id="remove-file-${assessment.id}" class="text-red-500 hover:text-red-700 text-sm font-medium cursor-pointer">
                                                            Remove
                                                        </button>

                                                    </div>

                                                </div>

                                                <input type="file" id="project-file-${assessment.id}" name="project_file" accept="application/pdf" class="hidden"/>

                                                <span id="error-project-file-${assessment.id}" class="text-red-500 font-bold text-xs pt-2"></span>

                                                <!-- TEXT MODE -->
                                                <div id="project-text-container-${assessment.id}" class="hidden mt-4">

                                                    <textarea id="project-text-${assessment.id}" rows="6" name="project_text"
                                                        class="w-full border border-gray-300 rounded-xl p-3 text-sm outline-none resize-none"
                                                        placeholder="Tulis jawaban project kamu di sini..."></textarea>

                                                    <span id="error-project-text-${assessment.id}"
                                                        class="text-red-500 font-bold text-xs pt-2"></span>

                                                </div>

                                            </form>

                                        </div>

                                    `}

                                ` : ''}


                            </div>

                            ${assessment.assessment_mode !== 'project' ? `
                                <div class="mt-8">
                                    ${btnStartExam}
                                </div>
                            ` :
                            `
                                ${btnSubmitProject}
                            `
                        
                            }

                        </div>
                    `;

                    $('#assessment-grid').append(gridCard);
                }

            });

        }
    });
}

function formatDate(dateString) {

    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const date = parseLocalDateTime(dateString);

    if (!date) return '-';

    const day = date.getDate();
    const monthName = months[date.getMonth()];
    const year = date.getFullYear();
    const hour = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');

    return `${day} ${monthName} ${year} (${hour}:${minute})`;
}

function parseLocalDateTime(dateStr) {

    if (!dateStr) return null;

    const [datePart, timePart] = dateStr.split(' ');
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour, minute] = timePart.split(':').map(Number);

    return new Date(year, month - 1, day, hour, minute);
}

function startExamLocalTime(assessmentId) {

    $.getJSON(`/lms/check-assessment-status/${assessmentId}`, function (response) {

        const now = new Date();
        const start = parseLocalDateTime(response.start_date);
        const end = parseLocalDateTime(response.end_date);

        if (now < start) {

            Swal.fire({
                icon: 'warning',
                title: 'Belum Mulai',
                text: 'Sesi Asesmen belum dimulai.'
            });

            return;
        }

        if (now > end) {

            Swal.fire({
                icon: 'warning',
                title: 'Sudah Selesai',
                text: 'Sesi Asesmen telah berakhir.'
            });

            return;
        }

        const container = $('#container-load-assessment-schedule');

        const role = container.data('role');
        const schoolName = container.data('school-name');
        const schoolId = container.data('school-id');
        const curriculumId = container.data('curriculum-id');
        const mapelId = container.data('mapel-id');
        const assessmentTypeId = container.data('assessment-type-id');

        window.location.href =
            `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${selectedSemester}/test/${assessmentId}`;
    });
}

function setProjectMode(id, mode) {

    const fileContainer = document.getElementById(`project-file-container-${id}`);
    const textContainer = document.getElementById(`project-text-container-${id}`);
    const submissionType = document.getElementById(`submission-type-${id}`);

    const btnFile = document.getElementById(`btn-file-${id}`);
    const btnText = document.getElementById(`btn-text-${id}`);

    if (mode === 'file') {

        fileContainer.classList.remove('hidden');
        textContainer.classList.add('hidden');

        btnFile.classList.add('bg-[#0071BC]', 'text-white');
        btnText.classList.remove('bg-[#0071BC]', 'text-white');

        submissionType.value = 'file';

    } else {

        const projectFile = document.getElementById(`project-file-${id}`);
        const filePreview = document.getElementById(`file-preview-${id}`);
        const fileName = document.getElementById(`file-name-${id}`);
        const fileSize = document.getElementById(`file-size-${id}`);

        projectFile.value = '';

        filePreview.classList.add('hidden');

        fileName.textContent = '';
        fileSize.textContent = '';

        fileContainer.classList.add('hidden');
        textContainer.classList.remove('hidden');

        btnText.classList.add('bg-[#0071BC]', 'text-white');
        btnFile.classList.remove('bg-[#0071BC]', 'text-white');

        submissionType.value = 'text';

    }
}

$(document).on('click', '[id^="remove-file-"]', function () {

    const id = this.id.replace('remove-file-', '');

    const projectFile = document.getElementById(`project-file-${id}`);
    const filePreview = document.getElementById(`file-preview-${id}`);
    const fileName = document.getElementById(`file-name-${id}`);
    const fileSize = document.getElementById(`file-size-${id}`);
    const fileContainer = document.getElementById(`project-file-container-${id}`);

    projectFile.value = '';

    filePreview.classList.add('hidden');

    fileName.textContent = '';
    fileSize.textContent = '';

    fileContainer.classList.remove('hidden');

});

$(document).on('change', 'input[type="file"][id^="project-file-"]', function () {

    const id = this.id.replace('project-file-', '');

    const filePreview = document.getElementById(`file-preview-${id}`);
    const fileName = document.getElementById(`file-name-${id}`);
    const fileSize = document.getElementById(`file-size-${id}`);
    const fileContainer = document.getElementById(`project-file-container-${id}`);

    if (!this.files || this.files.length === 0) return;

    const file = this.files[0];

    fileName.textContent = truncateText(file.name, 25);
    fileSize.textContent = formatFileSize(file.size);

    filePreview.classList.remove('hidden');
    fileContainer.classList.add('hidden');

});

function formatFileSize(bytes) {

    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';

    return (bytes / 1048576).toFixed(2) + ' MB';
}

function truncateText(text, maxLength) {

    return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;

}

function previewStudentFile(file) {

    const modal = document.getElementById('studentFilePreviewModal');
    const container = document.getElementById('student-file-preview-content');

    const fileUrl = `/assessment/assessment-file-submission/${file}`;
    const extension = file.split('.').pop().toLowerCase();

    let html = '';

    // VIDEO
    if (extension === 'mp4') {

        html = `
            <video controls class="w-full h-125 object-contain rounded-lg bg-black">
                <source src="${fileUrl}" type="video/mp4">
                Browser tidak mendukung video
            </video>
        `;

    }

    // PDF
    else if (extension === 'pdf') {

        html = `
            <iframe 
                src="${fileUrl}" 
                class="w-full h-150 rounded-lg border">
            </iframe>
        `;

    }

    // FILE LAIN
    else {

        html = `
            <div class="text-center p-10">
                <p class="mb-4 text-gray-500">Preview tidak tersedia</p>

                <a 
                    href="${fileUrl}" 
                    target="_blank"
                    class="btn btn-primary btn-sm">
                    Download File
                </a>
            </div>
        `;

    }

    container.innerHTML = html;

    modal.showModal();
}

let isProcessing = false;

$(document).on('click', '[id^="btn-submit-project-"]', function (e) {
    e.preventDefault();

    const btn = $(this);
    const assessmentId = this.id.replace('btn-submit-project-', '');

    if (isProcessing) return;
    isProcessing = true;

    btn.prop('disabled', true);

    const container = $('#container-load-assessment-schedule');

    const role = container.data('role');
    const schoolName = container.data('school-name');
    const schoolId = container.data('school-id');
    const curriculumId = container.data('curriculum-id');
    const mapelId = container.data('mapel-id');
    const assessmentTypeId = container.data('assessment-type-id');

    const form = document.getElementById(`project-submission-form-${assessmentId}`);

    if (!form) {
        console.error("Form tidak ditemukan");
        return;
    }

    const formData = new FormData(form);

    formData.append('assessment_id', assessmentId);

    const fileInput = document.getElementById(`project-file-${assessmentId}`);
    const textValue = $(`#project-text-${assessmentId}`).val();

    // FILE
    if (fileInput.files.length > 0) {
        formData.append('project_file', fileInput.files[0]);
    }

    // TEXT
    if (textValue && textValue.trim() !== '') {
        formData.append('project_text', textValue);
    }

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${selectedSemester}/form/${assessmentId}/project-submission`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-project-submission').html(`
                <div class=" w-full flex justify-center">
                    <div class="fixed z-9999">
                            <div id="alertSuccess"
                                class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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

            changeSemester(selectedSemester);

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const response = xhr.responseJSON;

                // EXAM EXPIRED
                if (response?.status === 'expired' || response?.status === 'not_started') {

                    Swal.fire({
                        icon: 'warning',
                        title: 'expired',
                        text: response.message,
                    });

                    changeSemester(selectedSemester);
                }

                // VALIDATION ERROR
                if (xhr.status === 422 && response?.errors) {
                    const errors = response.errors;
                    
                    $.each(errors, function (field, messages) {
    
                        if (field === 'project_file') {
    
                            $(`#error-project-file-${assessmentId}`).text(messages[0]);
                            $(`#project-file-${assessmentId}`).addClass('border-red-400 border');
    
                        }
    
                        if (field === 'project_text') {
    
                            $(`#error-project-text-${assessmentId}`).text(messages[0]);
                            $(`#project-text-${assessmentId}`).addClass('border-red-400 border');
    
                        }
    
                    });
                }

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});