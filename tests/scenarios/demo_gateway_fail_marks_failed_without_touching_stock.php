<?php
/**
 * POSTing action=fail on a valid DEMO- pending order must mark it
 * 'failed' and must not touch stock -- mirrors the real
 * markOrderFailed() contract webhooks/*.php already relies on.
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

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_fail.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKDEMOFAIL', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'DEMO-ffffffffffffffff', 'BDT')");
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
    'trackid'    => 'TRKDEMOFAIL',
    'action'     => 'fail',
];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKDEMOFAIL'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_marked_failed' => $order && $order['payment_status'] === 'failed',
        'stock_untouched' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

include($ROOT . '/demo-gateway.php');
