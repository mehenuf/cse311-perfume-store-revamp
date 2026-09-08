<?php
/**
 * A Stripe-Signature header that does not match the payload (wrong
 * secret, tampered body, or forged header) must be rejected outright --
 * the order must stay pending and no payment_events row is written.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_stripe_webhook_badsig.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKSTRIPE002', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'cs_test_456', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

putenv('STRIPE_WEBHOOK_SECRET=whsec_real_secret');

$payload = json_encode([
    'id'   => 'evt_2',
    'type' => 'checkout.session.completed',
    'data' => ['object' => [
        'id' => 'cs_test_456', 'client_reference_id' => 'TRKSTRIPE002',
        'payment_status' => 'paid', 'amount_total' => 200000, 'currency' => 'bdt',
        'payment_intent' => 'pi_forged',
    ]],
]);

function paymentReadRawBody()
{
    global $payload;
    return $payload;
}

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
// Signed with the wrong secret -- as if an attacker guessed/forged it.
$timestamp = time();
$_SERVER['HTTP_STRIPE_SIGNATURE'] = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_wrong_secret');

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKSTRIPE002'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $events = $pdo->query("SELECT * FROM payment_events")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'order_still_pending' => $order && $order['payment_status'] === 'pending',
        'stock_untouched' => $perfume && (int) $perfume['qty'] === 10,
        'no_event_logged' => count($events) === 0,
    ];
});

include($ROOT . '/webhooks/stripe.php');
