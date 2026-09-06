<?php
/**
 * The account settings form marks username, date of birth and admin rights
 * as fixed. This must hold even against a request crafted to include those
 * fields anyway -- functions/updateaccount.php reads only name/email/
 * contacts/address from $_POST, so admin_check=1, a spoofed username and a
 * spoofed dob in the request body must all be silently ignored, while the
 * fields that ARE allowed still save correctly.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_update_protected.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, contacts, address, dob, admin_check)
            VALUES ('arif', 'hash', 'Arif Rahman', 'arif@example.com', '01710000000', 'Old Address', '1995-05-05', 0)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
$_POST = [
    'update_account_btn' => '1',
    'name' => 'Arif Updated Name',
    'email' => 'arif.updated@example.com',
    'contacts' => '01788888888',
    'address' => 'Updated Address',
    // Everything below is a privilege-escalation / tamper attempt that this
    // endpoint must ignore completely.
    'admin_check' => '1',
    'username' => 'hacker',
    'dob' => '2000-01-01',
    'password' => 'attacker-supplied-hash',
    'id' => '999',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT * FROM customer WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'still_not_admin' => (int) $row['admin_check'] === 0,
        'username_unchanged' => $row['username'] === 'arif',
        'dob_unchanged' => $row['dob'] === '1995-05-05',
        'password_unchanged' => $row['password'] === 'hash',
        'no_second_row_created' => (int) $pdo->query("SELECT COUNT(*) AS n FROM customer")->fetch(PDO::FETCH_ASSOC)['n'] === 1,
        'allowed_fields_still_saved' => $row['name'] === 'Arif Updated Name' && $row['email'] === 'arif.updated@example.com',
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/updateaccount.php');
