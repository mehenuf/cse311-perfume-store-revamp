<?php
/**
 * Stylesheet guard.
 *
 * Symptom this exists for: the page occasionally renders as bare, unstyled
 * HTML, and a manual refresh fixes it.
 *
 * Cause: the stylesheet request came back as something that is not CSS. On
 * free shared hosting this is usually the host's own interstitial or security
 * check being served in place of the file on a cold connection; it can also be
 * a truncated response or a 404 behind a stale cache. The browser drops the
 * sheet, keeps the HTML, and paints it naked.
 *
 * Two independent detections, because they fail differently:
 *   1. the link element's own error event  -- fires for a 404 or a bad MIME
 *   2. a sentinel custom property lookup   -- also catches a sheet that
 *      arrived but was empty or truncated, where no error ever fires
 *
 * Recovery is a single cache-busting reload. sessionStorage holds a one-shot
 * flag so a genuinely broken deployment shows an unstyled page once rather
 * than trapping the visitor in a reload loop.
 *
 * $cssProbe names a custom property that only the page's own stylesheet
 * defines. Defaults to the storefront's; the admin passes its own.
 */
$cssProbe = isset($cssProbe) && $cssProbe !== '' ? $cssProbe : '--shell';
?>
<script>
(function () {
    var KEY = 'ps-css-retry';

    function retry(why) {
        try {
            if (sessionStorage.getItem(KEY)) return;   // already tried once
            sessionStorage.setItem(KEY, why);
        } catch (e) {
            return;                                     // no storage, no retry
        }
        var u = new URL(window.location.href);
        u.searchParams.set('_r', Date.now());
        window.location.replace(u.toString());
    }

    var link = document.getElementById('store-css');
    if (link) link.addEventListener('error', function () { retry('error'); });

    document.addEventListener('DOMContentLoaded', function () {
        // --shell only exists if store.css actually applied.
        var probe = getComputedStyle(document.documentElement)
            .getPropertyValue(<?= json_encode($cssProbe) ?>).trim();
        if (probe) {
            try { sessionStorage.removeItem(KEY); } catch (e) {}
            // Tidy the cache-buster out of the address bar after a successful
            // recovery, so the visitor never sees it or bookmarks it.
            if (window.history && /[?&]_r=/.test(window.location.search)) {
                var clean = new URL(window.location.href);
                clean.searchParams.delete('_r');
                window.history.replaceState(null, '', clean.toString());
            }
            return;
        }
        retry('empty');
    });
})();
</script>
