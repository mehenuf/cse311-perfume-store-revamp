<?php
/**
 * A token past its expiry must be refused even though the hash matches --
 * the whole point of the expiry column.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_reset_expired.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$rawToken  = 'an-expired-token-value';
$tokenHash = hash('sha256', $rawToken);
$expiredAt = date('Y-m-d H:i:s', time() - 60); // one minute in the past
$stmt = $pdo->prepare("INSERT INTO customer (username, password, name, email, admin_check, reset_token_hash, reset_token_expires)
                        VALUES ('arif', 'old-hash', 'Arif Rahman', 'arif@example.com', 0, ?, ?)");
$stmt->execute([$tokenHash, $expiredAt]);

$con = new stdClass();
$_SESSION = [];
$_POST = [
    'reset_password_btn' => '1',
    'token' => $rawToken,
    'password' => 'ShouldNotApply1',
    'repassword' => 'ShouldNotApply1',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT password FROM customer WHERE email = 'arif@example.com'")->fetch(PDO::FETCH_ASSOC);
    return [
        'password_unchanged' => $row['password'] === 'old-hash',
        'rejection_message_shown' => isset($_SESSION['message']) && str_contains($_SESSION['message'], 'invalid or has expired'),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/resetpassword.php');
