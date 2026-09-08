<?php
/**
 * A correctly-signed checkout.session.completed with payment_status=paid
 * must mark the order paid, decrement stock, and clear the account's
 * cart -- all from markOrderPaid() in functions/payments/common.php.
 * paymentReadRawBody() is stubbed (the CLI SAPI has no real request body
 * to read php://input from) but the signature is computed for real and
 * verified for real -- this is not a signature-check bypass.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_stripe_webhook_paid.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif','h','Arif','arif@example.com',0)");
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKSTRIPE001', 1, 'Arif Rahman', 'arif@example.com', 2000, 'STRIPE', 'pending', 'cs_test_123', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");
$pdo->exec("INSERT INTO cart (user_id, perfume_id, perfume_qty) VALUES (1, 1, 2)");

$secret = 'whsec_test_secret';
putenv('STRIPE_WEBHOOK_SECRET=' . $secret);

$payload = json_encode([
    'id'   => 'evt_1',
    'type' => 'checkout.session.completed',
    'data' => ['object' => [
        'id' => 'cs_test_123',
        'client_reference_id' => 'TRKSTRIPE001',
        'payment_status' => 'paid',
        'amount_total' => 200000, // 2000.00 BDT in poisha
        'currency' => 'bdt',
        'payment_intent' => 'pi_abc',
    ]],
]);
// The real verifier requires |now - t| within its tolerance window, so
// the timestamp has to be "now" for this signature to be accepted.
$timestamp = time();
$signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

function paymentReadRawBody()
{
    global $payload;
    return $payload;
}

// Stubbed so this never attempts a real SMTP/mail() send regardless of
// whatever SMTP_HOST happens to be configured on the machine running the
// suite -- see payment_webhook_idempotent_on_duplicate_event.php for why.
function sendAppEmail($to, $subject, $body)
{
    return true;
}

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_STRIPE_SIGNATURE'] = "t=$timestamp,v1=$signature";

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKSTRIPE001'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $cart = $pdo->query("SELECT * FROM cart WHERE user_id = 1")->fetchAll(PDO::FETCH_ASSOC);
    $events = $pdo->query("SELECT * FROM payment_events WHERE gateway = 'STRIPE'")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'order_marked_paid' => $order && $order['payment_status'] === 'paid',
        'gateway_ref_recorded' => $order && $order['gateway_ref'] === 'pi_abc',
        'paid_at_set' => $order && $order['paid_at'] !== null,
        'stock_decremented' => $perfume && (int) $perfume['qty'] === 8,
        'account_cart_cleared' => count($cart) === 0,
        'one_payment_event_logged' => count($events) === 1,
    ];
});

include($ROOT . '/webhooks/stripe.php');
