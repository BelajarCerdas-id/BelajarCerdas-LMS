// ============================================================
//  ANTI-CHEAT — MOBILE PLATFORM LAYER
//  Applies to: Android & other non-iOS mobile browsers
//  (Chrome Mobile, Samsung Internet, Firefox Mobile, etc.)
//
//  Detection here:
//   - visibilitychange (app switch / home screen / tab switch)
//   - pagehide / pageshow (bfcache restore)
//   - Page Lifecycle freeze/resume (backgrounding suspends JS)
//   - viewport width-ratio heuristic (split-screen detection)
//  Note: iOS uses anticheat-ios.js instead — Safari's Page
//  Lifecycle events don't fire reliably there, so iOS relies on
//  the server heartbeat gap plus the timer freeze detection.
// ============================================================

function antiCheatIsAndroidMobile() {
    const ua = navigator.userAgent || '';
    if (/iphone|ipad|ipod/i.test(ua)) return false;
    if (/ipad/i.test(ua)) return false;
    // iPadOS masquerades as Macintosh with touch support
    if (/Macintosh/.test(ua) && navigator.maxTouchPoints > 1) return false;
    return /android|mobile/i.test(ua);
}

window.AntiCheatPlatforms = window.AntiCheatPlatforms || [];
window.AntiCheatPlatforms.push({
    init: function () {
        if (!antiCheatIsAndroidMobile()) return;

        // ---- Page Visibility API: app switch / home screen / tab switch ----
        document.addEventListener("visibilitychange", function () {
            if (shouldSkipAntiCheat()) return;

            if (document.hidden) {
                reportCheating('visibility_hidden');
            }
        });

        // ---- pagehide: page unloading or hidden (app switch / navigation) ----
        window.addEventListener("pagehide", function () {
            if (shouldSkipAntiCheat()) return;
            reportCheating('page_hide');
        });

        // ---- pageshow persisted: page restored from bfcache ----
        window.addEventListener("pageshow", function (e) {
            if (e.persisted && !shouldSkipAntiCheat()) {
                reportCheating('page_restored_from_cache');
            }
        });

        // ---- Page Lifecycle: freeze/resume (backgrounding) ----
        // "freeze" fires when the browser is about to suspend JS
        document.addEventListener("freeze", function () {
            if (shouldSkipAntiCheat()) return;
            reportCheating('page_freeze');
        });

        // "resume" fires when JS execution resumes after a freeze
        document.addEventListener("resume", function () {
            if (shouldSkipAntiCheat()) return;
            reportCheating('page_resume');
        });

        // ---- Viewport ratio heuristic: split-screen detection ----
        const vv = window.visualViewport;
        if (!vv) return; // Not supported in very old browsers

        let lastReportedRatio = 1;
        // Capture initial viewport dimensions to distinguish keyboard from split-screen
        const initialWidth = vv.width;

        function checkViewportRatio() {
            if (shouldSkipAntiCheat()) return;

            // Skip if user is actively typing — virtual keyboard resizes the viewport
            if (isInteractingWithInput()) return;

            // Use screen.availWidth for more accurate comparison (excludes system UI)
            const screenWidth = screen.availWidth || screen.width;
            const widthRatio = vv.width / screenWidth;

            // Only check WIDTH ratio — virtual keyboard only shrinks height, not width.
            // Split-screen reduces the width. Also verify width actually changed from
            // initial (not just a height-only keyboard resize).
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
});
