<?php
/**
 * An X-CC-Webhook-Signature that does not match the raw body must be
 * rejected before the JSON is even parsed -- the order stays pending and
 * stock is untouched.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_coinbase_webhook_badsig.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKCOIN002', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'COINBASE', 'pending', 'charge_def', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

putenv('COINBASE_COMMERCE_WEBHOOK_SECRET=coinbase_real_secret');

$payload = json_encode([
    'event' => ['id' => 'evt_cb_2', 'type' => 'charge:confirmed', 'data' => [
        'id' => 'charge_def', 'metadata' => ['tracking_no' => 'TRKCOIN002'],
        'pricing' => ['local' => ['amount' => '2000.00', 'currency' => 'BDT']],
    ]],
]);

function paymentReadRawBody()
{
    global $payload;
    return $payload;
}

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
// Signed with the wrong secret.
$_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'] = hash_hmac('sha256', $payload, 'coinbase_wrong_secret');

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKCOIN002'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_still_pending' => $order && $order['payment_status'] === 'pending',
        'stock_untouched' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

include($ROOT . '/webhooks/coinbase.php');
