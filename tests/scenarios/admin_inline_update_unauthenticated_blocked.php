<?php
/**
 * The AJAX endpoint behind the inline price/stock editor is a second way
 * to reach the same columns admin/Includes/code.php writes -- it needs
 * the same admin-only guard, checked here the same way.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_inline_unauth.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 13800, 1)");

$con = new stdClass();
$_SESSION = [];
$_POST = ['perfume_id' => '1', 'price' => '99999', 'qty' => '0'];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT price, qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'price_unchanged' => (float) $row['price'] === 13800.0,
        'qty_unchanged' => (int) $row['qty'] === 10,
    ];
});

chdir($ROOT . '/admin/Includes');
include($ROOT . '/admin/Includes/inline-update.php');
