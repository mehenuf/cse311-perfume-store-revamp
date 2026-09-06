<?php
/**
 * A guest with items in their session cart checks out. The order must be
 * created with user_id = NULL (no account attached), and the guest's
 * session cart must be cleared afterward -- there is no `cart` table row
 * to delete for a guest, only the session key.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_guest_checkout.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 13800, 1)");

$con = new stdClass();
// Start the session ourselves first: placeorder.php calls session_start()
// too, and a second call is a harmless no-op that preserves whatever is
// already in $_SESSION -- calling it in the other order would instead
// load the (empty) on-disk session and silently wipe the cart we set here,
// which is exactly the bug this ordering avoids.
session_start();
$_SESSION['guest_cart'] = [1 => 2];
$_POST = [
    'placeorder' => '1',
    'name' => 'Guest Buyer',
    'email' => 'guest@example.com',
    'contact' => '+8801700000000',
    'zipcode' => '1205',
    'address' => 'House 1, Road 1, Dhaka',
];

shim_report(function () use ($pdo) {
    $order = $pdo->query("SELECT * FROM orders")->fetch(PDO::FETCH_ASSOC);
    $items = $pdo->query("SELECT * FROM order_item")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'order_created' => $order !== false,
        'user_id_is_null' => $order && $order['user_id'] === null,
        'delivery_name_stored' => $order && $order['name'] === 'Guest Buyer',
        'total_price_correct' => $order && (float) $order['total_price'] === 27600.0,
        'one_order_item' => count($items) === 1,
        'guest_cart_cleared' => empty($_SESSION['guest_cart']),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/placeorder.php');
