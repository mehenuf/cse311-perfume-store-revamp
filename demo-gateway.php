<?php
/**
 * Stand-in for a gateway's own hosted checkout page, used only when
 * PAYMENT_DEMO_MODE is on (see config/env.example.php). Lets the online
 * payment flow -- pending order, redirect to the "gateway", the
 * gateway's confirmation driving markOrderPaid()/markOrderFailed(),
 * landing back on payment-return.php -- be demoed end to end with no
 * real merchant credentials and no outbound network call.
 *
 * Only ever acts on an order whose payment_id was stamped 'DEMO-...' by
 * functions/pay-*.php's own demo-mode branch, so this cannot be pointed
 * at a genuine pending gateway order (e.g. one left over from before
 * demo mode was switched on) to fraudulently mark it paid.
 */
session_start();
require_once __DIR__ . '/functions/functions.php';
require_once __DIR__ . '/functions/payments/common.php';

if (!paymentDemoModeEnabled()) {
    http_response_code(404);
    exit;
}

$trackingNo = $_GET['trackid'] ?? ($_POST['trackid'] ?? '');
$order      = $trackingNo !== '' ? findOrderByTrackingNo($trackingNo) : null;

if (!$order || $order['payment_status'] !== 'pending' || strpos((string) $order['payment_id'], 'DEMO-') !== 0) {
    http_response_code(404);
    exit;
}

$gatewayLabels = [
    'STRIPE'     => ['name' => 'Stripe', 'method' => 'Card, Google Pay or Apple Pay', 'color' => '#635bff'],
    'SSLCOMMERZ' => ['name' => 'SSLCommerz', 'method' => 'bKash, Rocket, Nagad or Bangla QR', 'color' => '#00a651'],
    'COINBASE'   => ['name' => 'Coinbase Commerce', 'method' => 'Crypto', 'color' => '#0052ff'],
];
$gateway = $order['payment_mode'];
$label   = $gatewayLabels[$gateway] ?? ['name' => $gateway, 'method' => '', 'color' => 'var(--ink)'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit;
    }

    $action  = $_POST['action'] ?? '';
    $eventId = 'demo-' . $order['id'] . '-' . $action . '-' . bin2hex(random_bytes(4));

    if ($action === 'succeed') {
        markOrderPaid(
            $order['id'], $gateway, $eventId, 'demo.payment_succeeded',
            $order['payment_id'], $order['total_price'], $order['currency'],
            json_encode(['demo' => true, 'tracking_no' => $order['tracking_no']])
        );
        header('Location: payment-return.php?trackid=' . urlencode($order['tracking_no']));
        exit;
    }

    if ($action === 'fail') {
        markOrderFailed(
            $order['id'], $gateway, $eventId, 'demo.payment_failed',
            json_encode(['demo' => true, 'tracking_no' => $order['tracking_no']])
        );
        header('Location: payment-return.php?trackid=' . urlencode($order['tracking_no']) . '&result=fail');
        exit;
    }

    header('Location: demo-gateway.php?trackid=' . urlencode($order['tracking_no']));
    exit;
}

$pageTitle       = $label['name'] . ' (demo)';
$pageDescription = 'Demo checkout page.';
$csrfToken       = csrfToken();
include('includes/header.php');
?>

<section class="section shell" style="max-width:560px">
    <div class="panel" style="border-top:4px solid <?= e($label['color']) ?>">
        <div class="panel__head" style="display:flex;align-items:center;justify-content:space-between;gap:var(--s-3)">
            <h1 style="font-size:var(--t-h3);margin:0"><?= e($label['name']) ?></h1>
            <span class="badge" style="background:#fff3cd;color:#7a5b00;border-color:#e9d385">DEMO -- no real charge</span>
        </div>

        <p class="field__hint">
            This is a stand-in for <?= e($label['name']) ?>'s own hosted checkout page, shown because
            <code>PAYMENT_DEMO_MODE</code> is on. No card, wallet, or wallet address is actually charged --
            use the buttons below to simulate how a real payment would resolve.
        </p>

        <div class="summary__row">
            <span>Order</span>
            <span><?= e($order['tracking_no']) ?></span>
        </div>
        <div class="summary__row">
            <span>Method</span>
            <span><?= e($label['method']) ?></span>
        </div>
        <div class="summary__total">
            <span>Amount</span>
            <span><?= e($order['currency']) ?> <?= number_format((float) $order['total_price'], 2) ?></span>
        </div>

        <form method="post" style="margin-top:var(--s-5)">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="trackid" value="<?= e($order['tracking_no']) ?>">
            <button class="btn btn--primary btn--lg btn--block" type="submit" name="action" value="succeed">
                Simulate successful payment
            </button>
        </form>
        <form method="post" style="margin-top:var(--s-3)">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="trackid" value="<?= e($order['tracking_no']) ?>">
            <button class="btn btn--quiet btn--block" type="submit" name="action" value="fail">
                Simulate failed / cancelled payment
            </button>
        </form>
    </div>
</section>

<?php include('includes/footer.php'); ?>
