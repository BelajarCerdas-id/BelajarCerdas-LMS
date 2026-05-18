function paginateMaterialList() {

    const container = document.getElementById('container');

    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const meetingNumber = container.dataset.meetingNumber;
    const semester = container.dataset.semester;

    if (!role || !schoolName || !schoolId || !subjectTeacherId || !meetingNumber || !semester) return;

    fetchData();

    function fetchData() {

        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/${meetingNumber}/semester/${semester}/meeting-management/material/paginate`,
            method: 'GET',

            success: function (response) {

                const materialList = $('#grid-material-list');

                materialList.empty();
                
                if (response.data.length > 0) {
                    
                    $.each(response.data, function (index, item) {
                        // format tanggal
                        const formatDate = (dateString) => {
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
                            const date = new Date(dateString);
                            const day = date.getDate();
                            const monthName = months[date.getMonth()];
                            const year = date.getFullYear();
        
                            return `${day}-${monthName}-${year}`;
                        };
                        
                        const originalFilename = item.lms_content?.lms_content_item[0]?.original_filename ?? 'Judul tidak tersedia';
                        
                        const meetingDate = item.meeting_date ? formatDate(item.meeting_date) : 'Tanggal tidak tersedia';

                        const fileName = item.lms_content?.lms_content_item[0]?.value_file ?? '';

                        const fileUrl = `/lms-contents/${fileName}`;

                        const safeOriginalFilename = originalFilename.replace(/'/g, "\\'").replace(/\r/g, '').replace(/\n/g, '');

                        const card = `
                            <div onclick="openMateriModal('${safeOriginalFilename}', '${meetingDate}', '${fileUrl}')"

                                class="cursor-pointer flex items-center gap-4 p-4 rounded-2xl border border-gray-300 bg-slate-50 hover:bg-white hover:border-indigo-300 
                                    hover:shadow-md transition-all shrink-0 group">

                                <!-- ICON -->
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-indigo-500 border border-slate-100 shadow-sm shrink-0 
                                    group-hover:bg-indigo-50 transition-colors">
                                    <i class="fas fa-file-pdf text-lg"></i>
                                </div>

                                <!-- CONTENT -->
                                <div class="flex-1 min-w-0">

                                    <h4 class="font-bold text-slate-700 text-sm truncate group-hover:text-indigo-600 transition-colors">
                                        ${originalFilename}
                                    </h4>

                                    <p class="text-[11px] text-slate-400 mt-1">
                                        Dirilis: ${meetingDate}
                                    </p>

                                </div>

                                <!-- BUTTON -->
                                <button class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm cursor-pointer">

                                    <i class="fas fa-expand-arrows-alt"></i>

                                </button>

                            </div>
                        `;

                        materialList.append(card);
                    });

                    $('#empty-message-material-list').hide();

                } else {

                    $('#empty-message-material-list').show();
                }
            },

            error: function (xhr, status, error) {

                console.error('Terjadi kesalahan:', status, error);
            }
        });
    }
}

$(document).ready(function () {
    paginateMaterialList();
});

function openMateriModal(title, meetingDate, fileUrl) {

    const modal = document.getElementById('materiDetailModal');

    const titleElement = document.getElementById('detailMateriJudul');
    const dateElement = document.getElementById('detailMateriTanggal');
    const previewContainer = document.getElementById('materiPreviewContainer');

    // SET DATA
    titleElement.textContent = title;
    dateElement.textContent = `Dirilis: ${meetingDate}`;

    // RESET
    previewContainer.innerHTML = '';

    // VALIDASI KOSONG
    if (!fileUrl || fileUrl === '/lms-contents/') {

        renderFileNotFound();
        modal.showModal();
        return;
    }

    // LOADING
    previewContainer.innerHTML = `
        <div id="pdfLoadingState"
            class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100 z-10">

            <span class="loading loading-spinner loading-lg text-indigo-500"></span>

            <p class="mt-4 text-sm text-slate-500 font-medium">
                Memuat dokumen...
            </p>
        </div>
    `;

    modal.showModal();

    // CEK FILE EXIST
    fetch(fileUrl, { method: 'GET' })

        .then(response => {

            // STATUS GAGAL
            if (!response.ok) {

                renderFileNotFound();
                return;
            }

            // CEK CONTENT TYPE
            const contentType = response.headers.get('content-type');

            // BUKAN PDF
            if (!contentType || !contentType.includes('application/pdf')) {

                renderFileNotFound();
                return;
            }

            // FILE VALID
            previewContainer.innerHTML = `
            <iframe src="${fileUrl}" class="w-full h-full rounded-xl bg-white" frameborder="0"></iframe>
        `;
        })

        .catch(error => {

            console.error(error);

            renderFileNotFound();
        });

    // RENDER ERROR UI
    function renderFileNotFound() {

        previewContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center text-center h-full px-6">

                <div class="w-24 h-24 rounded-full bg-red-100 flex items-center justify-center mb-6">
                    <i class="fas fa-file-circle-xmark text-4xl text-red-400 cursor-pointer"></i>
                </div>

                <h3 class="text-xl font-bold text-slate-700 mb-2">
                    File Materi Tidak Ditemukan
                </h3>

                <p class="text-sm text-slate-500 max-w-md leading-relaxed">
                    File tidak tersedia, telah dihapus,
                    atau path file tidak valid.
                </p>

            </div>
        `;
    }
}