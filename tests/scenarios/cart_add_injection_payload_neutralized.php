<?php
/**
 * perfume_id carries an injection-style payload ("1 OR 1=1"). The (int)
 * cast in functions/cart-function.php must reduce it to a harmless integer
 * before it ever reaches a query -- this field used to be passed through
 * completely unescaped.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_cart_injection.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif','h','Arif','arif@example.com',0)");
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 13800, 1)");
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Bleu de Chanel', 10, 15200, 1)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
$_POST = ['scope' => 'add', 'perfume_id' => '1 OR 1=1', 'perfume_qty' => '2'];

shim_report(function () use ($pdo) {
    $rows = $pdo->query("SELECT perfume_id FROM cart WHERE user_id = 1")->fetchAll(PDO::FETCH_ASSOC);
    return [
        'exactly_one_row_added' => count($rows) === 1,
        'perfume_id_cast_to_int' => count($rows) === 1 && (int) $rows[0]['perfume_id'] === 1,
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/cart-function.php');
