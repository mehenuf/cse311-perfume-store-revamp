<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'Dashboard';
include('Includes/header.php');
include('../functions/myfunctions.php');

/** Single-value query helper. */
function scalar($sql, $fallback = 0)
{
    global $con;
    $r = mysqli_query($con, $sql);
    if (!$r) return $fallback;
    $row = mysqli_fetch_row($r);
    return $row ? $row[0] : $fallback;
}

$revenue      = (float) scalar("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status <> 4");
$orderCount   = (int)   scalar("SELECT COUNT(*) FROM orders");
$pending      = (int)   scalar("SELECT COUNT(*) FROM orders WHERE status = 0");
$customers    = (int)   scalar("SELECT COUNT(*) FROM customer WHERE admin_check = 0");
$liveCount    = (int)   scalar("SELECT COUNT(*) FROM perfumes WHERE status = 1");
$hiddenCount  = (int)   scalar("SELECT COUNT(*) FROM perfumes WHERE status = 0");
$outOfStock   = (int)   scalar("SELECT COUNT(*) FROM perfumes WHERE qty = 0 AND status = 1");

$recent = mysqli_query($con,
    "SELECT id, tracking_no, name, total_price, status, created_at
     FROM orders ORDER BY created_at DESC LIMIT 6");

$lowStock = mysqli_query($con,
    "SELECT id, name, qty, image_path FROM perfumes
     WHERE status = 1 AND qty <= 12 ORDER BY qty ASC LIMIT 6");
?>

<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>Everything that needs your attention, first.</p>
    </div>
    <a class="btn btn--primary" href="add.php">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Add product
    </a>
</div>

<div class="stats">
    <div class="stat">
        <p class="stat__k">Revenue</p>
        <p class="stat__v"><?= taka($revenue) ?></p>
        <p class="stat__m">Across <?= $orderCount ?> order<?= $orderCount === 1 ? '' : 's' ?>, cancellations excluded</p>
    </div>
    <div class="stat">
        <p class="stat__k">Awaiting action</p>
        <p class="stat__v"><?= $pending ?></p>
        <p class="stat__m"><?= $pending ? '<b>Needs processing</b>' : 'Nothing waiting' ?></p>
    </div>
    <div class="stat">
        <p class="stat__k">Customers</p>
        <p class="stat__v"><?= $customers ?></p>
        <p class="stat__m">Registered accounts</p>
    </div>
    <div class="stat">
        <p class="stat__k">Catalogue</p>
        <p class="stat__v"><?= $liveCount ?></p>
        <p class="stat__m"><?= $hiddenCount ?> unpublished<?= $outOfStock ? ', <b>' . $outOfStock . ' out of stock</b>' : '' ?></p>
    </div>
</div>

<div class="grid-2">

    <section class="card">
        <div class="card__head">
            <h2>Latest orders</h2>
            <a class="btn btn--quiet btn--sm" href="orders.php">All orders</a>
        </div>
        <div class="card__body card__body--flush">
            <?php if ($recent && mysqli_num_rows($recent) > 0) { ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Tracking</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="num">Total</th>
                                <th scope="col"><span class="visually-hidden">Open</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $o) { ?>
                                <tr>
                                    <td class="table__title"><?= e($o['tracking_no']) ?></td>
                                    <td>
                                        <?= e($o['name']) ?>
                                        <span class="table__sub"><?= e(date('j M Y', strtotime($o['created_at']))) ?></span>
                                    </td>
                                    <td><span class="badge" data-status="<?= (int) $o['status'] ?>"><?= e(orderStatus($o['status'])) ?></span></td>
                                    <td class="num"><?= taka($o['total_price']) ?></td>
                                    <td class="table__actions">
                                        <a class="btn btn--ghost btn--sm" href="order-history.php?trackid=<?= urlencode($o['tracking_no']) ?>">Open</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty">
                    <span class="empty__icon"><i class="fa-solid fa-inbox" aria-hidden="true"></i></span>
                    <p>No orders have been placed yet.</p>
                </div>
            <?php } ?>
        </div>
    </section>

    <section class="card">
        <div class="card__head">
            <h2>Running low</h2>
            <a class="btn btn--quiet btn--sm" href="perfume.php">Catalogue</a>
        </div>
        <div class="card__body">
            <?php if ($lowStock && mysqli_num_rows($lowStock) > 0) { ?>
                <?php foreach ($lowStock as $p) { ?>
                    <div class="line-item">
                        <img src="../images/<?= e($p['image_path']) ?>" alt="" loading="lazy">
                        <div>
                            <p class="line-item__name"><?= e($p['name']) ?></p>
                            <p class="line-item__meta"><?= (int) $p['qty'] ?> left in stock</p>
                        </div>
                        <a class="btn btn--ghost btn--sm" href="edit-perfume.php?id=<?= (int) $p['id'] ?>">Edit</a>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="empty">
                    <span class="empty__icon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                    <p>Every published bottle is comfortably in stock.</p>
                </div>
            <?php } ?>
        </div>
    </section>

</div>

<?php include('Includes/footer.php'); ?>
