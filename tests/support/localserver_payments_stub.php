<?php
/**
 * Dev-only bootstrap for previewing the payment-method chooser and its
 * redirect flow with no real gateway credentials and no outbound network
 * call -- stacks on top of localserver_bootstrap.php's SQLite shim by also
 * pre-defining paymentHttpRequest() (functions/payments/http.php's
 * function_exists() test seam), so "Continue to payment" actually
 * redirects somewhere instead of either calling a real API or fatally
 * erroring where curl is not installed.
 *
 * Every fake "hosted page" it returns is just this app's own
 * payment-return.php, so a manual click-through can see the pending ->
 * (nothing confirms it, since there is no real webhook call here) state
 * for real, without a live sandbox account. NOT part of the deployed app.
 *
 * Usage: php -d auto_prepend_file=tests/support/localserver_payments_stub.php -S localhost:8000
 */
require __DIR__ . '/localserver_bootstrap.php';

function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
{
    if (strpos($url, 'stripe.com') !== false) {
        parse_str(is_string($body) ? $body : '', $params);
        $tracking = $params['client_reference_id'] ?? 'UNKNOWN';
        return ['status' => 200, 'error' => null, 'body' => json_encode([
            'id'  => 'cs_test_local_preview',
            'url' => '/payment-return.php?trackid=' . urlencode($tracking) . '&simulated=stripe',
        ])];
    }

    if (strpos($url, 'sslcommerz.com') !== false) {
        parse_str(is_string($body) ? $body : '', $params);
        $tracking = $params['tran_id'] ?? 'UNKNOWN';
        return ['status' => 200, 'error' => null, 'body' => json_encode([
            'status' => 'SUCCESS',
            'sessionkey' => 'sess_local_preview',
            'GatewayPageURL' => '/payment-return.php?trackid=' . urlencode($tracking) . '&simulated=sslcommerz',
        ])];
    }

    if (strpos($url, 'commerce.coinbase.com') !== false) {
        $decoded = json_decode(is_string($body) ? $body : '{}', true);
        $tracking = $decoded['metadata']['tracking_no'] ?? 'UNKNOWN';
        return ['status' => 201, 'error' => null, 'body' => json_encode(['data' => [
            'id' => 'charge_local_preview',
            'hosted_url' => '/payment-return.php?trackid=' . urlencode($tracking) . '&simulated=coinbase',
        ]])];
    }

    return ['status' => 0, 'error' => 'No local preview stub for ' . $url, 'body' => ''];
}
