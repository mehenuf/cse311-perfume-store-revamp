<?php
/** Storefront footer. Expects $basePath ('' at root, '../' one level down). */
$basePath   = isset($basePath) ? $basePath : '';
$footBrands = array_slice(brandListAlphabetical(), 0, 4, true);
?>
<footer class="footer">
    <div class="shell">
        <div class="footer__grid">

            <div>
                <a class="nav__brand" href="<?= $basePath ?>index.php" style="margin-bottom:var(--s-5)">
                    <svg class="nav__mark" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9.6 2.5h4.8v2.9H9.6z" stroke="currentColor" stroke-width="1.1"/>
                        <path d="M8 5.4h8l1.6 3.1v11.1a1.9 1.9 0 0 1-1.9 1.9H8.3a1.9 1.9 0 0 1-1.9-1.9V8.5L8 5.4Z"
                              stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/>
                        <path d="M9.4 11.6h5.2" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
                    </svg>
                    <span>Perfume<b>Store</b></span>
                </a>
                <p style="color:var(--fg-muted);font-size:var(--t-sm);max-width:34ch">
                    A curated house of designer and niche fragrance, delivered anywhere in Bangladesh.
                    Every bottle is sourced sealed and checked before it ships.
                </p>
            </div>

            <div>
                <h3>Shop</h3>
                <ul>
                    <li><a href="<?= $basePath ?>perfumes.php">Full collection</a></li>
                    <li><a href="<?= $basePath ?>shoppingcart.php">Your cart</a></li>
                    <li><a href="<?= $basePath ?>orders.php">Your orders</a></li>
                    <li><a href="<?= $basePath ?>checkout.php">Checkout</a></li>
                </ul>
            </div>

            <div>
                <h3>Houses</h3>
                <ul>
                    <?php foreach ($footBrands as $slug => $b) { ?>
                        <li><a href="<?= $basePath ?>brands/<?= $slug ?>.php"><?= e($b['label']) ?></a></li>
                    <?php } ?>
                    <li><a href="<?= $basePath ?>brands.php">All houses</a></li>
                </ul>
            </div>

            <div>
                <h3>Contact</h3>
                <ul>
                    <li><a href="tel:+8801700000001">+880 1700 000001</a></li>
                    <li><a href="mailto:mehenuf@gmail.com">mehenuf@gmail.com</a></li>
                    <li style="color:var(--fg-muted);font-size:var(--t-sm);line-height:1.7">
                        House 12, Road 5<br>
                        Dhanmondi, Dhaka 1205
                    </li>
                </ul>
            </div>

        </div>

        <div class="footer__note">
            <span>Copyright <?= date('Y') ?> Mehenuf Hossain Bhuiyan. All rights reserved.</span>
            <span>Cash on delivery across Bangladesh</span>
        </div>
    </div>
</footer>
