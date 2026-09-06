<?php
/**
 * Every field is required -- a blank contact number (or any other blank
 * field) must be rejected rather than saved as an empty string.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_update_empty_field.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, contacts, address, admin_check)
            VALUES ('arif', 'hash', 'Arif Rahman', 'arif@example.com', '01710000000', 'Old Address', 0)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
$_POST = [
    'update_account_btn' => '1',
    'name' => 'Arif Rahman',
    'email' => 'arif@example.com',
    'contacts' => '   ', // whitespace-only, trims to blank
    'address' => 'Old Address',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT contacts FROM customer WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'contacts_unchanged' => $row['contacts'] === '01710000000',
        'rejection_message_shown' => isset($_SESSION['message']) && str_contains($_SESSION['message'], 'value'),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/updateaccount.php');
