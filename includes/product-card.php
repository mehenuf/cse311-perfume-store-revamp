<?php
/**
 * One product card. Expects $p, a row from the perfumes table.
 * The whole card is the link target; the price and stock stay readable.
 */
list($stockText, $stockLow) = stockLabel($p['qty']);
?>
<article class="product" data-product data-reveal>
    <div class="product__media">
        <img src="images/<?= e($p['image_path']) ?>"
             alt="<?= e($p['name']) ?> bottle"
             loading="lazy" decoding="async" width="480" height="600">
    </div>
    <div class="product__body">
        <h3 class="product__name">
            <a class="product__link" href="display-perfume.php?name=<?= urlencode($p['name']) ?>">
                <?= e($p['name']) ?>
            </a>
        </h3>
        <p class="product__meta"><?= e($p['volume']) ?></p>
        <div class="product__foot">
            <span class="product__price"><?= taka($p['price']) ?></span>
            <span class="product__stock" data-low="<?= $stockLow ? 'true' : 'false' ?>"><?= e($stockText) ?></span>
        </div>
    </div>
</article>
