<?php
session_start();
include('../config/dbcon.php');
require_once('../includes/helpers.php');

if (!isset($_POST['reset_password_btn'])) {
    header('Location: ../forgot-password.php');
    exit;
}

$token      = isset($_POST['token']) ? (string) $_POST['token'] : '';
$password   = isset($_POST['password']) ? (string) $_POST['password'] : '';
$repassword = isset($_POST['repassword']) ? (string) $_POST['repassword'] : '';

if (!csrfVerify($_POST['csrf_token'] ?? null)) {
    $_SESSION['message'] = 'Your session expired. Please try again.';
    $_SESSION['message_kind'] = 'error';
    header('Location: ../reset-password.php?token=' . urlencode($token));
    exit;
}

if ($token === '') {
    header('Location: ../forgot-password.php');
    exit;
}

// Re-checked here, not just trusted from the page that rendered the form --
// the token could have expired, or already been used, in the time it took
// to fill in the form.
$tokenHash = hash('sha256', $token);
$now = date('Y-m-d H:i:s');
$stmt = mysqli_prepare($con, "SELECT id FROM customer WHERE reset_token_hash = ? AND reset_token_expires > ?");
mysqli_stmt_bind_param($stmt, 'ss', $tokenHash, $now);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    $_SESSION['message'] = "That reset link is invalid or has expired. Request a new one.";
    $_SESSION['message_kind'] = 'error';
    header('Location: ../forgot-password.php');
    exit;
}

if ($password === '' || $password !== $repassword) {
    $_SESSION['message'] = "Passwords don't match. Please make sure both fields are the same.";
    $_SESSION['message_kind'] = 'error';
    header('Location: ../reset-password.php?token=' . urlencode($token));
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['message'] = "Your new password needs to be at least 8 characters.";
    $_SESSION['message_kind'] = 'error';
    header('Location: ../reset-password.php?token=' . urlencode($token));
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

// The token is cleared in the same statement as the password change, so it
// can never be replayed to reset the password a second time.
$update = mysqli_prepare($con,
    "UPDATE customer SET password = ?, reset_token_hash = NULL, reset_token_expires = NULL WHERE id = ?");
mysqli_stmt_bind_param($update, 'si', $hashed, $user['id']);
mysqli_stmt_execute($update);

// An already-authenticated visitor (they logged in normally after
// requesting the link, then used it within its 1-hour window) should not be
// told to log in again -- login.php would just bounce them straight back to
// the homepage while showing them that stale instruction.
if (isset($_SESSION['auth'])) {
    $_SESSION['message'] = "Your password has been reset.";
    header('Location: ../account.php');
    exit;
}

$_SESSION['message'] = "Your password has been reset. Log in with your new password.";
header('Location: ../login.php');
