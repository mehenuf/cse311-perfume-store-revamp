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
 *
 * The page below plays out a full method-specific entry step (a card
 * form, a mobile-wallet number + PIN, or a crypto address) so a demo
 * walkthrough looks and feels like a real hosted checkout -- but every
 * one of those fields is read and validated in the BROWSER only. The
 * server-side contract driving markOrderPaid()/markOrderFailed() below
 * is exactly the two POST actions ('succeed' / 'fail') the original
 * two-button version of this page already used; nothing entered in the
 * card/wallet/crypto step is ever sent to or trusted by the server.
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
$label   = $gatewayLabels[$gateway] ?? ['name' => $gateway, 'method' => '', 'color' => 'var(--gold)'];

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
$amount          = number_format((float) $order['total_price'], 2);
include('includes/header.php');
?>

<section class="section shell" style="max-width:520px">
    <div class="panel pay-panel" data-pay-gateway="<?= e($gateway) ?>" style="border-top:4px solid <?= e($label['color']) ?>">

        <div class="pay-panel__head">
            <div class="pay-panel__brand" style="--pay-color:<?= e($label['color']) ?>">
                <?= paymentBrandMark($gateway) ?>
                <h1><?= e($label['name']) ?></h1>
            </div>
            <span class="badge pay-panel__demo-badge">Demo &middot; no real charge</span>
        </div>

        <p class="field__hint" style="margin-top:var(--s-3)">
            This is a stand-in for <?= e($label['name']) ?>'s own hosted checkout page, shown because
            <code>PAYMENT_DEMO_MODE</code> is on. Nothing entered below is charged, stored, or sent
            anywhere -- it only decides which demo outcome to simulate.
        </p>

        <div class="pay-summary">
            <div>
                <span class="pay-summary__label">Order <?= e($order['tracking_no']) ?></span>
                <span class="pay-summary__method"><?= e($label['method']) ?></span>
            </div>
            <div class="pay-summary__amount"><?= e($order['currency']) ?> <?= $amount ?></div>
        </div>

        <!-- ============================== STRIPE: card entry ============================== -->
        <?php if ($gateway === 'STRIPE') { ?>
            <div data-pay-step="method">
                <div class="field">
                    <label for="pay-card-number">Card number</label>
                    <div class="pay-card-input">
                        <input class="input" id="pay-card-number" type="text" inputmode="numeric"
                               autocomplete="cc-number" placeholder="4242 4242 4242 4242"
                               maxlength="23" data-card-number>
                        <span class="pay-card-brand" data-card-brand aria-hidden="true"></span>
                    </div>
                </div>
                <div class="form-grid form-grid--2">
                    <div class="field">
                        <label for="pay-card-expiry">Expiry</label>
                        <input class="input" id="pay-card-expiry" type="text" inputmode="numeric"
                               autocomplete="cc-exp" placeholder="MM / YY" maxlength="7" data-card-expiry>
                    </div>
                    <div class="field">
                        <label for="pay-card-cvc">CVC</label>
                        <input class="input" id="pay-card-cvc" type="text" inputmode="numeric"
                               autocomplete="cc-csc" placeholder="123" maxlength="4" data-card-cvc>
                    </div>
                </div>
                <div class="field">
                    <label for="pay-card-name">Name on card</label>
                    <input class="input" id="pay-card-name" type="text" autocomplete="cc-name"
                           placeholder="As printed on the card" data-card-name>
                </div>

                <p class="field__hint pay-test-hint">
                    Demo card numbers: <code>4242 4242 4242 4242</code> succeeds &middot;
                    <code>4000 0000 0000 0002</code> is declined. Any other card that passes a basic
                    checksum will succeed.
                </p>

                <button class="btn btn--primary btn--lg btn--block" type="button" data-pay-submit disabled>
                    Pay <?= e($order['currency']) ?> <?= $amount ?>
                </button>
                <button class="btn btn--quiet btn--block" type="button" data-pay-force-fail>
                    Simulate a declined payment instead
                </button>
            </div>
        <?php } ?>

        <!-- ======================= SSLCOMMERZ: mobile wallet / card ======================= -->
        <?php if ($gateway === 'SSLCOMMERZ') { ?>
            <div data-pay-step="method">
                <div class="pay-method-grid" data-wallet-picker role="radiogroup" aria-label="Payment method">
                    <?php
                    $wallets = [
                        'bkash'  => ['label' => 'bKash',  'color' => '#e2136e'],
                        'rocket' => ['label' => 'Rocket',  'color' => '#8c3494'],
                        'nagad'  => ['label' => 'Nagad',   'color' => '#f6921e'],
                        'card'   => ['label' => 'Card',    'color' => '#4a4a52'],
                        'qr'     => ['label' => 'Bangla QR', 'color' => '#00a651'],
                    ];
                    foreach ($wallets as $slug => $w) { ?>
                        <button type="button" class="pay-method-tile" data-wallet-option="<?= e($slug) ?>"
                                style="--tile-color:<?= e($w['color']) ?>" aria-pressed="false">
                            <?= e($w['label']) ?>
                        </button>
                    <?php } ?>
                </div>

                <div data-wallet-form="mobile" hidden>
                    <div class="field">
                        <label for="pay-msisdn">Mobile number</label>
                        <input class="input" id="pay-msisdn" type="tel" inputmode="numeric"
                               placeholder="01XXXXXXXXX" maxlength="11" data-msisdn>
                    </div>
                    <div class="field">
                        <label for="pay-wallet-pin">PIN</label>
                        <input class="input" id="pay-wallet-pin" type="password" inputmode="numeric"
                               placeholder="••••" maxlength="5" data-wallet-pin>
                        <span class="field__hint">Demo PIN: any digits work &middot; <code>0000</code> is declined.</span>
                    </div>
                </div>

                <div data-wallet-form="card" hidden>
                    <div class="field">
                        <label for="pay-ssl-card-number">Card number</label>
                        <div class="pay-card-input">
                            <input class="input" id="pay-ssl-card-number" type="text" inputmode="numeric"
                                   placeholder="4242 4242 4242 4242" maxlength="23" data-card-number>
                            <span class="pay-card-brand" data-card-brand aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="pay-ssl-card-expiry">Expiry</label>
                            <input class="input" id="pay-ssl-card-expiry" type="text" inputmode="numeric"
                                   placeholder="MM / YY" maxlength="7" data-card-expiry>
                        </div>
                        <div class="field">
                            <label for="pay-ssl-card-cvc">CVC</label>
                            <input class="input" id="pay-ssl-card-cvc" type="text" inputmode="numeric"
                                   placeholder="123" maxlength="4" data-card-cvc>
                        </div>
                    </div>
                </div>

                <div data-wallet-form="qr" hidden>
                    <div class="pay-qr">
                        <?= paymentFakeQr() ?>
                    </div>
                    <p class="field__hint" style="text-align:center">Scan with any banking app, then confirm below.</p>
                </div>

                <button class="btn btn--primary btn--lg btn--block" type="button" data-pay-submit disabled
                        style="margin-top:var(--s-5)">
                    Pay <?= e($order['currency']) ?> <?= $amount ?>
                </button>
                <button class="btn btn--quiet btn--block" type="button" data-pay-force-fail>
                    Simulate a declined payment instead
                </button>
            </div>
        <?php } ?>

        <!-- ============================ COINBASE: crypto address ============================ -->
        <?php if ($gateway === 'COINBASE') { ?>
            <div data-pay-step="method">
                <div class="pay-method-grid" data-coin-picker role="radiogroup" aria-label="Cryptocurrency">
                    <?php foreach (['BTC', 'ETH', 'USDC', 'LTC'] as $i => $coin) { ?>
                        <button type="button" class="pay-method-tile" data-coin-option="<?= e($coin) ?>"
                                aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"><?= e($coin) ?></button>
                    <?php } ?>
                </div>

                <div class="pay-qr"><?= paymentFakeQr() ?></div>

                <div class="pay-crypto-address">
                    <code data-coin-address>bc1q</code>
                    <button type="button" class="btn btn--ghost btn--sm" data-copy-address>Copy</button>
                </div>

                <div class="pay-crypto-amount">
                    <span>Send exactly</span>
                    <strong data-coin-amount>&hellip;</strong>
                </div>

                <p class="field__hint" style="text-align:center">
                    Window closes in <span data-pay-countdown>15:00</span> &middot; this address is unique to this charge
                </p>

                <button class="btn btn--primary btn--lg btn--block" type="button" data-pay-submit>
                    I've sent the payment
                </button>
                <button class="btn btn--quiet btn--block" type="button" data-pay-force-fail>
                    Cancel this charge
                </button>
            </div>
        <?php } ?>

        <!-- ================================== processing ================================== -->
        <div data-pay-step="processing" hidden>
            <div class="pay-spinner" aria-hidden="true"></div>
            <p class="pay-processing-text">Contacting <?= e($label['name']) ?>&hellip;</p>
        </div>

        <form method="post" data-pay-real-form="succeed" hidden>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="trackid" value="<?= e($order['tracking_no']) ?>">
            <input type="hidden" name="action" value="succeed">
        </form>
        <form method="post" data-pay-real-form="fail" hidden>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="trackid" value="<?= e($order['tracking_no']) ?>">
            <input type="hidden" name="action" value="fail">
        </form>
    </div>
</section>

<style>
    .pay-panel__head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: var(--s-4);
    }
    .pay-panel__brand { display: flex; align-items: center; gap: var(--s-3); }
    .pay-panel__brand svg { color: var(--pay-color, var(--gold)); flex-shrink: 0; }
    .pay-panel__brand h1 { font-size: var(--t-h3); margin: 0; }
    .pay-panel__demo-badge {
        background: #fff3cd; color: #7a5b00; border-color: #e9d385; white-space: nowrap;
    }

    .pay-summary {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--s-3) var(--s-4);
        margin: var(--s-5) 0; padding: var(--s-4) var(--s-5);
        background: var(--ink-sunken); border: 1px solid var(--line);
    }
    .pay-summary > div { min-width: 0; }
    .pay-summary__label { display: block; font-size: var(--t-sm); color: var(--fg-muted); }
    .pay-summary__method { display: block; font-size: var(--t-xs); color: var(--fg-faint); margin-top: .15rem; }
    .pay-summary__amount {
        font-family: 'Jost', sans-serif; font-weight: 600; font-size: var(--t-h3);
        overflow-wrap: break-word;
    }

    [data-pay-step] { margin-top: var(--s-5); }

    .pay-card-input { position: relative; }
    .pay-card-input .input { padding-inline-end: 3.2rem; font-variant-numeric: tabular-nums; letter-spacing: .04em; }
    .pay-card-brand {
        position: absolute; top: 50%; right: var(--s-3); transform: translateY(-50%);
        font-size: var(--t-xs); font-weight: 700; letter-spacing: .04em; color: var(--fg-faint);
        text-transform: uppercase; pointer-events: none;
    }
    .pay-card-brand[data-brand='visa'] { color: #1a1f71; }
    .pay-card-brand[data-brand='mastercard'] { color: #eb001b; }
    .pay-card-brand[data-brand='amex'] { color: #2e77bc; }
    html[data-theme='dark'] .pay-card-brand[data-brand='visa'],
    html[data-theme='dark'] .pay-card-brand[data-brand='mastercard'],
    html[data-theme='dark'] .pay-card-brand[data-brand='amex'] { filter: brightness(1.6); }

    .pay-test-hint { margin-top: var(--s-3); }
    .pay-test-hint code { background: var(--ink-sunken); padding: .1rem .4rem; border: 1px solid var(--line); font-size: .82em; }

    .pay-method-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(88px, 1fr)); gap: var(--s-3);
        margin-bottom: var(--s-5);
    }
    .pay-method-tile {
        padding: var(--s-4) var(--s-2); text-align: center; font-size: var(--t-xs); font-weight: 600;
        background: var(--surface); border: 1px solid var(--line); color: var(--fg);
        cursor: pointer; transition: border-color .15s var(--ease), transform .15s var(--ease);
    }
    .pay-method-tile:hover { border-color: var(--tile-color, var(--gold)); transform: translateY(-1px); }
    .pay-method-tile[aria-pressed='true'] {
        border-color: var(--tile-color, var(--gold)); box-shadow: 0 0 0 1px var(--tile-color, var(--gold));
        color: var(--tile-color, var(--fg));
    }

    .pay-qr {
        display: flex; justify-content: center; margin: 0 0 var(--s-4);
        padding: var(--s-4); background: #fff; border: 1px solid var(--line);
    }
    .pay-qr svg { width: 148px; height: 148px; }

    .pay-crypto-address {
        display: flex; align-items: center; gap: var(--s-3); justify-content: space-between;
        padding: var(--s-3) var(--s-4); background: var(--ink-sunken); border: 1px solid var(--line);
        margin-bottom: var(--s-4);
    }
    .pay-crypto-address code { font-size: var(--t-xs); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pay-crypto-amount {
        display: flex; justify-content: space-between; font-size: var(--t-sm); color: var(--fg-muted);
        margin-bottom: var(--s-4);
    }
    .pay-crypto-amount strong { color: var(--fg); font-family: 'Jost', sans-serif; }

    .pay-spinner {
        width: 40px; height: 40px; margin: var(--s-6) auto var(--s-4);
        border: 3px solid var(--line); border-top-color: var(--gold); border-radius: 50%;
        animation: pay-spin .8s linear infinite;
    }
    .pay-processing-text { text-align: center; color: var(--fg-muted); font-size: var(--t-sm); }
    @keyframes pay-spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .pay-spinner { animation-duration: 2.4s; } }

    [data-field-invalid='true'] { border-color: var(--danger) !important; }
</style>

<script>
(function () {
    'use strict';
    var panel = document.querySelector('[data-pay-gateway]');
    if (!panel) return;

    var gateway = panel.dataset.payGateway;

    /* ---- card number: format, brand-detect, Luhn ---- */
    function luhnValid(digits) {
        var sum = 0, alt = false;
        for (var i = digits.length - 1; i >= 0; i--) {
            var n = parseInt(digits.charAt(i), 10);
            if (alt) { n *= 2; if (n > 9) n -= 9; }
            sum += n; alt = !alt;
        }
        return digits.length >= 12 && sum % 10 === 0;
    }

    function detectBrand(digits) {
        if (/^4/.test(digits)) return 'visa';
        if (/^(5[1-5]|2[2-7])/.test(digits)) return 'mastercard';
        if (/^3[47]/.test(digits)) return 'amex';
        return '';
    }

    function wireCardField(numberEl, brandEl) {
        if (!numberEl) return;
        numberEl.addEventListener('input', function () {
            var digits = numberEl.value.replace(/\D/g, '').slice(0, 19);
            numberEl.value = digits.replace(/(.{4})/g, '$1 ').trim();
            var brand = detectBrand(digits);
            if (brandEl) { brandEl.textContent = brand; brandEl.dataset.brand = brand; }
        });
    }

    function wireExpiryField(el) {
        if (!el) return;
        el.addEventListener('input', function () {
            var digits = el.value.replace(/\D/g, '').slice(0, 4);
            el.value = digits.length > 2 ? digits.slice(0, 2) + ' / ' + digits.slice(2) : digits;
        });
    }

    function wireCvcField(el) {
        if (!el) return;
        el.addEventListener('input', function () { el.value = el.value.replace(/\D/g, '').slice(0, 4); });
    }

    panel.querySelectorAll('[data-card-number]').forEach(function (el) {
        wireCardField(el, el.closest('.pay-card-input').querySelector('[data-card-brand]'));
    });
    panel.querySelectorAll('[data-card-expiry]').forEach(wireExpiryField);
    panel.querySelectorAll('[data-card-cvc]').forEach(wireCvcField);

    var msisdn = panel.querySelector('[data-msisdn]');
    if (msisdn) msisdn.addEventListener('input', function () {
        msisdn.value = msisdn.value.replace(/\D/g, '').slice(0, 11);
    });
    var pin = panel.querySelector('[data-wallet-pin]');
    if (pin) pin.addEventListener('input', function () {
        pin.value = pin.value.replace(/\D/g, '').slice(0, 5);
    });

    /* ---- SSLCommerz wallet-tile switching ---- */
    var walletPicker = panel.querySelector('[data-wallet-picker]');
    var walletSlug = null;
    if (walletPicker) {
        var tiles = Array.prototype.slice.call(walletPicker.querySelectorAll('[data-wallet-option]'));
        var forms = {
            mobile: panel.querySelector('[data-wallet-form="mobile"]'),
            card: panel.querySelector('[data-wallet-form="card"]'),
            qr: panel.querySelector('[data-wallet-form="qr"]'),
        };
        function showWallet(slug) {
            walletSlug = slug;
            tiles.forEach(function (t) { t.setAttribute('aria-pressed', String(t.dataset.walletOption === slug)); });
            var formKey = slug === 'card' ? 'card' : (slug === 'qr' ? 'qr' : 'mobile');
            Object.keys(forms).forEach(function (k) { if (forms[k]) forms[k].hidden = k !== formKey; });
            validate();
        }
        tiles.forEach(function (t) { t.addEventListener('click', function () { showWallet(t.dataset.walletOption); }); });
    }

    /* ---- Coinbase coin picker + fake address/amount/countdown ---- */
    var coinPicker = panel.querySelector('[data-coin-picker]');
    if (coinPicker) {
        var coinTiles = Array.prototype.slice.call(coinPicker.querySelectorAll('[data-coin-option]'));
        var addressEl = panel.querySelector('[data-coin-address]');
        var amountEl = panel.querySelector('[data-coin-amount]');
        var seedAddrs = {
            BTC: 'bc1q4z9k2m8x7wq3n5j6h1v0c8t2r4y6u8i0o2p4a6s',
            ETH: '0x71C7656EC7ab88b098defB751B7401B5f6d8976',
            USDC: '0x71C7656EC7ab88b098defB751B7401B5f6d8976',
            LTC: 'ltc1qxyz9k2m8x7wq3n5j6h1v0c8t2r4y6u8i0o2p4a6',
        };
        function pickCoin(coin) {
            coinTiles.forEach(function (t) { t.setAttribute('aria-pressed', String(t.dataset.coinOption === coin)); });
            if (addressEl) addressEl.textContent = seedAddrs[coin] || seedAddrs.BTC;
            if (amountEl) amountEl.textContent = coinEstimate(coin);
        }
        function coinEstimate(coin) {
            var total = parseFloat('<?= $amount ?>'.replace(/,/g, '')) || 0;
            var bdtPerUsd = 120;
            var usd = total / bdtPerUsd;
            var rates = { BTC: 0.000016, ETH: 0.00031, USDC: 1, LTC: 0.011 };
            var qty = usd * (rates[coin] || 0);
            var decimals = coin === 'USDC' ? 2 : 6;
            return qty.toFixed(decimals) + ' ' + coin;
        }
        coinTiles.forEach(function (t) { t.addEventListener('click', function () { pickCoin(t.dataset.coinOption); }); });
        pickCoin('BTC');

        var copyBtn = panel.querySelector('[data-copy-address]');
        if (copyBtn) copyBtn.addEventListener('click', function () {
            var text = addressEl ? addressEl.textContent : '';
            if (navigator.clipboard) navigator.clipboard.writeText(text).catch(function () {});
            copyBtn.textContent = 'Copied';
            window.setTimeout(function () { copyBtn.textContent = 'Copy'; }, 1400);
        });

        var countdownEl = panel.querySelector('[data-pay-countdown]');
        if (countdownEl) {
            var remaining = 15 * 60;
            window.setInterval(function () {
                remaining = Math.max(0, remaining - 1);
                var m = Math.floor(remaining / 60), s = remaining % 60;
                countdownEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            }, 1000);
        }
    }

    /* ---- enable/disable the Pay button as the visible fields validate ---- */
    var submitBtn = panel.querySelector('[data-pay-submit]');

    function currentCardFields() {
        var scope = walletSlug === 'card'
            ? panel.querySelector('[data-wallet-form="card"]')
            : panel.querySelector('[data-pay-step="method"]');
        if (!scope) return null;
        return {
            number: scope.querySelector('[data-card-number]'),
            expiry: scope.querySelector('[data-card-expiry]'),
            cvc: scope.querySelector('[data-card-cvc]'),
        };
    }

    function cardIsValid() {
        var f = currentCardFields();
        if (!f || !f.number) return false;
        var digits = f.number.value.replace(/\D/g, '');
        if (!luhnValid(digits)) return false;

        var exp = (f.expiry ? f.expiry.value : '').replace(/\D/g, '');
        if (exp.length !== 4) return false;
        var mm = parseInt(exp.slice(0, 2), 10), yy = parseInt(exp.slice(2), 10);
        if (mm < 1 || mm > 12) return false;
        var now = new Date();
        var currentYy = now.getFullYear() % 100, currentMm = now.getMonth() + 1;
        if (yy < currentYy || (yy === currentYy && mm < currentMm)) return false;

        var cvcLen = detectBrand(digits) === 'amex' ? 4 : 3;
        var cvc = (f.cvc ? f.cvc.value : '').replace(/\D/g, '');
        return cvc.length === cvcLen;
    }

    function validate() {
        if (!submitBtn) return;
        var ok = true;
        if (gateway === 'STRIPE') {
            ok = cardIsValid();
        } else if (gateway === 'SSLCOMMERZ') {
            if (!walletSlug) ok = false;
            else if (walletSlug === 'card') ok = cardIsValid();
            else if (walletSlug === 'qr') ok = true;
            else {
                var num = panel.querySelector('[data-msisdn]');
                var p = panel.querySelector('[data-wallet-pin]');
                ok = !!num && /^01\d{9}$/.test(num.value) && !!p && p.value.length >= 4;
            }
        }
        submitBtn.disabled = !ok;
    }

    panel.addEventListener('input', validate);
    validate();

    /* ---- decide the simulated outcome from what was actually entered ---- */
    function willDeclineCard() {
        var f = currentCardFields();
        if (!f || !f.number) return false;
        var digits = f.number.value.replace(/\D/g, '');
        return digits === '4000000000000002' || digits === '4000000000009995';
    }

    function willDeclineWallet() {
        var p = panel.querySelector('[data-wallet-pin]');
        return !!p && p.value === '0000';
    }

    function runOutcome(succeed) {
        var stepMethod = panel.querySelector('[data-pay-step="method"]');
        var stepProcessing = panel.querySelector('[data-pay-step="processing"]');
        if (stepMethod) stepMethod.hidden = true;
        if (stepProcessing) stepProcessing.hidden = false;

        window.setTimeout(function () {
            var form = panel.querySelector('[data-pay-real-form="' + (succeed ? 'succeed' : 'fail') + '"]');
            if (form) form.submit();
        }, 1450);
    }

    if (submitBtn) submitBtn.addEventListener('click', function () {
        if (submitBtn.disabled) return;
        submitBtn.disabled = true;
        var succeed = true;
        if (gateway === 'STRIPE') succeed = !willDeclineCard();
        else if (gateway === 'SSLCOMMERZ') succeed = walletSlug === 'card' ? !willDeclineCard() : !willDeclineWallet();
        else if (gateway === 'COINBASE') succeed = true;
        runOutcome(succeed);
    });

    var forceFailBtn = panel.querySelector('[data-pay-force-fail]');
    if (forceFailBtn) forceFailBtn.addEventListener('click', function () { runOutcome(false); });
})();
</script>

<?php include('includes/footer.php'); ?>
