<?php
session_start();

if (isset($_SESSION['auth'])) {
    header('Location: index.php');
    exit;
}

$pageTitle       = 'Log in';
$pageDescription = 'Log in to your Perfume Store account.';
include('includes/header.php');
?>

<div class="shell auth-wrap">
    <div class="panel auth-card">
        <div class="panel__head">
            <h1 style="font-size:var(--t-h2)">Welcome back</h1>
            <p style="color:var(--fg-muted);font-size:var(--t-sm);margin-top:var(--s-2)">
                Log in to reach your cart and order history.
            </p>
        </div>

        <form action="functions/authcode.php" method="post" class="form-grid">
            <div class="field">
                <label for="li-username">Username</label>
                <input class="input" id="li-username" name="var_username" type="text" required
                       autocomplete="username" placeholder="Your username">
            </div>

            <div class="field">
                <label for="li-password">Password</label>
                <input class="input" id="li-password" name="var_password" type="password" required
                       autocomplete="current-password" placeholder="Your password">
            </div>

            <button class="btn btn--primary btn--lg btn--block" type="submit" name="login_btn">Log in</button>
        </form>

        <p style="margin-top:var(--s-5);padding-top:var(--s-4);border-top:1px solid var(--line);color:var(--fg-muted);font-size:var(--t-sm)">
            No account yet? <a href="register.php" style="color:var(--gold);font-weight:600">Create one</a>
        </p>
    </div>
</div>

<?php include('includes/footer.php'); ?>
