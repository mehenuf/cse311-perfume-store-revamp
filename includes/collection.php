<?php
/**
 * Shared collection listing, used by perfumes.php and every brand page.
 *
 * Expects:
 *   $collectionTitle  string
 *   $collectionLede   string
 *   $collectionCrumbs array  label => href (null for the current page)
 *   $collectionRows   mysqli_result
 */
echo crumb($collectionCrumbs);
?>
<section class="section shell">
    <header class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)"><?= e($collectionTitle) ?></h1>
            <p><?= e($collectionLede) ?></p>
        </div>
        <?php if ($collectionRows && mysqli_num_rows($collectionRows) > 0) { ?>
            <p style="color:var(--text-faint);font-size:var(--t-sm);margin:0">
                <?= mysqli_num_rows($collectionRows) ?> available
            </p>
        <?php } ?>
    </header>

    <?php if ($collectionRows && mysqli_num_rows($collectionRows) > 0) { ?>
        <div class="card-grid card-grid--4">
            <?php foreach ($collectionRows as $p) { include(__DIR__ . '/product-card.php'); } ?>
        </div>
    <?php } else { ?>
        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-wind" aria-hidden="true"></i></span>
            <h2 style="font-size:var(--t-h3)">Nothing here yet</h2>
            <p>This shelf is empty for now. Browse the full collection to find something else.</p>
            <a class="btn btn--primary" href="perfumes.php">View the collection</a>
        </div>
    <?php } ?>
</section>
