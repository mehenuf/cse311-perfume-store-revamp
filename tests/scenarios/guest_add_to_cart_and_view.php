<?php
/**
 * A visitor with no session adds an item to their cart. It must land in
 * $_SESSION['guest_cart'] (no DB row -- there is no account to key one by)
 * and displayCart() must return it joined against the live catalogue.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_guest_cart.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 13800, 1)");

$con = new stdClass();
$_SESSION = [];
$_POST = ['scope' => 'add', 'perfume_id' => '1', 'perfume_qty' => '2'];

shim_report(function () use ($ROOT) {
    $sessionOk = isset($_SESSION['guest_cart'][1]) && $_SESSION['guest_cart'][1] === 2;

    chdir($ROOT . '/functions');
    require $ROOT . '/functions/functions.php';
    $rows = displayCart();

    return [
        'guest_cart_session_set' => $sessionOk,
        'display_cart_returns_one_row' => count($rows) === 1,
        'display_cart_row_shape_correct' => count($rows) === 1
            && $rows[0]['perfume_id'] === 1
            && $rows[0]['perfume_quantity'] === 2
            && $rows[0]['perfume_name'] === 'Dior Sauvage',
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/cart-function.php');
