<?php
/**
 * Stripe webhook -- called server-to-server by Stripe, never by the
 * customer's browser. The raw request body must be read before anything
 * else touches php://input, and its signature verified against
 * STRIPE_WEBHOOK_SECRET (functions/payments/stripe.php's
 * stripeVerifyWebhookSignature()) before the JSON body is trusted at all.
 */
require_once __DIR__ . '/../functions/payments/common.php';
require_once __DIR__ . '/../functions/payments/stripe.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$payload   = paymentReadRawBody();
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$secret    = appEnv('STRIPE_WEBHOOK_SECRET', '');

if ($secret === '' || !stripeVerifyWebhookSignature($payload, $sigHeader, $secret)) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

$event = json_decode($payload, true);
if (!is_array($event) || empty($event['id']) || empty($event['type'])) {
    http_response_code(400);
    echo 'Malformed event';
    exit;
}

$type    = $event['type'];
$object  = $event['data']['object'] ?? [];
$eventId = $event['id'];

$handled = in_array($type, [
    'checkout.session.completed',
    'checkout.session.async_payment_succeeded',
    'checkout.session.async_payment_failed',
], true);

if (!$handled) {
    // Any event type we don't act on is still acknowledged -- Stripe only
    // needs a 2xx to stop retrying; we simply have nothing to do for it.
    http_response_code(200);
    echo 'Ignored';
    exit;
}

$trackingNo = $object['client_reference_id'] ?? '';
$order = $trackingNo !== '' ? findOrderByTrackingNo($trackingNo) : null;

if (!$order || $order['payment_mode'] !== 'STRIPE') {
    http_response_code(200);
    echo 'Order not found';
    exit;
}

try {
    if ($type === 'checkout.session.async_payment_failed') {
        markOrderFailed($order['id'], 'STRIPE', $eventId, $type, $payload);
        http_response_code(200);
        echo 'OK';
        exit;
    }
} catch (\Throwable $e) {
    error_log('Stripe webhook processing failed for order ' . $order['id'] . ': ' . $e->getMessage());
    http_response_code(500);
    echo 'Processing error';
    exit;
}

$paymentStatus = $object['payment_status'] ?? '';
if ($paymentStatus !== 'paid') {
    // completed but not yet paid (e.g. a delayed payment method) -- wait
    // for the async_payment_succeeded/failed event that follows.
    http_response_code(200);
    echo 'Awaiting payment';
    exit;
}

$amount     = ((float) ($object['amount_total'] ?? 0)) / 100;
$currency   = strtoupper($object['currency'] ?? '');
$gatewayRef = $object['payment_intent'] ?? $object['id'];

try {
    markOrderPaid($order['id'], 'STRIPE', $eventId, $type, $gatewayRef, $amount, $currency, $payload);
} catch (\Throwable $e) {
    error_log('Stripe webhook processing failed for order ' . $order['id'] . ': ' . $e->getMessage());
    http_response_code(500);
    echo 'Processing error';
    exit;
}

http_response_code(200);
echo 'OK';
