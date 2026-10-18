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
let heartbeatInterval = null;
let lastTickTimestamp = 0;
window.isSelectingFile = false;

window.setSelectingFile = function (active) {
    window.isSelectingFile = !!active;
    if (!active) {
        antiCheatCooldown = true;
        setTimeout(function () {
            antiCheatCooldown = false;
        }, 1500);
    }
};

// ============================================================
//  GUARD HELPER — single source of truth for skip conditions
// ============================================================

function shouldSkipAntiCheat() {
    return examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen || window.isSelectingFile;
}

// ============================================================
//  FULLSCREEN HELPERS (standard + webkit for Safari)
// ============================================================

function isFullscreenSupported() {
    return !!(
        document.fullscreenEnabled ||
        document.webkitFullscreenEnabled
    );
}

function getFullscreenElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || null;
}

// ============================================================
//  TIMER FUNCTIONS
// ============================================================

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

// ============================================================
//  ANTI-CHEAT LAYER 1: Page Visibility API
//  - Reliable on desktop and Android
//  - Partially reliable on iOS Safari (misses Control Center, Notification Center)
// ============================================================

function cheatingDetection() {
    if (cheatingListenerAttached) return;
    cheatingListenerAttached = true;

    // visibilitychange: fires on tab switch, minimize, app switch (desktop + Android)
    document.addEventListener("visibilitychange", function () {
        if (shouldSkipAntiCheat()) return;

        if (document.hidden) {
            reportCheating('visibility_hidden');
        }
    });

    // pagehide: fires when the page is being unloaded or hidden (iOS app switch / navigation)
    window.addEventListener("pagehide", function () {
        if (shouldSkipAntiCheat()) return;
        reportCheating('page_hide');
    });

    // pageshow with persisted flag: fires when the page is restored from bfcache (back/forward navigation)
    // This catches iOS Safari restoring the page from background
    window.addEventListener("pageshow", function (e) {
        if (e.persisted && !shouldSkipAntiCheat()) {
            reportCheating('page_restored_from_cache');
        }
    });
}

// ============================================================
//  ANTI-CHEAT LAYER 2: Page Lifecycle — freeze/resume
//  - Catches iOS/Android backgrounding via the Page Lifecycle API
//  - Falls back to timestamp gap detection in the timer (see runCountdown)
// ============================================================

function detectFreezeResume() {
    // "freeze" fires when browser is about to suspend JS (mobile backgrounding)
    document.addEventListener("freeze", function () {
        if (shouldSkipAntiCheat()) return;
        reportCheating('page_freeze');
    });

    // "resume" fires when JS execution resumes after a freeze
    document.addEventListener("resume", function () {
        if (shouldSkipAntiCheat()) return;
        reportCheating('page_resume');
    });
}

// ============================================================
//  ANTI-CHEAT LAYER 3: Server Heartbeat
//  - Sends a ping to the server every 5 seconds
//  - If iOS suspends JS, the pings stop and the server detects the gap
//  - This is the MOST RELIABLE method for detecting iOS backgrounding
// ============================================================

function startHeartbeat() {
    if (heartbeatInterval) return;

    heartbeatInterval = setInterval(function () {
        if (examFinished) {
            clearInterval(heartbeatInterval);
            heartbeatInterval = null;
            return;
        }

        const heartbeatUrl = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/heartbeat`;
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: heartbeatUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                timestamp: Date.now(),
                is_selecting_file: !!window.isSelectingFile
            },
            timeout: 4000,
            success: function (res) {
                if (examFinished) return;

                // Server detected a heartbeat gap (e.g., iOS backgrounding)
                if (res.status === 'warning') {
                    isWarningModalOpen = true;
                    const maxCount = res.max_count || 5;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan!',
                        text: `Kamu terdeteksi meninggalkan halaman ujian, batas kesempatan (${res.count}/${maxCount})`,
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
                    stopHeartbeat();

                    isWarningModalOpen = true;
                    Swal.fire({
                        icon: 'error',
                        title: 'Ujian dihentikan',
                        text: 'Terlalu sering meninggalkan halaman.',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });

                    autoSubmitUnSavedQuestions(null, 'cheating');
                }
            }
        });
    }, 5000);
}

function stopHeartbeat() {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
        heartbeatInterval = null;
    }
}

// ============================================================
//  ANTI-CHEAT LAYER 4: Viewport Ratio Heuristic
//  - Detects split-screen (Android) and Slide Over (iPadOS)
//  - Uses visualViewport API to compare viewport width to screen width
// ============================================================

function detectViewportResize() {
    const vv = window.visualViewport;
    if (!vv) return; // Not supported in very old browsers

    let lastReportedRatio = 1;
    // Capture initial viewport dimensions to distinguish keyboard from split-screen
    const initialWidth = vv.width;
    const initialHeight = vv.height;

    function checkViewportRatio() {
        if (shouldSkipAntiCheat()) return;

        // Skip if user is actively typing — virtual keyboard resizes the viewport
        if (isInteractingWithInput()) return;

        // Use screen.availWidth for more accurate comparison (excludes system UI)
        const screenWidth = screen.availWidth || screen.width;
        const widthRatio = vv.width / screenWidth;

        // Only check WIDTH ratio — virtual keyboard only shrinks height, not width.
        // Split-screen and Slide Over reduce the width.
        // Also verify width actually changed from initial (not just a height-only keyboard resize)
        const widthChanged = Math.abs(vv.width - initialWidth) > 50;

        if (widthRatio < 0.80 && lastReportedRatio >= 0.80 && widthChanged) {
            reportCheating('viewport_split_screen');
        }
        lastReportedRatio = widthRatio;
    }

    vv.addEventListener('resize', function () {
        // Delay to let the viewport settle (keyboard animation takes ~300ms)
        setTimeout(checkViewportRatio, 400);
    });

    // Also check on window resize as a fallback
    window.addEventListener('resize', function () {
        setTimeout(checkViewportRatio, 400);
    });
}

// ============================================================
//  ANTI-CHEAT LAYER 5: Fullscreen Exit Detection (Desktop)
//  - Standard fullscreenchange + webkit for Safari
// ============================================================

function detectFullscreenExit() {
    const handleFullscreenChange = function () {
        const fsEl = getFullscreenElement();
        if (fsEl) {
            // Entered fullscreen — disable cooldown
            antiCheatCooldown = false;
            return;
        }

        if (shouldSkipAntiCheat()) return;

        // Skip if user is typing — on some mobile browsers, opening the
        // virtual keyboard causes fullscreen to exit automatically
        if (isInteractingWithInput()) return;

        // Only report if fullscreen was actually supported on this device
        if (isFullscreenSupported()) {
            reportCheating('fullscreen_exit');
        }
    };

    document.addEventListener("fullscreenchange", handleFullscreenChange);
    document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
}

// ============================================================
//  ANTI-CHEAT LAYER 6: Keyboard Shortcut Blocking (Desktop)
//  - Blocks DevTools, tab management, source view, and clipboard shortcuts
//  - Only relevant on desktop (mobile doesn't have these shortcuts)
// ============================================================

function detectKeyboardCheating() {
    document.addEventListener("keydown", function (e) {
        if (shouldSkipAntiCheat()) return;

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
        if (e.key === "Escape") {
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

        // Copy, Paste, Cut, Select All outside of inputs
        if (isCmdOrCtrl && ['c', 'v', 'x', 'a'].includes(key)) {
            if (!isInputElement(document.activeElement)) {
                e.preventDefault();
            }
        }
    }, true);
}

// ============================================================
//  ANTI-CHEAT LAYER 7: Window Blur Detection (Desktop + Android)
//  - Supplementary detection for focus loss
//  - Heavily filtered to avoid false positives on mobile
// ============================================================

function detectWindowBlur() {
    window.addEventListener("blur", function () {
        if (shouldSkipAntiCheat()) return;
        if (document.hidden) return; // Already handled by visibilitychange

        // Skip if user is interacting with any input or CKEditor
        if (isInteractingWithInput()) return;

        // Skip if blur was triggered by interactive form elements or modals
        const activeEl = document.activeElement;
        if (activeEl) {
            const tag = activeEl.tagName;
            // Mobile browsers trigger blur for native pickers, virtual keyboard, etc.
            if (['INPUT', 'SELECT', 'TEXTAREA', 'LABEL', 'BUTTON'].includes(tag) || activeEl.isContentEditable) {
                return;
            }
            // Skip if inside a modal/dialog or CKEditor
            if (activeEl.closest && (
                activeEl.closest('.swal2-container') ||
                activeEl.closest('dialog') ||
                activeEl.closest('.modal') ||
                activeEl.closest('[role="dialog"]') ||
                activeEl.closest('.ck-editor') ||
                activeEl.closest('.ck')
            )) {
                return;
            }
        }

        if (blurTimeout) clearTimeout(blurTimeout);

        // 500ms buffer: filter out transient mobile browser picker/keyboard blurs
        // Increased from 250ms for better mobile compatibility
        blurTimeout = setTimeout(() => {
            if (shouldSkipAntiCheat()) return;
            if (document.hidden) return;
            if (document.hasFocus && document.hasFocus()) return;

            reportCheating('window_blur');
        }, 500);
    });

    window.addEventListener("focus", function () {
        if (blurTimeout) {
            clearTimeout(blurTimeout);
            blurTimeout = null;
        }
    });
}

// ============================================================
//  ANTI-CHEAT LAYER 8: Content Protection
//  - Copy/paste, right-click, text selection blocking
//  - Preserves functionality within input fields
// ============================================================

function isInputElement(el) {
    if (!el) return false;
    const tag = el.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || el.isContentEditable) return true;
    // CKEditor wraps content in .ck-editor__editable contenteditable divs
    if (el.closest && (el.closest('.ck-editor') || el.closest('.ck'))) return true;
    return false;
}

/**
 * Check if the user is actively interacting with any input field, CKEditor,
 * or any element that could trigger false positive anti-cheat events
 * (e.g., virtual keyboard opening/closing on mobile).
 */
function isInteractingWithInput() {
    if (window.isSelectingFile) return true;

    const activeEl = document.activeElement;
    if (!activeEl) return false;

    // Standard form elements (including file inputs)
    const tag = activeEl.tagName;
    if (['INPUT', 'SELECT', 'TEXTAREA'].includes(tag) || activeEl.isContentEditable) return true;
    if (activeEl.type === 'file') return true;

    // CKEditor rich text editor
    if (activeEl.closest && (activeEl.closest('.ck-editor') || activeEl.closest('.ck'))) return true;
    if (activeEl.classList && activeEl.classList.contains('ck-editor__editable')) return true;

    // Check if any CKEditor instance is focused (handles iframe-based editors)
    const ckFocused = document.querySelector('.ck-editor__editable.ck-focused, .ck-focused');
    if (ckFocused) return true;

    return false;
}

function disableCopyPaste() {
    const blockUnlessInput = function (e) {
        if (!isInputElement(document.activeElement)) {
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
            user-select: none !important;
            -webkit-touch-callout: none !important;
        }
        input, textarea, [contenteditable="true"] {
            -webkit-user-select: text !important;
            user-select: text !important;
            -webkit-touch-callout: default !important;
        }
    `;
    document.head.appendChild(style);
}

// ============================================================
//  FULLSCREEN ENFORCEMENT
// ============================================================

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
        }
    } catch (e) {
        // Suppress unhandled exceptions if user interaction policy rejected request
    }
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

// ============================================================
//  ATTEMPT STATUS CHECK
// ============================================================

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
                const maxCount = res.max_count || 5;
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: `Kamu terdeteksi meninggalkan halaman ujian, batas kesempatan (${res.count}/${maxCount})`,
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
                stopHeartbeat();

                isWarningModalOpen = true;
                Swal.fire({
                    icon: 'error',
                    title: 'Ujian dihentikan',
                    text: 'Terlalu sering meninggalkan halaman.',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                autoSubmitUnSavedQuestions(null, 'cheating');
            }
        }
    });
}

// ============================================================
//  CHEAT REPORT — central reporting with cooldown & dedup
// ============================================================

function reportCheating(reason = 'unspecified') {
    if (shouldSkipAntiCheat()) return;

    const now = Date.now();

    // 1500ms cooldown to avoid cascading/duplicate triggers from related events
    // (e.g. visibilitychange + blur + pagehide can all fire within milliseconds)
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
                const maxCount = res.max_count || 5;
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: `Kamu terdeteksi meninggalkan halaman ujian, batas kesempatan (${res.count}/${maxCount})`,
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
                stopHeartbeat();

                isWarningModalOpen = true;
                Swal.fire({
                    icon: 'error',
                    title: 'Ujian dihentikan',
                    text: 'Terlalu sering meninggalkan halaman.',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                autoSubmitUnSavedQuestions(null, 'cheating');
            }
        },
        error: function () {
            // Restore antiCheatCooldown on error so detection can resume
            setTimeout(() => {
                antiCheatCooldown = false;
            }, 2000);
        }
    });
}

// ============================================================
//  INIT — orchestrates all anti-cheat layers
// ============================================================

function initAntiCheatSystem() {
    if (antiCheatInitialized) return;
    antiCheatInitialized = true;

    // Layer 1: Page Visibility API (desktop + Android, partial iOS)
    cheatingDetection();

    // Layer 2: Page Lifecycle freeze/resume (mobile backgrounding)
    detectFreezeResume();

    // Layer 3: Server Heartbeat (most reliable for iOS)
    startHeartbeat();

    // Layer 4: Viewport ratio heuristic (split-screen / Slide Over)
    detectViewportResize();

    // Layer 5: Fullscreen exit detection (desktop)
    detectFullscreenExit();

    // Layer 6: Keyboard shortcut blocking (desktop)
    detectKeyboardCheating();

    // Layer 7: Window blur detection (desktop + Android)
    detectWindowBlur();

    // Layer 8: Content protection
    disableCopyPaste();
    disableRightClick();
    disableTextSelection();

    // Enforce fullscreen on page reload (desktop only)
    enforceFullscreenAfterReload();

    // If device doesn't support HTML5 fullscreen (e.g. iPhone), activate anti-cheat after brief buffer
    setTimeout(() => {
        const START_KEY = `timer_assessment_test_start_${assessmentId}`;
        if (!isFullscreenSupported() && localStorage.getItem(START_KEY)) {
            antiCheatCooldown = false;
        }
    }, 1500);

    // Safety net: when window regains focus after native camera/gallery dialog, reset file selection with cooldown
    window.addEventListener('focus', function () {
        if (window.isSelectingFile) {
            setTimeout(function () {
                window.setSelectingFile(false);
            }, 800);
        }
    });
}