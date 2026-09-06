<?php
session_start();
include('functions/functions.php');

$pageTitle       = 'Your cart';
$pageDescription = 'Review the fragrances in your cart before checking out.';
include('includes/header.php');

$cartItems = displayCart();
$lines     = [];
$total     = 0;
$count     = 0;

if ($cartItems) {
    foreach ($cartItems as $row) {
        $lines[] = $row;
        $total  += $row['price'] * $row['perfume_quantity'];
        $count  += (int) $row['perfume_quantity'];
    }
}

echo crumb(['Home' => 'index.php', 'Cart' => null]);
?>

<section class="section shell">
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)">Your cart</h1>
            <p data-cart-item-count><?= $count ?> <?= $count === 1 ? 'item' : 'items' ?></p>
        </div>
    </div>

    <?php if (count($lines) === 0) { ?>

        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i></span>
            <h2 style="font-size:var(--t-h3)">Your cart is empty</h2>
            <p>Nothing here yet. Find something worth wearing.</p>
            <a class="btn btn--primary" href="perfumes.php">Browse the collection</a>
        </div>

    <?php } else { ?>

        <div class="cart-layout">

            <div class="panel">
                <?php foreach ($lines as $line) { ?>
                    <div class="cart-line" data-cart-line data-unit-price="<?= (float) $line['price'] ?>">

                        <a class="cart-line__media" href="display-perfume.php?name=<?= urlencode($line['perfume_name']) ?>">
                            <?php if (trim($line['image_path']) !== '') { ?>
                                <img src="images/<?= e($line['image_path']) ?>"
                                     alt="<?= e($line['perfume_name']) ?> bottle" loading="lazy" width="168" height="210">
                            <?php } else { ?><?= pendingMedia($line['perfume_name']) ?><?php } ?>
                        </a>

                        <div>
                            <a class="cart-line__name" href="display-perfume.php?name=<?= urlencode($line['perfume_name']) ?>">
                                <?= e($line['perfume_name']) ?>
                            </a>
                            <p class="cart-line__price">
                                <?php if ($line['discount_active']) { ?>
                                    <span class="price-block__was"><?= taka($line['original_price']) ?></span>
                                <?php } ?>
                                <?= taka($line['price']) ?> each
                            </p>
                        </div>

                        <div class="cart-line__controls"
                             data-qty data-cart-qty data-min="1" data-max="10"
                             data-perfume-id="<?= (int) $line['perfume_id'] ?>">
                            <div class="qty">
                                <button type="button" data-qty-step="-1" aria-label="Decrease quantity"
                                        <?= (int) $line['perfume_quantity'] <= 1 ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-minus" aria-hidden="true"></i>
                                </button>
                                <input type="number" value="<?= (int) $line['perfume_quantity'] ?>"
                                       min="1" max="10" data-qty-input
                                       aria-label="Quantity of <?= e($line['perfume_name']) ?>" readonly>
                                <button type="button" data-qty-step="1" aria-label="Increase quantity"
                                        <?= (int) $line['perfume_quantity'] >= 10 ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                </button>
                            </div>

                            <button class="btn btn--danger btn--sm" type="button"
                                    data-remove-line="<?= (int) $line['cart_id'] ?>">
                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                <span class="visually-hidden">Remove <?= e($line['perfume_name']) ?></span>
                                Remove
                            </button>
                        </div>

                        <div style="text-align:right;font-family:'Jost', sans-serif;font-weight:600;white-space:nowrap"
                             data-line-total><?= taka($line['price'] * $line['perfume_quantity']) ?></div>
                    </div>
                <?php } ?>
            </div>

            <aside class="panel summary">
                <div class="panel__head">
                    <h2 style="font-size:var(--t-h3)">Order summary</h2>
                </div>

                <div class="summary__row">
                    <span>Subtotal</span>
                    <span data-cart-total><?= taka($total) ?></span>
                </div>
                <div class="summary__row">
                    <span>Delivery</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary__row">
                    <span>Payment</span>
                    <span>Cash on delivery</span>
                </div>

                <div class="summary__total">
                    <span>Total</span>
                    <span data-cart-total><?= taka($total) ?></span>
                </div>

                <a class="btn btn--primary btn--lg btn--block" href="checkout.php" style="margin-top:var(--s-5)">
                    Checkout
                </a>
                <a class="btn btn--quiet btn--block" href="perfumes.php" style="margin-top:var(--s-2)">
                    Keep shopping
                </a>
            </aside>

        </div>

    <?php } ?>
</section>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
