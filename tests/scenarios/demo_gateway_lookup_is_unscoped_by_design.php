<?php
/**
 * Documents (does not "fix") a deliberate design point worth flagging:
 * demo-gateway.php resolves the order via findOrderByTrackingNo() -- the
 * same *unscoped* lookup functions/payments/common.php reserves for real
 * webhook use (see its docblock), not the session/account-scoped
 * validateTrackID() customer-facing pages use. So a session belonging to
 * a completely different account (here, none at all -- no $_SESSION
 * 'auth' set) can still process someone else's DEMO- pending order simply
 * by knowing/guessing its tracking_no.
 *
 * The only thing standing between that and a real problem is: (1) this
 * whole file 404s unless PAYMENT_DEMO_MODE=1, which must never be true in
 * production, and (2) it only ever acts on orders already stamped
 * 'DEMO-' -- a real customer's gateway order can never be reached this
 * way (see demo_gateway_404_for_real_gateway_order.php). Given that, the
 * remaining exposure is: anyone on the same demo deployment who observes
 * or guesses a tracking_no (random_int(1000000, 999999999999) plus a
 * 4-char name prefix -- see createPendingOrder()) could mark someone
 * else's demo order paid/failed early. Low severity for a feature that
 * must only ever run in a non-production demo environment with fake
 * orders, but it is NOT the same guarantee real webhooks get from gateway
 * signature verification, and should not be treated as one.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function appEnv($key, $default = '')
{
    if ($key === 'PAYMENT_DEMO_MODE') {
        return '1';
    }
    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
}

function sendAppEmail($to, $subject, $body)
{
    return true;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_unscoped_lookup.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('owner','h','Order Owner','owner@example.com',0)");
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
// Order belongs to user_id 1 ("owner"); this scenario's own session is a
// guest with no 'auth' set at all -- a stranger to that order.
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKNOTMINE', 1, 'Order Owner', 'owner@example.com', 2000, 'STRIPE', 'pending', 'DEMO-2222222222222222', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

// Started here (not left for demo-gateway.php's own session_start() to be
// the first call), so $_SESSION set below actually survives into it --
// see demo_gateway_rejects_missing_csrf.php's comment for why.
$con = new stdClass();
session_start();
// No $_SESSION['auth'] at all -- an unrelated guest session.
$_SESSION['csrf_token'] = 'real-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'csrf_token' => 'real-token',
    'trackid'    => 'TRKNOTMINE',
    'action'     => 'succeed',
];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKNOTMINE'")->fetch(PDO::FETCH_ASSOC);
    return [
        // This is the documented, accepted-risk behaviour, not a bug fix
        // to verify -- an unrelated session CAN resolve and act on
        // someone else's DEMO order once it knows the tracking_no.
        'unrelated_session_can_still_mark_it_paid' => $order && $order['payment_status'] === 'paid',
    ];
});

include($ROOT . '/demo-gateway.php');
