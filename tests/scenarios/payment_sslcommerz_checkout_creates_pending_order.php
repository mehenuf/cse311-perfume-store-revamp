<?php
/**
 * Same contract as payment_stripe_checkout_creates_pending_order.php, for
 * the SSLCommerz Session API path (bKash/Rocket/Nagad/Bangla QR/cards).
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    return [
        'status' => 200,
        'body'   => json_encode(['status' => 'SUCCESS', 'sessionkey' => 'sess_abc123',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/sess_abc123']),
        'error'  => null,
    ];
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_sslcommerz_pending.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");

$con = new stdClass();
session_start();
$_SESSION['guest_cart'] = [1 => 2];
$_SESSION['csrf_token'] = 'test-csrf-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
putenv('SSLCOMMERZ_STORE_ID=teststore');
putenv('SSLCOMMERZ_STORE_PASSWORD=testpass');
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
        'payment_mode_sslcommerz' => $order && $order['payment_mode'] === 'SSLCOMMERZ',
        'payment_status_pending' => $order && $order['payment_status'] === 'pending',
        'gateway_session_key_attached' => $order && $order['payment_id'] === 'sess_abc123',
        'stock_not_yet_decremented' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/pay-sslcommerz.php');
