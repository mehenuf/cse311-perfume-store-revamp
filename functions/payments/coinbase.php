<?php
/**
 * Coinbase Commerce -- crypto checkout via a hosted charge page.
 *
 * Docs: https://commerce.coinbase.com/docs/api/#charges,
 *       https://commerce.coinbase.com/docs/api/#webhooks
 */

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/../../config/dbcon.php';

if (!function_exists('coinbaseApiBase')) {
    function coinbaseApiBase()
    {
        return 'https://api.commerce.coinbase.com';
    }
}

if (!function_exists('coinbaseCreateCharge')) {
    /**
     * @return array{ok:bool, hosted_url?:string, charge_id?:string, error?:string}
     */
    function coinbaseCreateCharge(array $order)
    {
        $apiKey = appEnv('COINBASE_COMMERCE_API_KEY', '');
        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'Coinbase Commerce is not configured.'];
        }

        $base = appBaseUrl();
        $payload = json_encode([
            'name'         => 'Perfume order ' . $order['tracking_no'],
            'description'  => 'Order ' . $order['tracking_no'],
            'pricing_type' => 'fixed_price',
            'local_price'  => [
                'amount'   => number_format((float) $order['total_price'], 2, '.', ''),
                'currency' => $order['currency'],
            ],
            'metadata'     => ['tracking_no' => $order['tracking_no']],
            'redirect_url' => $base . '/payment-return.php?trackid=' . urlencode($order['tracking_no']),
            'cancel_url'   => $base . '/payment-return.php?trackid=' . urlencode($order['tracking_no']) . '&result=cancel',
        ]);

        $response = paymentHttpRequest(
            'POST',
            coinbaseApiBase() . '/charges',
            [
                'X-CC-Api-Key: ' . $apiKey,
                'X-CC-Version: 2018-03-22',
                'Content-Type: application/json',
            ],
            $payload
        );

        $data = json_decode($response['body'], true);

        if ($response['status'] !== 201 || !is_array($data) || empty($data['data']['hosted_url'])) {
            $message = is_array($data) && isset($data['error']['message']) ? $data['error']['message'] : 'Coinbase Commerce request failed.';
            return ['ok' => false, 'error' => $message];
        }

        return [
            'ok'         => true,
            'hosted_url' => $data['data']['hosted_url'],
            'charge_id'  => $data['data']['id'],
        ];
    }
}

if (!function_exists('coinbaseVerifyWebhookSignature')) {
    /** HMAC-SHA256 of the *raw* request body against X-CC-Webhook-Signature, timing-safe. */
    function coinbaseVerifyWebhookSignature($rawBody, $sigHeader, $secret)
    {
        if (!is_string($sigHeader) || $sigHeader === '') {
            return false;
        }
        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $sigHeader);
    }
}
