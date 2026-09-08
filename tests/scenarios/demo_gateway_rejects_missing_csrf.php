<?php
/**
 * A POST to demo-gateway.php with a missing/forged csrf_token must be
 * rejected (403) and must NOT mark the order paid -- the same CSRF
 * contract every other checkout POST in this app enforces.
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

// sendOrderPaidEmail() must never even be reached for a rejected request;
// stubbing sendAppEmail() (rather than trusting that) documents the
// expectation and keeps this hermetic regardless of local SMTP config.
function sendAppEmail($to, $subject, $body)
{
    return true;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_csrf.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKDEMOCSRF', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'DEMO-dddddddddddddddd', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

// Must be started here, not left for demo-gateway.php's own session_start()
// to be the first call -- the first session_start() in a request replaces
// $_SESSION wholesale with whatever the (nonexistent, in this throwaway
// CLI process) session store has, discarding anything set before it. This
// matches every other checkout scenario's session_start()-then-populate
// order (see payment_checkout_missing_csrf_rejected.php).
session_start();
$_SESSION['csrf_token'] = 'real-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'csrf_token' => 'forged-token',
    'trackid'    => 'TRKDEMOCSRF',
    'action'     => 'succeed',
];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKDEMOCSRF'")->fetch(PDO::FETCH_ASSOC);
    $perfume = $pdo->query("SELECT qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    // Not asserting the 403 status itself: demo-gateway.php's own
    // session_start() call here is a harmless-but-noisy second call (the
    // scenario already started one so $_SESSION survives into it), and
    // that notice reaches output before http_response_code(403) runs,
    // which makes the code itself unobservable from here -- same
    // header()-after-output limitation the existing
    // payment_checkout_missing_csrf_rejected.php scenario already works
    // around by asserting only DB-observable outcomes.
    return [
        'order_still_pending' => $order && $order['payment_status'] === 'pending',
        'stock_untouched' => $perfume && (int) $perfume['qty'] === 10,
    ];
});

include($ROOT . '/demo-gateway.php');
