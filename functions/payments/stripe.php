<?php
/**
 * Stripe -- cards, Google Pay and Apple Pay, all through a Stripe Checkout
 * Session (a page hosted on checkout.stripe.com). A hosted Checkout Session
 * shows whichever wallets/cards are enabled on the Stripe account
 * automatically, with no payment_method_types list to maintain here and,
 * importantly, no Apple Pay domain-verification file to host ourselves --
 * that requirement only applies to a Payment Request Button embedded on
 * our own page, not Stripe's own hosted Checkout domain.
 *
 * Docs: https://stripe.com/docs/api/checkout/sessions,
 *       https://stripe.com/docs/webhooks/signatures
 */

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/../../config/dbcon.php';

if (!function_exists('stripeApiBase')) {
    function stripeApiBase()
    {
        return 'https://api.stripe.com/v1';
    }
}

if (!function_exists('stripeCreateCheckoutSession')) {
    /**
     * @return array{ok:bool, checkout_url?:string, session_id?:string, error?:string}
     */
    function stripeCreateCheckoutSession(array $order)
    {
        $secretKey = appEnv('STRIPE_SECRET_KEY', '');
        if ($secretKey === '') {
            return ['ok' => false, 'error' => 'Stripe is not configured.'];
        }

        $base = appBaseUrl();
        // Stripe wants the smallest currency unit (e.g. poisha/cents) as an
        // integer. BDT and USD are both 2-decimal currencies; this store
        // only ever charges in those, so *100 is exact.
        $unitAmount = (int) round(((float) $order['total_price']) * 100);

        $params = [
            'mode'                => 'payment',
            'client_reference_id' => $order['tracking_no'],
            'customer_email'      => $order['email'],
            'success_url'         => $base . '/payment-return.php?trackid=' . urlencode($order['tracking_no']),
            'cancel_url'          => $base . '/payment-return.php?trackid=' . urlencode($order['tracking_no']) . '&result=cancel',
            'line_items' => [[
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => strtolower($order['currency']),
                    'unit_amount'  => $unitAmount,
                    'product_data' => ['name' => 'Perfume order ' . $order['tracking_no']],
                ],
            ]],
        ];

        $response = paymentHttpRequest(
            'POST',
            stripeApiBase() . '/checkout/sessions',
            [
                'Authorization: Bearer ' . $secretKey,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            http_build_query($params)
        );

        $data = json_decode($response['body'], true);

        if ($response['status'] !== 200 || !is_array($data) || empty($data['url'])) {
            $message = is_array($data) && isset($data['error']['message']) ? $data['error']['message'] : 'Stripe request failed.';
            return ['ok' => false, 'error' => $message];
        }

        return ['ok' => true, 'checkout_url' => $data['url'], 'session_id' => $data['id']];
    }
}

if (!function_exists('stripeVerifyWebhookSignature')) {
    /**
     * Hand-rolled per Stripe's documented Stripe-Signature scheme: the
     * header carries a timestamp and one or more v1 signatures, each an
     * HMAC-SHA256 of "{timestamp}.{raw payload}" using the webhook signing
     * secret. A timestamp outside the tolerance window is rejected even if
     * the signature matches, to block a captured request being replayed
     * later.
     */
    function stripeVerifyWebhookSignature($payload, $sigHeader, $secret, $toleranceSeconds = 300)
    {
        if (!is_string($sigHeader) || $sigHeader === '') {
            return false;
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $sigHeader) as $part) {
            $pair = explode('=', $part, 2);
            if (count($pair) !== 2) {
                continue;
            }
            if ($pair[0] === 't') {
                $timestamp = $pair[1];
            } elseif ($pair[0] === 'v1') {
                $signatures[] = $pair[1];
            }
        }

        if ($timestamp === null || !$signatures) {
            return false;
        }
        if (abs(time() - (int) $timestamp) > $toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }
        return false;
    }
}
