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
 *   $collectionShowSaleFilter     bool  hide the "On sale" chip on a page
 *                                       where every row is already
 *                                       discounted (default true)
 *   $collectionShowFeaturedFilter bool  hide the "Featured" chip on a page
 *                                       where every row already is one
 *                                       (default true)
 */
$basePath = isset($basePath) ? $basePath : '';
$showSaleFilter     = $collectionShowSaleFilter ?? true;
$showFeaturedFilter = $collectionShowFeaturedFilter ?? true;

// Pulled into a plain array once, rather than iterated as a mysqli_result,
// so the price/brand bounds below and the card grid loop can both read it
// -- a mysqli_result can only be walked forward, once.
$collectionList = [];
if ($collectionRows) {
    while ($row = mysqli_fetch_assoc($collectionRows)) {
        $collectionList[] = $row;
    }
}
$hasRows  = count($collectionList) > 0;
$rowCount = count($collectionList);

$minPrice = 0;
$maxPrice = 0;
$brandsPresent = [];
if ($hasRows) {
    $prices = [];
    foreach ($collectionList as $row) {
        $prices[] = perfumePricing($row)['final'];
        $slug = brandSlugOf($row['name']);
        if ($slug !== null && !isset($brandsPresent[$slug])) {
            $brand = brandBySlug($slug);
            if ($brand) {
                $brandsPresent[$slug] = $brand['label'];
            }
        }
    }
    $minPrice = (int) (floor(min($prices) / 100) * 100);
    $maxPrice = (int) (ceil(max($prices) / 100) * 100);
    if ($maxPrice <= $minPrice) {
        $maxPrice = $minPrice + 100;
    }
    asort($brandsPresent);
}
// A brand page (brands/dior.php) is already scoped to one house -- a
// checklist of one item that can never be unchecked without emptying the
// grid isn't a filter, it's a decoration. Only worth showing once a
// collection actually spans more than one house.
$showBrandFilter = count($brandsPresent) > 1;
// Unique enough to keep this partial's element ids collision-free if a page
// ever included it twice; a real object identity isn't needed here.
$uid = mt_rand(1000, 999999);

echo crumb($collectionCrumbs);
?>
<section class="section shell">
    <header class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)"><?= e($collectionTitle) ?></h1>
            <p><?= e($collectionLede) ?></p>
        </div>
    </header>

    <?php if ($hasRows) { ?>
        <div class="collection-layout" data-collection-filters>

            <button type="button" class="btn btn--ghost filters-toggle" data-filters-toggle
                    aria-expanded="false" aria-controls="filters-panel-<?= $uid ?>">
                <i class="fa-solid fa-sliders" aria-hidden="true"></i> Filters
            </button>

            <aside class="filters-panel" id="filters-panel-<?= $uid ?>" data-filters-panel>
                <div class="filters-panel__head">
                    <h2>Filters</h2>
                    <button type="button" class="chip chip--reset" data-filter-reset hidden>Clear all</button>
                </div>

                <div class="filter-group">
                    <label for="cf-search-<?= $uid ?>" class="filter-group__title">Search</label>
                    <div class="field filter-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <input class="input" id="cf-search-<?= $uid ?>" type="search"
                               placeholder="Name or note&hellip;" data-filter-search autocomplete="off">
                    </div>
                </div>

                <div class="filter-group" data-price-range
                     data-price-floor="<?= $minPrice ?>" data-price-ceil="<?= $maxPrice ?>">
                    <span class="filter-group__title" id="cf-price-label-<?= $uid ?>">Price</span>
                    <div class="price-inputs" role="group" aria-labelledby="cf-price-label-<?= $uid ?>">
                        <label class="visually-hidden" for="cf-price-min-<?= $uid ?>">Minimum price, Taka</label>
                        <input class="input price-inputs__field" id="cf-price-min-<?= $uid ?>" type="number"
                               inputmode="numeric" min="<?= $minPrice ?>" max="<?= $maxPrice ?>" step="100"
                               value="<?= $minPrice ?>" data-price-min-number>
                        <span class="price-inputs__sep" aria-hidden="true">&ndash;</span>
                        <label class="visually-hidden" for="cf-price-max-<?= $uid ?>">Maximum price, Taka</label>
                        <input class="input price-inputs__field" id="cf-price-max-<?= $uid ?>" type="number"
                               inputmode="numeric" min="<?= $minPrice ?>" max="<?= $maxPrice ?>" step="100"
                               value="<?= $maxPrice ?>" data-price-max-number>
                    </div>
                    <div class="price-slider">
                        <div class="price-slider__track">
                            <div class="price-slider__fill" data-price-fill></div>
                        </div>
                        <input class="price-slider__input" type="range" min="<?= $minPrice ?>" max="<?= $maxPrice ?>"
                               step="100" value="<?= $minPrice ?>" data-price-min-range
                               aria-label="Minimum price, Taka">
                        <input class="price-slider__input" type="range" min="<?= $minPrice ?>" max="<?= $maxPrice ?>"
                               step="100" value="<?= $maxPrice ?>" data-price-max-range
                               aria-label="Maximum price, Taka">
                    </div>
                </div>

                <?php if ($showBrandFilter) { ?>
                    <fieldset class="filter-group">
                        <legend class="filter-group__title">Brand</legend>
                        <div class="filter-checklist" data-filter-brand-list>
                            <?php foreach ($brandsPresent as $slug => $label) { ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" value="<?= e($slug) ?>" data-filter-brand>
                                    <?= e($label) ?>
                                </label>
                            <?php } ?>
                        </div>
                    </fieldset>
                <?php } ?>

                <fieldset class="filter-group">
                    <legend class="filter-group__title">For</legend>
                    <div class="chip-row" role="radiogroup" aria-label="Gender">
                        <label class="chip chip--radio">
                            <input type="radio" name="cf-gender-<?= $uid ?>" value="all" checked
                                   class="visually-hidden" data-filter-gender> All
                        </label>
                        <label class="chip chip--radio">
                            <input type="radio" name="cf-gender-<?= $uid ?>" value="men"
                                   class="visually-hidden" data-filter-gender> Men
                        </label>
                        <label class="chip chip--radio">
                            <input type="radio" name="cf-gender-<?= $uid ?>" value="women"
                                   class="visually-hidden" data-filter-gender> Women
                        </label>
                        <label class="chip chip--radio">
                            <input type="radio" name="cf-gender-<?= $uid ?>" value="unisex"
                                   class="visually-hidden" data-filter-gender> Unisex
                        </label>
                    </div>
                </fieldset>

                <fieldset class="filter-group">
                    <legend class="filter-group__title">Availability</legend>
                    <div class="chip-row">
                        <button type="button" class="chip" data-filter-toggle="stock" aria-pressed="false">In stock</button>
                        <?php if ($showSaleFilter) { ?>
                            <button type="button" class="chip" data-filter-toggle="sale" aria-pressed="false">On sale</button>
                        <?php } ?>
                        <?php if ($showFeaturedFilter) { ?>
                            <button type="button" class="chip" data-filter-toggle="featured" aria-pressed="false">Featured</button>
                        <?php } ?>
                    </div>
                </fieldset>
            </aside>

            <div class="collection-main">
                <div class="collection-main__head">
                    <p class="section-head__count" data-collection-count data-total="<?= $rowCount ?>"
                       role="status" aria-live="polite">
                        <?= $rowCount ?> available
                    </p>
                    <div class="field sort-field">
                        <label for="cf-sort-<?= $uid ?>" class="visually-hidden">Sort</label>
                        <select class="select" id="cf-sort-<?= $uid ?>" data-filter-sort>
                            <option value="default">Sort: Featured</option>
                            <option value="name-asc">Name: A&ndash;Z</option>
                            <option value="name-desc">Name: Z&ndash;A</option>
                            <option value="price-asc">Price: Low to high</option>
                            <option value="price-desc">Price: High to low</option>
                        </select>
                    </div>
                </div>

                <div class="card-grid card-grid--4" data-collection-grid>
                    <?php foreach ($collectionList as $p) { include(__DIR__ . '/product-card.php'); } ?>
                </div>

                <div class="empty" data-collection-empty hidden>
                    <span class="empty__icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                    <h2 style="font-size:var(--t-h3)">Nothing matches those filters</h2>
                    <p>Try loosening the price range or clearing a filter to see the whole shelf again.</p>
                    <button type="button" class="btn btn--primary" data-filter-reset>Clear filters</button>
                </div>
            </div>
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
