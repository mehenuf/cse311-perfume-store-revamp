<?php
/**
 * Requesting a reset for a real email must store a hashed token with an
 * expiry, and never store the raw token anywhere.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_reset_request.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check)
            VALUES ('arif', 'h', 'Arif Rahman', 'arif@example.com', 0)");

$con = new stdClass();
$_SESSION = [];
$_POST = ['request_reset_btn' => '1', 'email' => 'arif@example.com'];
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['SCRIPT_NAME'] = '/functions/forgotpassword.php';

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT reset_token_hash, reset_token_expires FROM customer WHERE email = 'arif@example.com'")->fetch(PDO::FETCH_ASSOC);
    return [
        'token_hash_set' => !empty($row['reset_token_hash']) && strlen($row['reset_token_hash']) === 64,
        'expiry_set' => !empty($row['reset_token_expires']),
        'expiry_in_the_future' => !empty($row['reset_token_expires']) && strtotime($row['reset_token_expires']) > time(),
        'generic_message_shown' => isset($_SESSION['message']) && str_contains($_SESSION['message'], 'If an account exists'),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/forgotpassword.php');
