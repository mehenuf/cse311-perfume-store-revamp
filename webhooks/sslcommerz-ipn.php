<?php
/**
 * SSLCommerz IPN (Instant Payment Notification) -- called server-to-server
 * by SSLCommerz, never by the customer's browser. The POST body itself is
 * never trusted: every notification is re-checked against SSLCommerz's own
 * Order Validation API (sslcommerzValidateIpn()) before anything in our
 * database changes, per SSLCommerz's own integration guidance.
 *
 * Always responds 200 once the notification has been durably recorded
 * (even for a legitimately failed payment) so SSLCommerz stops retrying;
 * only a request we could not verify at all gets a non-200, so it *is*
 * retried later.
 */
require_once __DIR__ . '/../functions/payments/common.php';
require_once __DIR__ . '/../functions/payments/sslcommerz.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$validation = sslcommerzValidateIpn($_POST);

if (!$validation['ok'] && !isset($validation['raw'])) {
    // Could not even reach/parse the Validation API -- ask SSLCommerz to retry.
    http_response_code(502);
    echo 'Could not verify payment';
    exit;
}

// tran_id/val_id always come from sslcommerzValidateIpn()'s own return value
// (sourced from the Validation API's response about this specific val_id),
// never from the raw $_POST body -- that body is attacker-reachable and
// proves nothing on its own.
$trackingNo = $validation['tran_id'];
$order = $trackingNo !== '' ? findOrderByTrackingNo($trackingNo) : null;

if (!$order || $order['payment_mode'] !== 'SSLCOMMERZ') {
    // Durably nothing-to-do: acknowledge so this specific bad tran_id is not retried forever.
    http_response_code(200);
    echo 'Order not found';
    exit;
}

$eventId = $validation['val_id'];
$payload = json_encode($_POST);

try {
    if ($validation['ok']) {
        markOrderPaid($order['id'], 'SSLCOMMERZ', $eventId, 'ipn_valid', $validation['val_id'],
            $validation['amount'], $validation['currency'], $payload);
    } else {
        markOrderFailed($order['id'], 'SSLCOMMERZ', $eventId, 'ipn_' . strtolower($validation['raw']['status'] ?? 'failed'), $payload);
    }
} catch (\Throwable $e) {
    // An unexpected DB failure while recording this event -- never silently
    // acknowledge it as handled. A 5xx here makes SSLCommerz retry the IPN
    // later instead of the payment confirmation being lost for good.
    error_log('SSLCommerz IPN processing failed for order ' . $order['id'] . ': ' . $e->getMessage());
    http_response_code(500);
    echo 'Processing error';
    exit;
}

http_response_code(200);
echo 'OK';
