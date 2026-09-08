<?php
/**
 * The key fraud guard: even with PAYMENT_DEMO_MODE=1, demo-gateway.php
 * must refuse to act on a genuine pending gateway order -- one whose
 * payment_id is a real Stripe Checkout Session id (or SSLCommerz session
 * key / Coinbase charge id), not the 'DEMO-' prefix functions/pay-*.php's
 * demo branch stamps on. Without this guard, turning demo mode on for a
 * client walkthrough would let anyone who knows/guesses a tracking number
 * mark a real customer's still-unpaid gateway order as paid for free.
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

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_real_order.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKREALORDER', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'cs_test_real_session_123', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['trackid'] = 'TRKREALORDER';

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKREALORDER'")->fetch(PDO::FETCH_ASSOC);
    return [
        'responds_404' => http_response_code() === 404,
        'real_order_still_pending' => $order && $order['payment_status'] === 'pending',
        'real_order_ref_unchanged' => $order && $order['payment_id'] === 'cs_test_real_session_123',
    ];
});

include($ROOT . '/demo-gateway.php');
