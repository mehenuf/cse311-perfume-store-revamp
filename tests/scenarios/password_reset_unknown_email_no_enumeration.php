<?php
/**
 * Requesting a reset for an email that is not registered must show the
 * exact same message as a real one -- otherwise the form becomes a way
 * to check which emails have accounts.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_reset_unknown.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));

$con = new stdClass();
$_SESSION = [];
$_POST = ['request_reset_btn' => '1', 'email' => 'nobody@example.com'];
$_SERVER['HTTP_HOST'] = 'example.test';
$_SERVER['SCRIPT_NAME'] = '/functions/forgotpassword.php';

shim_report(function () {
    return [
        'same_generic_message_shown' => isset($_SESSION['message']) && str_contains($_SESSION['message'], 'If an account exists'),
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/forgotpassword.php');
