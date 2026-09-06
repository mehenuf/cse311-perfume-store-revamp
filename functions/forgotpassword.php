<?php
session_start();
include('../config/dbcon.php');

if (!isset($_POST['request_reset_btn'])) {
    header('Location: ../forgot-password.php');
    exit;
}

$email = trim((string) $_POST['email']);

// This message is shown whether or not the email matches an account, and
// whether or not sending actually succeeds -- the request form must never
// reveal which emails are registered, or it becomes a way to enumerate
// customer accounts.
$_SESSION['message'] = "If an account exists for that email, we've sent a link to reset the "
    . "password. Check your inbox (and spam folder) -- the link expires in an hour.";

if ($email !== '') {
    $stmt = mysqli_prepare($con, "SELECT id, username, email FROM customer WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user) {
        // Only the hash is stored -- the same reasoning as the password
        // itself: a stolen copy of this table cannot be replayed to reset
        // anyone's password. The raw token exists only in this request and
        // in the email it is about to go out in.
        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expires   = date('Y-m-d H:i:s', time() + 3600);

        $update = mysqli_prepare($con,
            "UPDATE customer SET reset_token_hash = ?, reset_token_expires = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, 'ssi', $tokenHash, $expires, $user['id']);
        mysqli_stmt_execute($update);

        $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // This file lives in functions/, one level below the site root --
        // the same depth every other functions/*.php redirect assumes.
        $siteRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
        $resetUrl = $scheme . '://' . $host . $siteRoot . '/reset-password.php?token=' . urlencode($rawToken);

        $subject = 'Reset your Perfume Store password';
        $body = "Hi " . $user['username'] . ",\n\n"
              . "Someone (hopefully you) asked to reset the password on your Perfume Store account.\n\n"
              . "Reset it here -- this link works for one hour:\n" . $resetUrl . "\n\n"
              . "If you didn't request this, you can ignore this email; your password will not change.\n";
        $headers = "From: no-reply@" . $host . "\r\n";

        // mail() has no reliable way to report real delivery failure here,
        // and per the note above the visitor sees the same message either
        // way -- this is a best-effort send, not a guarantee.
        @mail($user['email'], $subject, $body, $headers);
    }
}

header('Location: ../forgot-password.php');
