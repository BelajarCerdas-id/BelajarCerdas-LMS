let studentAttendanceData = {};
let myAbsensiChart = null;

let currentTaskId = null;
let taskGradesData = {};

document.addEventListener('DOMContentLoaded', function () {
    const dataStats = {
        hadir: window.attendanceStats?.hadir ?? 0,
        izin: window.attendanceStats?.izin ?? 0,
        sakit: window.attendanceStats?.sakit ?? 0,
        alpa: window.attendanceStats?.alpa ?? 0
    };

    initOrUpdateChart(dataStats);
});

function switchTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.add('hidden');
    });

    document.getElementById('tab-' + tabId).classList.remove('hidden');

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove(
            'bg-white',
            'text-indigo-600',
            'z-10',
            'shadow-none'
        );

        btn.classList.add(
            'bg-slate-100',
            'text-slate-500',
            'z-0',
            'shadow-inner'
        );
    });

    const activeBtn = document.getElementById('btn-tab-' + tabId);

    activeBtn.classList.remove(
        'bg-slate-100',
        'text-slate-500',
        'z-0',
        'shadow-inner'
    );

    activeBtn.classList.add(
        'bg-white',
        'text-indigo-600',
        'z-10',
        'shadow-none'
    );
}

function openAttendanceModal() {

    const modal = document.getElementById('attendanceModal');

    modal.showModal();

    paginateStudentAttendance();
}

function submitAksi(message, modalId) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        timer: 2000,
        showConfirmButton: false
    }).then(() => {
        closeModal(modalId);
    });
}