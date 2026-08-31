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

        <div class="panel" style="padding:0;overflow:hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Tracking</th>
                            <th scope="col">Placed</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) { ?>
                            <tr>
                                <td style="font-family:'Outfit',sans-serif;font-weight:600;white-space:nowrap">
                                    <?= e($order['tracking_no']) ?>
                                </td>
                                <td style="color:var(--text-muted);white-space:nowrap">
                                    <?= e(date('j M Y', strtotime($order['created_at']))) ?>
                                </td>
                                <td style="font-family:'Outfit',sans-serif;font-weight:600;white-space:nowrap">
                                    <?= taka($order['total_price']) ?>
                                </td>
                                <td>
                                    <span class="badge" data-status="<?= (int) $order['status'] ?>">
                                        <?= e(orderStatus($order['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right">
                                    <a class="btn btn--ghost btn--sm"
                                       href="order-details.php?trackid=<?= urlencode($order['tracking_no']) ?>">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
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
