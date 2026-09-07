<?php
/**
 * Dev-only bootstrap: runs the real app against SQLite via the mysqli shim,
 * so the full site (storefront + admin) can be exercised in a browser
 * without a MySQL server. NOT part of the deployed app.
 *
 * Usage: php -S localhost:8000 -t . -d auto_prepend_file=tests/support/localserver_bootstrap.php
 */
require __DIR__ . '/mysqli_shim.php';

$dbPath = sys_get_temp_dir() . '/perfumestore_local_preview.sqlite';

if (!file_exists($dbPath) || (isset($_GET['__reseed']))) {
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo = shim_boot($dbPath, $schema);

    $seed = file_get_contents(dirname(__DIR__, 2) . '/database/_seed_body.sql');
    // Strip comments, split on ; -- good enough for this dialect-neutral seed file.
    $seed = preg_replace('/^--.*$/m', '', $seed);
    foreach (array_filter(array_map('trim', explode(';', $seed))) as $stmt) {
        $pdo->exec($stmt);
    }
} else {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $GLOBALS['__pdo'] = $pdo;
}

// config/dbcon.php calls these mysqli_* connect functions; the shim above
// stubs them out, so its require just needs to not blow up on the real
// mysqli_ssl_set() etc. We short-circuit before it even runs a real connect.
if (!function_exists('mysqli_error')) {
    function mysqli_error($con) { return ''; }
}
if (!function_exists('mysqli_real_escape_string')) {
    function mysqli_real_escape_string($con, $s) { return addslashes($s); }
}
if (!function_exists('mysqli_affected_rows')) {
    function mysqli_affected_rows($con) { return 1; }
}
