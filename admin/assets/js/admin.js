/* ==========================================================================
   Admin behaviour. Vanilla, no dependencies.
   An Operate surface needs three things: the rail opens on small screens,
   feedback appears, and destructive actions ask first.
   ========================================================================== */
(function () {
    'use strict';

    /* ---- toasts ---- */
    var host;
    function toast(message, kind) {
        if (!message) return;
        if (!host) {
            host = document.createElement('div');
            host.className = 'toasts';
            host.setAttribute('role', 'status');
            host.setAttribute('aria-live', 'polite');
            document.body.appendChild(host);
        }
        var el = document.createElement('div');
        el.className = 'toast';
        el.dataset.kind = kind || 'info';
        el.textContent = message;
        host.appendChild(el);
        window.setTimeout(function () {
            el.dataset.leaving = 'true';
            window.setTimeout(function () { el.remove(); }, 400);
        }, 3600);
    }
    window.adminToast = toast;

    /* ---- rail ---- */
    function initRail() {
        var rail = document.querySelector('[data-rail]');
        var toggle = document.querySelector('[data-rail-toggle]');
        var scrim = document.querySelector('[data-rail-scrim]');
        if (!rail || !toggle) return;

        function set(open) {
            rail.dataset.open = String(open);
            if (scrim) scrim.dataset.open = String(open);
            toggle.setAttribute('aria-expanded', String(open));
        }
        toggle.addEventListener('click', function () {
            set(rail.dataset.open !== 'true');
        });
        if (scrim) scrim.addEventListener('click', function () { set(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') set(false);
        });
    }

    /* ---- confirm before anything destructive ---- */
    function initConfirm() {
        document.addEventListener('submit', function (e) {
            var form = e.target.closest('[data-confirm]');
            if (!form) return;
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    }

    /* ---- live image preview when picking a product photo ---- */
    function initImagePreview() {
        var input = document.querySelector('[data-image-input]');
        var preview = document.querySelector('[data-image-preview]');
        var placeholder = document.querySelector('[data-image-placeholder]');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            var url = URL.createObjectURL(file);
            preview.src = url;
            preview.hidden = false;
            if (placeholder) placeholder.hidden = true;
            preview.addEventListener('load', function () { URL.revokeObjectURL(url); }, { once: true });
        });
    }

    function boot() { initRail(); initConfirm(); initImagePreview(); }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
