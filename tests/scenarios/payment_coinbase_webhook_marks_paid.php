<?php
/**
 * A correctly-signed charge:confirmed event must mark the order paid and
 * decrement stock, reading the order to update from metadata.tracking_no
 * (set at charge-creation time) rather than trusting anything else in
 * the payload for identity. The amount checked against the order's own
 * total_price comes from data.payments[].value.local -- Coinbase's own
 * record of what was actually detected on-chain -- not data.pricing.local,
 * which is only an echo of the price this app requested at charge-creation
 * time and would make the check tautological.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function sendAppEmail($to, $subject, $body)
{
    return true;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_coinbase_webhook_paid.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKCOIN001', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'COINBASE', 'pending', 'charge_abc', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$secret = 'coinbase_test_secret';
putenv('COINBASE_COMMERCE_WEBHOOK_SECRET=' . $secret);

$payload = json_encode([
    'event' => [
        'id' => 'evt_cb_1',
        'type' => 'charge:confirmed',
        'data' => [
            'id' => 'charge_abc',
            'metadata' => ['tracking_no' => 'TRKCOIN001'],
            // Deliberately different from the actually-checked field below,
            // to prove the webhook is NOT reading this tautological echo.
            'pricing' => ['local' => ['amount' => '999.00', 'currency' => 'BDT']],
            'payments' => [
                ['value' => ['local' => ['amount' => '2000.00', 'currency' => 'BDT']]],
            ],
        ],
    ],
]);
$signature = hash_hmac('sha256', $payload, $secret);

function paymentReadRawBody()
{
    global $payload;
    return $payload;
}

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'] = $signature;

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKCOIN001'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_marked_paid' => $order && $order['payment_status'] === 'paid',
        'stock_decremented' => $perfume && (int) $perfume['qty'] === 8,
    ];
});

include($ROOT . '/webhooks/coinbase.php');
