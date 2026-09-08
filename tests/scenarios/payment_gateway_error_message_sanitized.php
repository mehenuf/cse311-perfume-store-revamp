<?php
/**
 * Regression test: when Stripe's Checkout Session API call fails (e.g. a
 * misconfigured/rotated secret key), the raw gateway error text -- which
 * Stripe's own API can partially echo back, including key material, e.g.
 * "Invalid API Key provided: sk_test_...abcd" -- must never reach
 * $_SESSION['message'], since that gets rendered verbatim into a toast on
 * the customer's next page load (includes/footer.php). Only a generic
 * message belongs there; the raw detail is for the server log only.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

// Forces demo mode off regardless of the developer machine's own
// config/env.php (PAYMENT_DEMO_MODE is a machine-local setting, not
// something this suite should be sensitive to) while still honouring
// putenv() overrides the same way the real appEnv() does -- see
// config/dbcon.php's appEnv() for the precedence this mirrors.
function appEnv($key, $default = '')
{
    if ($key === 'PAYMENT_DEMO_MODE') {
        return '0';
    }
    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
}

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    return [
        'status' => 401,
        'body'   => json_encode(['error' => ['message' => 'Invalid API Key provided: sk_test_51H2SECRETFRAGMENTabcd']]),
        'error'  => null,
    ];
}

$pdo = shim_boot(__DIR__ . '/../.tmp_payment_gateway_error_sanitized.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pdo->exec("INSERT INTO perfumes (name, qty, price, status) VALUES ('Dior Sauvage', 10, 1000, 1)");

$con = new stdClass();
session_start();
$_SESSION['guest_cart'] = [1 => 2];
$_SESSION['csrf_token'] = 'test-csrf-token';
$_SERVER['REQUEST_METHOD'] = 'POST';
putenv('STRIPE_SECRET_KEY=sk_test_dummy');
$_POST = [
    'csrf_token' => 'test-csrf-token',
    'name' => 'Guest Buyer', 'email' => 'guest@example.com', 'contact' => '+8801700000000',
    'zipcode' => '1205', 'address' => 'House 1, Road 1, Dhaka',
];

shim_report(function () {
    $message = $_SESSION['message'] ?? '';
    return [
        'generic_message_shown' => strpos($message, 'not available right now') !== false,
        'secret_fragment_not_leaked' => strpos($message, 'sk_test_51H2SECRETFRAGMENTabcd') === false,
        'raw_error_text_not_leaked' => strpos($message, 'Invalid API Key') === false,
    ];
});

chdir($ROOT . '/functions');
include($ROOT . '/functions/pay-stripe.php');
