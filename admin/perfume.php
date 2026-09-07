<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'Products';
include('Includes/header.php');
include('../functions/myfunctions.php');
require_once('../includes/helpers.php');

$rows = mysqli_query($con, "SELECT * FROM perfumes ORDER BY status DESC, name ASC");
$justUpdatedId = isset($_GET['updated']) ? (int) $_GET['updated'] : 0;
?>

<div class="page-head">
    <div>
        <h1>Products</h1>
        <p><?= $rows ? mysqli_num_rows($rows) : 0 ?> in the catalogue. Unpublish rather than delete, so order history stays intact.</p>
    </div>
    <a class="btn btn--primary" href="add.php">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Add product
    </a>
</div>

<section class="card">
    <div class="card__head">
        <div>
            <h2>Catalogue</h2>
            <p style="color:var(--fg-faint);font-size:var(--t-xs);margin-top:.2rem">
                Edit price or stock directly and click Save -- no need to open the full edit page for these two.
            </p>
        </div>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($rows && mysqli_num_rows($rows) > 0) { ?>
            <div class="table-wrap">
                <table class="table" data-inline-edit-table>
                    <thead>
                        <tr>
                            <th scope="col" colspan="2">Product</th>
                            <th scope="col">Volume</th>
                            <th scope="col" class="num">Price (Tk)</th>
                            <th scope="col" class="num">Stock</th>
                            <th scope="col">Discount</th>
                            <th scope="col">Visibility</th>
                            <th scope="col" class="table__actions-head"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $item) {
                            $live = (int) $item['status'] === 1;
                            $qty  = (int) $item['qty'];
                            $qtyStyle = $qty === 0 ? 'color:var(--danger)' : ($qty <= 12 ? 'color:var(--warn)' : '');
                            $pricing = perfumePricing($item);
                        ?>
                            <tr id="perfume-<?= (int) $item['id'] ?>" data-inline-edit-row data-perfume-id="<?= (int) $item['id'] ?>"
                                <?= $justUpdatedId === (int) $item['id'] ? 'data-just-updated' : '' ?>>
                                <td style="width: 84px; padding-right: 0;">
                                    <?php if (trim($item['image_path']) !== '') { ?>
                                        <img class="table__thumb" src="../images/<?= e($item['image_path']) ?>" alt="" loading="lazy">
                                    <?php } else { ?>
                                        <span class="table__thumb table__thumb--empty" title="No photograph yet">
                                            <i class="fa-regular fa-image" aria-hidden="true"></i>
                                        </span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <p class="table__title"><?= e($item['name']) ?></p>
                                    <?php if ((int) $item['trending'] === 1) { ?>
                                        <span class="badge badge--gold">Featured</span>
                                    <?php } ?>
                                </td>
                                <td><?= e($item['volume']) ?></td>
                                <td class="num">
                                    <input class="input input--inline" type="number" min="0" step="1"
                                           value="<?= (int) $item['price'] ?>"
                                           data-inline-field="price" aria-label="Price for <?= e($item['name']) ?>">
                                </td>
                                <td class="num">
                                    <input class="input input--inline" type="number" min="0" step="1"
                                           value="<?= $qty ?>" style="<?= $qtyStyle ?>"
                                           data-inline-field="qty" aria-label="Stock for <?= e($item['name']) ?>">
                                </td>
                                <td>
                                    <?php if ($pricing['active']) { ?>
                                        <span class="badge" style="color:var(--danger);border-color:color-mix(in srgb, var(--danger) 45%, transparent)">
                                            -<?= $pricing['percent'] ?>%
                                        </span>
                                    <?php } elseif ((int) $item['discount_percent'] > 0) { ?>
                                        <span class="badge" title="Scheduled, not active yet or already ended">Scheduled</span>
                                    <?php } else { ?>
                                        <span style="color:var(--fg-faint)">&mdash;</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge <?= $live ? 'badge--live' : 'badge--hidden' ?>">
                                        <?= $live ? 'Published' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td class="table__actions">
                                    <div class="table__actions-inner">
                                        <button class="btn btn--ghost btn--sm" type="button" data-inline-save>
                                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save
                                        </button>
                                        <a class="btn btn--ghost btn--sm" href="edit-perfume.php?id=<?= (int) $item['id'] ?>">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="empty">
                <span class="empty__icon"><i class="fa-solid fa-bottle-droplet" aria-hidden="true"></i></span>
                <p>No products yet. Add the first one to open the store.</p>
                <a class="btn btn--primary btn--sm" href="add.php">Add product</a>
            </div>
        <?php } ?>
    </div>
</section>

<?php include('Includes/footer.php'); ?>
