<?php
/**
 * Logged in, but not an admin. Must be blocked the same as an anonymous
 * visitor -- admin_check is the gate, not merely having a session.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

shim_report(function () {
    return ['execution_stopped' => !$GLOBALS['__reached']];
});

session_start();
$_SESSION['auth'] = true;
$_SESSION['admin_check'] = 0;
$_SESSION['auth_user'] = ['user_id' => 3, 'username' => 'arif', 'email' => 'arif@example.com'];

$GLOBALS['__reached'] = false;
include($ROOT . '/middleware/adminmiddleware.php');
$GLOBALS['__reached'] = true; // must never run for this scenario
