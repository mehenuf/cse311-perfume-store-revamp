<?php
/**
 * A malformed email must be rejected before it ever reaches the database,
 * leaving the account's real email untouched.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_update_bad_email.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif', 'hash', 'Arif Rahman', 'arif@example.com', 0)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
$_POST = [
    'update_account_btn' => '1',
    'name' => 'Arif Rahman',
    'email' => 'not-an-email',
    'contacts' => '01799999999',
    'address' => 'Some Address',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT email FROM customer WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'email_unchanged' => $row['email'] === 'arif@example.com',
        'rejection_message_shown' => isset($_SESSION['message']) && str_contains($_SESSION['message'], 'valid email'),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/updateaccount.php');
