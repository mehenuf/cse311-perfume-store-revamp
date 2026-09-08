<?php
/**
 * SSLCommerz -- the Bangladeshi payment aggregator whose own hosted
 * checkout page already offers bKash, Rocket, Nagad, Bangla QR and local
 * bank cards as selectable options, so this one integration covers all of
 * them instead of separate bKash/Rocket merchant integrations.
 *
 * Docs: https://developer.sslcommerz.com/doc/v4/
 */

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/../../config/dbcon.php';

if (!function_exists('sslcommerzApiBase')) {
    function sslcommerzApiBase()
    {
        return appEnv('SSLCOMMERZ_SANDBOX', '1') === '0'
            ? 'https://securepay.sslcommerz.com'
            : 'https://sandbox.sslcommerz.com';
    }
}

if (!function_exists('sslcommerzCreateSession')) {
    /**
     * Creates a Session API checkout session for one pending order and
     * returns the hosted page the customer's browser should be redirected
     * to. tran_id is the order's own tracking number, so the IPN callback
     * (which carries tran_id back) can be tied to the right order without
     * any extra lookup table.
     *
     * @return array{ok:bool, gateway_page_url?:string, session_key?:string, error?:string}
     */
    function sslcommerzCreateSession(array $order)
    {
        $storeId  = appEnv('SSLCOMMERZ_STORE_ID', '');
        $storePwd = appEnv('SSLCOMMERZ_STORE_PASSWORD', '');
        if ($storeId === '' || $storePwd === '') {
            return ['ok' => false, 'error' => 'SSLCommerz is not configured.'];
        }

        $base = appBaseUrl();
        $params = [
            'store_id'      => $storeId,
            'store_passwd'  => $storePwd,
            'total_amount'  => number_format((float) $order['total_price'], 2, '.', ''),
            'currency'      => $order['currency'],
            'tran_id'       => $order['tracking_no'],
            'success_url'   => $base . '/payment-return.php?trackid=' . urlencode($order['tracking_no']),
            'fail_url'      => $base . '/payment-return.php?trackid=' . urlencode($order['tracking_no']) . '&result=fail',
            'cancel_url'    => $base . '/payment-return.php?trackid=' . urlencode($order['tracking_no']) . '&result=cancel',
            'ipn_url'       => $base . '/webhooks/sslcommerz-ipn.php',
            'cus_name'      => $order['name'],
            'cus_email'     => $order['email'],
            'cus_add1'      => 'N/A',
            'cus_city'      => 'N/A',
            'cus_postcode'  => '0000',
            'cus_country'   => 'Bangladesh',
            'cus_phone'     => 'N/A',
            'shipping_method' => 'NO',
            'num_of_item'   => 1,
            'product_name'  => 'Perfume order ' . $order['tracking_no'],
            'product_category' => 'Perfume',
            'product_profile'  => 'general',
        ];

        $response = paymentHttpRequest(
            'POST',
            sslcommerzApiBase() . '/gwprocess/v4/api.php',
            ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query($params)
        );

        if ($response['status'] !== 200) {
            return ['ok' => false, 'error' => 'SSLCommerz request failed (' . $response['status'] . ').'];
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data) || ($data['status'] ?? '') !== 'SUCCESS' || empty($data['GatewayPageURL'])) {
            return ['ok' => false, 'error' => $data['failedreason'] ?? 'SSLCommerz did not return a checkout page.'];
        }

        return [
            'ok' => true,
            'gateway_page_url' => $data['GatewayPageURL'],
            'session_key'      => $data['sessionkey'] ?? '',
        ];
    }
}

if (!function_exists('sslcommerzValidateIpn')) {
    /**
     * The IPN POST body alone is never trusted -- SSLCommerz's own
     * integration guide requires calling the Order Validation API
     * server-to-server and trusting only that response. Returns the
     * validated tran_id/amount/currency/status, or ['ok'=>false] if the
     * val_id does not check out.
     */
    function sslcommerzValidateIpn(array $postData)
    {
        $valId = $postData['val_id'] ?? '';
        if ($valId === '') {
            return ['ok' => false, 'error' => 'Missing val_id.'];
        }

        $storeId  = appEnv('SSLCOMMERZ_STORE_ID', '');
        $storePwd = appEnv('SSLCOMMERZ_STORE_PASSWORD', '');

        $query = http_build_query([
            'val_id'       => $valId,
            'store_id'     => $storeId,
            'store_passwd' => $storePwd,
            'format'       => 'json',
        ]);

        $response = paymentHttpRequest(
            'GET',
            sslcommerzApiBase() . '/validator/api/validationserverAPI.php?' . $query,
            []
        );

        if ($response['status'] !== 200) {
            return ['ok' => false, 'error' => 'Validation API request failed.'];
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'Validation API returned an unreadable response.'];
        }

        // tran_id/val_id are always taken from THIS response, never from the
        // caller's raw $_POST -- the Validation API's own record of what
        // val_id corresponds to is authoritative even when the status it
        // reports is not VALID (e.g. a genuinely failed/cancelled payment),
        // so the webhook always knows exactly which order this val_id was
        // actually for, regardless of outcome.
        $status = $data['status'] ?? '';
        if ($status !== 'VALID' && $status !== 'VALIDATED') {
            return [
                'ok'      => false,
                'error'   => 'Payment not valid (status: ' . $status . ').',
                'val_id'  => $valId,
                'tran_id' => $data['tran_id'] ?? '',
                'raw'     => $data,
            ];
        }

        return [
            'ok'       => true,
            'val_id'   => $valId,
            'tran_id'  => $data['tran_id'] ?? '',
            'amount'   => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? '',
            'raw'      => $data,
        ];
    }
}
