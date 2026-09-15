<?php
session_start();
include('authenticate.php');

$pageTitle       = 'Changes saved';
$pageDescription = 'Your account details have been updated.';
include('includes/header.php');
?>

<section class="section shell" style="min-height:60vh; display:flex; align-items:center; justify-content:center;">
    <div class="panel auth-card" style="max-width:440px; text-align:center; padding:var(--s-7) var(--s-5);">
        <div class="saved-tick" aria-hidden="true">
            <i class="fa-solid fa-check"></i>
        </div>
        <h1 style="font-size:var(--t-h2); margin-top:var(--s-4)">Changes saved</h1>
        <p>Your account details have been updated.</p>
        <p aria-live="polite" data-redirect-note style="color:var(--fg-muted);font-size:var(--t-sm);margin-top:var(--s-2)">
            Taking you back home in <span data-redirect-count>5</span> seconds&hellip;
            <button type="button" class="auth-link" data-redirect-cancel style="color:var(--gold);background:none;border:0;cursor:pointer;text-decoration:underline">Stay on this page</button>
        </p>
        <a class="btn btn--secondary" href="index.php">Go to homepage now</a>
    </div>
</section>

<style>
.saved-tick {
    width: 72px; height: 72px; margin: 0 auto; border-radius: 50%;
    background: var(--gold, #b08d57); color: #14140f;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem;
}
@media (prefers-reduced-motion: no-preference) {
    .saved-tick { animation: saved-pop .4s ease-out; }
}
@keyframes saved-pop {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}
</style>

<script>
    (function () {
        var seconds = 5;
        var countEl = document.querySelector('[data-redirect-count]');
        var cancelBtn = document.querySelector('[data-redirect-cancel]');
        var noteEl = document.querySelector('[data-redirect-note]');
        var cancelled = false;

        cancelBtn.addEventListener('click', function () {
            cancelled = true;
            noteEl.hidden = true;
        });

        var tick = setInterval(function () {
            if (cancelled) { clearInterval(tick); return; }
            seconds -= 1;
            if (seconds <= 0) {
                clearInterval(tick);
                window.location.href = 'index.php';
                return;
            }
            countEl.textContent = String(seconds);
        }, 1000);
    })();
</script>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
