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
                // A drifting rail is a loop: it has no first or last card, so
                // disabling an arrow there would be a lie.
                if (rail.hasAttribute('data-rail-autoplay')) {
                    prev.disabled = false; next.disabled = false; return;
                }
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
        // Clearing the attribute is what makes the image visible again, so it
        // runs on load AND on error: a broken file shows its alt text rather
        // than a transparent hole.
        function arrived(img) { delete img.dataset.arriving; img.dataset.loaded = 'true'; }

        function watch(img) {
            if (img.dataset.loaded === 'true') return;
            // complete + naturalWidth means it came from cache this instant.
            // It has already painted, so never hide it -- that would flicker.
            if (img.complete && img.naturalWidth > 0) { img.dataset.loaded = 'true'; return; }

            // Only now do we opt this image into being hidden while it loads.
            img.dataset.arriving = 'true';
            img.addEventListener('load', function () { arrived(img); }, { once: true });
            img.addEventListener('error', function () { arrived(img); }, { once: true });
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
       Rail drift
       A slow, continuous pan, the way a shop turntable moves. It is a hint
       that the row is horizontal, not a carousel that takes the decision
       away: hover, touch, focus, wheel or an arrow hands control straight
       back, and it only resumes once the row has been left alone.

       The loop is seamless because the children are cloned once; when the
       scroll passes the width of the original set it is rewound by exactly
       that width, which is invisible since the content there is identical.
       --------------------------------------------------------------------- */
    function initRailDrift() {
        if (reduceMotion) return;

        document.querySelectorAll('[data-rail-autoplay]').forEach(function (rail) {
            var originals = Array.prototype.slice.call(rail.children);
            if (originals.length < 3) return;                 // nothing to loop
            if (rail.scrollWidth <= rail.clientWidth + 8) return; // already fits

            // Clones are decoration. They must not be announced twice, must
            // not appear in the tab order, and must not duplicate any id.
            originals.forEach(function (node) {
                var copy = node.cloneNode(true);
                copy.classList.add('rail__clone');
                copy.setAttribute('aria-hidden', 'true');
                copy.removeAttribute('id');
                copy.querySelectorAll('[id]').forEach(function (el) { el.removeAttribute('id'); });

                // The reveal observer only ever saw the originals, so a clone
                // that kept data-reveal would sit at opacity 0 for good and
                // the second half of the loop would pan through blanks.
                // Clones are decoration: they arrive already revealed.
                [copy].concat(Array.prototype.slice.call(copy.querySelectorAll('[data-reveal]')))
                    .forEach(function (el) {
                        if (!el.hasAttribute('data-reveal')) return;
                        el.removeAttribute('data-reveal');
                        el.setAttribute('data-shown', 'true');
                    });
                copy.querySelectorAll('a, button, input, select, textarea').forEach(function (el) {
                    el.setAttribute('tabindex', '-1');
                });
                rail.appendChild(copy);
            });

            var loopWidth = 0;
            function measure() {
                var firstClone = rail.querySelector('.rail__clone');
                loopWidth = firstClone
                    ? firstClone.offsetLeft - originals[0].offsetLeft
                    : 0;
            }
            measure();
            if (loopWidth <= 0) return;

            var SPEED = 26;          // px per second: slow enough to read a label
            var IDLE  = 2600;        // ms of being left alone before it resumes
            var pos = rail.scrollLeft;
            var running = false;
            var held = 0;            // pointer or focus is inside: never resume
            var resumeAt = 0;
            var last = 0;
            var frame = 0;

            function normalise(v) {
                v = v % loopWidth;
                return v < 0 ? v + loopWidth : v;
            }

            function tick(now) {
                frame = window.requestAnimationFrame(tick);
                var dt = last ? Math.min(now - last, 64) : 0;  // ignore tab-away gaps
                last = now;

                if (!running) {
                    if (held || now < resumeAt) return;
                    pos = normalise(rail.scrollLeft);
                    running = true;
                    rail.dataset.autoplay = 'running';
                    return;
                }
                pos = normalise(pos + SPEED * dt / 1000);
                rail.scrollLeft = pos;
            }

            function surrender() {
                running = false;
                resumeAt = window.performance.now() + IDLE;
                rail.dataset.autoplay = 'paused';
            }

            ['pointerdown', 'wheel', 'touchstart', 'keydown'].forEach(function (evt) {
                rail.addEventListener(evt, surrender, { passive: true });
            });
            rail.addEventListener('pointerenter', function () { held++; surrender(); });
            rail.addEventListener('pointerleave', function () { held = Math.max(0, held - 1); surrender(); });
            rail.addEventListener('focusin',  function () { held++; surrender(); });
            rail.addEventListener('focusout', function () { held = Math.max(0, held - 1); surrender(); });

            // The arrow buttons are a manual gesture like any other.
            ['prev', 'next'].forEach(function (dir) {
                var btn = document.querySelector('[data-rail-' + dir + '="' + rail.id + '"]');
                if (btn) btn.addEventListener('click', surrender);
            });

            // Two independent reasons to stop: the rail is off screen, or the
            // tab is in the background. Both are tracked, and the loop runs
            // only when neither applies -- otherwise whichever event fired
            // last would win and the other would be forgotten.
            var onScreen = false;

            function sync() {
                var want = onScreen && !document.hidden;
                if (want && !frame) {
                    last = 0;
                    frame = window.requestAnimationFrame(tick);
                } else if (!want && frame) {
                    window.cancelAnimationFrame(frame);
                    frame = 0;
                    running = false;
                    rail.dataset.autoplay = 'paused';
                }
            }

            new IntersectionObserver(function (entries) {
                onScreen = entries[0].isIntersecting;
                sync();
            }, { threshold: 0 }).observe(rail);

            document.addEventListener('visibilitychange', sync);

            // A resize changes the loop length; keep the rewind point honest.
            if ('ResizeObserver' in window) {
                new ResizeObserver(function () {
                    measure();
                    if (loopWidth > 0) pos = normalise(pos);
                }).observe(rail);
            }

            resumeAt = window.performance.now() + 900;   // let the page settle first
            rail.dataset.autoplay = 'paused';
        });
    }

    /* ---------------------------------------------------------------------
       Collection filters — a sidebar of grouped controls beside a card grid
       (the full collection, a brand page, on-sale, featured): free-text
       search, a price range, brand, gender, and availability, plus a sort
       control over the grid itself. Everything a filter needs is already in
       the DOM as data attributes on each .product card, so this only
       shows/hides and reorders nodes already rendered server-side -- no
       re-fetching, and it works with JS off too (the grid just shows the
       server's default order, unfiltered, with the sidebar visible and
       inert).
       --------------------------------------------------------------------- */
    function initPriceRange(root, onChange) {
        var group = root.querySelector('[data-price-range]');
        if (!group) return null;

        var floor = parseInt(group.dataset.priceFloor, 10) || 0;
        var ceil = parseInt(group.dataset.priceCeil, 10) || 0;
        var minRange = group.querySelector('[data-price-min-range]');
        var maxRange = group.querySelector('[data-price-max-range]');
        var minNumber = group.querySelector('[data-price-min-number]');
        var maxNumber = group.querySelector('[data-price-max-number]');
        var fill = group.querySelector('[data-price-fill]');
        if (!minRange || !maxRange) return null;

        var state = { min: floor, max: ceil };

        function clamp(v) { return Math.min(Math.max(v, floor), ceil); }

        function render() {
            minRange.value = state.min;
            maxRange.value = state.max;
            if (minNumber) minNumber.value = state.min;
            if (maxNumber) maxNumber.value = state.max;
            if (fill && ceil > floor) {
                var left = ((state.min - floor) / (ceil - floor)) * 100;
                var right = ((state.max - floor) / (ceil - floor)) * 100;
                fill.style.left = left + '%';
                fill.style.right = (100 - right) + '%';
            }
        }

        function setMin(v, notify) {
            state.min = Math.min(clamp(v), state.max);
            render();
            if (notify !== false) onChange();
        }
        function setMax(v, notify) {
            state.max = Math.max(clamp(v), state.min);
            render();
            if (notify !== false) onChange();
        }

        minRange.addEventListener('input', function () { setMin(parseInt(minRange.value, 10)); });
        maxRange.addEventListener('input', function () { setMax(parseInt(maxRange.value, 10)); });
        if (minNumber) {
            minNumber.addEventListener('change', function () { setMin(parseInt(minNumber.value, 10) || floor); });
        }
        if (maxNumber) {
            maxNumber.addEventListener('change', function () { setMax(parseInt(maxNumber.value, 10) || ceil); });
        }

        render();

        return {
            get: function () { return state; },
            reset: function () { state = { min: floor, max: ceil }; render(); },
            isActive: function () { return state.min > floor || state.max < ceil; }
        };
    }

    function initCollectionFilters() {
        document.querySelectorAll('[data-collection-filters]').forEach(function (root) {
            var panel = root.querySelector('[data-filters-panel]');
            var toggle = root.querySelector('[data-filters-toggle]');
            var grid = root.querySelector('[data-collection-grid]');
            if (!panel || !grid) return;

            var emptyState = root.querySelector('[data-collection-empty]');
            var countEl = root.querySelector('[data-collection-count]');
            var searchInput = panel.querySelector('[data-filter-search]');
            var sortSelect = root.querySelector('[data-filter-sort]');
            var chips = Array.prototype.slice.call(panel.querySelectorAll('[data-filter-toggle]'));
            var genderInputs = Array.prototype.slice.call(panel.querySelectorAll('[data-filter-gender]'));
            var brandInputs = Array.prototype.slice.call(panel.querySelectorAll('[data-filter-brand]'));
            var resetBtns = root.querySelectorAll('[data-filter-reset]');
            var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-product]'));
            var originalOrder = cards.slice();

            var state = { search: '', sort: 'default', gender: 'all' };
            chips.forEach(function (chip) { state[chip.dataset.filterToggle] = false; });

            var priceRange = initPriceRange(panel, apply);

            function selectedBrands() {
                return brandInputs.filter(function (i) { return i.checked; }).map(function (i) { return i.value; });
            }

            function apply() {
                var term = state.search.trim().toLowerCase();
                var brands = selectedBrands();
                var price = priceRange ? priceRange.get() : null;
                var visible = 0;

                cards.forEach(function (card) {
                    var cardPrice = parseFloat(card.dataset.price);
                    var matches = (!term
                            || card.dataset.name.indexOf(term) !== -1
                            || (card.dataset.notes || '').indexOf(term) !== -1)
                        && (!state.stock || card.dataset.inStock === '1')
                        && (!state.sale || card.dataset.onSale === '1')
                        && (!state.featured || card.dataset.featured === '1')
                        && (state.gender === 'all' || card.dataset.gender === state.gender)
                        && (brands.length === 0 || brands.indexOf(card.dataset.brand) !== -1)
                        && (!price || (cardPrice >= price.min && cardPrice <= price.max));
                    card.hidden = !matches;
                    if (matches) visible++;
                });

                var ordered = originalOrder.slice();
                var byName = function (a, b) { return a.dataset.name.localeCompare(b.dataset.name); };
                var byPrice = function (a, b) { return parseFloat(a.dataset.price) - parseFloat(b.dataset.price); };
                if (state.sort === 'name-asc') ordered.sort(byName);
                else if (state.sort === 'name-desc') ordered.sort(function (a, b) { return byName(b, a); });
                else if (state.sort === 'price-asc') ordered.sort(byPrice);
                else if (state.sort === 'price-desc') ordered.sort(function (a, b) { return byPrice(b, a); });
                ordered.forEach(function (card) { grid.appendChild(card); });

                if (countEl) {
                    var total = parseInt(countEl.dataset.total, 10) || originalOrder.length;
                    countEl.textContent = visible === total ? (total + ' available') : (visible + ' of ' + total + ' available');
                }
                if (emptyState) emptyState.hidden = visible !== 0;
                grid.hidden = visible === 0;

                var isFiltered = term !== '' || state.sort !== 'default' || state.gender !== 'all'
                    || brands.length > 0 || (priceRange && priceRange.isActive())
                    || chips.some(function (chip) { return state[chip.dataset.filterToggle]; });
                resetBtns.forEach(function (btn) { btn.hidden = !isFiltered; });
            }

            if (searchInput) {
                var debounce;
                searchInput.addEventListener('input', function () {
                    window.clearTimeout(debounce);
                    debounce = window.setTimeout(function () { state.search = searchInput.value; apply(); }, 120);
                });
            }
            if (sortSelect) {
                sortSelect.addEventListener('change', function () { state.sort = sortSelect.value; apply(); });
            }
            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var key = chip.dataset.filterToggle;
                    var next = chip.getAttribute('aria-pressed') !== 'true';
                    chip.setAttribute('aria-pressed', String(next));
                    state[key] = next;
                    apply();
                });
            });
            genderInputs.forEach(function (input) {
                input.addEventListener('change', function () {
                    if (input.checked) { state.gender = input.value; apply(); }
                });
            });
            brandInputs.forEach(function (input) {
                input.addEventListener('change', apply);
            });
            resetBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    state.search = ''; state.sort = 'default'; state.gender = 'all';
                    chips.forEach(function (chip) { state[chip.dataset.filterToggle] = false; chip.setAttribute('aria-pressed', 'false'); });
                    genderInputs.forEach(function (input) { input.checked = input.value === 'all'; });
                    brandInputs.forEach(function (input) { input.checked = false; });
                    if (priceRange) priceRange.reset();
                    if (searchInput) searchInput.value = '';
                    if (sortSelect) sortSelect.value = 'default';
                    apply();
                });
            });

            // Mobile: the sidebar starts collapsed behind a "Filters" button.
            // Desktop CSS forces the panel visible regardless of this state,
            // so this only ever matters below the sidebar breakpoint.
            if (toggle) {
                if (window.matchMedia('(max-width: 899px)').matches) {
                    panel.hidden = true;
                }
                toggle.addEventListener('click', function () {
                    var open = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', String(!open));
                    panel.hidden = open;
                });
            }
        });
    }

    /* ---------------------------------------------------------------------
       Checkout — payment method chooser routes the one form to whichever
       handler the selected method needs (functions/pay-stripe.php etc.),
       and keeps the order-summary "Payment" line in sync. Without this
       script the form still submits, just always as Cash on delivery —
       see the <noscript> note in checkout.php.
       --------------------------------------------------------------------- */
    function initCheckoutPayment() {
        var form = document.querySelector('[data-checkout-form]');
        if (!form) return;

        var radios = form.querySelectorAll('input[name="payment_method"]');
        var summaryLabel = form.querySelector('[data-checkout-summary-label]');
        var submitBtn = form.querySelector('[data-checkout-submit]');

        function apply(radio) {
            if (!radio) return;
            form.setAttribute('action', radio.getAttribute('data-checkout-action'));
            if (summaryLabel) {
                summaryLabel.textContent = radio.getAttribute('data-checkout-summary');
            }
            if (submitBtn) {
                submitBtn.textContent = radio.value === 'cod' ? 'Place order' : 'Continue to payment';
            }
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () { apply(radio); });
        });

        var checked = form.querySelector('input[name="payment_method"]:checked');
        apply(checked);
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
        initRailDrift();
        initCollectionFilters();
        initCheckoutPayment();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
