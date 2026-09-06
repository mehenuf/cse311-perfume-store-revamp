<?php
/**
 * A logged-in admin saves a new price and stock quantity from the
 * catalogue table without visiting the full edit page.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_inline_save.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 13800, 1)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['admin_check'] = 1;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'mehenuf', 'email' => 'mehenuf@gmail.com'];
$_POST = ['perfume_id' => '1', 'price' => '14500', 'qty' => '25'];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT price, qty FROM perfumes WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    return [
        'price_updated' => (float) $row['price'] === 14500.0,
        'qty_updated' => (int) $row['qty'] === 25,
    ];
});

chdir($ROOT . '/admin/Includes');
include($ROOT . '/admin/Includes/inline-update.php');
