<?php
/**
 * A request with no logged-in session must never be able to reach the
 * update statement, no matter what it POSTs.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_update_unauth.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif', 'hash', 'Arif Rahman', 'arif@example.com', 0)");

$con = new stdClass();
session_start();
$_SESSION = []; // no auth_user at all
$_POST = [
    'update_account_btn' => '1',
    'name' => 'Someone Else',
    'email' => 'takeover@example.com',
    'contacts' => '01700000000',
    'address' => 'Nowhere',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT name, email FROM customer WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'name_unchanged' => $row['name'] === 'Arif Rahman',
        'email_unchanged' => $row['email'] === 'arif@example.com',
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/updateaccount.php');
