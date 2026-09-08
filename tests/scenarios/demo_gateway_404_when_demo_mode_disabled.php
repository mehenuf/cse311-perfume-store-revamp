<?php
/**
 * demo-gateway.php must 404 whenever PAYMENT_DEMO_MODE is not the literal
 * string '1' -- even for an order whose payment_id already carries the
 * 'DEMO-' prefix (e.g. left over from a previous demo session, or crafted
 * by an attacker who guessed the convention) -- so the page can never be
 * reached once a deployment turns demo mode back off.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function appEnv($key, $default = '')
{
    if ($key === 'PAYMENT_DEMO_MODE') {
        return '0';
    }
    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
}

$pdo = shim_boot(__DIR__ . '/../.tmp_demo_gateway_disabled.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKDEMODISABLED', NULL, 'Guest Buyer', 'guest@example.com', 2000, 'STRIPE', 'pending', 'DEMO-aaaaaaaaaaaaaaaa', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

// demo-gateway.php itself calls session_start() as its first statement --
// not called here too, to avoid the harmless-but-noisy "session already
// active" notice that would otherwise print before the header this test
// asserts on.
$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['trackid'] = 'TRKDEMODISABLED';

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKDEMODISABLED'")->fetch(PDO::FETCH_ASSOC);
    return [
        'responds_404' => http_response_code() === 404,
        'order_untouched' => $order && $order['payment_status'] === 'pending',
    ];
});

include($ROOT . '/demo-gateway.php');
