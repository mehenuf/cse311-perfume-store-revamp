<?php
/**
 * Email carries a UNIQUE constraint. Trying to change to an email already
 * used by a different account must be rejected with a clear message and
 * leave both accounts' emails untouched, rather than surfacing a raw
 * database constraint error.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_update_dup_email.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif', 'hash', 'Arif Rahman', 'arif@example.com', 0)");
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('nusrat', 'hash', 'Nusrat Jahan', 'nusrat@example.com', 0)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
$_POST = [
    'update_account_btn' => '1',
    'name' => 'Arif Rahman',
    'email' => 'nusrat@example.com', // already taken by account id 2
    'contacts' => '01799999999',
    'address' => 'Some Address',
];

shim_report(function () use ($pdo) {
    $arif = $pdo->query("SELECT email FROM customer WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $nusrat = $pdo->query("SELECT email FROM customer WHERE id = 2")->fetch(PDO::FETCH_ASSOC);
    return [
        'requesting_account_email_unchanged' => $arif['email'] === 'arif@example.com',
        'other_account_email_unchanged' => $nusrat['email'] === 'nusrat@example.com',
        'rejection_message_shown' => isset($_SESSION['message']) && str_contains($_SESSION['message'], 'already uses'),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/updateaccount.php');
