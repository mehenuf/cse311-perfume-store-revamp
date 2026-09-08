<?php
/**
 * Regression test for a real vulnerability found in code review: the IPN
 * handler must act on the tran_id the Validation API itself reports for
 * the given val_id, never on whatever tran_id happened to be in the raw
 * $_POST body. Without this, an attacker could pair a val_id belonging to
 * their own (failed) transaction with an arbitrary victim tran_id in
 * $_POST and flip that victim's unrelated, currently-pending order to
 * 'failed'.
 *
 * Here $_POST claims tran_id=TRKVICTIM (a real pending order), but the
 * mocked Validation API says that val_id actually belongs to
 * TRKATTACKER's own order -- the webhook must act on TRKATTACKER's order
 * (or not find one at all), and must leave TRKVICTIM completely untouched.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    return [
        'status' => 200,
        // The Validation API's own record of val_id=val_attacker: it
        // belongs to the attacker's tran_id, not the victim's, and its
        // status is not VALID (e.g. the attacker's own cancelled payment).
        'body'   => json_encode(['status' => 'FAILED', 'tran_id' => 'TRKATTACKER', 'val_id' => 'val_attacker']),
        'error'  => null,
    ];
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_sslcommerz_forged_tranid.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");
$pdo->exec("INSERT INTO orders (tracking_no, user_id, name, email, total_price, payment_mode, payment_status, payment_id, currency)
            VALUES ('TRKVICTIM', NULL, 'Victim Buyer', 'victim@example.com', 2000, 'SSLCOMMERZ', 'pending', 'sess_victim', 'BDT')");
$pdo->exec("INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (1, 1, 2, 1000)");

$con = new stdClass();
$_SERVER['REQUEST_METHOD'] = 'POST';
// The attacker-controlled request body: a real victim tran_id, paired with
// a val_id that is not actually theirs.
$_POST = ['val_id' => 'val_attacker', 'tran_id' => 'TRKVICTIM'];

shim_report(function () use ($pdo) {
    $victim = $pdo->query("SELECT * FROM orders WHERE tracking_no = 'TRKVICTIM'")->fetch(PDO::FETCH_ASSOC);
    return [
        'victim_order_untouched' => $victim && $victim['payment_status'] === 'pending',
    ];
});

include($ROOT . '/webhooks/sslcommerz-ipn.php');
