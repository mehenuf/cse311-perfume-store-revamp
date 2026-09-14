<?php
session_start();
include('authenticate.php');
include('functions/functions.php');

$pageTitle       = 'Your orders';
$pageDescription = 'Track every order you have placed with Perfume Store.';
include('includes/header.php');

$orders = getOrderHistory();

echo crumb(['Home' => 'index.php', 'Orders' => null]);
?>

<section class="section shell">
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)">Your orders</h1>
            <p>Every order you have placed, newest first.</p>
        </div>
    </div>

    <?php if ($orders && mysqli_num_rows($orders) > 0) { ?>

        <div class="panel" style="padding:0 var(--s-6)">
            <?php foreach ($orders as $order) { ?>
                <div class="order-row">
                    <div>
                        <p class="order-row__id"><?= e($order['tracking_no']) ?></p>
                        <p class="order-row__date"><?= e(date('j M Y', strtotime($order['created_at']))) ?></p>
                    </div>
                    <div class="order-row__meta">
                        <span class="badge" data-status="<?= (int) $order['status'] ?>">
                            <?= e(orderStatus($order['status'])) ?>
                        </span>
                        <span class="order-row__total"><?= taka($order['total_price']) ?></span>
                        <a class="btn btn--ghost btn--sm"
                           href="order-details.php?trackid=<?= urlencode($order['tracking_no']) ?>">
                            View
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>

    <?php } else { ?>

        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
            <h2 style="font-size:var(--t-h3)">No orders yet</h2>
            <p>Once you place your first order it will show up here with its tracking number.</p>
            <a class="btn btn--primary" href="perfumes.php">Browse the collection</a>
        </div>

    <?php } ?>
</section>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
