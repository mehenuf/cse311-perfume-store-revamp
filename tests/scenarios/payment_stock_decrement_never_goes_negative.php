<?php
/**
 * Regression test: decrementStockForOrder() must never drive perfumes.qty
 * (an unsigned column) negative. This can happen because online-gateway
 * orders deliberately do not reserve stock at checkout time (see
 * createPendingOrder()) -- if the same stock sells out elsewhere (e.g. a
 * Cash on Delivery order, which decrements immediately) before a pending
 * gateway payment is confirmed, the confirmed order still needs to be
 * marked paid, but the stock it can no longer represent must be left
 * alone rather than forced negative.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function sendAppEmail($to, $subject, $body)
{
    return true;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_stock_negative_guard.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
// Only 1 unit left -- sold out from under this order by something else
// (e.g. a Cash on Delivery sale) while this gateway payment was pending.
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 1, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKOVERSELL', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'cs_test_oversell', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$con = new stdClass();
require $ROOT . '/functions/payments/common.php';

markOrderPaid(1, 'STRIPE', 'evt_oversell', 'checkout.session.completed', 'pi_oversell', 2000, 'BDT', '{}');

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'order_still_marked_paid' => $order && $order['payment_status'] === 'paid',
        'stock_never_went_negative' => $perfume && (int) $perfume['qty'] >= 0,
        'stock_left_at_one' => $perfume && (int) $perfume['qty'] === 1,
    ];
});
