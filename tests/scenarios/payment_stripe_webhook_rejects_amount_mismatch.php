<?php
/**
 * A validly-signed webhook whose amount does not match the order's own
 * total_price must never be marked paid -- the signature only proves
 * Stripe sent this, not that it is the payment we expect. The event is
 * still logged (so the same mismatched event does not retry forever),
 * but the order is left 'pending' for manual review rather than silently
 * accepted or refunded automatically.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_stripe_webhook_mismatch.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKSTRIPE003', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'cs_test_789', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$secret = 'whsec_test_secret';
putenv('STRIPE_WEBHOOK_SECRET=' . $secret);

// The order is worth 2000 BDT; this event (genuinely signed by the
// configured secret) claims only 500 was paid.
$payload = json_encode([
    'id'   => 'evt_3',
    'type' => 'checkout.session.completed',
    'data' => ['object' => [
        'id' => 'cs_test_789', 'client_reference_id' => 'TRKSTRIPE003',
        'payment_status' => 'paid', 'amount_total' => 50000, 'currency' => 'bdt',
        'payment_intent' => 'pi_underpaid',
    ]],
]);
$timestamp = time();
$signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

function paymentReadRawBody()
{
    global $payload;
    return $payload;
}

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_STRIPE_SIGNATURE'] = "t=$timestamp,v1=$signature";

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKSTRIPE003'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $events = $pdo->query("SELECT * FROM payment_events")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'order_stays_pending_not_paid' => $order && $order['payment_status'] === 'pending',
        'stock_untouched' => $perfume && (int) $perfume['qty'] === 10,
        'mismatch_event_logged_once' => count($events) === 1,
    ];
});

include($ROOT . '/webhooks/stripe.php');
