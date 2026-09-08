<?php
/**
 * With PAYMENT_DEMO_MODE=1, functions/pay-stripe.php must never call the
 * real stripeCreateCheckoutSession() (i.e. never attempt an outbound HTTP
 * call to Stripe) -- it must attach a 'DEMO-' reference and redirect
 * straight to demo-gateway.php instead. stripeCreateCheckoutSession() is
 * pre-defined here (before payments/stripe.php's own function_exists()
 * guard would define it) to throw if it is ever invoked, so this fails
 * loudly rather than silently passing if that branch regresses.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function appEnv($key, $default = '')
{
    if ($key === 'PAYMENT_DEMO_MODE') {
        return '1';
    }
    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
}

function stripeCreateCheckoutSession(array $order)
{
    throw new Exception('real Stripe gateway call must never happen while PAYMENT_DEMO_MODE=1');
}

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_mode_stripe_skips_gateway.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");

$con = new stdClass();
session_start();
$_SESSION['guest_cart'] = [1 => 2];
$_SESSION['csrf_token'] = 'test-csrf-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
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
        'payment_mode_stripe' => $order && $order['payment_mode'] === 'STRIPE',
        'payment_status_pending' => $order && $order['payment_status'] === 'pending',
        'demo_reference_attached' => $order && strpos((string) $order['payment_id'], 'DEMO-') === 0,
        'stock_not_yet_decremented' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/pay-stripe.php');
