<?php
/**
 * One product card. Expects $p, a row from the perfumes table.
 * The whole card is the link target; the price and stock stay readable.
 */
list($stockText, $stockLow) = stockLabel($p['qty']);
$pricing = perfumePricing($p);
?>
<article class="product" data-product data-reveal="rise"
         data-name="<?= e(strtolower($p['name'])) ?>"
         data-notes="<?= e(strtolower($p['perfume_notes'] ?? '')) ?>"
         data-price="<?= (float) $pricing['final'] ?>"
         data-in-stock="<?= (int) $p['qty'] > 0 ? '1' : '0' ?>"
         data-on-sale="<?= $pricing['active'] ? '1' : '0' ?>">
    <div class="product__media">
        <?php if (trim($p['image_path']) !== '') { ?>
            <img src="<?= $basePath ?>images/<?= e($p['image_path']) ?>"
                 alt="<?= e($p['name']) ?> bottle"
                 loading="lazy" decoding="async" width="480" height="600">
        <?php } else { ?>
            <?= pendingMedia($p['name']) ?>
        <?php } ?>
    </div>
    <div class="product__body">
        <h3 class="product__name">
            <a class="product__link" href="<?= $basePath ?>display-perfume.php?name=<?= urlencode($p['name']) ?>">
                <?= e($p['name']) ?>
            </a>
        </h3>
        <p class="product__meta"><?= e($p['volume']) ?></p>
        <div class="product__foot">
            <?= priceMarkup($p, 'product__price') ?>
            <span class="product__stock" data-low="<?= $stockLow ? 'true' : 'false' ?>"><?= e($stockText) ?></span>
        </div>
    </div>
</article>
