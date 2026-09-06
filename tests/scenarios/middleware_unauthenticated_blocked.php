<?php
/**
 * No session at all. The admin middleware must exit before any admin page
 * content is produced -- it must not just redirect and keep rendering.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

shim_report(function () {
    return ['execution_stopped' => !$GLOBALS['__reached']];
});

$GLOBALS['__reached'] = false;
include($ROOT . '/middleware/adminmiddleware.php');
$GLOBALS['__reached'] = true; // must never run for this scenario
