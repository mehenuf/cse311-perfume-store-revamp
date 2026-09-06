<?php
session_start();
include('../config/dbcon.php');

if (!isset($_SESSION['auth_user']['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_POST['update_account_btn'])) {
    header('Location: ../account.php');
    exit;
}

$userId   = (int) $_SESSION['auth_user']['user_id'];
$name     = trim((string) $_POST['name']);
$email    = trim((string) $_POST['email']);
$contacts = trim((string) $_POST['contacts']);
$address  = trim((string) $_POST['address']);

if ($name === '' || $email === '' || $contacts === '' || $address === '') {
    $_SESSION['message'] = 'Every field needs a value.';
    header('Location: ../account.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'Enter a valid email address.';
    header('Location: ../account.php');
    exit;
}

// Email carries a UNIQUE constraint -- checked here first so a collision
// with another account shows a clear message instead of a raw DB error.
$check_stmt = mysqli_prepare($con, "SELECT id FROM customer WHERE email = ? AND id != ?");
mysqli_stmt_bind_param($check_stmt, 'si', $email, $userId);
mysqli_stmt_execute($check_stmt);
if (mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) > 0) {
    $_SESSION['message'] = 'Another account already uses that email.';
    header('Location: ../account.php');
    exit;
}

// Deliberately the only four columns this statement can ever touch --
// username, dob, password and admin_check never appear anywhere in this
// file. Even a request crafted with those fields in its POST body changes
// nothing, since they are never read from $_POST at all: there is no path
// from a tampered request to changed admin rights, a changed username or a
// changed date of birth.
$update_stmt = mysqli_prepare($con,
    "UPDATE customer SET name = ?, email = ?, contacts = ?, address = ? WHERE id = ?");
mysqli_stmt_bind_param($update_stmt, 'ssssi', $name, $email, $contacts, $address, $userId);

if (mysqli_stmt_execute($update_stmt)) {
    $_SESSION['auth_user']['email'] = $email;
    $_SESSION['message'] = 'Your account details have been updated.';
    header('Location: ../account-saved.php');
    exit;
}

$_SESSION['message'] = 'Something went wrong. Please try again.';
header('Location: ../account.php');
