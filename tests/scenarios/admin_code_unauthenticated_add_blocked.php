<?php
/**
 * A direct POST to admin/Includes/code.php with no session at all. This is
 * the endpoint that used to have no auth check whatsoever -- a plain
 * unauthenticated POST could add, edit or delete any product. It must now
 * be blocked before the INSERT ever runs.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_code_unauth.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));

$con = new stdClass();
$_SESSION = [];
$_POST = [
    'addperfume_btn' => '1',
    'name' => 'Intruder Eau de Parfum',
    'perfume_notes' => 'n/a', 'description' => 'n/a', 'volume' => '100 ml',
    'qty' => '999', 'price' => '1',
];
$_FILES = ['image_path' => ['name' => '', 'tmp_name' => '', 'error' => 4, 'size' => 0]];

shim_report(function () use ($pdo) {
    $n = $pdo->query("SELECT COUNT(*) n FROM perfumes")->fetch(PDO::FETCH_ASSOC)['n'];
    return ['no_product_inserted' => (int) $n === 0];
});

chdir($ROOT . '/admin/Includes');
include($ROOT . '/admin/Includes/code.php');
