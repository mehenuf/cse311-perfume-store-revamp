<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'Edit product';
include('Includes/header.php');
include('../functions/myfunctions.php');

$data = null;
if (isset($_GET['id'])) {
    $rows = getViaID('perfumes', (int) $_GET['id']);
    if ($rows && mysqli_num_rows($rows) > 0) {
        $data = mysqli_fetch_assoc($rows);
    }
}

if (!$data) {
    ?>
    <div class="page-head"><div><h1>Product not found</h1></div></div>
    <section class="card">
        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
            <p>That product id does not match anything in the catalogue.</p>
            <a class="btn btn--primary btn--sm" href="perfume.php">Back to products</a>
        </div>
    </section>
    <?php
    include('Includes/footer.php');
    exit;
}
?>

<div class="page-head">
    <div>
        <h1><?= e($data['name']) ?></h1>
        <p>Product #<?= (int) $data['id'] ?></p>
    </div>
    <div style="display:flex;gap:var(--s-3)">
        <a class="btn btn--quiet" href="perfume.php">Back</a>
        <a class="btn btn--ghost" href="../display-perfume.php?name=<?= urlencode($data['name']) ?>" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View in store
        </a>
    </div>
</div>

<form action="Includes/code.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="get_id" value="<?= (int) $data['id'] ?>">
    <input type="hidden" name="old_image" value="<?= e($data['image_path']) ?>">

    <div class="grid-2">

        <section class="card">
            <div class="card__head"><h2>Details</h2></div>
            <div class="card__body">
                <div class="form-grid form-grid--2">

                    <div class="field span-2">
                        <label for="p-name">Name</label>
                        <input class="input" id="p-name" name="name" type="text" required value="<?= e($data['name']) ?>">
                    </div>

                    <div class="field span-2">
                        <label for="p-notes">Composition</label>
                        <textarea class="textarea" id="p-notes" name="perfume_notes" rows="3"><?= e($data['perfume_notes']) ?></textarea>
                    </div>

                    <div class="field span-2">
                        <label for="p-desc">Description</label>
                        <textarea class="textarea" id="p-desc" name="description" rows="4"><?= e($data['description']) ?></textarea>
                    </div>

                    <div class="field">
                        <label for="p-volume">Bottle size</label>
                        <input class="input" id="p-volume" name="volume" type="text" value="<?= e($data['volume']) ?>">
                    </div>

                    <div class="field">
                        <label for="p-price">Price in Taka</label>
                        <input class="input" id="p-price" name="price" type="number" min="0" step="1" required
                               value="<?= (int) $data['price'] ?>">
                    </div>

                    <div class="field">
                        <label for="p-qty">Stock</label>
                        <input class="input" id="p-qty" name="quantity" type="number" min="0" step="1" required
                               value="<?= (int) $data['qty'] ?>">
                    </div>

                </div>
            </div>
        </section>

        <div class="side-stack">

            <section class="card">
                <div class="card__head"><h2>Photo</h2></div>
                <div class="card__body">
                    <div class="upload">
                        <?php $hasShot = trim($data['image_path']) !== ''; ?>
                        <!-- An empty image_path would make this src "../images/",
                             a request for the directory itself: a 404 and a broken
                             image icon. A product with no photograph yet gets the
                             same designed placeholder the list uses. -->
                        <img class="preview-img" data-image-preview
                             src="<?= $hasShot ? '../images/' . e($data['image_path']) : '' ?>"
                             alt="<?= $hasShot ? 'Current photo of ' . e($data['name']) : '' ?>"
                             <?= $hasShot ? '' : 'hidden' ?>>
                        <?php if (!$hasShot) { ?>
                            <span class="preview-img table__thumb--empty" data-image-placeholder
                                  title="No photograph yet">
                                <i class="fa-regular fa-image" aria-hidden="true"></i>
                            </span>
                        <?php } ?>
                        <div class="field" style="width:100%">
                            <label for="p-image">Replace image</label>
                            <input class="file" id="p-image" name="image_path" type="file"
                                   accept="image/jpeg,image/png,image/webp" data-image-input>
                            <span class="field__hint">Leave empty to keep the current photo.</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__head"><h2>Visibility</h2></div>
                <div class="card__body switch-stack">
                    <label class="switch">
                        <input type="checkbox" name="status" <?= (int) $data['status'] === 1 ? 'checked' : '' ?>>
                        <span class="switch__track"></span>
                        <span class="switch__text">Published
                            <small>Visible in the storefront</small>
                        </span>
                    </label>
                    <label class="switch">
                        <input type="checkbox" name="trending" <?= (int) $data['trending'] === 1 ? 'checked' : '' ?>>
                        <span class="switch__track"></span>
                        <span class="switch__text">Featured
                            <small>Appears on the homepage</small>
                        </span>
                    </label>
                </div>
            </section>

            <section class="card">
                <div class="card__head"><h2>Discount</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="p-discount-percent">Percentage off</label>
                            <input class="input" id="p-discount-percent" name="discount_percent"
                                   type="number" min="0" max="90" step="1"
                                   value="<?= (int) $data['discount_percent'] ?>">
                            <span class="field__hint">The discounted price is calculated for you. Set to 0 to remove the discount.</span>
                        </div>
                        <div class="form-grid form-grid--2">
                            <div class="field">
                                <label for="p-discount-start">Starts</label>
                                <input class="input" id="p-discount-start" name="discount_starts_at" type="datetime-local"
                                       value="<?= e(toDatetimeLocal($data['discount_starts_at'])) ?>">
                                <span class="field__hint">Empty starts immediately.</span>
                            </div>
                            <div class="field">
                                <label for="p-discount-end">Ends</label>
                                <input class="input" id="p-discount-end" name="discount_ends_at" type="datetime-local"
                                       value="<?= e(toDatetimeLocal($data['discount_ends_at'])) ?>">
                                <span class="field__hint">Empty runs until you turn it off.</span>
                            </div>
                        </div>
                        <?php $pricing = perfumePricing($data); if ($pricing['active']) { ?>
                            <p style="color:var(--danger);font-size:var(--t-xs)">
                                Live now: <?= e(taka($pricing['original'])) ?> &rarr; <?= e(taka($pricing['final'])) ?>
                                (-<?= $pricing['percent'] ?>%)
                            </p>
                        <?php } ?>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit" name="save_edit_btn">Save changes</button>
        <a class="btn btn--quiet" href="perfume.php">Cancel</a>
    </div>
</form>

<section class="card" style="margin-top:var(--s-6)">
    <div class="card__head"><h2>Delete</h2></div>
    <div class="card__body" style="display:grid;gap:var(--s-4);justify-items:start">
        <p style="color:var(--fg-muted);font-size:var(--t-sm);max-width:62ch">
            Deleting removes the product and its photo permanently. If this bottle has ever been
            ordered it cannot be deleted, because past invoices reference it. Switch Published off
            instead, and it disappears from the storefront while the history stays intact.
        </p>
        <form action="Includes/code.php" method="post"
              data-confirm="Delete this product permanently? This cannot be undone.">
            <input type="hidden" name="delete_id" value="<?= (int) $data['id'] ?>">
            <button class="btn btn--danger" type="submit" name="dlt_perfume_btn">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i> Delete product
            </button>
        </form>
    </div>
</section>

<?php include('Includes/footer.php'); ?>
