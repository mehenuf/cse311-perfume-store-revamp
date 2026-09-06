<?php
/**
 * The reverse of the guest isolation case: a guest order (user_id IS NULL)
 * must not be visible to a logged-in account, even one that knows the
 * exact tracking number -- validateTrackID() requires user_id = the
 * session's own id when authenticated, which a NULL row can never match.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_account_vs_guest_order.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif','h','Arif','arif@example.com',0)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price) VALUES ('TRKGUEST001', NULL, 'Guest Buyer', 'guest@example.com', 13800)");

$con = new stdClass();
session_start();
$_SESSION['auth'] = true;
$_SESSION['auth_user'] = ['user_id' => 1, 'username' => 'arif', 'email' => 'arif@example.com'];
require $ROOT . '/functions/functions.php';

shim_report(function () {
    $result = validateTrackID('TRKGUEST001');
    return ['account_cannot_see_guest_order' => mysqli_num_rows($result) === 0];
});
