<?php
session_start();

if (isset($_SESSION['auth'])) {
    header('Location: index.php');
    exit;
}

$pageTitle       = 'Forgot password';
$pageDescription = 'Reset your Perfume Store password.';
include('includes/header.php');
?>

<div class="shell auth-wrap">
    <div class="panel auth-card">
        <div class="panel__head">
            <h1 style="font-size:var(--t-h2)">Forgot your password?</h1>
            <p style="color:var(--fg-muted);font-size:var(--t-sm);margin-top:var(--s-2)">
                Enter the email on your account and we will send you a link to choose a new password.
            </p>
        </div>

        <form action="functions/forgotpassword.php" method="post" class="form-grid">
            <div class="field">
                <label for="fp-email">Email</label>
                <input class="input" id="fp-email" name="email" type="email" required
                       autocomplete="email" placeholder="you@example.com">
            </div>

            <button class="btn btn--primary btn--lg btn--block" type="submit" name="request_reset_btn">Send reset link</button>
        </form>

        <p style="margin-top:var(--s-5);padding-top:var(--s-4);border-top:1px solid var(--line);color:var(--fg-muted);font-size:var(--t-sm)">
            Remembered it? <a href="login.php" style="color:var(--gold);font-weight:600">Log in</a>
        </p>
    </div>
</div>

<?php include('includes/footer.php'); ?>
