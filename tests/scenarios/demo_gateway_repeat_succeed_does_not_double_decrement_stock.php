<?php
/**
 * demo-gateway.php exit()s after handling one request, so a second
 * "Simulate successful payment" click (double-submit, or reloading the
 * demo page after the first redirect already fired) is a second, separate
 * process each generating its OWN random event id -- unlike a real
 * gateway's webhook retry, which resends the identical event id. This
 * calls markOrderPaid() directly with two different event ids (exactly
 * what demo-gateway.php's `'demo-' . $order['id'] . '-' . $action . '-' .
 * bin2hex(random_bytes(4))` produces on each POST) to check what actually
 * protects a repeat click: markOrderPaid()'s pre-fetched
 * $order['payment_status'] check (the order is already 'paid' by the time
 * the second call's SELECT runs), NOT the event-id idempotency guard
 * (isEventProcessed() sees two different ids and does not dedupe them).
 *
 * Net effect asserted here: stock is only ever decremented once (the
 * customer-facing guarantee that matters), but -- flagged as a minor,
 * non-blocking finding -- each repeat click still inserts its own
 * payment_events row instead of being recognised as a retry of the same
 * event, unlike every real gateway's webhook path.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function sendAppEmail($to, $subject, $body)
{
    return true;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_repeat_succeed.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKDEMOREPEAT', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'DEMO-1111111111111111', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$con = new stdClass();
require $ROOT . '/functions/payments/common.php';

// Same shape demo-gateway.php's succeed branch builds, called twice with
// two distinct random event ids -- exactly what two separate page loads
// of the same "Simulate successful payment" button would each produce.
markOrderPaid(1, 'STRIPE', 'demo-1-succeed-' . bin2hex(random_bytes(4)), 'demo.payment_succeeded', 'DEMO-1111111111111111', 2000, 'BDT', '{"demo":true}');
markOrderPaid(1, 'STRIPE', 'demo-1-succeed-' . bin2hex(random_bytes(4)), 'demo.payment_succeeded', 'DEMO-1111111111111111', 2000, 'BDT', '{"demo":true}');

shim_report(function () use ($pdo) {
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $events  = $pdo->query("SELECT * FROM payment_events WHERE order_id = 1")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'stock_decremented_exactly_once' => $perfume && (int) $perfume['qty'] === 8,
        // Documents the current (non-ideal but harmless) behaviour: each
        // repeat click's distinct random event id is NOT deduped against
        // the first, so two payment_events rows land instead of one.
        'finding_repeat_click_logs_two_distinct_events' => count($events) === 2,
    ];
});
