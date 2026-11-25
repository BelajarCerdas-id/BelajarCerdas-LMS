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
var fullscreenUnavailable = false;
var examPaused = false;
var examStopped = false;
var devtoolsTimer = null;

// ---- exam browser state is scoped per user ----
// Lab machines are shared between students. State keyed only by
// assessment/question id lets the next login inherit a previous
// student's timer, completion flag or durations. The exam blades expose
// the authenticated user id via data-user-id; every persisted exam key
// is suffixed with it and foreign/legacy keys are purged on page open.
var EXAM_USER_ID = '';
(function readExamUserId() {
    try {
        var el = document.getElementById('container-assessment-test-form')
            || document.getElementById('container-timer-assessment-test');
        if (el && el.getAttribute) {
            var raw = el.getAttribute('data-user-id');
            if (raw !== null && raw !== undefined && String(raw) !== '') EXAM_USER_ID = String(raw);
        }
    } catch (e) {}
})();

function examStateKey(base) {
    return EXAM_USER_ID ? base + '_u' + EXAM_USER_ID : base;
}
function examStartKey() {
    return examStateKey('timer_assessment_test_start_' + assessmentId);
}
function examExpireKey() {
    return examStateKey('timer_assessment_test_expire_' + assessmentId);
}
function examCompletedKey() {
    return examStateKey('assessment_' + assessmentId + '_all_answered');
}
function examDurationKey(questionId) {
    return examStateKey('duration_' + questionId);
}

// true when the key holds exam state belonging to another user (or to the
// pre-scoping legacy format) that must not leak into this session
function isForeignExamStateKey(key) {
    if (!EXAM_USER_ID) return false;
    var isExamState =
        /^timer_assessment_test_(start|expire)_\d+/.test(key) ||
        /^assessment_\d+_all_answered/.test(key) ||
        /^duration_\d+/.test(key);
    if (!isExamState) return false;
    var match = /_u(.+)$/.exec(key);
    return !match || match[1] !== EXAM_USER_ID;
}

function purgeForeignExamState() {
    try {
        var doomed = [];
        for (var i = 0; i < localStorage.length; i++) {
            var key = localStorage.key(i);
            if (key && isForeignExamStateKey(key)) doomed.push(key);
        }
        doomed.forEach(function (key) { localStorage.removeItem(key); });
    } catch (e) {}
}

// ---- server clock anchor ----
// Lab machine clocks can be minutes (or more) off, which used to shorten
// real exams: remaining time was always computed as server expire minus a
// possibly skewed Date.now(). Anchor the client to the server clock
// (form response server_now_iso, or start-timer server_now_ms) and use
// the corrected "now" for every expiry decision.
var examClockOffsetMs = null;

function setExamClockOffset(serverNowMs, clientNowMs) {
    if (typeof serverNowMs !== 'number' || !isFinite(serverNowMs)) return;
    examClockOffsetMs = serverNowMs - (typeof clientNowMs === 'number' ? clientNowMs : Date.now());
}
function examClockTrusted() {
    return typeof examClockOffsetMs === 'number';
}
function serverNowMs() {
    return Date.now() + (examClockOffsetMs || 0);
}
// accepts a server response carrying server_now_ms (epoch ms) or
// server_now_iso (ISO 8601) and anchors the clock to it
function applyExamServerClock(response) {
    if (!response) return;
    if (typeof response.server_now_ms === 'number') {
        return setExamClockOffset(response.server_now_ms, Date.now());
    }
    if (response.server_now_iso) {
        var parsed = Date.parse(response.server_now_iso);
        if (!isNaN(parsed)) setExamClockOffset(parsed, Date.now());
    }
}

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
    return examFinished || examPaused || isPageReloading || antiCheatCooldown || isWarningModalOpen || window.isSelectingFile;
}

// ---- fullscreen helpers (standard + webkit) ----
// iPhone/iPad report fullscreenEnabled=true but refuse documentElement fullscreen,
// so treat them as "no fullscreen" (no overlay, no fullscreen modal).
// fullscreenUnavailable: a real user click on the overlay was refused by the browser.
function isFullscreenSupported() {
    if (fullscreenUnavailable || antiCheatIsIOS()) return false;
    return !!(
        document.fullscreenEnabled ||
        document.webkitFullscreenEnabled
    );
}

function getFullscreenElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || null;
}

// resolves false only when the browser refused the request (callers that need to know: overlay button)
function enterFullscreen() {
    if (!isFullscreenSupported()) {
        antiCheatCooldown = false;
        return Promise.resolve(true);
    }

    // Browsers reject requestFullscreen while a transition is already in
    // progress (rapid exit->re-enter cycles); guard so requests don't pile up.
    if (getFullscreenElement() || fullscreenRequestInFlight) {
        return Promise.resolve(true);
    }

    const el = document.documentElement;
    const settle = function () { fullscreenRequestInFlight = false; };
    const refused = function () { settle(); return false; };
    try {
        fullscreenRequestInFlight = true;
        // hard reset in case the promise never settles (some WebKit versions)
        setTimeout(settle, 800);
        if (el.requestFullscreen) {
            return el.requestFullscreen().then(function () { settle(); return true; }, refused);
        } else if (el.webkitRequestFullscreen) {
            const p = el.webkitRequestFullscreen();
            if (p && typeof p.then === 'function') {
                return p.then(function () { settle(); return true; }, refused);
            }
            setTimeout(settle, 300);
            return Promise.resolve(true);
        }
        settle();
        return Promise.resolve(false);
    } catch (e) {
        // user-policy rejection thrown synchronously
        return Promise.resolve(refused());
    }
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

var unlockSocket = null;
var lockedPollTimer = null;

function handleBlocked() {
    // stay on the page and wait for a teacher's unlock instead of redirecting
    if (examFinished) return;
    examFinished = true;
    finalExamDuration = getTotalExamDuration();
    if (typeof saveQuestionDuration === 'function') saveQuestionDuration();
    stopHeartbeat();

    isWarningModalOpen = true;
    Swal.fire({
        icon: 'error',
        title: 'Ujian terkunci',
        text: 'Menunggu persetujuan guru. Halaman ini akan terbuka kembali otomatis setelah disetujui.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false
    });

    updateFullscreenOverlay();
    connectUnlockSocket();
    // fallback when the socket is unavailable or drops: 5s status poll
    if (!lockedPollTimer) lockedPollTimer = setInterval(pollUnlockStatus, 5000);
}

function examTokenUrl() {
    return '/lms/proctor/exam-token?assessment_id=' + encodeURIComponent(assessmentId);
}

// One exam socket per attempt: unlock decisions, teacher pause/resume/stop.
function connectUnlockSocket() {
    if (unlockSocket || examStopped) return;

    $.ajax({
        url: examTokenUrl(),
        method: 'GET',
        success: function (res) {
            if (unlockSocket || examStopped) return;
            var scheme = window.location.protocol === 'https:' ? 'wss' : 'ws';
            unlockSocket = new WebSocket(scheme + '://' + window.location.host + res.url + '?token=' + encodeURIComponent(res.token));

            unlockSocket.onmessage = function (msg) {
                var event;
                try { event = JSON.parse(msg.data); } catch (e) { return; }
                handleExamSocketEvent(event);
            };
            unlockSocket.onclose = function () {
                unlockSocket = null;
                setTimeout(connectUnlockSocket, 3000);
            };
        },
        error: function () {
            // retry; the status poll still covers the unlock
            setTimeout(connectUnlockSocket, 3000);
        }
    });
}

function handleExamSocketEvent(event) {
    if (event.type === 'unlock_resolved') {
        if (event.status === 'approved') resumeAfterUnlock(); else handleUnlockDenied();
    } else if (event.type === 'exam_paused') {
        handleExamPaused();
    } else if (event.type === 'exam_resumed') {
        handleExamResumed();
    } else if (event.type === 'exam_stopped') {
        handleExamStopped();
    } else if (event.type === 'resync') {
        pollUnlockStatus();
        if (examPaused) handleExamResumed();
    }
}

function pollUnlockStatus() {
    if (!examFinished) return;
    $.ajax({
        url: antiCheatUrl('attempt-status'),
        method: 'GET',
        success: function (res) {
            if (examFinished && res.status && res.status !== 'blocked') resumeAfterUnlock();
        }
    });
}

function closeUnlockSocket() {
    if (lockedPollTimer) { clearInterval(lockedPollTimer); lockedPollTimer = null; }
    if (unlockSocket) { unlockSocket.onclose = null; try { unlockSocket.close(); } catch (e) {} unlockSocket = null; }
}

function resumeAfterUnlock() {
    if (!examFinished) return;
    if (lockedPollTimer) { clearInterval(lockedPollTimer); lockedPollTimer = null; }
    examFinished = false;
    isWarningModalOpen = false;
    attemptStatusChecked = false;
    antiCheatCooldown = false;
    hidePausedBlockage();
    if (typeof Swal.close === 'function') Swal.close();
    startHeartbeat();
    updateFullscreenOverlay();
    checkAttemptStatus();
    if (typeof Swal.fire === 'function') {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Kunci dibuka. Lanjutkan ujian.', showConfirmButton: false, timer: 4000 });
    }
}

function handleUnlockDenied() {
    if (!examFinished) return;
    hidePausedBlockage();
    closeUnlockSocket();
    window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
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
// warningOnly=true logs the event without a strike (server stores severity=warning)
function reportCheating(reason, force, warningOnly) {
    reason = reason || 'unspecified';
    if (examFinished || isPageReloading) return false;
    if (!force && shouldSkipAntiCheat()) return false;

    const now = Date.now();

    // 4000ms cooldown to avoid cascading/duplicate triggers from related events
    // (e.g. visibilitychange + blur + pagehide can all fire within milliseconds)
    if (!force && now - lastCheatReport < 4000) {
        return false;
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
            force: !!force,
            warning: !!warningOnly
        },
        success: function (res) {
            if (examFinished) return;

            if (res.status === 'warning') {
                showStrikeWarning(res);
            }

            if (res.status === 'blocked') {
                handleBlocked();
            }

            // 'ok' (duplicate inside the server cooldown) or 'ignored': nothing to show, re-arm detection
            if (res.status !== 'warning' && res.status !== 'blocked' && !isWarningModalOpen) {
                antiCheatCooldown = false;
            }
        },
        error: function () {
            // Restore antiCheatCooldown on error so detection can resume
            setTimeout(function () {
                antiCheatCooldown = false;
            }, 2000);
        }
    });
    return true;
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

    const examWasStarted = localStorage.getItem(examStartKey());

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
// Only created when the browser really supports the Fullscreen API (desktop/Android);
// iPhone and iPad (isFullscreenSupported() false) get no overlay.
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
        enterFullscreen().then(function (entered) {
            // browser refused even a real click: never leave the exam locked behind the overlay
            if (entered === false) {
                fullscreenUnavailable = true;
                antiCheatCooldown = false;
            }
            updateFullscreenOverlay();
        });
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
    const hidden = getFullscreenElement() || !isFullscreenSupported() || examFinished || isPageReloading || window.isSelectingFile;
    overlay.style.display = hidden ? 'none' : 'flex';
}

// ---- teacher pause blockage: full-screen, blocks every input until resumed ----
const PAUSED_INPUT_EVENTS = ['keydown', 'keyup', 'keypress', 'mousedown', 'mouseup', 'click', 'dblclick',
    'contextmenu', 'wheel', 'touchstart', 'touchmove', 'touchend', 'pointerdown', 'pointerup'];

function showPausedBlockage() {
    if (document.getElementById('anticheat-paused-overlay')) return;
    const overlay = document.createElement('div');
    overlay.id = 'anticheat-paused-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:#0f172a;color:#fff;' +
        'display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;' +
        'font-family:system-ui,-apple-system,sans-serif;text-align:center;padding:24px;cursor:not-allowed;';
    overlay.innerHTML =
        '<div style="width:44px;height:44px;border:4px solid rgba(255,255,255,.25);border-top-color:#fff;border-radius:50%;animation:anticheat-spin 1s linear infinite;"></div>' +
        '<div style="font-size:22px;font-weight:700;">Ujian Dijeda oleh Guru</div>' +
        '<div style="opacity:.85;max-width:460px;font-size:15px;">Waktu berhenti sementara dan akan berjalan lagi otomatis ketika guru melanjutkan ujian.<br>Jangan menutup atau memuat ulang halaman ini.</div>' +
        '<style>@keyframes anticheat-spin{to{transform:rotate(360deg)}}</style>';
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    PAUSED_INPUT_EVENTS.forEach(function (type) {
        window.addEventListener(type, blockPausedInput, true);
    });
}

// capture-phase: swallow every user input while the pause overlay is on screen
function blockPausedInput(e) {
    if (!document.getElementById('anticheat-paused-overlay')) return removePausedInputBlock();
    e.preventDefault();
    e.stopImmediatePropagation();
}

function removePausedInputBlock() {
    PAUSED_INPUT_EVENTS.forEach(function (type) {
        window.removeEventListener(type, blockPausedInput, true);
    });
}

function hidePausedBlockage() {
    document.getElementById('anticheat-paused-overlay')?.remove();
    document.body.style.overflow = '';
    removePausedInputBlock();
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
        if (!isFullscreenSupported() && localStorage.getItem(examStartKey())) {
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
