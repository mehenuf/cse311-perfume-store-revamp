<?php
/**
 * The one place a server-to-server HTTPS request is made to a payment
 * gateway. No SDK, no Composer -- same "hand-roll the protocol" convention
 * includes/mailer.php already uses for SMTP.
 *
 * function_exists() guarded so a test scenario can define
 * paymentHttpRequest() itself *before* including a gateway file: the real
 * definition below is then skipped and every gateway call runs against the
 * test's fake response instead of the network, exactly the way
 * tests/support/mysqli_shim.php pre-defines mysqli_prepare() and friends.
 */

if (!function_exists('paymentReadRawBody')) {
    /**
     * The raw incoming request body, for the webhooks that verify a
     * signature computed over exact bytes (Stripe, Coinbase Commerce) --
     * php://input can only be read once per request, so every webhook
     * reads it through here rather than calling file_get_contents()
     * directly. Same function_exists() test-seam as paymentHttpRequest():
     * a scenario pre-defines this to hand the webhook a canned payload,
     * since PHP's CLI SAPI has no real request body to read php://input
     * from.
     */
    function paymentReadRawBody()
    {
        return file_get_contents('php://input');
    }
}

if (!function_exists('paymentHttpRequest')) {
    /**
     * @return array{status:int, body:string, error:?string} status is 0 if
     *         the request never reached the server (DNS/TLS/timeout/etc).
     */
    function paymentHttpRequest($method, $url, array $headers = [], $body = null, $timeoutSeconds = 20)
    {
        if (!function_exists('curl_init')) {
            // Same graceful-degradation convention as includes/mailer.php
            // ("deliberately never throws") and config/dbcon.php (a
            // friendly 503 instead of a raw fatal) -- a missing curl
            // extension must surface as an ordinary gateway failure the
            // caller already knows how to handle, not an uncaught error.
            return ['status' => 0, 'body' => '', 'error' => 'The curl extension is not available on this server.'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeoutSeconds),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            // A payment API redirecting us somewhere unexpected is not a
            // shape we ever want to silently follow.
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($ch);
        $errno        = curl_errno($ch);
        $error        = $errno ? curl_error($ch) : null;
        $status       = $errno ? 0 : (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'body'   => $responseBody === false ? '' : $responseBody,
            'error'  => $error,
        ];
    }
}
