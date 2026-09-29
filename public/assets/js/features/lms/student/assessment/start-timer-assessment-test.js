let countdown = null;
let finalExamDuration = 0;
let examFinished = false;
let cheatingListenerAttached = false;
let attemptStatusChecked = false;
let lastCheatReport = 0;
let isPageReloading = false;
let antiCheatCooldown = true;
let isWarningModalOpen = false;
let antiCheatInitialized = false;
let blurTimeout = null;

// Helper: Check if HTML5 Fullscreen API is supported by the device / browser
function isFullscreenSupported() {
    const doc = document;
    const el = doc.documentElement;
    return !!(
        doc.fullscreenEnabled ||
        doc.webkitFullscreenEnabled ||
        doc.mozFullScreenEnabled ||
        doc.msFullscreenEnabled ||
        el.requestFullscreen ||
        el.webkitRequestFullscreen ||
        el.mozRequestFullScreen ||
        el.msRequestFullscreen
    );
}

// Helper: Get active fullscreen element across all vendor prefixes
function getFullscreenElement() {
    return (
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement ||
        null
    );
}

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

        countdown = setInterval(() => {
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

    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const EXPIRE_KEY = `timer_assessment_test_expire_${assessmentId}`;

    const startTime = parseInt(localStorage.getItem(START_KEY));
    const expireTime = parseInt(localStorage.getItem(EXPIRE_KEY));
    if (!startTime || !expireTime) return;

    const totalDuration = Math.floor((expireTime - startTime) / 1000);
    const usedDuration = Math.floor((Date.now() - startTime) / 1000);
    const finalUsed = Math.min(usedDuration, totalDuration);

    const hours = Math.floor(finalUsed / 3600);
    const minutes = Math.floor((finalUsed % 3600) / 60);
    const seconds = finalUsed % 60;

    const formatted = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
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

function cheatingDetection() {
    if (cheatingListenerAttached) return;
    cheatingListenerAttached = true;

    // Visibility change detection (works on PC, Android, and iOS Safari)
    document.addEventListener("visibilitychange", function () {
        if (examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen) return;

        if (document.hidden) {
            reportCheating('visibility_hidden');
        }
    });

    // Pagehide listener for mobile app switching / multitasking (especially iOS Safari)
    window.addEventListener("pagehide", function () {
        if (examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen) return;
        reportCheating('page_hide');
    });
}

function checkAttemptStatus() {
    if (attemptStatusChecked) return;
    attemptStatusChecked = true;

    if (examFinished || isPageReloading || antiCheatCooldown) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/attempt-status`,
        method: 'GET',
        success: function (res) {
            if (examFinished) return;

            if (res.status === 'warning') {
                isWarningModalOpen = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: `Kamu terdeteksi meninggalkan halaman ujian, batas kesempatan (${res.count}/3)`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: isFullscreenSupported() ? 'Masuk Fullscreen' : 'Lanjutkan Ujian',
                    reverseButtons: true
                }).then((result) => {
                    isWarningModalOpen = false;
                    if (result.isConfirmed) {
                        examStarted = true;
                        enterFullscreen();
                    }
                    setTimeout(() => {
                        antiCheatCooldown = false;
                    }, 1000);
                });
            }

            if (res.status === 'blocked') {
                examFinished = true;
                finalExamDuration = getTotalExamDuration();
                saveQuestionDuration();
                stopTimer();
                stopQuestionTimer();

                isWarningModalOpen = true;
                Swal.fire({
                    icon: 'error',
                    title: 'Ujian dihentikan',
                    text: 'Terlalu sering meninggalkan halaman.',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                autoSubmitUnSavedQuestions();
            }
        }
    });
}

function enterFullscreen() {
    if (!isFullscreenSupported()) {
        antiCheatCooldown = false;
        return Promise.resolve();
    }

    const el = document.documentElement;
    try {
        if (el.requestFullscreen) {
            return el.requestFullscreen().catch(() => {});
        } else if (el.webkitRequestFullscreen) {
            return el.webkitRequestFullscreen();
        } else if (el.mozRequestFullScreen) {
            return el.mozRequestFullScreen();
        } else if (el.msRequestFullscreen) {
            return el.msRequestFullscreen();
        }
    } catch (e) {
        // Suppress unhandled exceptions if user interaction policy rejected request
    }
}

function detectFullscreenExit() {
    const handleFullscreenChange = function () {
        const fsEl = getFullscreenElement();
        if (fsEl) {
            antiCheatCooldown = false;
            return;
        }

        if (examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen) return;

        // Only report if fullscreen was actually supported on this device
        if (isFullscreenSupported()) {
            reportCheating('fullscreen_exit');
        }
    };

    // Standard + vendor-prefixed fullscreen events (iPadOS, older Chrome, etc.)
    document.addEventListener("fullscreenchange", handleFullscreenChange);
    document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
    document.addEventListener("mozfullscreenchange", handleFullscreenChange);
    document.addEventListener("MSFullscreenChange", handleFullscreenChange);
}

function detectKeyboardCheating() {
    document.addEventListener("keydown", function (e) {
        if (examFinished || isPageReloading || antiCheatCooldown) return;

        const isCmdOrCtrl = e.ctrlKey || e.metaKey;
        const key = e.key ? e.key.toLowerCase() : '';

        // DevTools shortcuts (F12, Ctrl/Cmd + Shift + I/J/C)
        if (e.key === "F12" || (isCmdOrCtrl && e.shiftKey && ['i', 'j', 'c'].includes(key))) {
            e.preventDefault();
            reportCheating('devtools_shortcut');
            return;
        }

        // F11 (Fullscreen toggle)
        if (e.key === "F11") {
            e.preventDefault();
            reportCheating('f11_shortcut');
            return;
        }

        // ESC (Exit Fullscreen)
        if (e.key === "Escape" || e.key === "Esc") {
            if (isFullscreenSupported() && getFullscreenElement()) {
                reportCheating('escape_exit');
            }
            return;
        }

        // Browser navigation / Tab management shortcuts (Ctrl/Cmd + T, W, N)
        if (isCmdOrCtrl && (key === 't' || key === 'w' || key === 'n')) {
            e.preventDefault();
            reportCheating('tab_shortcut');
            return;
        }

        // Tab navigation shortcuts (Ctrl/Cmd + Tab, Alt + Tab)
        if ((isCmdOrCtrl && e.key === "Tab") || (e.altKey && e.key === "Tab")) {
            reportCheating('alt_or_ctrl_tab');
            return;
        }

        // View source or Save shortcuts (Ctrl/Cmd + U, S, P)
        if (isCmdOrCtrl && (key === 'u' || key === 's' || key === 'p')) {
            e.preventDefault();
            return;
        }

        // Copy, Paste, Cut outside of inputs
        if (isCmdOrCtrl && ['c', 'v', 'x', 'a'].includes(key)) {
            const activeEl = document.activeElement;
            const isInput = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable);
            if (!isInput) {
                e.preventDefault();
            }
        }
    }, true);
}

function disableCopyPaste() {
    const blockUnlessInput = function (e) {
        const activeEl = document.activeElement;
        const isInput = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable);
        if (!isInput) {
            e.preventDefault();
        }
    };

    document.addEventListener("copy", blockUnlessInput);
    document.addEventListener("paste", blockUnlessInput);
    document.addEventListener("cut", blockUnlessInput);
}

function disableRightClick() {
    document.addEventListener("contextmenu", function (e) {
        e.preventDefault();
    });
}

function disableTextSelection() {
    if (document.getElementById('anti-cheat-selection-lock')) return;
    const style = document.createElement('style');
    style.id = 'anti-cheat-selection-lock';
    style.textContent = `
        body {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-touch-callout: none !important;
        }
        input, textarea, [contenteditable="true"] {
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            user-select: text !important;
            -webkit-touch-callout: default !important;
        }
    `;
    document.head.appendChild(style);
}

function detectWindowBlur() {
    window.addEventListener("blur", function () {
        if (examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen) return;
        if (document.hidden) return; // Handled by visibilitychange

        // Check if blur was triggered by interactive form input (e.g. mobile select dropdown, virtual keyboard)
        const activeEl = document.activeElement;
        if (activeEl) {
            const tag = activeEl.tagName;
            if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA' || activeEl.isContentEditable) {
                return;
            }
            if (activeEl.closest && (activeEl.closest('.swal2-container') || activeEl.closest('dialog') || activeEl.closest('.modal'))) {
                return;
            }
        }

        if (blurTimeout) clearTimeout(blurTimeout);

        // 250ms buffer: filter out transient mobile browser picker/keyboard blurs
        blurTimeout = setTimeout(() => {
            if (examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen) return;
            if (document.hidden) return;
            if (document.hasFocus && document.hasFocus()) return;

            reportCheating('window_blur');
        }, 250);
    });

    window.addEventListener("focus", function () {
        if (blurTimeout) {
            clearTimeout(blurTimeout);
            blurTimeout = null;
        }
    });
}

function enforceFullscreenAfterReload() {
    if (examFinished) return;
    if (!isFullscreenSupported()) return; // Skip for devices without Fullscreen API (e.g. iPhone)

    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const examWasStarted = localStorage.getItem(START_KEY);

    if (!examWasStarted) return;
    if (getFullscreenElement()) return;

    isWarningModalOpen = true;
    Swal.fire({
        icon: 'warning',
        title: 'Mode Fullscreen Wajib',
        text: 'Ujian harus dilakukan dalam mode fullscreen.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonText: 'Masuk Fullscreen'
    }).then((result) => {
        isWarningModalOpen = false;
        if (result.isConfirmed) {
            enterFullscreen();
        }
    });
}

function reportCheating(reason = 'unspecified') {
    if (examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen) return;

    const now = Date.now();

    // 1500ms cooldown to avoid cascading/duplicate triggers from related events
    if (now - lastCheatReport < 1500) {
        return;
    }

    lastCheatReport = now;
    antiCheatCooldown = true;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/report-tab-switch`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            reason: reason
        },
        success: function (res) {
            if (examFinished) return;

            if (res.status === 'warning') {
                isWarningModalOpen = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: `Kamu terdeteksi meninggalkan halaman ujian, batas kesempatan (${res.count}/3)`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: isFullscreenSupported() ? 'Masuk Fullscreen' : 'Lanjutkan Ujian',
                    reverseButtons: true
                }).then((result) => {
                    isWarningModalOpen = false;
                    if (result.isConfirmed) {
                        examStarted = true;
                        enterFullscreen();
                    }
                    setTimeout(() => {
                        antiCheatCooldown = false;
                    }, 1000);
                });
            }

            if (res.status === 'blocked') {
                examFinished = true;
                finalExamDuration = getTotalExamDuration();
                saveQuestionDuration();
                stopTimer();
                stopQuestionTimer();

                isWarningModalOpen = true;
                Swal.fire({
                    icon: 'error',
                    title: 'Ujian dihentikan',
                    text: 'Terlalu sering meninggalkan halaman.',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                autoSubmitUnSavedQuestions();
            }
        },
        error: function () {
            // Restore antiCheatCooldown on error
            setTimeout(() => {
                antiCheatCooldown = false;
            }, 2000);
        }
    });
}

function initAntiCheatSystem() {
    if (antiCheatInitialized) return;
    antiCheatInitialized = true;

    cheatingDetection();
    detectFullscreenExit();
    detectKeyboardCheating();
    detectWindowBlur();

    disableCopyPaste();
    disableRightClick();
    disableTextSelection();

    enforceFullscreenAfterReload();

    // If device doesn't support HTML5 fullscreen (e.g. iPhone), activate anti-cheat after brief buffer
    setTimeout(() => {
        const START_KEY = `timer_assessment_test_start_${assessmentId}`;
        if (!isFullscreenSupported() && localStorage.getItem(START_KEY)) {
            antiCheatCooldown = false;
        }
    }, 1500);
}