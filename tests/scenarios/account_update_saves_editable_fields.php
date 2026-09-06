<?php
/**
 * A logged-in customer can update their own name, email, phone number and
 * address from the account settings page.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_update_saves.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, contacts, address, dob, admin_check)
            VALUES ('arif', 'hash', 'Arif Rahman', 'arif@example.com', '01710000000', 'Old Address', '1995-05-05', 0)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
$_POST = [
    'update_account_btn' => '1',
    'name' => 'Arif Islam Rahman',
    'email' => 'arif.new@example.com',
    'contacts' => '01799999999',
    'address' => 'New House, New Road, Dhaka',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT * FROM customer WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'name_updated' => $row['name'] === 'Arif Islam Rahman',
        'email_updated' => $row['email'] === 'arif.new@example.com',
        'contacts_updated' => $row['contacts'] === '01799999999',
        'address_updated' => $row['address'] === 'New House, New Road, Dhaka',
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/updateaccount.php');
