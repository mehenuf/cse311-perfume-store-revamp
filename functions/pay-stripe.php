<?php
/**
 * Starts a Stripe Checkout Session for card / Google Pay / Apple Pay,
 * reached by checkout.php's payment-method chooser. See
 * functions/payments/common.php's beginGatewayCheckout() for the shared
 * bootstrap (CSRF check, field validation, pending-order creation) and
 * createPendingOrder() for why stock is not decremented here.
 */
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/payments/common.php';
require_once __DIR__ . '/payments/stripe.php';

$order = beginGatewayCheckout('STRIPE', 'STRIPE_CURRENCY');
if (!$order) {
    header('Location: ../checkout.php');
    exit;
}

if (paymentDemoModeEnabled()) {
    attachGatewayReference($order['id'], 'DEMO-' . bin2hex(random_bytes(8)));
    header('Location: ../demo-gateway.php?trackid=' . urlencode($order['tracking_no']));
    exit;
}

$session = stripeCreateCheckoutSession($order);
if (!$session['ok']) {
    // The gateway's raw error text (which can echo back a fragment of a
    // misconfigured API key, e.g. "Invalid API Key provided: sk_test_...")
    // stays server-side only -- never in the customer-facing message.
    error_log('Stripe checkout session creation failed for order ' . $order['id'] . ': ' . $session['error']);
    markOrderFailed($order['id'], 'STRIPE', 'create-failed-' . $order['id'], 'session_create_failed', $session['error']);
    $_SESSION['message'] = 'Card payment is not available right now. Please try again or choose another payment method.';
    header('Location: ../checkout.php');
    exit;
}

attachGatewayReference($order['id'], $session['session_id']);
header('Location: ' . $session['checkout_url']);
exit;
