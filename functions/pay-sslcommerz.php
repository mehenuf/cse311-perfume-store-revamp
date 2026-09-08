<?php
/**
 * Starts an SSLCommerz hosted-checkout session (bKash, Rocket, Nagad,
 * Bangla QR, local cards), reached by checkout.php's payment-method
 * chooser. See functions/payments/common.php's beginGatewayCheckout() for
 * the shared bootstrap and functions/pay-stripe.php for the overall shape.
 */
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/payments/common.php';
require_once __DIR__ . '/payments/sslcommerz.php';

$order = beginGatewayCheckout('SSLCOMMERZ', 'SSLCOMMERZ_CURRENCY');
if (!$order) {
    header('Location: ../checkout.php');
    exit;
}

$session = sslcommerzCreateSession($order);
if (!$session['ok']) {
    // The gateway's raw error text stays server-side only -- never in the
    // customer-facing message (see functions/pay-stripe.php for why).
    error_log('SSLCommerz session creation failed for order ' . $order['id'] . ': ' . $session['error']);
    markOrderFailed($order['id'], 'SSLCOMMERZ', 'create-failed-' . $order['id'], 'session_create_failed', $session['error']);
    $_SESSION['message'] = 'bKash/Rocket/card payment is not available right now. Please try again or choose another payment method.';
    header('Location: ../checkout.php');
    exit;
}

attachGatewayReference($order['id'], $session['session_key']);
header('Location: ' . $session['gateway_page_url']);
exit;
