/* ==========================================================================
   Theme switching. Shared by the storefront and the admin workspace, because
   a visitor's choice should follow them across both.

   Three states, two of them stored:
     nothing saved   follow the operating system, live
     'light'         forced light
     'dark'          forced dark

   includes/theme-boot.php has already put a saved choice on <html> before
   first paint. This file only handles changes to it.
   ========================================================================== */
(function () {
    'use strict';

    var root = document.documentElement;
    var system = window.matchMedia('(prefers-color-scheme: dark)');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function noop() {}

    function active() {
        var set = root.getAttribute('data-theme');
        if (set === 'light' || set === 'dark') return set;
        return system.matches ? 'dark' : 'light';
    }

    /* The browser chrome should match the page. Read the colour back out of
       the stylesheet rather than repeating a hex here, so it can never drift
       from the palette it is meant to mirror. The storefront names its ground
       --ink and the admin names it --bg. */
    function paintChrome() {
        var s = getComputedStyle(root);
        var ground = (s.getPropertyValue('--ink') || s.getPropertyValue('--bg')).trim();
        if (!ground) return;

        // Media-scoped values would keep overriding a deliberate choice.
        document.querySelectorAll('meta[name="theme-color"][media]').forEach(function (m) {
            m.remove();
        });
        var meta = document.querySelector('meta[name="theme-color"]:not([media])');
        if (!meta) {
            meta = document.createElement('meta');
            meta.setAttribute('name', 'theme-color');
            document.head.appendChild(meta);
        }
        meta.setAttribute('content', ground);
    }

    function relabel() {
        var next = active() === 'dark' ? 'light' : 'dark';
        var text = 'Switch to ' + next + ' theme';
        document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
            b.setAttribute('aria-label', text);
            b.setAttribute('title', text);
        });
    }

    function apply(theme) {
        root.setAttribute('data-theme', theme);
        try { localStorage.setItem('ps-theme', theme); } catch (e) { /* storage off */ }
        relabel();
        paintChrome();
    }

    function boot() {
        var buttons = document.querySelectorAll('[data-theme-toggle]');
        if (!buttons.length) return;

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var next = active() === 'dark' ? 'light' : 'dark';

                // Cross-fade the page rather than hard-cutting it. Both
                // stylesheets flag this state so their page transition, which
                // is a slide, gives way to a fade for this one change.
                //
                // The animation is a nicety; changing the theme is the job.
                // A hidden document aborts startViewTransition outright, and
                // an aborted transition may never invoke its update callback,
                // which left the theme unchanged when the button was pressed.
                // So the transition is only attempted when it can actually
                // run, and there is a fallback for every other way it fails.
                var canAnimate = !reduceMotion
                    && typeof document.startViewTransition === 'function'
                    && document.visibilityState === 'visible';

                if (!canAnimate) { apply(next); return; }

                root.dataset.themeSwitching = 'true';
                var clear = function () { delete root.dataset.themeSwitching; };
                var vt;
                try {
                    vt = document.startViewTransition(function () { apply(next); });
                } catch (e) {
                    clear();
                    apply(next);
                    return;
                }

                // If the callback never ran, apply the change anyway. Calling
                // apply twice is harmless; not calling it at all is the bug.
                if (vt.updateCallbackDone && vt.updateCallbackDone.then) {
                    vt.updateCallbackDone.then(noop, function () { apply(next); });
                }
                // Every promise a transition exposes must be handled, or an
                // ordinary abort -- a second click, a navigation mid-animation
                // -- surfaces as an unhandled rejection in the console.
                if (vt.ready && vt.ready.then) vt.ready.then(noop, noop);
                if (vt.finished && vt.finished.then) vt.finished.then(clear, clear);
                else clear();
            });
        });

        // Someone who never chose keeps following the OS, live.
        var onSystemChange = function () {
            if (root.hasAttribute('data-theme')) return;
            relabel();
            paintChrome();
        };
        if (system.addEventListener) system.addEventListener('change', onSystemChange);
        else if (system.addListener) system.addListener(onSystemChange);

        relabel();
        paintChrome();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
