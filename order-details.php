<?php
session_start();
include('authenticate.php');
include('functions/functions.php');

$order    = null;
$tracking = '';

if (isset($_GET['trackid'])) {
    $tracking   = $_GET['trackid'];
    $validation = validateTrackID($tracking);          // already scoped to the session user
    if ($validation && mysqli_num_rows($validation) > 0) {
        $order = mysqli_fetch_assoc($validation);
    }
}

$pageTitle       = $order ? 'Order ' . $order['tracking_no'] : 'Order not found';
$pageDescription = 'Order details and delivery status.';
include('includes/header.php');

if (!$order) {
    echo crumb(['Home' => 'index.php', 'Orders' => 'orders.php', 'Not found' => null]);
    ?>
    <section class="section shell">
        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
            <h1 style="font-size:var(--t-h2)">We could not find that order</h1>
            <p>That tracking number does not match any order on your account.</p>
            <a class="btn btn--primary" href="orders.php">Back to your orders</a>
        </div>
    </section>
    <?php
    include('includes/outro.php');
    include('includes/footer.php');
    exit;
}

$status    = (int) $order['status'];
$cancelled = $status === 4;

$items = mysqli_query($con,
    "SELECT oi.perfume_qty, oi.price, p.name, p.image_path
     FROM order_item oi
     JOIN perfumes p ON p.id = oi.perfume_id
     WHERE oi.order_id = " . (int) $order['id']);

echo crumb([
    'Home'   => 'index.php',
    'Orders' => 'orders.php',
    $order['tracking_no'] => null,
]);
?>

<section class="section shell">
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)">Order <?= e($order['tracking_no']) ?></h1>
            <p>Placed <?= e(date('j F Y', strtotime($order['created_at']))) ?></p>
        </div>
        <span class="badge" data-status="<?= $status ?>"><?= e(orderStatus($status)) ?></span>
    </div>

    <div class="cart-layout">

        <div class="panel">
            <div class="panel__head">
                <h2 style="font-size:var(--t-h3)">Items</h2>
            </div>

            <?php
            $itemTotal = 0;
            if ($items && mysqli_num_rows($items) > 0) {
                foreach ($items as $item) {
                    $itemTotal += $item['price'] * $item['perfume_qty'];
            ?>
                    <div class="cart-line" style="grid-template-columns:72px minmax(0,1fr) auto">
                        <div class="cart-line__media" style="width:72px">
                            <?php if (trim($item['image_path']) !== '') { ?>
                                <img src="images/<?= e($item['image_path']) ?>"
                                     alt="<?= e($item['name']) ?> bottle" loading="lazy" width="144" height="180">
                            <?php } else { ?><?= pendingMedia($item['name']) ?><?php } ?>
                        </div>
                        <div>
                            <span class="cart-line__name"><?= e($item['name']) ?></span>
                            <p class="cart-line__price">
                                <?= taka($item['price']) ?> x <?= (int) $item['perfume_qty'] ?>
                            </p>
                        </div>
                        <div style="text-align:right;font-family:'Jost', sans-serif;font-weight:600;white-space:nowrap">
                            <?= taka($item['price'] * $item['perfume_qty']) ?>
                        </div>
                    </div>
            <?php
                }
            }
            ?>

            <div class="summary__total">
                <span>Order total</span>
                <span><?= taka($order['total_price']) ?></span>
            </div>
        </div>

        <aside class="panel summary">
            <div class="panel__head">
                <h2 style="font-size:var(--t-h3)">Delivery</h2>
            </div>

            <div class="stack" style="font-size:var(--t-sm)">
                <div>
                    <div style="color:var(--fg-faint);font-size:var(--t-xs)">Recipient</div>
                    <div><?= e($order['name']) ?></div>
                </div>
                <div>
                    <div style="color:var(--fg-faint);font-size:var(--t-xs)">Contact</div>
                    <div><?= e($order['contacts']) ?></div>
                </div>
                <div>
                    <div style="color:var(--fg-faint);font-size:var(--t-xs)">Email</div>
                    <div style="word-break:break-word"><?= e($order['email']) ?></div>
                </div>
                <div>
                    <div style="color:var(--fg-faint);font-size:var(--t-xs)">Address</div>
                    <div><?= e($order['address']) ?><?= $order['zipcode'] ? ', ' . e($order['zipcode']) : '' ?></div>
                </div>
                <div>
                    <div style="color:var(--fg-faint);font-size:var(--t-xs)">Payment</div>
                    <div><?= e($order['payment_mode']) ?></div>
                </div>
            </div>

            <?php if ($cancelled) { ?>
                <p style="margin-top:var(--s-5);padding-top:var(--s-4);border-top:1px solid var(--line);color:var(--danger);font-size:var(--t-sm)">
                    This order was cancelled.
                </p>
            <?php } ?>

            <a class="btn btn--ghost btn--block" href="orders.php" style="margin-top:var(--s-5)">
                Back to your orders
            </a>
        </aside>

    </div>
</section>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
