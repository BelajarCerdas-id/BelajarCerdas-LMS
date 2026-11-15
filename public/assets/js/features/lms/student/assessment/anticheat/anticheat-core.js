// ============================================================
//  ANTI-CHEAT CORE (all platforms)
//  - Single source of shared state (declared here ONCE as `var`
//    so start-timer-assessment-test.js and
//    student-form-assessment-test.js keep working unchanged)
//  - Server API client, strike reporting, heartbeat, status poll
//  - Warning/blocked UI, fullscreen helpers
//  - Content protection (copy/paste/right-click/selection)
//  Platform-specific detection lives in:
//    anticheat-desktop.js  (Windows / macOS / Linux desktop browsers)
//    anticheat-mobile.js   (Android & other non-iOS mobile)
//    anticheat-ios.js      (iPhone / iPad / WKWebView)
// ============================================================

// ---- shared state (do not redeclare in other files) ----
var examFinished = false;
var finalExamDuration = 0;
var isPageReloading = false;
var attemptStatusChecked = false;
var lastCheatReport = 0;
var antiCheatCooldown = true;
var isWarningModalOpen = false;
var antiCheatInitialized = false;
var heartbeatInterval = null;
var fullscreenRequestInFlight = false;

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

// ---- platform registry (platform files push their init here) ----
window.AntiCheatPlatforms = window.AntiCheatPlatforms || [];

// ---- guard helper: single source of truth for skip conditions ----
function shouldSkipAntiCheat() {
    return examFinished || isPageReloading || antiCheatCooldown || isWarningModalOpen || window.isSelectingFile;
}

// ---- fullscreen helpers (standard + webkit) ----
function isFullscreenSupported() {
    return !!(
        document.fullscreenEnabled ||
        document.webkitFullscreenEnabled
    );
}

function getFullscreenElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || null;
}

function enterFullscreen() {
    if (!isFullscreenSupported()) {
        antiCheatCooldown = false;
        return Promise.resolve();
    }

    // Browsers reject requestFullscreen while a transition is already in
    // progress (rapid exit->re-enter cycles); guard so requests don't pile up.
    if (getFullscreenElement() || fullscreenRequestInFlight) {
        return Promise.resolve();
    }

    const el = document.documentElement;
    try {
        fullscreenRequestInFlight = true;
        const settle = function () { fullscreenRequestInFlight = false; };
        // hard reset in case the promise never settles (some WebKit versions)
        setTimeout(settle, 800);
        if (el.requestFullscreen) {
            return el.requestFullscreen().then(settle, settle);
        } else if (el.webkitRequestFullscreen) {
            const p = el.webkitRequestFullscreen();
            if (p && typeof p.then === 'function') return p.then(settle, settle);
            setTimeout(settle, 300);
            return p;
        }
    } catch (e) {
        fullscreenRequestInFlight = false;
        // Suppress unhandled exceptions if user interaction policy rejected request
    }
    return Promise.resolve();
}

// ---- endpoint helper ----
function antiCheatUrl(suffix) {
    return `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/${suffix}`;
}

// ---- strike UI (shared by all reporters) ----
function showStrikeWarning(res) {
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
    }).then(function (result) {
        isWarningModalOpen = false;
        if (result.isConfirmed) {
            examStarted = true;
            enterFullscreen();
        }
        setTimeout(function () {
            antiCheatCooldown = false;
        }, 1000);
    });
}

function handleBlocked() {
    examFinished = true;
    finalExamDuration = getTotalExamDuration();
    if (typeof saveQuestionDuration === 'function') saveQuestionDuration();
    if (typeof stopTimer === 'function') stopTimer();
    if (typeof stopQuestionTimer === 'function') stopQuestionTimer();
    stopHeartbeat();

    // force redirect once the blocked modal is closed (or the final
    // auto-submit finished, whichever happens last first)
    let blockedRedirected = false;
    const redirectAfterBlock = function () {
        if (blockedRedirected) return;
        blockedRedirected = true;
        window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
    };

    isWarningModalOpen = true;
    Swal.fire({
        icon: 'error',
        title: 'Ujian dihentikan',
        text: 'Terlalu sering meninggalkan halaman.',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function () {
        isWarningModalOpen = false;
        redirectAfterBlock();
    });

    updateFullscreenOverlay();

    if (typeof autoSubmitUnSavedQuestions === 'function') {
        autoSubmitUnSavedQuestions(redirectAfterBlock, 'cheating');
    } else {
        redirectAfterBlock();
    }
}

function handleHeartbeatResponse(res) {
    if (examFinished) return;

    if (res.status === 'ignored') return;

    if (res.status === 'warning') {
        showStrikeWarning(res);
    }

    if (res.status === 'blocked') {
        handleBlocked();
    }
}

// ---- server heartbeat (the most reliable detector on every platform) ----
function startHeartbeat() {
    if (heartbeatInterval) return;

    heartbeatInterval = setInterval(function () {
        if (examFinished) {
            clearInterval(heartbeatInterval);
            heartbeatInterval = null;
            updateFullscreenOverlay();
            return;
        }

        updateFullscreenOverlay();
        $.ajax({
            url: antiCheatUrl('heartbeat'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                timestamp: Date.now(),
                is_selecting_file: !!window.isSelectingFile
            },
            timeout: 8000,
            success: handleHeartbeatResponse
        });
    }, 10000);
}

function stopHeartbeat() {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
        heartbeatInterval = null;
    }
}

// ---- attempt status check (one-shot on page load) ----
function checkAttemptStatus() {
    if (attemptStatusChecked) return;
    attemptStatusChecked = true;

    if (examFinished || isPageReloading || antiCheatCooldown) return;

    $.ajax({
        url: antiCheatUrl('attempt-status'),
        method: 'GET',
        success: function (res) {
            if (examFinished) return;

            if (res.status === 'warning') {
                showStrikeWarning(res);
            }

            if (res.status === 'blocked') {
                handleBlocked();
            }
        }
    });
}

// ---- cheat report: central reporting with cooldown & dedup ----
// force=true bypasses the skip-flags and the 4000ms debounce — used for
// fullscreen violations where every exit must count.
function reportCheating(reason, force) {
    reason = reason || 'unspecified';
    if (examFinished || isPageReloading) return;
    if (!force && shouldSkipAntiCheat()) return;

    const now = Date.now();

    // 4000ms cooldown to avoid cascading/duplicate triggers from related events
    // (e.g. visibilitychange + blur + pagehide can all fire within milliseconds)
    if (!force && now - lastCheatReport < 4000) {
        return;
    }

    lastCheatReport = now;
    antiCheatCooldown = true;

    $.ajax({
        url: antiCheatUrl('report-tab-switch'),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            reason: reason,
            force: !!force
        },
        success: function (res) {
            if (examFinished) return;

            if (res.status === 'warning') {
                showStrikeWarning(res);
            }

            if (res.status === 'blocked') {
                handleBlocked();
            }
        },
        error: function () {
            // Restore antiCheatCooldown on error so detection can resume
            setTimeout(function () {
                antiCheatCooldown = false;
            }, 2000);
        }
    });
}

// ---- content protection (all platforms) ----
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

// ---- fullscreen enforcement after reload (desktop only) ----
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
    }).then(function (result) {
        isWarningModalOpen = false;
        if (result.isConfirmed) {
            enterFullscreen();
        }
    });
}

// ---- fullscreen overlay: exam content is hidden until fullscreen is active ----
// Only created when the browser supports the Fullscreen API (desktop/Android);
// iPhone has no Fullscreen API so nothing is overlaid there.
function ensureFullscreenOverlay() {
    if (!isFullscreenSupported()) return;
    if (document.getElementById('anticheat-fullscreen-overlay')) return;

    const overlay = document.createElement('div');
    overlay.id = 'anticheat-fullscreen-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:#0f172a;color:#fff;' +
        'display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;' +
        'font-family:system-ui,-apple-system,sans-serif;text-align:center;padding:24px;';
    overlay.innerHTML =
        '<div style="font-size:20px;font-weight:600;">Mode Fullscreen Wajib</div>' +
        '<div style="opacity:.8;max-width:420px;">Ujian hanya dapat dikerjakan dalam mode fullscreen. Klik tombol di bawah untuk melanjutkan.</div>' +
        '<button id="anticheat-fullscreen-btn" style="padding:12px 28px;border:0;border-radius:8px;background:#2563eb;color:#fff;font-size:16px;font-weight:600;cursor:pointer;">Masuk Fullscreen</button>';
    overlay.querySelector('#anticheat-fullscreen-btn').addEventListener('click', function () {
        enterFullscreen();
        setTimeout(updateFullscreenOverlay, 300);
    });
    document.body.appendChild(overlay);

    document.addEventListener('fullscreenchange', updateFullscreenOverlay);
    document.addEventListener('webkitfullscreenchange', updateFullscreenOverlay);
    updateFullscreenOverlay();
}

function updateFullscreenOverlay() {
    const overlay = document.getElementById('anticheat-fullscreen-overlay');
    if (!overlay) return;

    // visible whenever fullscreen is supported but not currently active
    const hidden = getFullscreenElement() || examFinished || isPageReloading || window.isSelectingFile;
    overlay.style.display = hidden ? 'none' : 'flex';
}

// ---- INIT: orchestrates content protection, heartbeat and platform layers ----
function initAntiCheatSystem() {
    if (antiCheatInitialized) return;
    antiCheatInitialized = true;

    // Content protection applies everywhere
    disableCopyPaste();
    disableRightClick();
    disableTextSelection();

    // Overlay the exam while fullscreen is not active (fullscreen-capable devices)
    ensureFullscreenOverlay();

    // Server heartbeat works on every platform
    startHeartbeat();

    // Platform-specific detection (desktop / mobile / ios files self-select)
    window.AntiCheatPlatforms.forEach(function (platform) {
        if (typeof platform.init === 'function') {
            platform.init();
        }
    });

    // Enforce fullscreen on page reload (desktop only)
    enforceFullscreenAfterReload();

    // If device doesn't support HTML5 fullscreen (e.g. iPhone), activate anti-cheat after brief buffer
    setTimeout(function () {
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
