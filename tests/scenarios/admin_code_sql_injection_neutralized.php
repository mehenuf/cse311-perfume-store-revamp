<?php
/**
 * A classic SQL-breakout payload in the product name. With the old
 * string-concatenated query this could inject arbitrary SQL (including
 * exfiltrating other tables' data into a visible field, or dropping a
 * table); with bind_param it must land as inert literal text.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_code_injection.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check)
            VALUES ('mehenuf', 'irrelevant-secret-hash', 'Owner', 'owner@example.com', 1)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['admin_check'] = 1;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'mehenuf', 'email' => 'mehenuf@gmail.com'];

$payload = "Evil', (SELECT password FROM customer LIMIT 1), 'x', 'x', 1, 'x', 1, 1, 1); DROP TABLE perfumes; --";
$_POST = [
    'addperfume_btn' => '1',
    'name' => $payload,
    'perfume_notes' => 'n/a', 'description' => 'n/a', 'volume' => '100 ml',
    'qty' => '1', 'price' => '1',
];
$_FILES = ['image_path' => ['name' => '', 'tmp_name' => '', 'error' => 4, 'size' => 0]];

shim_report(function () use ($pdo, $payload) {
    $tables = array_column($pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_ASSOC), 'name');
    $product = $pdo->query("SELECT name FROM perfumes")->fetch(PDO::FETCH_ASSOC);
    $customerPassword = $pdo->query("SELECT password FROM customer WHERE username = 'mehenuf'")->fetch(PDO::FETCH_ASSOC)['password'];
    return [
        'perfumes_table_survived' => in_array('perfumes', $tables, true),
        'payload_stored_as_literal_text' => $product && $product['name'] === $payload,
        'customer_password_not_exfiltrated' => $customerPassword === 'irrelevant-secret-hash',
    ];
});

chdir($ROOT . '/admin/Includes');
include($ROOT . '/admin/Includes/code.php');
