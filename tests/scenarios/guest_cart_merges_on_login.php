<?php
/**
 * A visitor shops as a guest, then logs in. Their session cart must be
 * folded into their account's DB cart (merging quantity if the account
 * already had that item) rather than silently disappearing at login.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_cart_merge.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif', 'arif1234', 'Arif Rahman', 'arif@example.com', 0)");
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 13800, 1)");   // id 1: already in the account's cart
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Bleu de Chanel', 10, 15200, 1)"); // id 2: only in the guest cart
$pdo->exec("INSERT INTO cart (user_id, perfume_id, perfume_qty) VALUES (1, 1, 1)"); // account already has 1x Dior Sauvage

$con = new stdClass();
// authcode.php calls session_start() too; starting it here first means its
// call is a harmless no-op that preserves what we set below, instead of
// loading an empty on-disk session over it.
session_start();
$_SESSION['guest_cart'] = [1 => 2, 2 => 3]; // +2 more Sauvage (should merge to 3), 3x Chanel (new row)
$_POST = ['login_btn' => '1', 'var_username' => 'arif', 'var_password' => 'arif1234'];

shim_report(function () use ($pdo) {
    $rows = $pdo->query("SELECT perfume_id, perfume_qty FROM cart WHERE user_id = 1 ORDER BY perfume_id")->fetchAll(PDO::FETCH_ASSOC);
    $byPerfume = [];
    foreach ($rows as $r) {
        $byPerfume[(int) $r['perfume_id']] = (int) $r['perfume_qty'];
    }
    return [
        'two_cart_rows' => count($rows) === 2,
        'existing_item_merged_not_duplicated' => ($byPerfume[1] ?? null) === 3,
        'new_item_added' => ($byPerfume[2] ?? null) === 3,
        'guest_cart_cleared_after_merge' => empty($_SESSION['guest_cart']),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/authcode.php');
