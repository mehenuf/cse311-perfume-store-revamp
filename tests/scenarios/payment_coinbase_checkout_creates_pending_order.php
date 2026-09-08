<?php
/**
 * Same contract as payment_stripe_checkout_creates_pending_order.php, for
 * the Coinbase Commerce charge-creation path (crypto).
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    return [
        'status' => 201,
        'body'   => json_encode(['data' => ['id' => 'charge_xyz789',
            'hosted_url' => 'https://commerce.coinbase.com/charges/charge_xyz789']]),
        'error'  => null,
    ];
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_coinbase_pending.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");

$con = new stdClass();
session_start();
$_SESSION['guest_cart'] = [1 => 2];
$_SESSION['csrf_token'] = 'test-csrf-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
putenv('COINBASE_COMMERCE_API_KEY=test_api_key');
$_POST = [
    'csrf_token' => 'test-csrf-token',
    'name' => 'Guest Buyer',
    'email' => 'guest@example.com',
    'contact' => '+8801700000000',
    'zipcode' => '1205',
    'address' => 'House 1, Road 1, Dhaka',
];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_created' => $order !== false,
        'payment_mode_coinbase' => $order && $order['payment_mode'] === 'COINBASE',
        'payment_status_pending' => $order && $order['payment_status'] === 'pending',
        'gateway_charge_id_attached' => $order && $order['payment_id'] === 'charge_xyz789',
        'stock_not_yet_decremented' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/pay-coinbase.php');
