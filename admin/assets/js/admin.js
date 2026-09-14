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
            delete preview.dataset.arriving;
            preview.dataset.loaded = 'true';
            if (placeholder) placeholder.hidden = true;
            preview.addEventListener('load', function () { URL.revokeObjectURL(url); }, { once: true });
        });
    }


    /* ---- images fade in as they decode, rather than popping ----
       The CSS never hides an image on its own. This marks one data-arriving
       only while it is genuinely still loading, and clears that on load or on
       error. If this file is stale or fails, nothing is marked and every
       thumbnail is simply visible -- which is how it must fail. */
    function initImageArrival() {
        function arrived(img) { delete img.dataset.arriving; img.dataset.loaded = 'true'; }
        document.querySelectorAll('img').forEach(function (img) {
            if (img.complete && img.naturalWidth > 0) { img.dataset.loaded = 'true'; return; }
            img.dataset.arriving = 'true';
            img.addEventListener('load', function () { arrived(img); }, { once: true });
            img.addEventListener('error', function () { arrived(img); }, { once: true });
        });
    }

    /* ---- inline price/stock editing on the catalogue table ----
       Edits price and qty directly from the product list, without opening
       the full edit page for a change this small. */
    function initInlineEdit() {
        var table = document.querySelector('[data-inline-edit-table]');
        if (!table) return;

        table.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-inline-save]');
            if (!btn) return;
            e.preventDefault();

            var row = btn.closest('[data-inline-edit-row]');
            var priceInput = row.querySelector('[data-inline-field="price"]');
            var qtyInput = row.querySelector('[data-inline-field="qty"]');
            var price = parseFloat(priceInput.value);
            var qty = parseInt(qtyInput.value, 10);

            if (isNaN(price) || price < 0 || isNaN(qty) || qty < 0) {
                toast('Enter a valid price and stock quantity.', 'error');
                return;
            }

            var original = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = 'Saving';

            fetch('Includes/inline-update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: new URLSearchParams({
                    perfume_id: row.dataset.perfumeId,
                    price: price,
                    qty: qty
                }).toString(),
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json().catch(function () { return {}; }); })
                .then(function (data) {
                    if (data && data.ok) {
                        toast('Saved.', 'success');
                    } else {
                        toast((data && data.message) || 'Could not save changes.', 'error');
                    }
                })
                .catch(function () {
                    toast('Could not save changes. Check your connection.', 'error');
                })
                .then(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
        });
    }

    /* ---- landing back on the list after an edit ----
       code.php redirects a saved edit to perfume.php?updated=<id>#perfume-<id>.
       The row already carries data-just-updated (server-rendered, so it works
       with JS disabled too) which drives the CSS wash animation; this just
       scrolls it into view under the sticky header and then drops the
       ?updated= param so a later refresh doesn't replay the highlight. */
    function initJustUpdated() {
        var row = document.querySelector('[data-just-updated]');
        if (!row) return;
        row.scrollIntoView({ block: 'center', behavior: 'auto' });
        window.setTimeout(function () { row.removeAttribute('data-just-updated'); }, 2500);
        if (window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.delete('updated');
            window.history.replaceState(null, '', url.pathname + url.search + url.hash);
        }
    }

    /* ---- sticky actions column ---- */
    function initStickyActionsSeam() {
        var wraps = document.querySelectorAll('.table-wrap');
        wraps.forEach(function (wrap) {
            function update() {
                wrap.dataset.scrolled = String(wrap.scrollLeft > 1);
                var max = wrap.scrollWidth - wrap.clientWidth;
                // Whether there is anything TO scroll to at all, independent
                // of scroll position -- the seam this drives was previously
                // gated on data-scrolled alone, so a table that could scroll
                // but had not been touched yet looked identical to one that
                // fit fully: Status/Total sat cropped with nothing marking a
                // boundary there until after the visitor had already found
                // it by accident.
                wrap.dataset.scrollable = String(max > 2);

                // Below the actions column's own 599px breakpoint it falls
                // back to scrolling with the row instead of staying pinned
                // (see admin.css) -- there is no sticky seam to reuse as a
                // hint there, so this table gets its own edge fade instead,
                // same contract as the storefront rail's data-edge: named
                // for which side still has content past it, not which side
                // the scroll position is at.
                if (max <= 2) { wrap.removeAttribute('data-fade'); return; }
                var atStart = wrap.scrollLeft <= 2;
                var atEnd = wrap.scrollLeft >= max - 2;
                wrap.dataset.fade = atStart ? 'right' : (atEnd ? 'left' : 'both');
            }
            wrap.addEventListener('scroll', update, { passive: true });
            update();
            if ('ResizeObserver' in window) new ResizeObserver(update).observe(wrap);
        });
    }

    /* ---- back to top: same sentinel + IntersectionObserver technique as
       the rail/table logic above, so this costs no scroll listener either ---- */
    function initBackToTop() {
        var btn = document.querySelector('[data-back-to-top]');
        if (!btn) return;

        var sentinel = document.createElement('div');
        sentinel.setAttribute('aria-hidden', 'true');
        sentinel.style.cssText = 'position:absolute;top:100vh;left:0;height:1px;width:1px;';
        document.body.prepend(sentinel);

        new IntersectionObserver(function (entries) {
            btn.dataset.visible = String(!entries[0].isIntersecting);
        }).observe(sentinel);

        btn.addEventListener('click', function () {
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
            var content = document.getElementById('content');
            if (content) {
                if (!content.hasAttribute('tabindex')) content.setAttribute('tabindex', '-1');
                content.focus({ preventScroll: true });
            }
        });
    }

    function boot() {
        initRail(); initConfirm(); initImagePreview(); initImageArrival(); initInlineEdit();
        initJustUpdated(); initStickyActionsSeam(); initBackToTop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
