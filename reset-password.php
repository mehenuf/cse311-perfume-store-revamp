<?php
session_start();
include('config/dbcon.php');

$token = isset($_GET['token']) ? (string) $_GET['token'] : '';
$valid = false;

if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $now = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($con, "SELECT id FROM customer WHERE reset_token_hash = ? AND reset_token_expires > ?");
    mysqli_stmt_bind_param($stmt, 'ss', $tokenHash, $now);
    mysqli_stmt_execute($stmt);
    $valid = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
}

$pageTitle       = 'Reset password';
$pageDescription = 'Choose a new password for your Perfume Store account.';
include('includes/header.php');
?>

<div class="shell auth-wrap">
    <div class="panel auth-card">
        <?php if ($valid) { ?>
            <div class="panel__head">
                <h1 style="font-size:var(--t-h2)">Choose a new password</h1>
                <p style="color:var(--fg-muted);font-size:var(--t-sm);margin-top:var(--s-2)">
                    Make it something you have not used elsewhere.
                </p>
            </div>

            <form action="functions/resetpassword.php" method="post" class="form-grid">
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="field">
                    <label for="rp-password">New password</label>
                    <input class="input" id="rp-password" name="password" type="password" required
                           minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                </div>

                <div class="field">
                    <label for="rp-repassword">Confirm new password</label>
                    <input class="input" id="rp-repassword" name="repassword" type="password" required
                           minlength="8" autocomplete="new-password" placeholder="Repeat your password">
                </div>

                <button class="btn btn--primary btn--lg btn--block" type="submit" name="reset_password_btn">Reset password</button>
            </form>
        <?php } else { ?>
            <div class="panel__head">
                <h1 style="font-size:var(--t-h2)">This link has expired</h1>
                <p style="color:var(--fg-muted);font-size:var(--t-sm);margin-top:var(--s-2)">
                    Reset links work once, for one hour after they're sent. Request a new one below.
                </p>
            </div>
            <a class="btn btn--primary btn--lg btn--block" href="forgot-password.php">Request a new link</a>
        <?php } ?>

        <p style="margin-top:var(--s-5);padding-top:var(--s-4);border-top:1px solid var(--line);color:var(--fg-muted);font-size:var(--t-sm)">
            Remembered it after all? <a href="login.php" style="color:var(--gold);font-weight:600">Log in</a>
        </p>
    </div>
</div>

<?php include('includes/footer.php'); ?>
