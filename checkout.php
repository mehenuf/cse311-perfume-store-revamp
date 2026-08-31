<?php
session_start();
include('authenticate.php');
include('functions/functions.php');

$pageTitle       = 'Checkout';
$pageDescription = 'Confirm your delivery details and place your order.';
include('includes/header.php');

$cartItems = displayCart();
$lines     = [];
$total     = 0;

if ($cartItems) {
    foreach ($cartItems as $row) {
        $lines[] = $row;
        $total  += $row['price'] * $row['perfume_quantity'];
    }
}

echo crumb(['Home' => 'index.php', 'Cart' => 'shoppingcart.php', 'Checkout' => null]);
?>

<section class="section shell">
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)">Checkout</h1>
            <p>Cash on delivery. You pay the courier when the bottle reaches you.</p>
        </div>
    </div>

    <?php if (count($lines) === 0) { ?>

        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i></span>
            <h2 style="font-size:var(--t-h3)">There is nothing to check out</h2>
            <p>Your cart is empty, so there is no order to place yet.</p>
            <a class="btn btn--primary" href="perfumes.php">Browse the collection</a>
        </div>

    <?php } else { ?>

        <form action="functions/placeorder.php" method="post">
            <div class="cart-layout">

                <div class="panel">
                    <div class="panel__head">
                        <h2 style="font-size:var(--t-h3)">Delivery details</h2>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="co-name">Full name</label>
                            <input class="input" id="co-name" name="name" type="text" required
                                   autocomplete="name" placeholder="Your full name">
                        </div>

                        <div class="field">
                            <label for="co-email">Email</label>
                            <input class="input" id="co-email" name="email" type="email" required
                                   autocomplete="email" placeholder="you@example.com">
                        </div>

                        <div class="field">
                            <label for="co-contact">Contact number</label>
                            <input class="input" id="co-contact" name="contact" type="tel" required
                                   autocomplete="tel" placeholder="+880 1700 000000">
                            <span class="field__hint">The courier will call this number.</span>
                        </div>

                        <div class="field">
                            <label for="co-zip">Zip code</label>
                            <input class="input" id="co-zip" name="zipcode" type="text" required
                                   autocomplete="postal-code" placeholder="1205">
                        </div>

                        <div class="field span-2">
                            <label for="co-address">Delivery address</label>
                            <textarea class="textarea" id="co-address" name="address" required rows="3"
                                      autocomplete="street-address"
                                      placeholder="House and road, area, city"></textarea>
                            <span class="field__hint">Include a landmark if the address is hard to find.</span>
                        </div>
                    </div>
                </div>

                <aside class="panel summary">
                    <div class="panel__head">
                        <h2 style="font-size:var(--t-h3)">Your order</h2>
                    </div>

                    <?php foreach ($lines as $line) { ?>
                        <div class="cart-line" style="grid-template-columns:56px minmax(0,1fr) auto">
                            <div class="cart-line__media" style="width:56px">
                                <img src="images/<?= e($line['image_path']) ?>"
                                     alt="<?= e($line['perfume_name']) ?> bottle" loading="lazy" width="112" height="140">
                            </div>
                            <div>
                                <span class="cart-line__name" style="font-size:var(--t-sm)">
                                    <?= e($line['perfume_name']) ?>
                                </span>
                                <p class="cart-line__price">Quantity <?= (int) $line['perfume_quantity'] ?></p>
                            </div>
                            <div style="text-align:right;font-family:'Outfit',sans-serif;font-weight:600;white-space:nowrap">
                                <?= taka($line['price'] * $line['perfume_quantity']) ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="summary__row" style="margin-top:var(--s-4)">
                        <span>Payment</span>
                        <span>Cash on delivery</span>
                    </div>

                    <div class="summary__total">
                        <span>Total</span>
                        <span><?= taka($total) ?></span>
                    </div>

                    <button class="btn btn--primary btn--lg btn--block" type="submit" name="placeorder"
                            style="margin-top:var(--s-5)">
                        Place order
                    </button>
                    <a class="btn btn--quiet btn--block" href="shoppingcart.php" style="margin-top:var(--s-2)">
                        Back to cart
                    </a>
                </aside>

            </div>
        </form>

    <?php } ?>
</section>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
