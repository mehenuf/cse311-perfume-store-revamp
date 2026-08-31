/* ==========================================================================
   Perfume Store — behaviour
   Vanilla. Replaces jQuery + Owl Carousel + alertify.
   No scroll listeners anywhere: nav state and reveals both use
   IntersectionObserver.
   ========================================================================== */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------------------------------------------------------------------
       Toasts — replaces alertify
       --------------------------------------------------------------------- */
    var toastHost;

    function toast(message, kind) {
        if (!message) return;
        if (!toastHost) {
            toastHost = document.createElement('div');
            toastHost.className = 'toasts';
            toastHost.setAttribute('role', 'status');
            toastHost.setAttribute('aria-live', 'polite');
            document.body.appendChild(toastHost);
        }
        var el = document.createElement('div');
        el.className = 'toast';
        el.dataset.kind = kind || 'info';
        el.textContent = message;
        toastHost.appendChild(el);

        window.setTimeout(function () {
            el.dataset.leaving = 'true';
            el.addEventListener('animationend', function () { el.remove(); }, { once: true });
            window.setTimeout(function () { el.remove(); }, 600);
        }, 3600);
    }
    window.storeToast = toast;

    /* ---------------------------------------------------------------------
       Navigation: sticky border state + mobile drawer + dropdowns
       --------------------------------------------------------------------- */
    function initNav() {
        var nav = document.querySelector('[data-nav]');
        if (!nav) return;

        // A 1px sentinel above the nav tells us when the page has scrolled,
        // without ever attaching a scroll listener.
        var sentinel = document.createElement('div');
        sentinel.setAttribute('aria-hidden', 'true');
        sentinel.style.cssText = 'position:absolute;top:0;left:0;height:1px;width:1px;';
        document.body.prepend(sentinel);

        new IntersectionObserver(function (entries) {
            nav.dataset.stuck = String(!entries[0].isIntersecting);
        }).observe(sentinel);

        var toggle = nav.querySelector('[data-nav-toggle]');
        var drawer = nav.querySelector('[data-nav-drawer]');
        if (toggle && drawer) {
            toggle.addEventListener('click', function () {
                var open = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!open));
                drawer.dataset.open = String(!open);
            });
        }

        // Dropdowns open on hover via CSS; this adds keyboard and touch support.
        nav.querySelectorAll('[data-nav-group]').forEach(function (group) {
            var trigger = group.querySelector('[data-nav-trigger]');
            if (!trigger) return;
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                var open = group.dataset.open === 'true';
                nav.querySelectorAll('[data-nav-group]').forEach(function (g) { g.dataset.open = 'false'; });
                group.dataset.open = String(!open);
                trigger.setAttribute('aria-expanded', String(!open));
            });
        });

        document.addEventListener('click', function (e) {
            if (nav.contains(e.target)) return;
            nav.querySelectorAll('[data-nav-group]').forEach(function (g) {
                g.dataset.open = 'false';
                var t = g.querySelector('[data-nav-trigger]');
                if (t) t.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            nav.querySelectorAll('[data-nav-group]').forEach(function (g) { g.dataset.open = 'false'; });
            if (toggle && drawer) {
                toggle.setAttribute('aria-expanded', 'false');
                drawer.dataset.open = 'false';
            }
        });
    }

    /* ---------------------------------------------------------------------
       Reveal on scroll — communicates section hierarchy as content arrives
       --------------------------------------------------------------------- */
    function initReveal() {
        var targets = document.querySelectorAll('[data-reveal]');
        if (!targets.length) return;

        if (reduceMotion || !('IntersectionObserver' in window)) {
            targets.forEach(function (el) { el.dataset.shown = 'true'; });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.dataset.shown = 'true';
                io.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        targets.forEach(function (el, i) {
            // Stagger siblings so a grid resolves in sequence rather than as a block.
            var group = el.parentElement;
            var index = group ? Array.prototype.indexOf.call(group.children, el) : i;
            el.style.setProperty('--reveal-delay', Math.min(index, 8) * 60 + 'ms');
            io.observe(el);
        });

        // Safety net. If anything is already on screen at load, or the observer
        // never fires for it, reveal it rather than leaving content invisible.
        // Hidden content is a far worse failure than a missed animation.
        function rescue() {
            targets.forEach(function (el) {
                if (el.dataset.shown === 'true') return;
                var r = el.getBoundingClientRect();
                if (r.top < window.innerHeight && r.bottom > 0) {
                    el.dataset.shown = 'true';
                    io.unobserve(el);
                }
            });
        }
        window.requestAnimationFrame(rescue);
        window.setTimeout(rescue, 1200);
    }

    /* ---------------------------------------------------------------------
       Horizontal rail — arrows, drag to pan
       --------------------------------------------------------------------- */
    function initRails() {
        document.querySelectorAll('[data-rail]').forEach(function (rail) {
            var prev = document.querySelector('[data-rail-prev="' + rail.id + '"]');
            var next = document.querySelector('[data-rail-next="' + rail.id + '"]');

            function step() {
                var first = rail.firstElementChild;
                return first ? first.getBoundingClientRect().width + 16 : 260;
            }

            function sync() {
                railEdge(rail);
                if (!prev || !next) return;
                var max = rail.scrollWidth - rail.clientWidth - 2;
                prev.disabled = rail.scrollLeft <= 2;
                next.disabled = rail.scrollLeft >= max;
            }

            if (prev) prev.addEventListener('click', function () {
                rail.scrollBy({ left: -step() * 2, behavior: reduceMotion ? 'auto' : 'smooth' });
            });
            if (next) next.addEventListener('click', function () {
                rail.scrollBy({ left: step() * 2, behavior: reduceMotion ? 'auto' : 'smooth' });
            });

            rail.addEventListener('scroll', sync, { passive: true });
            sync();

            // Pointer drag, desktop only. Touch already pans natively.
            var down = false, startX = 0, startLeft = 0, moved = 0;
            rail.addEventListener('pointerdown', function (e) {
                if (e.pointerType !== 'mouse') return;
                down = true; moved = 0;
                startX = e.clientX;
                startLeft = rail.scrollLeft;
            });
            rail.addEventListener('pointermove', function (e) {
                if (!down) return;
                var dx = e.clientX - startX;
                moved = Math.abs(dx);
                if (moved > 4) rail.dataset.dragging = 'true';
                rail.scrollLeft = startLeft - dx;
            });
            ['pointerup', 'pointerleave', 'pointercancel'].forEach(function (evt) {
                rail.addEventListener(evt, function () {
                    down = false;
                    delete rail.dataset.dragging;
                });
            });
        });
    }


    /* ---------------------------------------------------------------------
       Images arriving
       Marks each image once it has actually decoded, so CSS can fade it in
       instead of letting it pop. Cached images are marked immediately, which
       keeps a repeat visit instant rather than re-animating everything.
       --------------------------------------------------------------------- */
    function initImageArrival() {
        function mark(img) { img.dataset.loaded = 'true'; }

        function watch(img) {
            if (img.dataset.loaded === 'true') return;
            // complete + naturalWidth means it came from cache this instant
            if (img.complete && img.naturalWidth > 0) { mark(img); return; }
            img.addEventListener('load', function () { mark(img); }, { once: true });
            // A broken file must not stay invisible forever.
            img.addEventListener('error', function () { mark(img); }, { once: true });
        }

        document.querySelectorAll('img').forEach(watch);

        // Anything injected later (a reloaded cart fragment) gets the same treatment.
        if ('MutationObserver' in window) {
            new MutationObserver(function (records) {
                records.forEach(function (r) {
                    r.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) return;
                        if (node.tagName === 'IMG') watch(node);
                        else if (node.querySelectorAll) node.querySelectorAll('img').forEach(watch);
                    });
                });
            }).observe(document.body, { childList: true, subtree: true });
        }
    }

    /* ---------------------------------------------------------------------
       Shared element across a navigation
       Naming the photograph you clicked lets the view transition carry it
       into the product page, so the bottle grows instead of the page
       reloading. Names must be unique per document, so only ever one.
       --------------------------------------------------------------------- */
    function initSharedMedia() {
        if (!document.startViewTransition && !('CSSViewTransitionRule' in window)) {
            // No support: the click still navigates normally.
        }
        document.addEventListener('click', function (e) {
            var link = e.target.closest('.product__link, .cart-line__media, .cart-line__name');
            if (!link) return;
            var card = link.closest('.product') || link.closest('[data-cart-line]');
            if (!card) return;
            var media = card.querySelector('.product__media, .cart-line__media');
            if (!media) return;

            // A name must be unique in the document. On the product page the
            // detail image already carries it from CSS, so clicking a related
            // product would create a duplicate and abort the transition.
            var detail = document.querySelector('.detail__media');
            if (detail && detail !== media) detail.style.viewTransitionName = 'none';

            document.querySelectorAll('[style*="view-transition-name"]').forEach(function (el) {
                if (el !== detail) el.style.viewTransitionName = '';
            });
            media.style.viewTransitionName = 'product-media';
        }, true);
    }

    /* ---------------------------------------------------------------------
       Rail edges
       Fades whichever end still has content beyond it, so a horizontal
       scroller reads as continuous rather than clipped.
       --------------------------------------------------------------------- */
    function railEdge(rail) {
        var max = rail.scrollWidth - rail.clientWidth;
        if (max <= 2) { rail.removeAttribute('data-edge'); return; }
        var atStart = rail.scrollLeft <= 2;
        var atEnd = rail.scrollLeft >= max - 2;
        rail.dataset.edge = atStart ? 'end' : (atEnd ? 'start' : 'both');
    }

    /* ---------------------------------------------------------------------
       Quantity steppers
       --------------------------------------------------------------------- */
    function readQty(scope) {
        var input = scope.querySelector('[data-qty-input]');
        var v = parseInt(input && input.value, 10);
        return isNaN(v) ? 1 : v;
    }

    function initQty() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-qty-step]');
            if (!btn) return;
            e.preventDefault();

            var scope = btn.closest('[data-qty]');
            if (!scope) return;
            var input = scope.querySelector('[data-qty-input]');
            var min = parseInt(scope.dataset.min || '1', 10);
            var max = parseInt(scope.dataset.max || '10', 10);
            var next = readQty(scope) + parseInt(btn.dataset.qtyStep, 10);

            next = Math.max(min, Math.min(max, next));
            input.value = next;

            scope.querySelectorAll('[data-qty-step]').forEach(function (b) {
                b.disabled = (b.dataset.qtyStep === '-1' && next <= min) ||
                             (b.dataset.qtyStep === '1' && next >= max);
            });

            scope.dispatchEvent(new CustomEvent('qtychange', { bubbles: true, detail: { value: next } }));
        });
    }

    /* ---------------------------------------------------------------------
       Cart — same endpoint contract as before, fetch instead of jQuery
       --------------------------------------------------------------------- */
    var ENDPOINT = 'functions/cart-function.php';

    function post(payload) {
        return fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams(payload).toString(),
            credentials: 'same-origin'
        }).then(function (r) { return r.text(); }).then(function (t) { return t.trim(); });
    }

    function report(code, messages) {
        if (messages[code]) {
            toast(messages[code][0], messages[code][1]);
            return code === '200' || code === '201';
        }
        toast('Something went wrong. Please try again.', 'error');
        return false;
    }

    function initCart() {
        // Add to cart
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-add-to-cart]');
            if (!btn) return;
            e.preventDefault();

            var scope = btn.closest('[data-qty]') ||
                        btn.closest('[data-product]') ||
                        document;
            var qtyScope = scope.querySelector ? (scope.querySelector('[data-qty]') || scope) : scope;
            var qty = qtyScope.querySelector && qtyScope.querySelector('[data-qty-input]')
                ? readQty(qtyScope) : 1;

            var original = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = 'Adding';

            post({
                perfume_id: btn.dataset.addToCart,
                perfume_qty: qty,
                scope: 'add'
            }).then(function (code) {
                var ok = report(code, {
                    '201': ['Added to your cart.', 'success'],
                    '401': ['Please log in to add items to your cart.', 'error'],
                    '500': ['We could not add that. Please try again.', 'error'],
                    '69':  ['That is already in your cart.', 'error']
                });
                if (ok) bumpCartCount(1);
                if (code === '401') {
                    window.setTimeout(function () { window.location.href = 'login.php'; }, 1200);
                }
            }).catch(function () {
                toast('Network problem. Please try again.', 'error');
            }).finally(function () {
                btn.disabled = false;
                btn.innerHTML = original;
            });
        });

        // Quantity change inside the cart page
        document.addEventListener('qtychange', function (e) {
            var scope = e.target;
            var id = scope.dataset.perfumeId;
            if (!id || !scope.hasAttribute('data-cart-qty')) return;

            post({ perfume_id: id, perfume_qty: e.detail.value, scope: 'update' })
                .then(function (code) {
                    report(code, {
                        '200': ['Quantity updated.', 'success'],
                        '500': ['We could not update that quantity.', 'error']
                    });
                    if (code === '200') recalcTotals();
                })
                .catch(function () { toast('Network problem. Please try again.', 'error'); });
        });

        // Remove line
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove-line]');
            if (!btn) return;
            e.preventDefault();

            var line = btn.closest('[data-cart-line]');
            btn.disabled = true;

            post({ cart_id: btn.dataset.removeLine, scope: 'delete' })
                .then(function (code) {
                    if (code !== '200') {
                        btn.disabled = false;
                        toast('We could not remove that item.', 'error');
                        return;
                    }
                    toast('Removed from your cart.', 'success');
                    bumpCartCount(-1);
                    if (!line) return;

                    if (reduceMotion) { line.remove(); afterRemove(); return; }
                    line.style.transition = 'opacity 240ms var(--ease,ease), transform 240ms var(--ease,ease)';
                    line.style.opacity = '0';
                    line.style.transform = 'translateX(-12px)';
                    window.setTimeout(function () { line.remove(); afterRemove(); }, 250);
                })
                .catch(function () {
                    btn.disabled = false;
                    toast('Network problem. Please try again.', 'error');
                });
        });
    }

    function afterRemove() {
        recalcTotals();
        if (!document.querySelector('[data-cart-line]')) window.location.reload();
    }

    /* Recompute the order summary from the DOM so the page never lies about
       the total between a quantity change and the next full load. */
    function recalcTotals() {
        var lines = document.querySelectorAll('[data-cart-line]');
        if (!lines.length) return;

        var total = 0, count = 0;
        lines.forEach(function (line) {
            var unit = parseFloat(line.dataset.unitPrice || '0');
            var input = line.querySelector('[data-qty-input]');
            var qty = input ? parseInt(input.value, 10) || 0 : 0;
            total += unit * qty;
            count += qty;

            var lineTotal = line.querySelector('[data-line-total]');
            if (lineTotal) lineTotal.textContent = money(unit * qty);
        });

        var totalEl = document.querySelector('[data-cart-total]');
        if (totalEl) totalEl.textContent = money(total);
        var countEl = document.querySelector('[data-cart-item-count]');
        if (countEl) countEl.textContent = count + (count === 1 ? ' item' : ' items');
    }

    function money(n) {
        return 'Tk. ' + n.toLocaleString('en-BD', { maximumFractionDigits: 0 });
    }

    function bumpCartCount(delta) {
        var el = document.querySelector('[data-cart-count]');
        if (!el) return;
        var next = Math.max(0, (parseInt(el.textContent, 10) || 0) + delta);
        el.textContent = next;
        el.hidden = next === 0;

        // A beat, so a number that changed without a page load is noticed.
        if (reduceMotion || next === 0) return;
        el.dataset.bumped = 'true';
        window.setTimeout(function () { delete el.dataset.bumped; }, 460);
    }

    /* ---------------------------------------------------------------------
       Boot
       --------------------------------------------------------------------- */
    function boot() {
        initNav();
        initReveal();
        initRails();
        initQty();
        initCart();
        initImageArrival();
        initSharedMedia();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
