<?php
/**
 * A logged-in admin adds a product whose name and description contain an
 * apostrophe. The old string-concatenated INSERT would raise a SQL syntax
 * error on input like this; the prepared statement must not.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_code_quote.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['admin_check'] = 1;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'mehenuf', 'email' => 'mehenuf@gmail.com'];

$_POST = [
    'addperfume_btn' => '1',
    'name' => "Kilian L'Homme a la Rose",
    'perfume_notes' => 'Rose, Oud', 'description' => "It's smooth.", 'volume' => '50 ml',
    'qty' => '12', 'price' => '15600',
    'status' => 'on',
];
$_FILES = ['image_path' => ['name' => '', 'tmp_name' => '', 'error' => 4, 'size' => 0]];

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT * FROM perfumes")->fetch(PDO::FETCH_ASSOC);
    return [
        'row_created'  => $row !== false,
        'name_intact'  => $row && $row['name'] === "Kilian L'Homme a la Rose",
        'desc_intact'  => $row && $row['description'] === "It's smooth.",
        'qty_correct'  => $row && (int) $row['qty'] === 12,
        'price_correct'=> $row && (float) $row['price'] === 15600.0,
    ];
});

chdir($ROOT . '/admin/Includes');
include($ROOT . '/admin/Includes/code.php');
