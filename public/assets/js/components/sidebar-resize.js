/**
 * BelajarCerdas LMS - Resizable Sidebar Handler (PointerCapture Engine)
 * 
 * Features:
 * - Robust PointerEvent + setPointerCapture engine (immune to text selection, iframes, and dropped events)
 * - Triple-layer style application:
 *   1. CSS Custom Property (--sidebar-width) on :root
 *   2. Dynamic high-priority <style id="bc-sidebar-dynamic-styles"> tag (cache-proof & specificity-proof)
 *   3. Direct inline styles on active sidebar elements
 * - Persistent storage in localStorage with role-specific keys
 * - Double-click to reset to role default (250px for admin, 290px for student/teacher)
 * - Generous 12px grab zone centered right on the boundary
 * - Sensible bounds: 200px minimum to min(500px, 48vw) maximum
 * - Keyboard accessibility (<kbd>ArrowLeft</kbd> / <kbd>ArrowRight</kbd>, <kbd>Enter</kbd> to reset)
 */

(function () {
    'use strict';

    const MIN_WIDTH = 200;
    const STORAGE_KEY_GLOBAL = 'bc_sidebar_width';

    function getMaxWidth() {
        return Math.min(500, Math.floor(window.innerWidth * 0.48));
    }

    function getSidebarConfig(sidebarEl) {
        const isStudent = sidebarEl && sidebarEl.classList.contains('sidebar-beranda-student');
        return {
            defaultWidth: isStudent ? 290 : 250,
            storageKey: isStudent ? 'bc_sidebar_width_student' : 'bc_sidebar_width_admin'
        };
    }

    /**
     * Applies width via CSS variable, dynamic <style> tag, and inline styles.
     * Guarantees 100% immediate effect regardless of cached stylesheets or Tailwind specificity.
     */
    function applySidebarWidth(width) {
        // 1. Set CSS Custom Property
        document.documentElement.style.setProperty('--sidebar-width', `${width}px`);

        // 2. Dynamic high-priority stylesheet
        let styleTag = document.getElementById('bc-sidebar-dynamic-styles');
        if (!styleTag) {
            styleTag = document.createElement('style');
            styleTag.id = 'bc-sidebar-dynamic-styles';
            document.head.appendChild(styleTag);
        }

        styleTag.textContent = `
            :root {
                --sidebar-width: ${width}px !important;
            }
            @media (min-width: 768px) {
                .sidebar-beranda-administrator,
                .sidebar-beranda-student,
                .sidebar-beranda-school-admin,
                aside.sidebar-beranda-administrator,
                aside.sidebar-beranda-student,
                aside.sidebar-beranda-school-admin {
                    width: ${width}px !important;
                    min-width: ${width}px !important;
                    max-width: ${width}px !important;
                    overflow: visible !important;
                }
                .left-62\\.5,
                .md\\:left-62\\.5,
                .left-72\\.5,
                .md\\:left-72\\.5 {
                    left: ${width}px !important;
                }
                .w-\\[calc\\(100\\%-250px\\)\\],
                .md\\:w-\\[calc\\(100\\%-250px\\)\\],
                .w-\\[calc\\(100\\%-290px\\)\\],
                .md\\:w-\\[calc\\(100\\%-290px\\)\\] {
                    width: calc(100% - ${width}px) !important;
                }
            }
            body.sidebar-is-resizing,
            body.sidebar-is-resizing * {
                user-select: none !important;
                -webkit-user-select: none !important;
                cursor: col-resize !important;
                transition: none !important;
            }
            body.sidebar-is-resizing iframe {
                pointer-events: none !important;
            }
        `;

        // 3. Direct inline styling on sidebar elements
        const sidebars = document.querySelectorAll(
            'aside.sidebar-beranda-administrator, aside.sidebar-beranda-student, aside.sidebar-beranda-school-admin'
        );
        sidebars.forEach((s) => {
            s.style.width = `${width}px`;
            s.style.minWidth = `${width}px`;
            s.style.maxWidth = `${width}px`;
            s.style.overflow = 'visible';
        });
    }

    function setupSidebarResizer(sidebarEl) {
        if (!sidebarEl || sidebarEl.querySelector('.sidebar-resizer')) {
            return;
        }

        const config = getSidebarConfig(sidebarEl);
        const savedWidth = localStorage.getItem(config.storageKey) || localStorage.getItem(STORAGE_KEY_GLOBAL);

        let initialWidth = config.defaultWidth;
        if (savedWidth) {
            const parsed = parseInt(savedWidth, 10);
            if (!isNaN(parsed) && parsed >= MIN_WIDTH && parsed <= getMaxWidth()) {
                initialWidth = parsed;
            }
        }

        // Apply immediately
        applySidebarWidth(initialWidth);

        // Ensure sidebar has overflow visible so resizer isn't clipped
        sidebarEl.style.overflow = 'visible';

        // Create the draggable handle
        const resizer = document.createElement('div');
        resizer.className = 'sidebar-resizer';
        resizer.setAttribute('role', 'separator');
        resizer.setAttribute('aria-orientation', 'vertical');
        resizer.setAttribute('tabindex', '0');
        resizer.setAttribute('title', 'Tarik untuk mengubah ukuran sidebar • Klik dua kali untuk reset');

        sidebarEl.appendChild(resizer);

        let isDragging = false;
        let animationFrameId = null;

        // Modern pointer engine with PointerCapture
        function onPointerDown(e) {
            if (window.innerWidth < 768) return; // Desktop only
            if (e.button !== 0) return; // Left click only

            e.preventDefault();
            e.stopPropagation();

            isDragging = true;

            try {
                resizer.setPointerCapture(e.pointerId);
            } catch (_) {}

            resizer.classList.add('is-dragging');
            document.body.classList.add('sidebar-is-resizing');

            function onPointerMove(moveEvent) {
                if (!isDragging) return;
                moveEvent.preventDefault();

                const clientX = moveEvent.clientX;
                if (typeof clientX !== 'number') return;

                const maxWidth = getMaxWidth();
                const targetWidth = Math.max(MIN_WIDTH, Math.min(clientX, maxWidth));

                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                }

                animationFrameId = requestAnimationFrame(() => {
                    applySidebarWidth(targetWidth);
                });
            }

            function onPointerUp(upEvent) {
                if (!isDragging) return;
                isDragging = false;

                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                    animationFrameId = null;
                }

                try {
                    resizer.releasePointerCapture(upEvent.pointerId);
                } catch (_) {}

                resizer.classList.remove('is-dragging');
                document.body.classList.remove('sidebar-is-resizing');

                resizer.removeEventListener('pointermove', onPointerMove);
                resizer.removeEventListener('pointerup', onPointerUp);
                resizer.removeEventListener('pointercancel', onPointerUp);
                window.removeEventListener('pointermove', onPointerMove);
                window.removeEventListener('pointerup', onPointerUp);

                // Read applied width and persist
                const clientX = upEvent.clientX;
                const finalWidth = Math.max(MIN_WIDTH, Math.min(clientX, getMaxWidth()));
                if (!isNaN(finalWidth)) {
                    localStorage.setItem(config.storageKey, finalWidth);
                    localStorage.setItem(STORAGE_KEY_GLOBAL, finalWidth);
                    applySidebarWidth(finalWidth);
                }
            }

            // Listen both on resizer (captured) and window (fallback)
            resizer.addEventListener('pointermove', onPointerMove);
            resizer.addEventListener('pointerup', onPointerUp);
            resizer.addEventListener('pointercancel', onPointerUp);
            window.addEventListener('pointermove', onPointerMove);
            window.addEventListener('pointerup', onPointerUp);
        }

        resizer.addEventListener('pointerdown', onPointerDown);

        // Fallback for mousedown in case pointerdown has environment quirks
        resizer.addEventListener('mousedown', (e) => {
            if (e.button === 0) {
                e.preventDefault();
            }
        });

        // Double click to reset to default width
        resizer.addEventListener('dblclick', (e) => {
            e.preventDefault();
            e.stopPropagation();
            localStorage.removeItem(config.storageKey);
            localStorage.removeItem(STORAGE_KEY_GLOBAL);
            applySidebarWidth(config.defaultWidth);
        });

        // Keyboard accessibility
        resizer.addEventListener('keydown', (e) => {
            const currentWidthStr = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
            let currentWidth = parseInt(currentWidthStr, 10) || config.defaultWidth;

            if (e.key === 'ArrowRight') {
                e.preventDefault();
                currentWidth = Math.min(currentWidth + 12, getMaxWidth());
                applySidebarWidth(currentWidth);
                localStorage.setItem(config.storageKey, currentWidth);
                localStorage.setItem(STORAGE_KEY_GLOBAL, currentWidth);
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                currentWidth = Math.max(currentWidth - 12, MIN_WIDTH);
                applySidebarWidth(currentWidth);
                localStorage.setItem(config.storageKey, currentWidth);
                localStorage.setItem(STORAGE_KEY_GLOBAL, currentWidth);
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                localStorage.removeItem(config.storageKey);
                localStorage.removeItem(STORAGE_KEY_GLOBAL);
                applySidebarWidth(config.defaultWidth);
            }
        });
    }

    function init() {
        const sidebars = document.querySelectorAll(
            'aside.sidebar-beranda-administrator, aside.sidebar-beranda-student, aside.sidebar-beranda-school-admin'
        );

        sidebars.forEach(setupSidebarResizer);
    }

    // Responsive window resize safeguard
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            const currentWidthStr = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
            const currentWidth = parseInt(currentWidthStr, 10);
            const maxWidth = getMaxWidth();
            if (!isNaN(currentWidth) && currentWidth > maxWidth) {
                applySidebarWidth(maxWidth);
            }
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
