<?php
/**
 * Every gateway retries a webhook delivery on anything but a 2xx
 * response, so markOrderPaid() (functions/payments/common.php) must be
 * safe to call twice with the exact same event id -- the second call
 * must not decrement stock again or send a second email. This calls
 * markOrderPaid() directly (rather than the full webhooks/stripe.php
 * script, which exit()s after handling one request and so cannot be
 * invoked twice within a single process) to isolate exactly the
 * mechanism being tested.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_idempotent_event.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKIDEMPOTENT', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'cs_test_dup', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

// Stubbed so this never attempts a real SMTP/mail() send regardless of
// whatever SMTP_HOST happens to be configured on the machine running the
// suite -- functions/payments/common.php's sendOrderPaidEmail() calls the
// real sendAppEmail() only if nothing has defined it first (same
// function_exists() seam as everything else in this test harness).
function sendAppEmail($to, $subject, $body)
{
    return true;
}

$con = new stdClass();
require $ROOT . '/functions/payments/common.php';

markOrderPaid(1, 'STRIPE', 'evt_dup', 'checkout.session.completed', 'pi_dup', 2000, 'BDT', '{}');
markOrderPaid(1, 'STRIPE', 'evt_dup', 'checkout.session.completed', 'pi_dup', 2000, 'BDT', '{}');

shim_report(function () use ($pdo) {
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $events  = $pdo->query("SELECT * FROM payment_events WHERE event_id = 'evt_dup'")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'stock_decremented_exactly_once' => $perfume && (int) $perfume['qty'] === 8,
        'exactly_one_event_row' => count($events) === 1,
    ];
});
