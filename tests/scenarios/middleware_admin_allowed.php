<?php
/**
 * Logged in as an admin. This is the control case: the guard must not
 * over-block a legitimate admin session.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

shim_report(function () {
    return ['execution_continued' => $GLOBALS['__reached']];
});

session_start();
$_SESSION['auth'] = true;
$_SESSION['admin_check'] = 1;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'mehenuf', 'email' => 'mehenuf@gmail.com'];

$GLOBALS['__reached'] = false;
include($ROOT . '/middleware/adminmiddleware.php');
$GLOBALS['__reached'] = true; // must run for this scenario
