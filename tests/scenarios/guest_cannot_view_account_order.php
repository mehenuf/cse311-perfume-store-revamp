<?php
/**
 * An order belongs to a real account. A visitor with no session must not
 * be able to look it up by tracking number -- validateTrackID() requires
 * user_id IS NULL for a guest lookup, so an account's order (user_id set)
 * can never match.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_guest_vs_account_order.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO customer (username, password, name, email, admin_check) VALUES ('arif','h','Arif','arif@example.com',0)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price) VALUES ('TRKACCOUNT1', 1, 'Arif Rahman', 'arif@example.com', 13800)");

$con = new stdClass();
$_SESSION = []; // no session at all: a guest
require $ROOT . '/functions/functions.php';

shim_report(function () {
    $result = validateTrackID('TRKACCOUNT1');
    return ['guest_cannot_see_account_order' => mysqli_num_rows($result) === 0];
});
