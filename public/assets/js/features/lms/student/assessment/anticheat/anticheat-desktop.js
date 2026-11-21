// ============================================================
//  ANTI-CHEAT — DESKTOP PLATFORM LAYER
//  Applies to: Windows / macOS / Linux desktop browsers
//  (Chromium, Firefox, Safari desktop). Excluded: mobile UAs
//  and touch-capable "Mac" devices (iPadOS masquerades as Mac).
//
//  Detection here:
//   - visibilitychange (tab switch / minimize)
//   - window blur/focus with false-positive filtering
//   - fullscreen exit (standard + webkit prefixes)
//   - keyboard shortcuts (devtools, fullscreen, tab mgmt, clipboard)
// ============================================================

var antiCheatBlurTimeout = null;
var reEnterTimer = null;

function antiCheatIsDesktop() {
    const ua = navigator.userAgent || '';
    const isMobileUa = /android|iphone|ipad|ipod|mobile/i.test(ua);
    const isTouchMac = /Macintosh/.test(ua) && navigator.maxTouchPoints > 1; // iPadOS
    return !isMobileUa && !isTouchMac;
}

window.AntiCheatPlatforms = window.AntiCheatPlatforms || [];
window.AntiCheatPlatforms.push({
    init: function () {
        if (!antiCheatIsDesktop()) return;

        // ---- FORCE FULLSCREEN (as hard as the browser allows) ----
        // Browsers require a user gesture to enter fullscreen, so we:
        //  1. try immediately (init usually runs inside the start-button gesture)
        //  2. re-request on EVERY user gesture (click / key / pointer / touch)
        //     until fullscreen is actually active
        function tryEnterFullscreen() {
            if (examFinished || isPageReloading || window.isSelectingFile) return;
            if (!isFullscreenSupported()) return;
            if (getFullscreenElement()) return;

            enterFullscreen();
            setTimeout(updateFullscreenOverlay, 300);
        }

        tryEnterFullscreen();

        const gestureTypes = ['click', 'keydown', 'pointerdown', 'touchstart'];
        const gestureHandler = function () { tryEnterFullscreen(); };
        gestureTypes.forEach(function (type) {
            document.addEventListener(type, gestureHandler, { capture: true, passive: true });
        });

        // ---- Page Visibility API: tab switch / minimize ----
        document.addEventListener("visibilitychange", function () {
            if (shouldSkipAntiCheat()) return;

            if (document.hidden) {
                reportCheating('visibility_hidden');
            }
        });

        // ---- Fullscreen exit detection (standard + webkit for Safari) ----
        // Every exit counts immediately (no debounce) and fullscreen is
        // re-requested on the spot: Chrome then requires the user to HOLD
        // Esc to exit fullscreen again, effectively locking the Escape key.
        const handleFullscreenChange = function () {
            const fsEl = getFullscreenElement();
            if (fsEl) {
                // Entered fullscreen — disable cooldown, hide overlay
                antiCheatCooldown = false;
                updateFullscreenOverlay();
                return;
            }

            if (examFinished || isPageReloading || window.isSelectingFile) return;

            // Only report if fullscreen was actually supported on this device
            if (isFullscreenSupported()) {
                reportCheating('fullscreen_exit', true);
                // Defer the re-request until the exit transition has settled:
                // an immediate request during an ongoing transition is rejected
                // by the browser, and if the user re-enters fullscreen within
                // this window themselves, the pending re-request is skipped.
                if (reEnterTimer) clearTimeout(reEnterTimer);
                reEnterTimer = setTimeout(function () {
                    reEnterTimer = null;
                    if (!getFullscreenElement()) {
                        tryEnterFullscreen();
                    }
                }, 250);
                updateFullscreenOverlay();
            }
        };

        document.addEventListener("fullscreenchange", handleFullscreenChange);
        document.addEventListener("webkitfullscreenchange", handleFullscreenChange);

        // The page unloads right after a reload key, which cancels a normal ajax
        // request; sendBeacon still goes out.
        function logReloadWarning() {
            if (examFinished) return;
            const body = new FormData();
            body.append('_token', $('meta[name="csrf-token"]').attr('content'));
            body.append('reason', 'reload_shortcut');
            body.append('warning', '1');
            navigator.sendBeacon(antiCheatUrl('report-tab-switch'), body);
        }

        // ---- Keyboard shortcut blocking ----
        const isWindows = /Windows/.test(navigator.userAgent || '');

        document.addEventListener("keydown", function (e) {
            if (shouldSkipAntiCheat()) return;

            const isCmdOrCtrl = e.ctrlKey || e.metaKey;
            const key = e.key ? e.key.toLowerCase() : '';

            // Windows key / Super key (e.key 'Meta' on Chromium, 'OS' on older
            // Firefox). Only struck on Windows: on macOS 'Meta' is Cmd, which
            // fires constantly for normal copy/paste shortcuts.
            if ((e.key === 'Meta' || e.key === 'OS') && isWindows) {
                reportCheating('windows_key');
                return;
            }

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

            // ESC (Exit Fullscreen) — do not strike here: the actual exit is
            // struck by fullscreenchange (forced). Re-request happens via the
            // gesture handler below so Chrome requires holding Esc to exit.
            if (e.key === "Escape") {
                return;
            }

            // Reload (F5, Ctrl/Cmd + R) is allowed and logged as a warning, not a strike
            if (e.key === 'F5' || (isCmdOrCtrl && key === 'r')) {
                logReloadWarning();
                return;
            }

            // Browser navigation / Tab management shortcuts (Ctrl/Cmd + T, W, N, L address bar)
            if (isCmdOrCtrl && ['t', 'w', 'n', 'l'].includes(key)) {
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

        // ---- Window blur detection (heavily filtered) ----
        window.addEventListener("blur", function () {
            if (shouldSkipAntiCheat()) return;
            if (document.hidden) return; // Already handled by visibilitychange

            // Focus inside a text field/button does NOT excuse leaving the window
            // (alt-tab while typing an essay is exactly the cheat). Only modal/dialog
            // popups that legitimately take focus are skipped.
            const activeEl = document.activeElement;
            if (activeEl && activeEl.closest && (
                activeEl.closest('.swal2-container') ||
                activeEl.closest('dialog') ||
                activeEl.closest('.modal') ||
                activeEl.closest('[role="dialog"]')
            )) {
                return;
            }

            if (antiCheatBlurTimeout) clearTimeout(antiCheatBlurTimeout);

            // Leaving the window while typing is logged as a warning, not a strike
            const typing = isInteractingWithInput();

            // 500ms buffer: filter out transient blurs (pickers, dev tools)
            antiCheatBlurTimeout = setTimeout(function () {
                if (shouldSkipAntiCheat()) return;
                if (document.hidden) return;
                if (document.hasFocus && document.hasFocus()) return;

                reportCheating('window_blur', false, typing);
            }, 500);
        });

        window.addEventListener("focus", function () {
            if (antiCheatBlurTimeout) {
                clearTimeout(antiCheatBlurTimeout);
                antiCheatBlurTimeout = null;
            }
        });

        // ---- Extension detection (Grammarly) ----
        // Grammarly marks <body>/<html> with data-* attributes and injects an
        // in-page widget / custom element, so a periodic DOM check catches it
        // even when nothing loses focus. Struck once per exam; a report dropped
        // by the debounce is retried on the next check.
        var grammarlyReported = false;
        function detectGrammarly() {
            if (grammarlyReported || shouldSkipAntiCheat()) return;
            if (document.querySelector(
                'grammarly-desktop-integration, [data-gr-ext-installed], ' +
                '[data-new-gr-c-s-check-loaded], [data-grammarly-shadow-root]'
            )) {
                grammarlyReported = reportCheating('grammarly_extension');
            }
        }
        detectGrammarly();
        setInterval(detectGrammarly, 3000);
    }
});
