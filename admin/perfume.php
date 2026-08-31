<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'Products';
include('Includes/header.php');
include('../functions/myfunctions.php');

$rows = mysqli_query($con, "SELECT * FROM perfumes ORDER BY status DESC, name ASC");
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
    <div class="card__body card__body--flush">
        <?php if ($rows && mysqli_num_rows($rows) > 0) { ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" colspan="2">Product</th>
                            <th scope="col">Volume</th>
                            <th scope="col" class="num">Price</th>
                            <th scope="col" class="num">Stock</th>
                            <th scope="col">Visibility</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $item) {
                            $live = (int) $item['status'] === 1;
                            $qty  = (int) $item['qty'];
                            $qtyStyle = $qty === 0 ? 'color:var(--danger)' : ($qty <= 12 ? 'color:var(--warn)' : '');
                        ?>
                            <tr>
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
                                <td class="num"><?= taka($item['price']) ?></td>
                                <td class="num" style="<?= $qtyStyle ?>"><?= $qty ?></td>
                                <td>
                                    <span class="badge <?= $live ? 'badge--live' : 'badge--hidden' ?>">
                                        <?= $live ? 'Published' : 'Hidden' ?>
                                    </span>
                                </td>
                                <td class="table__actions">
                                    <a class="btn btn--ghost btn--sm" href="edit-perfume.php?id=<?= (int) $item['id'] ?>">Edit</a>
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
