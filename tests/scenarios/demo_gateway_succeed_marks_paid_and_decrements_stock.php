<?php
/**
 * POSTing action=succeed on a valid DEMO- pending order must mark it
 * paid, decrement stock, and record the order's own total_price/currency
 * as the "confirmed" amount (demo-gateway.php passes the order's own
 * figures to markOrderPaid(), so this can never trip the real
 * amount/currency mismatch guard -- there being no real gateway payload to
 * mismatch against is inherent to a demo).
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

function sendAppEmail($to, $subject, $body)
{
    return true;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_succeed.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKDEMOSUCCEED', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'DEMO-eeeeeeeeeeeeeeee', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

// Started here (not left for demo-gateway.php's own session_start() to be
// the first call), so $_SESSION set below actually survives into it --
// see demo_gateway_rejects_missing_csrf.php's comment for why.
$con = new stdClass();
session_start();
$_SESSION['csrf_token'] = 'real-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'csrf_token' => 'real-token',
    'trackid'    => 'TRKDEMOSUCCEED',
    'action'     => 'succeed',
];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKDEMOSUCCEED'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $events = $pdo->query("SELECT * FROM payment_events WHERE gateway = 'STRIPE'")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'order_marked_paid' => $order && $order['payment_status'] === 'paid',
        'gateway_ref_is_demo_payment_id' => $order && $order['gateway_ref'] === 'DEMO-eeeeeeeeeeeeeeee',
        'paid_at_set' => $order && $order['paid_at'] !== null,
        'stock_decremented' => $perfume && (int) $perfume['qty'] === 8,
        'one_payment_event_logged' => count($events) === 1,
        'event_type_is_demo' => count($events) === 1 && $events[0]['event_type'] === 'demo.payment_succeeded',
    ];
});

include($ROOT . '/demo-gateway.php');
