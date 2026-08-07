/**
 * NiaModal — standardized modal controller.
 *
 * Handles open/close, fade+scale transitions, backdrop click, Escape key,
 * body scroll locking, and keeping the overlay clear of the sidebar so it
 * never covers persistent layout chrome.
 *
 * Usage:
 *   NiaModal.open('myModal');
 *   NiaModal.close('myModal');
 *   Any element with [data-modal-open="myModal"] opens it on click.
 *   Any element inside the panel with [data-modal-close] closes it on click.
 */
(function (window, document) {
    'use strict';

    var openStack = [];

    function getSidebarOffset() {
        var sidebar = document.querySelector('.sidebar');
        if (!sidebar) return 0;
        if (window.innerWidth <= 768) return 0; // sidebar overlays content on mobile
        if (sidebar.classList.contains('active')) return 0; // collapsed
        var rect = sidebar.getBoundingClientRect();
        return rect.width || 0;
    }

    function applyOffset(overlay) {
        overlay.style.setProperty('--modal-offset-left', getSidebarOffset() + 'px');
    }

    function onResize() {
        openStack.forEach(function (overlay) {
            applyOffset(overlay);
        });
    }

    function onKeydown(e) {
        if (e.key === 'Escape' && openStack.length) {
            close(openStack[openStack.length - 1].id);
        }
    }

    function open(id) {
        var overlay = document.getElementById(id);
        if (!overlay) return;

        applyOffset(overlay);
        overlay.style.display = 'flex';
        // Force reflow so the opacity/transform transition actually runs.
        void overlay.offsetWidth;
        overlay.classList.add('is-open');

        if (!openStack.includes(overlay)) openStack.push(overlay);
        document.body.style.overflow = 'hidden';

        if (openStack.length === 1) {
            document.addEventListener('keydown', onKeydown);
            window.addEventListener('resize', onResize);
        }

        var focusTarget = overlay.querySelector('[autofocus], input, textarea, select, button');
        if (focusTarget) focusTarget.focus({ preventScroll: true });
    }

    function close(id) {
        var overlay = document.getElementById(id);
        if (!overlay) return;

        overlay.classList.remove('is-open');
        openStack = openStack.filter(function (o) { return o !== overlay; });

        var ANIM_MS = 200;
        window.setTimeout(function () {
            if (!overlay.classList.contains('is-open')) {
                overlay.style.display = 'none';
            }
        }, ANIM_MS);

        if (!openStack.length) {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            window.removeEventListener('resize', onResize);
        }
    }

    function init() {
        document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) close(overlay.id);
            });
            overlay.querySelectorAll('[data-modal-close]').forEach(function (btn) {
                btn.addEventListener('click', function () { close(overlay.id); });
            });
        });

        document.querySelectorAll('[data-modal-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                open(trigger.getAttribute('data-modal-open'));
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-scan for modals/triggers added dynamically (e.g. via fetch/innerHTML).
    window.NiaModal = { open: open, close: close, rescan: init };
})(window, document);