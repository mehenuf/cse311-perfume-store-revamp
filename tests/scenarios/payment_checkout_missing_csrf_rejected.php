<?php
/**
 * functions/pay-stripe.php (and its sslcommerz/coinbase siblings, which
 * share the exact same guard) must refuse to start a payment -- and must
 * not create an order at all -- when the submitted csrf_token does not
 * match the session's. No paymentHttpRequest() stub is defined: a bad
 * request must be rejected before any gateway call would even happen.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_csrf_rejected.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");

$con = new stdClass();
session_start();
$_SESSION['guest_cart'] = [1 => 2];
$_SESSION['csrf_token'] = 'real-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'csrf_token' => 'forged-token',
    'name' => 'Guest Buyer',
    'email' => 'guest@example.com',
    'contact' => '+8801700000000',
    'zipcode' => '1205',
    'address' => 'House 1, Road 1, Dhaka',
];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders")->fetch(PDO::FETCH_ASSOC);
    return ['no_order_created' => $order === false];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/pay-stripe.php');
