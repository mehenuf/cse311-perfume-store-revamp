<?php
/**
 * Coinbase Commerce webhook -- called server-to-server by Coinbase, never
 * by the customer's browser. Signature verified against the raw body
 * before the JSON is trusted at all (functions/payments/coinbase.php's
 * coinbaseVerifyWebhookSignature()).
 *
 * Only charge:confirmed / charge:failed are acted on. charge:created,
 * charge:pending, charge:delayed and charge:resolved are acknowledged
 * (200, so Coinbase does not retry) but not acted on -- those cover edge
 * cases like an under/overpaid charge that Coinbase support resolves
 * manually, which is intentionally out of scope here; check the Coinbase
 * Commerce dashboard directly for those.
 */
require_once __DIR__ . '/../functions/payments/common.php';
require_once __DIR__ . '/../functions/payments/coinbase.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$payload   = paymentReadRawBody();
$sigHeader = $_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'] ?? '';
$secret    = appEnv('COINBASE_COMMERCE_WEBHOOK_SECRET', '');

if ($secret === '' || !coinbaseVerifyWebhookSignature($payload, $sigHeader, $secret)) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

$body = json_decode($payload, true);
$event = $body['event'] ?? null;
if (!is_array($event) || empty($event['id']) || empty($event['type'])) {
    http_response_code(400);
    echo 'Malformed event';
    exit;
}

$type    = $event['type'];
$data    = $event['data'] ?? [];
$eventId = $event['id'];

if (!in_array($type, ['charge:confirmed', 'charge:failed'], true)) {
    http_response_code(200);
    echo 'Ignored';
    exit;
}

$trackingNo = $data['metadata']['tracking_no'] ?? '';
$order = $trackingNo !== '' ? findOrderByTrackingNo($trackingNo) : null;

if (!$order || $order['payment_mode'] !== 'COINBASE') {
    http_response_code(200);
    echo 'Order not found';
    exit;
}

try {
    if ($type === 'charge:failed') {
        markOrderFailed($order['id'], 'COINBASE', $eventId, $type, $payload);
        http_response_code(200);
        echo 'OK';
        exit;
    }
} catch (\Throwable $e) {
    error_log('Coinbase webhook processing failed for order ' . $order['id'] . ': ' . $e->getMessage());
    http_response_code(500);
    echo 'Processing error';
    exit;
}

// data.pricing.local is just an echo of the price THIS APP itself
// requested when the charge was created (functions/payments/coinbase.php's
// coinbaseCreateCharge()) -- comparing against it would be tautological,
// always "matching" regardless of what was actually paid. data.payments is
// Coinbase's own record of the blockchain transaction(s) it detected for
// this charge, which is the genuine, gateway-attested amount to check
// against the order's total_price.
$payments = $data['payments'] ?? [];
if ($payments) {
    $amount = 0.0;
    $currency = '';
    foreach ($payments as $payment) {
        $amount += (float) ($payment['value']['local']['amount'] ?? 0);
        $currency = strtoupper($payment['value']['local']['currency'] ?? $currency);
    }
} else {
    // Should not happen for a charge:confirmed event, but fail safe rather
    // than trust the request-time price if it ever does.
    $amount = 0.0;
    $currency = '';
}
$gatewayRef = $data['id'] ?? $trackingNo;

try {
    markOrderPaid($order['id'], 'COINBASE', $eventId, $type, $gatewayRef, $amount, $currency, $payload);
} catch (\Throwable $e) {
    error_log('Coinbase webhook processing failed for order ' . $order['id'] . ': ' . $e->getMessage());
    http_response_code(500);
    echo 'Processing error';
    exit;
}

http_response_code(200);
echo 'OK';
