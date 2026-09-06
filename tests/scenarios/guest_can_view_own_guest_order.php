<?php
/**
 * Positive case, paired with guest_cannot_view_account_order.php: a guest
 * order (user_id IS NULL) must still be visible to a signed-out visitor
 * who has its tracking number -- this is the whole point of guest
 * checkout (an order confirmation page immediately after ordering).
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_guest_views_own_order.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price) VALUES ('TRKGUEST002', NULL, 'Guest Buyer', 'guest@example.com', 13800)");

$con = new stdClass();
$_SESSION = [];
require $ROOT . '/functions/functions.php';

shim_report(function () {
    $result = validateTrackID('TRKGUEST002');
    $row = mysqli_fetch_assoc($result);
    return [
        'guest_finds_own_order' => $row !== null,
        'correct_order_returned' => $row && $row['name'] === 'Guest Buyer',
    ];
});
