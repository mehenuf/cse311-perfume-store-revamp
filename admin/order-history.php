<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'Order';
include('Includes/header.php');
include('../functions/myfunctions.php');

$order = null;
if (isset($_GET['trackid'])) {
    $tracking   = $_GET['trackid'];
    $validation = validateTrackID($tracking);
    if ($validation && mysqli_num_rows($validation) > 0) {
        $order = mysqli_fetch_assoc($validation);
    }
}

if (!$order) {
    ?>
    <div class="page-head"><div><h1>Order not found</h1></div></div>
    <section class="card">
        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
            <p>That tracking number does not match any order.</p>
            <a class="btn btn--primary btn--sm" href="orders.php">Back to orders</a>
        </div>
    </section>
    <?php
    include('Includes/footer.php');
    exit;
}

$status = (int) $order['status'];
$items  = mysqli_query($con,
    "SELECT oi.perfume_qty, oi.price, p.name, p.image_path
     FROM order_item oi
     JOIN perfumes p ON p.id = oi.perfume_id
     WHERE oi.order_id = " . (int) $order['id']);

$statuses = [0 => 'Processing', 1 => 'Completed', 2 => 'Shipped', 3 => 'Delivered', 4 => 'Cancelled'];
?>

<div class="page-head">
    <div>
        <h1><?= e($order['tracking_no']) ?></h1>
        <p>Placed <?= e(date('j F Y \a\t H:i', strtotime($order['created_at']))) ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:var(--s-4)">
        <span class="badge" data-status="<?= $status ?>"><?= e(orderStatus($status)) ?></span>
        <a class="btn btn--quiet" href="orders.php">Back</a>
    </div>
</div>

<div class="grid-2">

    <section class="card">
        <div class="card__head"><h2>Items</h2></div>
        <div class="card__body">
            <?php if ($items && mysqli_num_rows($items) > 0) { ?>
                <?php foreach ($items as $item) { ?>
                    <div class="line-item">
                        <img src="../images/<?= e($item['image_path']) ?>" alt="" loading="lazy">
                        <div>
                            <p class="line-item__name"><?= e($item['name']) ?></p>
                            <p class="line-item__meta"><?= taka($item['price']) ?> x <?= (int) $item['perfume_qty'] ?></p>
                        </div>
                        <p class="line-item__amt"><?= taka($item['price'] * $item['perfume_qty']) ?></p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="empty"><p>This order has no line items.</p></div>
            <?php } ?>

            <div class="total-row">
                <span>Total</span>
                <span><?= taka($order['total_price']) ?></span>
            </div>
        </div>
    </section>

    <div class="side-stack">

        <section class="card">
            <div class="card__head"><h2>Update status</h2></div>
            <div class="card__body">
                <form action="Includes/code.php" method="post" class="form">
                    <input type="hidden" name="tracking_no" value="<?= e($order['tracking_no']) ?>">
                    <div class="field">
                        <label for="o-status">Status</label>
                        <select class="select" id="o-status" name="order_status">
                            <?php foreach ($statuses as $code => $label) { ?>
                                <option value="<?= $code ?>" <?= $status === $code ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php } ?>
                        </select>
                        <span class="field__hint">The customer sees this on their orders page.</span>
                    </div>
                    <button class="btn btn--primary btn--block" type="submit" name="updateOrder_btn">Save status</button>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card__head"><h2>Deliver to</h2></div>
            <div class="card__body">
                <dl class="kv">
                    <div>
                        <dt>Recipient</dt>
                        <dd><?= e($order['name']) ?></dd>
                    </div>
                    <div>
                        <dt>Contact</dt>
                        <dd><a href="tel:<?= e($order['contacts']) ?>"><?= e($order['contacts']) ?></a></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd style="word-break:break-word"><a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a></dd>
                    </div>
                    <div>
                        <dt>Address</dt>
                        <dd><?= e($order['address']) ?><?= $order['zipcode'] ? ', ' . e($order['zipcode']) : '' ?></dd>
                    </div>
                    <div>
                        <dt>Payment</dt>
                        <dd><?= e($order['payment_mode']) ?></dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="card">
            <div class="card__head"><h2>Account</h2></div>
            <div class="card__body">
                <dl class="kv">
                    <div>
                        <dt>Username</dt>
                        <dd><?= e($order['username']) ?></dd>
                    </div>
                    <div>
                        <dt>Registered name</dt>
                        <dd><?= e($order['id_name']) ?></dd>
                    </div>
                    <div>
                        <dt>Registered email</dt>
                        <dd style="word-break:break-word"><?= e($order['id_email']) ?></dd>
                    </div>
                </dl>
            </div>
        </section>

    </div>
</div>

<?php include('Includes/footer.php'); ?>
