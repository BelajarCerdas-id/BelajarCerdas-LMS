function openAttendanceModal() {

    const modal = document.getElementById('attendanceModal');

    modal.showModal();

    paginateStudentAttendance();
}

function paginateStudentAttendance() {

    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const meetingNumber = container.dataset.meetingNumber;
    const semester = container.dataset.semester;

    const studentContainer = $('#studentListContainer');

    // LOADING
    studentContainer.html(`
        <div class="flex flex-col items-center justify-center py-14">
            <i class="fas fa-circle-notch fa-spin text-4xl text-emerald-500 mb-4"></i>

            <p class="font-semibold text-slate-600">
                Memuat data siswa...
            </p>
        </div>
    `);

    $.ajax({

        url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/${meetingNumber}/semester/${semester}/student-attendance/paginate`,

        method: 'GET',

        success: function (response) {

            studentContainer.empty();

            if (response.data.length === 0) {

                studentContainer.html(`
                    <div class="border-2 border-dashed border-slate-200 rounded-3xl bg-white py-14 text-center">
                        <i class="fas fa-users text-4xl text-slate-300 mb-4"></i>

                        <h4 class="font-bold text-slate-600 text-lg">
                            Belum Ada Siswa
                        </h4>

                        <p class="text-sm text-slate-400 mt-1">
                            Tidak ada data siswa pada kelas ini.
                        </p>
                    </div>
                `);

                return;
            }

            $.each(response.data, function (index, student) {

                const studentName = student.student_profile?.nama_lengkap ?? 'Nama tidak tersedia';

                const savedStatus = student.subject_attendance?.[0]?.attendance_status;

                studentAttendanceData[student.id] = savedStatus;

                const card = `
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-4 hover:border-emerald-200 hover:shadow-md transition-all">

                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                            <!-- PROFILE -->
                            <div class="flex items-center gap-4">

                                <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-lg font-black shadow-md">
                                    ${studentName.charAt(0)}
                                </div>

                                <div>
                                    <h4 class="font-bold text-slate-700 text-base">
                                        ${studentName}
                                    </h4>

                                    <p class="text-xs text-slate-400 mt-1">
                                        ${student.role ?? 'Role tidak tersedia'}
                                    </p>
                                </div>

                            </div>

                            <!-- STATUS -->
                            <div class="flex flex-wrap gap-2">

                                ${renderAttendanceButton(student.id, 'hadir', savedStatus)}
                                ${renderAttendanceButton(student.id, 'izin', savedStatus)}
                                ${renderAttendanceButton(student.id, 'sakit', savedStatus)}
                                ${renderAttendanceButton(student.id, 'alpa', savedStatus)}

                            </div>

                        </div>

                    </div>
                `;

                studentContainer.append(card);
            });
        },

        error: function () {

            studentContainer.html(`
                <div class="border-2 border-dashed border-red-200 rounded-3xl bg-red-50 py-14 text-center">

                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>

                    <h4 class="font-bold text-red-600 text-lg">
                        Gagal Memuat Data
                    </h4>

                    <p class="text-sm text-red-500 mt-1">
                        Terjadi kesalahan saat mengambil data siswa.
                    </p>

                </div>
            `);
        }
    });
}

function renderAttendanceButton(studentId, status, activeStatus) {

    const config = {
        hadir: {
            active: 'bg-emerald-500 text-white border-emerald-500 shadow-md shadow-emerald-100 cursor-pointer',
            inactive: 'bg-white text-emerald-500 border-emerald-200 hover:bg-emerald-50 cursor-pointer'
        },

        izin: {
            active: 'bg-blue-500 text-white border-blue-500 shadow-md shadow-blue-100 cursor-pointer',
            inactive: 'bg-white text-blue-500 border-blue-200 hover:bg-blue-50 cursor-pointer'
        },

        sakit: {
            active: 'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-100 cursor-pointer',
            inactive: 'bg-white text-amber-500 border-amber-200 hover:bg-amber-50 cursor-pointer'
        },

        alpa: {
            active: 'bg-red-500 text-white border-red-500 shadow-md shadow-red-100 cursor-pointer',
            inactive: 'bg-white text-red-500 border-red-200 hover:bg-red-50 cursor-pointer'
        }
    };

    const isActive = status === activeStatus;

    return `
        <button
            onclick="setAttendanceStatus(${studentId}, '${status}')"

            class=" attendance-btn-${studentId} border px-4 py-2 rounded-xl text-xs font-extrabold uppercase tracking-wide transition-all 
                ${isActive ? config[status].active : config[status].inactive}" data-status="${status}">
                ${status}
        </button>
    `;
}

function setAttendanceStatus(studentId, status) {

    studentAttendanceData[studentId] = status;

    const buttons = document.querySelectorAll(`.attendance-btn-${studentId}`);

    buttons.forEach(button => {

        const buttonStatus = button.dataset.status;

        button.className = `attendance-btn-${studentId} border px-4 py-2 rounded-xl text-xs font-extrabold uppercase tracking-wide transition-all`;

        if (buttonStatus === status) {

            if (status === 'hadir') {
                button.classList.add(
                    'bg-emerald-500',
                    'text-white',
                    'border-emerald-500'
                );
            }

            if (status === 'izin') {
                button.classList.add(
                    'bg-blue-500',
                    'text-white',
                    'border-blue-500'
                );
            }

            if (status === 'sakit') {
                button.classList.add(
                    'bg-amber-500',
                    'text-white',
                    'border-amber-500'
                );
            }

            if (status === 'alpa') {
                button.classList.add(
                    'bg-red-500',
                    'text-white',
                    'border-red-500'
                );
            }

        } else {

            button.classList.add(
                'bg-white',
                'text-slate-400',
                'border-slate-200'
            );
        }
    });
}

function submitAttendance() {

    const container = document.getElementById('container');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;
    const meetingNumber = container.dataset.meetingNumber;
    const semester = container.dataset.semester;

    const attendances = [];

    Object.keys(studentAttendanceData).forEach(studentId => {

        attendances.push({
            student_id: studentId,
            status: studentAttendanceData[studentId]
        });
    });

    $.ajax({

        url: `/lms/${role}/${schoolName}/${schoolId}/subject-attendance/classes/subject-teacher/${subjectTeacherId}/meeting-list/${meetingNumber}/semester/${semester}/student-attendance/store`,

        method: 'POST',

        data: {
            attendances: attendances,
            _token: $('meta[name="csrf-token"]').attr('content')
        },

        success: function (response) {
            const modal = document.getElementById('attendanceModal');

            modal.close();

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: response.message,
                timer: 2000,
            });

            document.getElementById('attendanceModal').close();

            fetchAttendanceChart();
        },

        error: function () {
            const modal = document.getElementById('attendanceModal');

            modal.close();

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat menyimpan presensi'
            });
        }
    });
}