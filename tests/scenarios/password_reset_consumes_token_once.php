<?php
/**
 * A valid token resets the password and is immediately invalidated -- a
 * second attempt with the same token (a replay, or the link clicked
 * twice) must be rejected rather than resetting the password again.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_reset_consume.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$rawToken  = 'known-test-token-value';
$tokenHash = hash('sha256', $rawToken);
$expires   = date('Y-m-d H:i:s', time() + 3600);
$stmt = $pdo->prepare("INSERT INTO customer (username, password, name, email, admin_check, reset_token_hash, reset_token_expires)
                        VALUES ('arif', 'old-hash', 'Arif Rahman', 'arif@example.com', 0, ?, ?)");
$stmt->execute([$tokenHash, $expires]);

$con = new stdClass();

// Registered before either include(): both calls below end in exit() on at
// least one path, and a shutdown function only fires if it was registered
// before the process actually exits.
shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT password, reset_token_hash, reset_token_expires FROM customer WHERE email = 'arif@example.com'")->fetch(PDO::FETCH_ASSOC);
    return [
        'password_changed_to_first_value' => password_verify('NewPassw0rd!', $row['password']),
        'replay_did_not_change_password' => !password_verify('SecondAttempt1', $row['password']),
        'token_cleared_after_use' => empty($row['reset_token_hash']) && empty($row['reset_token_expires']),
    ];
});

$_SESSION = [];
$_POST = [
    'reset_password_btn' => '1',
    'token' => $rawToken,
    'password' => 'NewPassw0rd!',
    'repassword' => 'NewPassw0rd!',
];

chdir($ROOT . '/functions');
include($ROOT . '/functions/resetpassword.php');

// A second, separate request replaying the same (now-consumed) token.
$_SESSION = [];
$_POST = [
    'reset_password_btn' => '1',
    'token' => $rawToken,
    'password' => 'SecondAttempt1',
    'repassword' => 'SecondAttempt1',
];
include($ROOT . '/functions/resetpassword.php');
