<?php
session_start();

if (isset($_SESSION['auth'])) {
    header('Location: index.php');
    exit;
}

$pageTitle       = 'Create account';
$pageDescription = 'Create a Perfume Store account to order and track fragrances.';
include('includes/header.php');
?>

<div class="shell auth-wrap">
    <div class="panel auth-card" style="max-width:620px">
        <div class="panel__head">
            <h1 style="font-size:var(--t-h2)">Create your account</h1>
            <p style="color:var(--text-muted);font-size:var(--t-sm);margin-top:var(--s-2)">
                It takes a minute, and your cart follows you between visits.
            </p>
        </div>

        <form action="functions/authcode.php" method="post" class="form-grid form-grid--2">
            <div class="field">
                <label for="rg-name">Full name</label>
                <input class="input" id="rg-name" name="name" type="text" required
                       autocomplete="name" placeholder="Your full name">
            </div>

            <div class="field">
                <label for="rg-username">Username</label>
                <input class="input" id="rg-username" name="username" type="text" required
                       autocomplete="username" placeholder="Pick a username">
            </div>

            <div class="field span-2">
                <label for="rg-email">Email</label>
                <input class="input" id="rg-email" name="email" type="email" required
                       autocomplete="email" placeholder="you@example.com">
            </div>

            <div class="field">
                <label for="rg-contact">Contact number</label>
                <input class="input" id="rg-contact" name="contacts" type="tel" required
                       autocomplete="tel" placeholder="+880 1700 000000">
            </div>

            <div class="field">
                <label for="rg-dob">Date of birth</label>
                <input class="input" id="rg-dob" name="dob" type="date" required
                       autocomplete="bday" max="<?= date('Y-m-d') ?>">
                <span class="field__hint">Used only to confirm you are old enough to order.</span>
            </div>

            <div class="field span-2">
                <label for="rg-address">Address</label>
                <textarea class="textarea" id="rg-address" name="address" required rows="2"
                          autocomplete="street-address" placeholder="House and road, area, city"></textarea>
            </div>

            <div class="field">
                <label for="rg-password">Password</label>
                <input class="input" id="rg-password" name="password" type="password" required
                       minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
            </div>

            <div class="field">
                <label for="rg-repassword">Confirm password</label>
                <input class="input" id="rg-repassword" name="repassword" type="password" required
                       minlength="8" autocomplete="new-password" placeholder="Repeat your password">
            </div>

            <div class="span-2">
                <button class="btn btn--primary btn--lg btn--block" type="submit" name="signup_btn">
                    Create account
                </button>
            </div>
        </form>

        <p style="margin-top:var(--s-5);padding-top:var(--s-4);border-top:1px solid var(--line);color:var(--text-muted);font-size:var(--t-sm)">
            Already have an account? <a href="login.php" style="color:var(--accent);font-weight:600">Log in</a>
        </p>
    </div>
</div>

<?php include('includes/footer.php'); ?>
