<?php
/**
 * demo-gateway.php must 404 for a DEMO- order that is already 'paid' --
 * otherwise reloading/bookmarking the demo checkout page after a demo run
 * would let "Simulate successful payment" be pressed again on an order
 * already resolved. (demo-gateway.php exit()s on its 404 path, so this is
 * a separate scenario file from the 'failed' case rather than two GETs in
 * one process -- see demo_gateway_404_for_already_failed_order.php.)
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

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_already_paid.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency, gateway_ref)
            VALUES ('TRKDEMOPAID', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'paid', 'DEMO-bbbbbbbbbbbbbbbb', 'BDT', 'demo-ref')");

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['trackid'] = 'TRKDEMOPAID';

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKDEMOPAID'")->fetch(PDO::FETCH_ASSOC);
    return [
        'responds_404' => http_response_code() === 404,
        'still_paid_untouched' => $order && $order['payment_status'] === 'paid',
    ];
});

include($ROOT . '/demo-gateway.php');
