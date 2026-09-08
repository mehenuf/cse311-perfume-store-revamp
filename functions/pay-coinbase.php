<?php
/**
 * Starts a Coinbase Commerce hosted charge (crypto), reached by
 * checkout.php's payment-method chooser. See
 * functions/payments/common.php's beginGatewayCheckout() for the shared
 * bootstrap and functions/pay-stripe.php for the overall shape.
 */
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/payments/common.php';
require_once __DIR__ . '/payments/coinbase.php';

$order = beginGatewayCheckout('COINBASE', 'COINBASE_COMMERCE_CURRENCY');
if (!$order) {
    header('Location: ../checkout.php');
    exit;
}

$charge = coinbaseCreateCharge($order);
if (!$charge['ok']) {
    // The gateway's raw error text stays server-side only -- never in the
    // customer-facing message (see functions/pay-stripe.php for why).
    error_log('Coinbase charge creation failed for order ' . $order['id'] . ': ' . $charge['error']);
    markOrderFailed($order['id'], 'COINBASE', 'create-failed-' . $order['id'], 'charge_create_failed', $charge['error']);
    $_SESSION['message'] = 'Crypto payment is not available right now. Please try again or choose another payment method.';
    header('Location: ../checkout.php');
    exit;
}

attachGatewayReference($order['id'], $charge['charge_id']);
header('Location: ' . $charge['hosted_url']);
exit;
