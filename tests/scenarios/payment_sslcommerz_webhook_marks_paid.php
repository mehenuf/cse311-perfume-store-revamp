<?php
/**
 * The SSLCommerz IPN handler never trusts the POST body itself -- it must
 * call the Order Validation API and only act on that response. This
 * stubs paymentHttpRequest() to stand in for that API call, returning a
 * 'VALID' status for the order's own tran_id/amount, and asserts the
 * order is marked paid and stock decremented from that authoritative
 * response (not from $_POST, which is deliberately left mostly empty
 * here to prove that).
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    return [
        'status' => 200,
        'body'   => json_encode(['status' => 'VALID', 'tran_id' => 'TRKSSL001', 'val_id' => 'val_123',
            'amount' => '2000.00', 'currency' => 'BDT']),
        'error'  => null,
    ];
}

function sendAppEmail($to, $subject, $body)
{
    return true;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_sslcommerz_webhook_paid.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKSSL001', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'SSLCOMMERZ', 'pending', 'sess_abc', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['val_id' => 'val_123', 'tran_id' => 'TRKSSL001'];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKSSL001'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_marked_paid' => $order && $order['payment_status'] === 'paid',
        'gateway_ref_is_val_id' => $order && $order['gateway_ref'] === 'val_123',
        'stock_decremented' => $perfume && (int) $perfume['qty'] === 8,
    ];
});

include($ROOT . '/webhooks/sslcommerz-ipn.php');
