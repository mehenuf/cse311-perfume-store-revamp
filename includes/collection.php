<?php
/**
 * Shared collection listing, used by perfumes.php, discounts.php,
 * featured.php and every brand page.
 *
 * Expects:
 *   $collectionTitle  string
 *   $collectionLede   string
 *   $collectionCrumbs array  label => href (null for the current page)
 *   $collectionRows   mysqli_result
 *
 * Optional:
 *   $collectionShowSaleFilter bool  hide the "On sale" chip on a page where
 *                                   every row is already discounted (default true)
 */
$basePath = isset($basePath) ? $basePath : '';
$showSaleFilter = $collectionShowSaleFilter ?? true;
$hasRows = $collectionRows && mysqli_num_rows($collectionRows) > 0;
$rowCount = $hasRows ? mysqli_num_rows($collectionRows) : 0;
echo crumb($collectionCrumbs);
?>
<section class="section shell">
    <header class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)"><?= e($collectionTitle) ?></h1>
            <p><?= e($collectionLede) ?></p>
        </div>
        <?php if ($hasRows) { ?>
            <p class="section-head__count" data-collection-count data-total="<?= $rowCount ?>"
               role="status" aria-live="polite">
                <?= $rowCount ?> available
            </p>
        <?php } ?>
    </header>

    <?php if ($hasRows) { ?>
        <div class="filter-bar" data-collection-filters>
            <div class="filter-bar__row">
                <div class="field filter-bar__search">
                    <label for="cf-search-<?= spl_object_id($collectionRows) ?>" class="visually-hidden">Search this collection</label>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input class="input" id="cf-search-<?= spl_object_id($collectionRows) ?>" type="search"
                           placeholder="Search by name or note&hellip;" data-filter-search autocomplete="off">
                </div>
                <div class="field filter-bar__sort">
                    <label for="cf-sort-<?= spl_object_id($collectionRows) ?>" class="visually-hidden">Sort</label>
                    <select class="select" id="cf-sort-<?= spl_object_id($collectionRows) ?>" data-filter-sort>
                        <option value="default">Sort: Featured</option>
                        <option value="name-asc">Name: A&ndash;Z</option>
                        <option value="name-desc">Name: Z&ndash;A</option>
                        <option value="price-asc">Price: Low to high</option>
                        <option value="price-desc">Price: High to low</option>
                    </select>
                </div>
            </div>
            <div class="filter-bar__chips">
                <button type="button" class="chip" data-filter-toggle="stock" aria-pressed="false">In stock only</button>
                <?php if ($showSaleFilter) { ?>
                    <button type="button" class="chip" data-filter-toggle="sale" aria-pressed="false">On sale</button>
                <?php } ?>
                <button type="button" class="chip chip--reset" data-filter-reset hidden>Clear filters</button>
            </div>
        </div>

        <div class="card-grid card-grid--4" data-collection-grid>
            <?php foreach ($collectionRows as $p) { include(__DIR__ . '/product-card.php'); } ?>
        </div>

        <div class="empty" data-collection-empty hidden>
            <span class="empty__icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
            <h2 style="font-size:var(--t-h3)">Nothing matches those filters</h2>
            <p>Try a different search term, or clear the filters to see the whole shelf again.</p>
            <button type="button" class="btn btn--primary" data-filter-reset>Clear filters</button>
        </div>
    <?php } else { ?>
        <div class="empty">
            <span class="empty__icon"><i class="fa-solid fa-wind" aria-hidden="true"></i></span>
            <h2 style="font-size:var(--t-h3)">Nothing here yet</h2>
            <p>This shelf is empty for now. Browse the full collection to find something else.</p>
            <a class="btn btn--primary" href="<?= $basePath ?>perfumes.php">View the collection</a>
        </div>
    <?php } ?>
</section>
