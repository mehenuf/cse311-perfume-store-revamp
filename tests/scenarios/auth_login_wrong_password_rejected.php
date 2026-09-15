<?php
/**
 * A correctly hashed password, but the submitted password is wrong.
 * Must be rejected, and the stored hash must be untouched.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_auth_wrong.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$hash = password_hash('correct-horse', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO customer (username, password, name, email, admin_check)
            VALUES ('nusrat', ?, 'Nusrat Jahan', 'nusrat@example.com', 0)");
$stmt->execute([$hash]);

$con = new stdClass();
// Started here (not left to authcode.php's own session_start()) so the
// csrf_token set below survives -- calling session_start() a second time
// inside authcode.php is then a harmless no-op instead of loading an empty
// on-disk session over what we just set.
session_start();
$_SESSION = ['csrf_token' => 'test-csrf-token'];
$_POST = ['login_btn' => '1', 'csrf_token' => 'test-csrf-token', 'var_username' => 'nusrat', 'var_password' => 'totally-wrong'];

shim_report(function () use ($pdo, $hash) {
    $row = $pdo->query("SELECT password FROM customer WHERE username = 'nusrat'")->fetch(PDO::FETCH_ASSOC);
    return [
        'session_not_authenticated' => !isset($_SESSION['auth']),
        'rejection_message_shown'   => isset($_SESSION['message']) && str_contains($_SESSION['message'], "isn't right"),
        'hash_unchanged'            => $row['password'] === $hash,
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/authcode.php');
