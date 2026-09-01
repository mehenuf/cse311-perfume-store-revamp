<?php
/**
 * Theme boot.
 *
 * Must be printed inside <head>, BEFORE any stylesheet is applied to content,
 * so the chosen theme is on <html> for the very first paint. Anything later
 * flashes the wrong colours for a frame.
 *
 * Contract with the CSS:
 *   no attribute        follow the system (prefers-color-scheme)
 *   data-theme="light"  force light
 *   data-theme="dark"   force dark
 *
 * Kept deliberately tiny and inline: an external file would be a second
 * network round trip in front of first paint.
 */
?>
<script>
(function () {
    try {
        var saved = localStorage.getItem('ps-theme');
        if (saved === 'light' || saved === 'dark') {
            document.documentElement.setAttribute('data-theme', saved);
        }
    } catch (e) {
        /* private mode, storage disabled: fall through to the system theme */
    }

    /* Cross-document view transitions: promise hygiene.

       Both stylesheets declare `@view-transition { navigation: auto }`, so the
       browser runs a transition on every navigation and hands it over through
       the pageswap / pagereveal events. Those transitions abort routinely and
       legitimately: the document is hidden, a second link is clicked before
       the first settles, the tab goes to the background. Every abort rejects
       the transition's promises, and since nothing awaits them each one prints
       an "Uncaught (in promise) InvalidStateError" for something that is not a
       fault. A no-op rejection handler marks them handled and changes nothing
       else -- an aborted transition already falls back to a plain navigation.

       This has to be registered here, inline in the head, rather than in a
       deferred script: pagereveal fires for the incoming document before
       deferred scripts execute, so a listener added later never sees it. */
    function quiet(vt) {
        if (!vt) return;
        ['ready', 'finished', 'updateCallbackDone'].forEach(function (key) {
            if (vt[key] && vt[key]['catch']) vt[key]['catch'](function () {});
        });
    }
    window.addEventListener('pagereveal', function (e) { quiet(e.viewTransition); });
    window.addEventListener('pageswap',   function (e) { quiet(e.viewTransition); });
})();
</script>
