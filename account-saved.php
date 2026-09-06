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
        <p>Your account details have been updated. Taking you back home&hellip;</p>
        <a class="btn btn--secondary" href="index.php">Go to homepage now</a>
    </div>
</section>

<style>
.saved-tick {
    width: 72px; height: 72px; margin: 0 auto; border-radius: 50%;
    background: var(--gold, #b08d57); color: #14140f;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem;
    animation: saved-pop .4s ease-out;
}
@keyframes saved-pop {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}
</style>

<script>
    setTimeout(function () {
        window.location.href = 'index.php';
    }, 2500);
</script>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
