<?php
/**
 * A customer row created before password hashing existed still holds a
 * plain-text password. Login must still succeed, and the stored value must
 * be rehashed immediately afterward -- see functions/authcode.php.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_auth_legacy.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check)
            VALUES ('arif', 'arif1234', 'Arif Rahman', 'arif@example.com', 0)");

$con = new stdClass(); // dbcon.php only runs its body when $con is unset
$_SESSION = [];
$_POST = ['login_btn' => '1', 'var_username' => 'arif', 'var_password' => 'arif1234'];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT password FROM customer WHERE username = 'arif'")->fetch(PDO::FETCH_ASSOC);
    return [
        'session_authenticated' => isset($_SESSION['auth']) && $_SESSION['auth'] === true,
        'session_user_id_set'   => isset($_SESSION['auth_user']['user_id']),
        'password_rehashed'     => str_starts_with($row['password'], '$2y$'),
        'rehash_verifies'       => password_verify('arif1234', $row['password']),
        'no_longer_plaintext'   => $row['password'] !== 'arif1234',
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/authcode.php');
