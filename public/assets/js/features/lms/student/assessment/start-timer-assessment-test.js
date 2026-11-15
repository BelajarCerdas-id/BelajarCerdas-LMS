// ============================================================
//  EXAM TIMER ONLY — anticheat detection lives in
//  anticheat/anticheat-core.js + platform files.
//  Shared state (examFinished, finalExamDuration, ...) and the
//  anticheat API (reportCheating, shouldSkipAntiCheat,
//  stopHeartbeat, enterFullscreen, ...) are provided by core.
// ============================================================

let countdown = null;
let lastTickTimestamp = 0;

// Function to display modal when exam time expires
function emptyTime() {
    isWarningModalOpen = true;
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Maaf, waktu ujian kamu sudah habis.',
        allowOutsideClick: false,
        allowEscapeKey: false
    });
}

function startTimer() {
    if (!containerFormAssessment.length) return;
    if (countdown !== null) return;
    if (examFinished) return;

    const timerExam = document.getElementById('timer-assessment-test');
    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const EXPIRE_KEY = `timer_assessment_test_expire_${assessmentId}`;

    const expireTime = localStorage.getItem(EXPIRE_KEY);

    if (expireTime && parseInt(expireTime) > Date.now()) {
        const remaining = Math.floor((parseInt(expireTime) - Date.now()) / 1000);
        runCountdown(remaining);
    } else {
        // Jika expireTime di localStorage sudah lewat atau belum ada,
        // SELALU verifikasi dengan server terlebih dahulu (karena admin bisa saja baru membuka kunci / memperpanjang durasi)
        startNewCountdown();
    }

    function startNewCountdown() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/start-timer`,
            method: 'GET',
            success: function (response) {
                const startTime = response.start_time;
                const expireTime = response.expire_time;
                localStorage.setItem(START_KEY, startTime);
                localStorage.setItem(EXPIRE_KEY, expireTime);

                const remaining = Math.ceil((expireTime - Date.now()) / 1000);
                if (remaining > 0) {
                    runCountdown(remaining);
                } else {
                    clearInterval(countdown);
                    countdown = null;

                    if (timerExam) timerExam.textContent = 'Waktu Habis';

                    finalExamDuration = getTotalExamDuration();
                    saveQuestionDuration();
                    emptyTime();
                    autoSubmitUnSavedQuestions();

                    localStorage.removeItem(EXPIRE_KEY);
                    localStorage.removeItem(START_KEY);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const response = xhr.responseJSON;
                    if (response?.status === 'not_started') {
                        isWarningModalOpen = true;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Assessment Belum Dimulai',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            isWarningModalOpen = false;
                        });
                        return;
                    }

                    if (response?.status === 'expired') {
                        isWarningModalOpen = true;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Assessment Telah Berakhir',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            isWarningModalOpen = false;
                        });
                        return;
                    }
                }
            }
        });
    }

    function runCountdown(seconds) {
        updateTimerDisplay(seconds);

        // Initialize tick timestamp for freeze detection
        lastTickTimestamp = Date.now();

        countdown = setInterval(() => {
            const now = Date.now();

            // Freeze detection: if the gap between ticks is > 3 seconds,
            // the browser was likely backgrounded (especially iOS Safari).
            // JS execution is suspended when the app goes to background on mobile.
            if (lastTickTimestamp && (now - lastTickTimestamp) > 3000) {
                if (!shouldSkipAntiCheat()) {
                    reportCheating('js_freeze_detected');
                }
            }
            lastTickTimestamp = now;

            seconds--;
            updateTimerDisplay(seconds);

            if (seconds <= 0 && !examFinished) {
                examFinished = true;

                clearInterval(countdown);
                countdown = null;

                if (timerExam) timerExam.textContent = 'Waktu Habis';

                saveQuestionDuration();
                finalExamDuration = getTotalExamDuration();
                emptyTime();
                autoSubmitUnSavedQuestions();

                localStorage.removeItem(START_KEY);
                localStorage.removeItem(EXPIRE_KEY);
            }
        }, 1000);
    }

    function updateTimerDisplay(seconds) {
        if (!timerExam) return;
        const safeSeconds = Math.max(0, seconds);
        const hours = Math.floor(safeSeconds / 3600);
        const minutes = Math.floor((safeSeconds % 3600) / 60);
        const secs = safeSeconds % 60;
        timerExam.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
}

function stopTimer() {
    clearInterval(countdown);
    countdown = null;
}

function getTotalExamDuration() {
    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const EXPIRE_KEY = `timer_assessment_test_expire_${assessmentId}`;

    const startTime = parseInt(localStorage.getItem(START_KEY));
    const expireTime = parseInt(localStorage.getItem(EXPIRE_KEY));
    if (!startTime || !expireTime) return 0;

    const now = Date.now();
    const totalDuration = Math.floor((expireTime - startTime) / 1000);
    const remaining = Math.max(0, Math.floor((expireTime - now) / 1000));
    return totalDuration - remaining;
}
