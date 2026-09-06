<?php
/**
 * Saving the form without changing the email (still your own current one)
 * must succeed -- the duplicate-email check excludes the requesting
 * account's own row (WHERE email = ? AND id != ?), so this must not be
 * mistaken for a collision.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_update_same_email.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, contacts, address, admin_check)
            VALUES ('arif', 'hash', 'Arif Rahman', 'arif@example.com', '01710000000', 'Old Address', 0)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
$_POST = [
    'update_account_btn' => '1',
    'name' => 'Arif Rahman Updated',
    'email' => 'arif@example.com', // unchanged
    'contacts' => '01710000000',
    'address' => 'New Address',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT name, address FROM customer WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'not_rejected_as_duplicate' => !isset($_SESSION['message']) || !str_contains($_SESSION['message'], 'already uses'),
        'other_fields_still_saved' => $row['name'] === 'Arif Rahman Updated' && $row['address'] === 'New Address',
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/updateaccount.php');
