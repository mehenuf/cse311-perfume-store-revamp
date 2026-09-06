<?php
/**
 * A perfume with an active discount (no dates set -- "on until turned
 * off") must price at the discounted rate everywhere price is read:
 * perfumePricing() directly, and the cart total computed from it.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_discount_active.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status, discount_percent) VALUES ('Dior Sauvage', 10, 10000, 1, 20)");

$con = new stdClass();
session_start();
$_SESSION['guest_cart'] = [1 => 2];
require $ROOT . '/functions/functions.php';

shim_report(function () {
    $rows = displayCart();
    $row = $rows[0] ?? null;
    return [
        'row_present' => $row !== null,
        'discount_active_flag' => $row && $row['discount_active'] === true,
        'original_price_preserved' => $row && (float) $row['original_price'] === 10000.0,
        'discounted_price_correct' => $row && (float) $row['price'] === 8000.0, // 10000 * 0.8
        'percent_reported' => $row && $row['discount_percent'] === 20,
    ];
});
