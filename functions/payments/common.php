<?php
/**
 * Shared plumbing for every online payment gateway (SSLCommerz, Stripe,
 * Coinbase Commerce): creating the pending order all three redirect away
 * from, and confirming/failing it when the gateway calls back. Cash on
 * Delivery does not use any of this -- it stays exactly as
 * functions/placeorder.php already had it.
 */

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/mailer.php';

if (!function_exists('appBaseUrl')) {
    /**
     * Absolute base URL (no trailing slash) used to build the success/
     * cancel/webhook URLs handed to a gateway -- those are followed by the
     * customer's browser or the gateway's own servers, so a relative path
     * will not do. APP_BASE_URL (config/env.php) wins when set; otherwise
     * this is derived from the current request, which is right for local
     * testing but should be set explicitly in production.
     */
    function appBaseUrl()
    {
        $configured = rtrim(appEnv('APP_BASE_URL', ''), '/');
        if ($configured !== '') {
            return $configured;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}

if (!function_exists('paymentDemoModeEnabled')) {
    /**
     * PAYMENT_DEMO_MODE lets checkout.php's Stripe/SSLCommerz/Coinbase
     * options be demoed end to end with no real merchant credentials and
     * no outbound HTTP call -- each functions/pay-*.php entry point skips
     * the real gateway API call and sends the browser to demo-gateway.php
     * instead, which drives the same markOrderPaid()/markOrderFailed()
     * path a real webhook would. See config/env.example.php.
     */
    function paymentDemoModeEnabled()
    {
        return appEnv('PAYMENT_DEMO_MODE', '0') === '1';
    }
}

if (!function_exists('validateCheckoutFields')) {
    /** Same required-fields contract functions/placeorder.php uses. */
    function validateCheckoutFields(array $fields)
    {
        foreach (['name', 'email', 'contact', 'zipcode', 'address'] as $key) {
            if (!isset($fields[$key]) || trim((string) $fields[$key]) === '') {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('createPendingOrder')) {
    /**
     * Inserts the order + its line items for an online-gateway checkout.
     * Unlike functions/placeorder.php's Cash on Delivery insert, this does
     * NOT decrement stock yet -- a customer who starts a card/bKash/crypto
     * payment and never finishes it must not have reserved inventory that
     * was never paid for. Stock moves only in markOrderPaid(), once the
     * gateway's webhook actually confirms the money arrived.
     *
     * @return array|null The new order row (with 'id'), or null if a
     *                     $fields entry was missing.
     */
    function createPendingOrder(array $fields, array $cartItems, $gateway, $currency)
    {
        global $con;

        if (!validateCheckoutFields($fields) || !$cartItems) {
            return null;
        }

        $totalcost = 0;
        foreach ($cartItems as $item) {
            $totalcost += $item['price'] * $item['perfume_quantity'];
        }

        $isAuthed = isset($_SESSION['auth']);
        $userId   = $isAuthed ? (int) $_SESSION['auth_user']['user_id'] : null;
        $prefix   = $isAuthed ? $_SESSION['auth_user']['username'] : $fields['name'];

        $trackingNo = 'TRK' . random_int(1000000, 999999999999) . substr($prefix, 0, 4);

        $stmt = mysqli_prepare($con,
            "INSERT INTO orders
                (tracking_no, user_id, name, email, contacts, address, zipcode,
                 total_price, payment_mode, payment_status, currency)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        mysqli_stmt_bind_param($stmt, 'sisssssdss',
            $trackingNo, $userId, $fields['name'], $fields['email'], $fields['contact'],
            $fields['address'], $fields['zipcode'], $totalcost, $gateway, $currency);

        try {
            if (!mysqli_stmt_execute($stmt)) {
                return null;
            }
        } catch (\Throwable $e) {
            // The only constraint this INSERT can violate is
            // uq_orders_tracking -- astronomically unlikely given
            // tracking_no's randomness, but mysqli's default error mode
            // throws rather than returning false, so this must be caught
            // the same way logPaymentEvent() catches its own equivalent
            // case, or the customer would see a raw fatal error instead of
            // the friendly "we could not start your order" redirect every
            // caller already expects from a null return.
            return null;
        }

        $orderId = mysqli_insert_id($con);

        $insertItem = mysqli_prepare($con,
            "INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $perfumeId = (int) $item['perfume_id'];
            $qty       = (int) $item['perfume_quantity'];
            $price     = (float) $item['price'];
            mysqli_stmt_bind_param($insertItem, 'iiid', $orderId, $perfumeId, $qty, $price);
            mysqli_stmt_execute($insertItem);
        }

        return [
            'id'          => $orderId,
            'tracking_no' => $trackingNo,
            'total_price' => $totalcost,
            'currency'    => $currency,
            'email'       => $fields['email'],
            'name'        => $fields['name'],
            'user_id'     => $userId,
            'is_guest'    => !$isAuthed,
        ];
    }
}

if (!function_exists('beginGatewayCheckout')) {
    /**
     * The bootstrap shared by every functions/pay-*.php entry point: method
     * + CSRF check, pulling the delivery fields out of $_POST, and creating
     * the pending order. Returns the new order array on success; on any
     * failure it sets $_SESSION['message'] where a customer-facing reason
     * makes sense (never for the CSRF/method check itself, matching every
     * other silent-redirect guard in this app) and returns null -- the
     * caller's only job from there is `if (!$order) { redirect; exit; }`.
     */
    function beginGatewayCheckout($gateway, $currencyEnvKey)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
            return null;
        }

        $fields = [
            'name'    => $_POST['name'] ?? '',
            'email'   => $_POST['email'] ?? '',
            'contact' => $_POST['contact'] ?? '',
            'zipcode' => $_POST['zipcode'] ?? '',
            'address' => $_POST['address'] ?? '',
        ];

        $cartItems = displayCart();
        if (!validateCheckoutFields($fields) || !$cartItems) {
            $_SESSION['message'] = 'Please fill in every delivery field before checking out.';
            return null;
        }

        $currency = appEnv($currencyEnvKey, 'BDT');
        $order = createPendingOrder($fields, $cartItems, $gateway, $currency);
        if (!$order) {
            $_SESSION['message'] = 'We could not start your order. Please try again.';
            return null;
        }

        return $order;
    }
}

if (!function_exists('attachGatewayReference')) {
    /** Records the gateway's own session/charge id once it has been created. */
    function attachGatewayReference($orderId, $gatewayPaymentId)
    {
        global $con;
        $stmt = mysqli_prepare($con, "UPDATE orders SET payment_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $gatewayPaymentId, $orderId);
        mysqli_stmt_execute($stmt);
    }
}

if (!function_exists('findOrderByTrackingNo')) {
    /**
     * Unscoped lookup for webhook use only -- unlike functions/functions.php's
     * validateTrackID() (which restricts to the current session's own
     * orders for a customer-facing page), a webhook has already proven its
     * own authenticity via signature/validation-API and is allowed to
     * resolve any order by tracking number, which every gateway hands back
     * to us as tran_id / client_reference_id / metadata.tracking_no.
     */
    function findOrderByTrackingNo($trackingNo)
    {
        global $con;
        $stmt = mysqli_prepare($con, "SELECT * FROM orders WHERE tracking_no = ?");
        mysqli_stmt_bind_param($stmt, 's', $trackingNo);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }
}

if (!function_exists('isEventProcessed')) {
    function isEventProcessed($gateway, $eventId)
    {
        global $con;
        $stmt = mysqli_prepare($con, "SELECT id FROM payment_events WHERE gateway = ? AND event_id = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $gateway, $eventId);
        mysqli_stmt_execute($stmt);
        return mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
    }
}

if (!function_exists('logPaymentEvent')) {
    /**
     * Records a webhook event as the idempotency gate: the unique
     * constraint on (gateway, event_id) is what makes a provider's retried
     * webhook delivery a no-op instead of a second stock decrement or a
     * second confirmation email. The isEventProcessed() check handles the
     * common case cheaply; the try/catch is what actually makes this safe
     * under a genuine race between two concurrent deliveries of the same
     * event, since mysqli's default error mode (PHP 8.1+) throws on a
     * constraint violation rather than returning false, and the SQLite
     * test shim throws PDOException for the same reason.
     *
     * A failed insert is re-checked against isEventProcessed() before being
     * treated as "already handled": if the event still isn't there, the
     * insert failed for some OTHER reason (a lock timeout, a dropped
     * connection, ...), and that must never be silently swallowed as
     * success -- the exception is re-thrown so the caller (and ultimately
     * the webhook script) can fail loudly instead of quietly discarding a
     * genuine payment confirmation.
     *
     * @return bool true if this is the first time this exact event was
     *              recorded; false if it was already logged (a retry).
     * @throws \Throwable on a real, non-duplicate database failure.
     */
    function logPaymentEvent($orderId, $gateway, $eventId, $eventType, $payload)
    {
        global $con;

        if (isEventProcessed($gateway, $eventId)) {
            return false;
        }

        try {
            $stmt = mysqli_prepare($con,
                "INSERT INTO payment_events (order_id, gateway, event_id, event_type, payload)
                 VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'issss', $orderId, $gateway, $eventId, $eventType, $payload);
            return (bool) mysqli_stmt_execute($stmt);
        } catch (\Throwable $e) {
            if (isEventProcessed($gateway, $eventId)) {
                return false; // lost a race against a concurrent delivery of this same event
            }
            throw $e;
        }
    }
}

if (!function_exists('markPaymentEventProcessed')) {
    function markPaymentEventProcessed($gateway, $eventId)
    {
        global $con;
        $stmt = mysqli_prepare($con,
            "UPDATE payment_events SET processed_at = CURRENT_TIMESTAMP WHERE gateway = ? AND event_id = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $gateway, $eventId);
        mysqli_stmt_execute($stmt);
    }
}

if (!function_exists('decrementStockForOrder')) {
    /**
     * Decrements perfumes.qty for every line in the order, computed
     * server-side in one atomic `qty = qty - ?` UPDATE per line rather than
     * a PHP-side read-then-write -- two orders for the same perfume
     * confirmed paid at nearly the same moment are then serialized by the
     * database's own row-level locking instead of racing to overwrite each
     * other's read. `AND qty >= ?` stops the column (an unsigned int) from
     * ever being driven negative: if stock already ran out from elsewhere
     * in the pending-but-unreserved window between checkout and payment
     * confirmation, that line's stock is left as-is rather than forced
     * negative or clamped -- the order stays paid regardless (there is no
     * refund flow to unwind it -- see docs/payments.md), it just cannot
     * reflect a sale of stock that no longer existed.
     */
    function decrementStockForOrder($orderId)
    {
        global $con;
        $items = mysqli_query($con,
            "SELECT perfume_id, perfume_qty FROM order_item WHERE order_id = " . (int) $orderId);
        if (!$items) {
            return;
        }

        $updateStmt = mysqli_prepare($con, "UPDATE perfumes SET qty = qty - ? WHERE id = ? AND qty >= ?");

        foreach ($items as $item) {
            $perfumeId  = (int) $item['perfume_id'];
            $orderedQty = (int) $item['perfume_qty'];

            mysqli_stmt_bind_param($updateStmt, 'iii', $orderedQty, $perfumeId, $orderedQty);
            mysqli_stmt_execute($updateStmt);
        }
    }
}

if (!function_exists('sendOrderPaidEmail')) {
    function sendOrderPaidEmail($order)
    {
        $subject = 'Payment received -- order ' . $order['tracking_no'];
        $body    = "Hi " . $order['name'] . ",\n\n"
            . "We have received your payment for order " . $order['tracking_no'] . " ("
            . taka($order['total_price']) . "). It is now being processed.\n\n"
            . "Thank you for shopping with us.";
        sendAppEmail($order['email'], $subject, $body);
    }
}

if (!function_exists('clearCartForOrder')) {
    /**
     * Removes only the perfumes that were actually part of $orderId from an
     * account's cart -- not the whole cart -- so an item added after this
     * order was placed (e.g. while its gateway payment was still pending)
     * survives. Guest carts are session-only and cannot be reached from
     * here (a webhook has no browser session); see payment-return.php for
     * the guest-side equivalent.
     */
    function clearCartForOrder($userId, $orderId)
    {
        global $con;
        $stmt = mysqli_prepare($con,
            "DELETE FROM cart WHERE user_id = ?
             AND perfume_id IN (SELECT perfume_id FROM order_item WHERE order_id = ?)");
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $orderId);
        mysqli_stmt_execute($stmt);
    }
}

if (!function_exists('markOrderPaid')) {
    /**
     * The single place any gateway's webhook confirms money actually
     * arrived. Idempotent three ways over: logPaymentEvent()'s unique
     * constraint stops the exact same event being applied twice; the
     * payment_status pre-check below is a cheap short-circuit for the
     * common case; and the final UPDATE's own `AND payment_status <>
     * 'paid'` clause plus its affected-rows check is what actually closes
     * the race for two *different* events landing for the same order at
     * nearly the same moment (e.g. one webhook request marking it paid,
     * another marking it failed, both having read the row before either
     * wrote) -- whichever UPDATE's WHERE clause no longer matches simply
     * skips the rest of its own side effects instead of trusting a stale
     * PHP-side snapshot.
     *
     * $amount/$currency are the gateway's own authoritative figures for
     * this payment -- always compared against the order's own
     * total_price/currency before anything is marked paid. A mismatch is
     * logged (so a retry does not loop forever) but never silently
     * accepted; the order stays 'pending' for manual review.
     */
    function markOrderPaid($orderId, $gateway, $eventId, $eventType, $gatewayRef, $amount, $currency, $rawPayload)
    {
        global $con;

        $order = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM orders WHERE id = " . (int) $orderId));
        if (!$order) {
            return false;
        }

        $isNewEvent = logPaymentEvent($orderId, $gateway, $eventId, $eventType, $rawPayload);

        if ($order['payment_status'] === 'paid') {
            // Already confirmed by an earlier event for this same order.
            return true;
        }
        if (!$isNewEvent) {
            // This exact event was already processed once; nothing left to do.
            return true;
        }

        $expected = round((float) $order['total_price'], 2);
        $received = round((float) $amount, 2);
        $currencyOk = strcasecmp((string) $currency, (string) $order['currency']) === 0;

        if (abs($expected - $received) > 0.01 || !$currencyOk) {
            markPaymentEventProcessed($gateway, $eventId);
            return false;
        }

        $stmt = mysqli_prepare($con,
            "UPDATE orders SET payment_status = 'paid', gateway_ref = ?, paid_at = CURRENT_TIMESTAMP
             WHERE id = ? AND payment_status <> 'paid'");
        mysqli_stmt_bind_param($stmt, 'si', $gatewayRef, $orderId);
        mysqli_stmt_execute($stmt);
        markPaymentEventProcessed($gateway, $eventId);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            // Lost the race: another request already changed this order's
            // status between our SELECT and this UPDATE. Whichever request
            // actually won already ran (or will run) its own side effects.
            return true;
        }

        decrementStockForOrder($orderId);

        // An account's cart can be cleared straight from here (no session
        // needed, unlike a guest's session-backed cart) -- same as
        // functions/placeorder.php already does for a Cash on Delivery order.
        if ($order['user_id'] !== null) {
            clearCartForOrder($order['user_id'], $orderId);
        }

        sendOrderPaidEmail($order);

        return true;
    }
}

if (!function_exists('markOrderFailed')) {
    function markOrderFailed($orderId, $gateway, $eventId, $eventType, $rawPayload)
    {
        global $con;

        $order = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM orders WHERE id = " . (int) $orderId));
        if (!$order) {
            return false;
        }

        $isNewEvent = logPaymentEvent($orderId, $gateway, $eventId, $eventType, $rawPayload);
        if (!$isNewEvent || $order['payment_status'] === 'paid') {
            // Never downgrade an order that is already confirmed paid.
            markPaymentEventProcessed($gateway, $eventId);
            return true;
        }

        // Only a still-'pending' order can transition to 'failed' -- the
        // WHERE clause (not the earlier stale-snapshot check above) is what
        // actually closes the race against a concurrent markOrderPaid() for
        // the same order, the same way markOrderPaid()'s own conditional
        // UPDATE does.
        $stmt = mysqli_prepare($con,
            "UPDATE orders SET payment_status = 'failed' WHERE id = ? AND payment_status = 'pending'");
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);

        markPaymentEventProcessed($gateway, $eventId);
        return true;
    }
}
