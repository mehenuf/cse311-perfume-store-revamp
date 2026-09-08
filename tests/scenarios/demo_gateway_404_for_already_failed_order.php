<?php
/**
 * Same contract as demo_gateway_404_for_already_paid_order.php, for an
 * order already marked 'failed'.
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

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_already_failed.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKDEMOFAILED', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'failed', 'DEMO-cccccccccccccccc', 'BDT')");

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['trackid'] = 'TRKDEMOFAILED';

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKDEMOFAILED'")->fetch(PDO::FETCH_ASSOC);
    return [
        'responds_404' => http_response_code() === 404,
        'still_failed_untouched' => $order && $order['payment_status'] === 'failed',
    ];
});

include($ROOT . '/demo-gateway.php');
