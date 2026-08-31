<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'Add product';
include('Includes/header.php');
?>

<div class="page-head">
    <div>
        <h1>Add product</h1>
        <p>New bottles go live immediately unless you switch Published off.</p>
    </div>
    <a class="btn btn--quiet" href="perfume.php">Cancel</a>
</div>

<form action="Includes/code.php" method="post" enctype="multipart/form-data">
    <div class="grid-2">

        <section class="card">
            <div class="card__head"><h2>Details</h2></div>
            <div class="card__body">
                <div class="form-grid form-grid--2">

                    <div class="field span-2">
                        <label for="p-name">Name</label>
                        <input class="input" id="p-name" name="name" type="text" required placeholder="Dior Sauvage">
                        <span class="field__hint">Start with the house, so the brand pages pick it up.</span>
                    </div>

                    <div class="field span-2">
                        <label for="p-notes">Composition</label>
                        <textarea class="textarea" id="p-notes" name="perfume_notes" rows="3"
                                  placeholder="Top: Bergamot, Pepper. Heart: Lavender. Base: Ambroxan, Cedar."></textarea>
                    </div>

                    <div class="field span-2">
                        <label for="p-desc">Description</label>
                        <textarea class="textarea" id="p-desc" name="description" rows="4"
                                  placeholder="How it wears, and who it is for."></textarea>
                    </div>

                    <div class="field">
                        <label for="p-volume">Bottle size</label>
                        <input class="input" id="p-volume" name="volume" type="text" placeholder="100 ml">
                    </div>

                    <div class="field">
                        <label for="p-price">Price in Taka</label>
                        <input class="input" id="p-price" name="price" type="number" min="0" step="1" required placeholder="13800">
                    </div>

                    <div class="field">
                        <label for="p-qty">Stock</label>
                        <input class="input" id="p-qty" name="qty" type="number" min="0" step="1" required placeholder="24">
                    </div>

                </div>
            </div>
        </section>

        <div class="side-stack">

            <section class="card">
                <div class="card__head"><h2>Photo</h2></div>
                <div class="card__body">
                    <div class="upload">
                        <img class="preview-img" data-image-preview alt="" hidden>
                        <div class="preview-img preview-img--empty" data-image-placeholder>
                            <i class="fa-solid fa-image" aria-hidden="true"></i>
                        </div>
                        <div class="field">
                            <label for="p-image">Image file</label>
                            <input class="file" id="p-image" name="image_path" type="file"
                                   accept="image/jpeg,image/png,image/webp" data-image-input>
                            <span class="field__hint">JPG, PNG or WebP. Portrait crops sit best in the grid.</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__head"><h2>Visibility</h2></div>
                <div class="card__body switch-stack">
                    <label class="switch">
                        <input type="checkbox" name="status" checked>
                        <span class="switch__track"></span>
                        <span class="switch__text">Published
                            <small>Visible in the storefront</small>
                        </span>
                    </label>
                    <label class="switch">
                        <input type="checkbox" name="trending">
                        <span class="switch__track"></span>
                        <span class="switch__text">Featured
                            <small>Appears on the homepage</small>
                        </span>
                    </label>
                </div>
            </section>

        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit" name="addperfume_btn">Save product</button>
        <a class="btn btn--quiet" href="perfume.php">Cancel</a>
    </div>
</form>

<?php include('Includes/footer.php'); ?>
