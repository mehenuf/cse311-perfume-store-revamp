<?php
/**
 * Landing page for all three online gateways once they redirect the
 * browser back. The only thing ever trusted here is the order's own
 * payment_status in the database -- set exclusively by the matching
 * webhook in webhooks/ -- never the ?result= query string a gateway
 * appends, which anyone could type into the address bar by hand.
 */
session_start();
include('functions/functions.php');

$isAuthed = isset($_SESSION['auth']);
$order    = null;

if (isset($_GET['trackid'])) {
    // Same guest/account-scoped lookup order-details.php uses -- a guest
    // can only ever land on their own guest order, an account only its own.
    $validation = validateTrackID($_GET['trackid']);
    if ($validation && mysqli_num_rows($validation) > 0) {
        $order = mysqli_fetch_assoc($validation);
    }
}

$pageTitle       = $order ? 'Order ' . $order['tracking_no'] : 'Order not found';
$pageDescription = 'Payment status for your order.';
include('includes/header.php');

if (!$order) {
    ?>
    <section class="section shell">
        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
            <h1 style="font-size:var(--t-h2)">We could not find that order</h1>
            <p>That tracking number does not match any order<?= $isAuthed ? ' on your account' : '' ?>.</p>
            <a class="btn btn--primary" href="perfumes.php">Back to the collection</a>
        </div>
    </section>
    <?php
    include('includes/outro.php');
    include('includes/footer.php');
    exit;
}

// The guest session cart cannot be reached from the webhook that actually
// confirms payment (a webhook is a separate server-to-server request with
// no browser session), so a guest's cart is cleared here instead, the
// first time they land on a paid order. Only the perfumes that were
// actually part of THIS order are removed -- not the whole session cart --
// so an item added after checkout (while the gateway payment was still
// pending) survives, the same scoping markOrderPaid() applies for an
// account's cart. An account's cart is already cleared from inside
// markOrderPaid() itself.
if ($order['payment_status'] === 'paid' && $order['user_id'] === null && !empty($_SESSION['guest_cart'])) {
    $paidItems = mysqli_query($con, "SELECT perfume_id FROM order_item WHERE order_id = " . (int) $order['id']);
    foreach ($paidItems as $paidItem) {
        unset($_SESSION['guest_cart'][(int) $paidItem['perfume_id']]);
    }
}

$statusCopy = [
    'paid'    => ['Payment received', 'Thank you -- your payment has been confirmed and the order is now being processed.'],
    'pending' => ['Confirming your payment', "We're still waiting for your payment provider to confirm this. This can take a minute or two -- refresh this page to check again."],
    'failed'  => ['Payment did not go through', 'Your payment was not completed, so this order has not been charged. You can try again or choose a different payment method.'],
    'cod'     => ['Cash on delivery', 'Pay the courier when your order arrives.'],
    'refunded' => ['Refunded', 'This order has been refunded.'],
];
[$heading, $message] = $statusCopy[$order['payment_status']] ?? ['Order received', 'Thank you for your order.'];

echo crumb(['Home' => 'index.php', $order['tracking_no'] => null]);
?>

<section class="section shell">
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)"><?= e($heading) ?></h1>
            <p><?= e($message) ?></p>
        </div>
        <span class="badge" data-payment-status="<?= e($order['payment_status']) ?>">
            <?= e(ucfirst($order['payment_status'])) ?>
        </span>
    </div>

    <div class="panel">
        <div class="stack" style="font-size:var(--t-sm)">
            <div>
                <div style="color:var(--fg-faint);font-size:var(--t-xs)">Order</div>
                <div><?= e($order['tracking_no']) ?></div>
            </div>
            <div>
                <div style="color:var(--fg-faint);font-size:var(--t-xs)">Total</div>
                <div><?= taka($order['total_price']) ?></div>
            </div>
            <div>
                <div style="color:var(--fg-faint);font-size:var(--t-xs)">Payment method</div>
                <div><?= e($order['payment_mode']) ?></div>
            </div>
        </div>

        <?php if ($order['payment_status'] === 'pending') { ?>
            <a class="btn btn--primary btn--block" href="payment-return.php?trackid=<?= urlencode($order['tracking_no']) ?>"
               style="margin-top:var(--s-5)">
                Refresh status
            </a>
        <?php } elseif ($order['payment_status'] === 'failed') { ?>
            <a class="btn btn--primary btn--block" href="checkout.php" style="margin-top:var(--s-5)">
                Try again
            </a>
        <?php } ?>

        <a class="btn btn--ghost btn--block"
           href="<?= $isAuthed ? 'order-details.php?trackid=' . urlencode($order['tracking_no']) : 'perfumes.php' ?>"
           style="margin-top:var(--s-3)">
            <?= $isAuthed ? 'View order details' : 'Keep shopping' ?>
        </a>
    </div>
</section>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
