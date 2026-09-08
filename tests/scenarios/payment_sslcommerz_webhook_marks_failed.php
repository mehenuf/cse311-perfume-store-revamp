<?php
/**
 * When SSLCommerz's own Validation API reports a status other than
 * VALID/VALIDATED for a val_id (e.g. the customer's payment genuinely
 * failed), the order must be marked 'failed', never 'paid', and stock
 * must not move.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    return [
        'status' => 200,
        'body'   => json_encode(['status' => 'FAILED', 'tran_id' => 'TRKSSL002', 'val_id' => 'val_456']),
        'error'  => null,
    ];
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_sslcommerz_webhook_failed.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKSSL002', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'SSLCOMMERZ', 'pending', 'sess_def', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['val_id' => 'val_456', 'tran_id' => 'TRKSSL002'];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKSSL002'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_marked_failed' => $order && $order['payment_status'] === 'failed',
        'stock_untouched' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

include($ROOT . '/webhooks/sslcommerz-ipn.php');
