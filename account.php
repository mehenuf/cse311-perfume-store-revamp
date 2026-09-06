<?php
session_start();
include('authenticate.php');
include('functions/functions.php');

$pageTitle       = 'Account settings';
$pageDescription = 'Update your name, email, phone number and delivery address.';
include('includes/header.php');

$userId = (int) $_SESSION['auth_user']['user_id'];
$me_stmt = mysqli_prepare($con, "SELECT username, name, email, contacts, address, dob FROM customer WHERE id = ?");
mysqli_stmt_bind_param($me_stmt, 'i', $userId);
mysqli_stmt_execute($me_stmt);
$me = mysqli_fetch_assoc(mysqli_stmt_get_result($me_stmt));

echo crumb(['Home' => 'index.php', 'Account settings' => null]);
?>

<section class="section shell">
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)">Account settings</h1>
            <p>Keep your contact and delivery details current. Your username and date of birth can't be changed here.</p>
        </div>
    </div>

    <div class="panel auth-card" style="max-width:620px">
        <form action="functions/updateaccount.php" method="post" class="form-grid form-grid--2">

            <div class="field">
                <label for="ac-name">Full name</label>
                <input class="input" id="ac-name" name="name" type="text" required
                       autocomplete="name" value="<?= e($me['name']) ?>">
            </div>

            <div class="field">
                <label for="ac-username">Username</label>
                <input class="input" id="ac-username" type="text" value="<?= e($me['username']) ?>" disabled>
                <span class="field__hint">Usernames can't be changed.</span>
            </div>

            <div class="field span-2">
                <label for="ac-email">Email</label>
                <input class="input" id="ac-email" name="email" type="email" required
                       autocomplete="email" value="<?= e($me['email']) ?>">
            </div>

            <div class="field">
                <label for="ac-contact">Contact number</label>
                <input class="input" id="ac-contact" name="contacts" type="tel" required
                       autocomplete="tel" value="<?= e($me['contacts']) ?>">
            </div>

            <div class="field">
                <label for="ac-dob">Date of birth</label>
                <input class="input" id="ac-dob" type="text"
                       value="<?= $me['dob'] ? e(date('j F Y', strtotime($me['dob']))) : 'Not set' ?>" disabled>
                <span class="field__hint">Date of birth can't be changed.</span>
            </div>

            <div class="field span-2">
                <label for="ac-address">Delivery address</label>
                <textarea class="textarea" id="ac-address" name="address" required rows="3"
                          autocomplete="street-address"><?= e($me['address']) ?></textarea>
            </div>

            <div class="span-2">
                <button class="btn btn--primary btn--lg btn--block" type="submit" name="update_account_btn">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</section>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
