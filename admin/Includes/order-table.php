<?php
/**
 * Shared order list. Used by orders.php, active-orders.php and previous-orders.php.
 *
 * Expects:
 *   $listTitle  string
 *   $listLede   string
 *   $listRows   mysqli_result
 *   $emptyText  string
 */
?>
<div class="page-head">
    <div>
        <h1><?= e($listTitle) ?></h1>
        <p><?= e($listLede) ?></p>
    </div>
    <p class="stat__k" style="margin:0">
        <?= $listRows ? mysqli_num_rows($listRows) : 0 ?> order<?= ($listRows && mysqli_num_rows($listRows) === 1) ? '' : 's' ?>
    </p>
</div>

<section class="card">
    <div class="card__body card__body--flush">
        <?php if ($listRows && mysqli_num_rows($listRows) > 0) { ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Tracking</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Placed</th>
                            <th scope="col">Status</th>
                            <th scope="col">Payment</th>
                            <th scope="col" class="num">Total</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listRows as $o) { ?>
                            <tr>
                                <td class="table__title"><?= e($o['tracking_no']) ?></td>
                                <td>
                                    <?= e($o['name']) ?>
                                    <span class="table__sub"><?= e($o['contacts']) ?></span>
                                </td>
                                <td><?= e(date('j M Y', strtotime($o['created_at']))) ?></td>
                                <td>
                                    <span class="badge" data-status="<?= (int) $o['status'] ?>">
                                        <?= e(orderStatus($o['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" data-payment-status="<?= e($o['payment_status'] ?? 'cod') ?>">
                                        <?= e(ucfirst($o['payment_status'] ?? 'cod')) ?>
                                    </span>
                                    <span class="table__sub"><?= e($o['payment_mode']) ?></span>
                                </td>
                                <td class="num"><?= taka($o['total_price']) ?></td>
                                <td class="table__actions">
                                    <a class="btn btn--ghost btn--sm"
                                       href="order-history.php?trackid=<?= urlencode($o['tracking_no']) ?>">Open</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="empty">
                <span class="empty__icon"><i class="fa-solid fa-inbox" aria-hidden="true"></i></span>
                <p><?= e($emptyText) ?></p>
            </div>
        <?php } ?>
    </div>
</section>
