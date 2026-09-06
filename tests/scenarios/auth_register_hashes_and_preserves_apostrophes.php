<?php
/**
 * A fresh registration must never store the raw password, and names/
 * addresses containing an apostrophe must round-trip exactly -- the old
 * string-concatenated INSERT broke on values like these.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_auth_register.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));

$con = new stdClass();
$_SESSION = [];
$_POST = [
    'signup_btn' => '1',
    'name' => "Tanvir O'Ahmed",
    'username' => 'tanvir',
    'password' => 'S3cret!!',
    'repassword' => 'S3cret!!',
    'email' => 'tanvir@example.com',
    'address' => "Flat 3B, O'Connor Road",
    'contacts' => '+8801700000000',
    'dob' => '1997-03-30',
];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT * FROM customer WHERE username = 'tanvir'")->fetch(PDO::FETCH_ASSOC);
    return [
        'row_created'      => $row !== false,
        'name_intact'      => $row && $row['name'] === "Tanvir O'Ahmed",
        'address_intact'   => $row && $row['address'] === "Flat 3B, O'Connor Road",
        'password_is_hash' => $row && str_starts_with($row['password'], '$2y$'),
        'not_raw_password' => $row && $row['password'] !== 'S3cret!!',
        'hash_verifies'    => $row && password_verify('S3cret!!', $row['password']),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/authcode.php');
