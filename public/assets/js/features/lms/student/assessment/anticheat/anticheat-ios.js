// ============================================================
//  ANTI-CHEAT — iOS PLATFORM LAYER
//  Applies to: iPhone / iPad / iPod Safari and WKWebView
//  (includes iPadOS, which masquerades as "Macintosh" with touch)
//
//  iOS specifics:
//   - No Fullscreen API on iPhone → the core releases the
//     anti-cheat cooldown automatically (isFullscreenSupported())
//   - Page Lifecycle freeze/resume events don't fire reliably in
//     Safari → backgrounding is caught by the server heartbeat
//     gap (core) and the timer's freeze-gap detection
//   - pagehide/pageshow are the visible signals here
// ============================================================

function antiCheatIsIOS() {
    const ua = navigator.userAgent || '';
    if (/iphone|ipad|ipod/i.test(ua)) return true;
    // iPadOS 13+ masquerades as Macintosh with touch support
    return /Macintosh/.test(ua) && navigator.maxTouchPoints > 1;
}

window.AntiCheatPlatforms = window.AntiCheatPlatforms || [];
window.AntiCheatPlatforms.push({
    init: function () {
        if (!antiCheatIsIOS()) return;

        // ---- pagehide: app switch / navigation (fires before visibilitychange
        // does in some iOS WebView scenarios) ----
        window.addEventListener("pagehide", function () {
            if (shouldSkipAntiCheat()) return;
            reportCheating('page_hide');
        });

        // ---- pageshow persisted: page restored from bfcache (back from
        // background on iOS Safari) ----
        window.addEventListener("pageshow", function (e) {
            if (e.persisted && !shouldSkipAntiCheat()) {
                reportCheating('page_restored_from_cache');
            }
        });

        // ---- visibilitychange: fires on most app switches; the server
        // heartbeat gap is the safety net for the cases it misses
        // (Control Center, Notification Center, killed renderer) ----
        document.addEventListener("visibilitychange", function () {
            if (shouldSkipAntiCheat()) return;

            if (document.hidden) {
                reportCheating('visibility_hidden');
            }
        });
    }
});
