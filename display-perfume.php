<?php
session_start();
include('functions/functions.php');
require_once('includes/helpers.php');

$product = null;
if (isset($_GET['name'])) {
    $rows = getViaNameActive('perfumes', $_GET['name']);
    if ($rows && mysqli_num_rows($rows) > 0) {
        $product = mysqli_fetch_assoc($rows);
    }
}

$pageTitle       = $product ? $product['name'] : 'Fragrance not found';
$pageDescription = $product
    ? mb_substr(strip_tags($product['description']), 0, 155)
    : 'That fragrance is not in our archive.';

include('includes/header.php');

if (!$product) {
    echo crumb(['Home' => 'index.php', 'Collection' => 'perfumes.php', 'Not found' => null]);
    ?>
    <section class="section shell">
        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
            <h1 style="font-size:var(--t-h2)">We could not find that fragrance</h1>
            <p>It may have been unpublished or renamed. The rest of the shelf is still here.</p>
            <a class="btn btn--primary" href="perfumes.php">View the collection</a>
        </div>
    </section>
    <?php
    include('includes/outro.php');
    include('includes/footer.php');
    exit;
}

list($stockText, $stockLow) = stockLabel($product['qty']);
$inStock  = (int) $product['qty'] > 0;
$maxQty   = max(1, min(10, (int) $product['qty']));

echo crumb([
    'Home'       => 'index.php',
    'Collection' => 'perfumes.php',
    $product['name'] => null,
]);
?>

<section class="section shell detail">

    <div class="detail__media">
        <?php if (trim($product['image_path']) !== '') { ?>
            <img src="images/<?= e($product['image_path']) ?>"
                 alt="<?= e($product['name']) ?> bottle"
                 width="800" height="1000" decoding="async">
        <?php } else { ?>
            <?= pendingMedia($product['name']) ?>
        <?php } ?>
    </div>

    <div class="detail__body stack" data-qty data-min="1" data-max="<?= $maxQty ?>">

        <p style="color:var(--fg-faint);font-size:var(--t-sm);margin:0"><?= e($stockText) ?></p>
        <h1 class="detail__title" style="margin-top:var(--s-2)"><?= e($product['name']) ?></h1>
        <?= priceMarkup($product, 'detail__price') ?>

        <p style="color:var(--fg-muted)"><?= e($product['description']) ?></p>

        <dl class="notes">
            <div>
                <dt>Composition</dt>
                <dd><?= e($product['perfume_notes']) ?></dd>
            </div>
        </dl>

        <dl class="spec">
            <div>
                <dt>Bottle</dt>
                <dd><?= e($product['volume']) ?></dd>
            </div>
            <div>
                <dt>Availability</dt>
                <dd><?= $inStock ? (int) $product['qty'] . ' in stock' : 'Sold out' ?></dd>
            </div>
        </dl>

        <?php if (!$inStock) { ?>
            <p style="color:var(--warn);font-size:var(--t-sm)">
                This bottle is out of stock. Check back soon or browse something similar.
            </p>
            <a class="btn btn--ghost btn--lg" href="perfumes.php">Browse the collection</a>

        <?php } else { ?>
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:var(--s-4)">
                <div class="qty">
                    <button type="button" data-qty-step="-1" aria-label="Decrease quantity" disabled>
                        <i class="fa-solid fa-minus" aria-hidden="true"></i>
                    </button>
                    <input type="number" value="1" min="1" max="<?= $maxQty ?>"
                           data-qty-input aria-label="Quantity" readonly>
                    <button type="button" data-qty-step="1" aria-label="Increase quantity"
                            <?= $maxQty <= 1 ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                </div>

                <button class="btn btn--primary btn--lg" type="button"
                        data-add-to-cart="<?= (int) $product['id'] ?>">
                    <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Add to cart
                </button>
            </div>
        <?php } ?>
    </div>
</section>

<?php
// More from the same house, matched on the first word of the product name.
$firstWord   = strtok($product['name'], ' ');
$productId   = (int) $product['id'];
$likePattern = $firstWord . '%';
$related_stmt = mysqli_prepare($con,
    "SELECT * FROM perfumes
     WHERE status = 1 AND name LIKE ? AND id <> ?
     ORDER BY price DESC LIMIT 4");
mysqli_stmt_bind_param($related_stmt, 'si', $likePattern, $productId);
mysqli_stmt_execute($related_stmt);
$related = mysqli_stmt_get_result($related_stmt);

if ($related && mysqli_num_rows($related) > 0) { ?>
    <section class="section section--sunken">
        <div class="shell">
            <div class="section-head">
                <div><h2>More from <?= e($firstWord) ?></h2></div>
            </div>
            <div class="card-grid card-grid--4">
                <?php foreach ($related as $p) { include('includes/product-card.php'); } ?>
            </div>
        </div>
    </section>
<?php }

include('includes/outro.php');
include('includes/footer.php');
?>
