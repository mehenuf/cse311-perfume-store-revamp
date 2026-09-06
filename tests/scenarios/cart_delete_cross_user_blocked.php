<?php
/**
 * User B tries to delete User A's cart row by id. This must be refused --
 * both the ownership check and the DELETE itself are scoped to the
 * session's own user_id, so guessing or iterating another cart_id can
 * never remove someone else's line.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_cart_idor.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif','h','Arif','arif@example.com',0)");
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('nusrat','h','Nusrat','nusrat@example.com',0)");
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 13800, 1)");
$pdo->exec("INSERT INTO cart (user_id, perfume_id, perfume_qty) VALUES (1, 1, 1)"); // belongs to User A (id 1)

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 2, 'username' => 'nusrat', 'email' => 'nusrat@example.com']; // User B
$_POST = ['scope' => 'delete', 'cart_id' => '1'];

shim_report(function () use ($pdo) {
    $stillThere = $pdo->query("SELECT COUNT(*) n FROM cart WHERE id = 1")->fetch(PDO::FETCH_ASSOC)['n'];
    return ['other_users_row_survives' => (int) $stillThere === 1];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/cart-function.php');
