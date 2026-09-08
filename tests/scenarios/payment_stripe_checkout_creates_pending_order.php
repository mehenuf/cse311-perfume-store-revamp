<?php
/**
 * Starting a Stripe checkout must create a 'pending' order with the
 * Checkout Session id attached, and must NOT touch stock yet -- unlike
 * Cash on Delivery, nothing has been paid for at this point.
 * paymentHttpRequest() is pre-defined here (before payments/http.php is
 * required) so this runs with no real network call, the same seam
 * mysqli_shim.php uses for mysqli_*.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

// Forces demo mode off regardless of the developer machine's own
// config/env.php -- see payment_gateway_error_message_sanitized.php.
function appEnv($key, $default = '')
{
    if ($key === 'PAYMENT_DEMO_MODE') {
        return '0';
    }
    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
}

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    return [
        'status' => 200,
        'body'   => json_encode(['id' => 'cs_test_123', 'url' => 'https://checkout.stripe.com/pay/cs_test_123']),
        'error'  => null,
    ];
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_stripe_pending.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");

$con = new stdClass();
session_start();
$_SESSION['guest_cart'] = [1 => 2];
$_SESSION['csrf_token'] = 'test-csrf-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
putenv('STRIPE_SECRET_KEY=sk_test_dummy');
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
    $items = $pdo->query("SELECT * FROM order_item")->fetchAll(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_created' => $order !== false,
        'payment_mode_stripe' => $order && $order['payment_mode'] === 'STRIPE',
        'payment_status_pending' => $order && $order['payment_status'] === 'pending',
        'gateway_session_id_attached' => $order && $order['payment_id'] === 'cs_test_123',
        'total_price_correct' => $order && (float) $order['total_price'] === 2000.0,
        'one_order_item' => count($items) === 1,
        'stock_not_yet_decremented' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/pay-stripe.php');
