<?php
session_start();
include('functions/functions.php');
include('functions/brandsearchfunctions.php');

$pageTitle       = '';
$pageDescription = 'Designer and niche fragrance, curated and delivered across Bangladesh. '
                 . 'Dior, Chanel, Tom Ford, Mancera, Lattafa and Hugo Boss.';

$trending = getAllTrending('perfumes');

// Four bottles for the hero mosaic, and real counts for the figures below.
$heroShots = mysqli_query($con,
    "SELECT name, image_path FROM perfumes
     WHERE status = 1 AND trending = 1
     ORDER BY price DESC LIMIT 3");

$publishedCount = 0;
if ($r = mysqli_query($con, "SELECT COUNT(*) AS n FROM perfumes WHERE status = 1")) {
    $row = mysqli_fetch_assoc($r);
    $publishedCount = (int) $row['n'];
}

// Houses shown on the homepage, straight from the registry.
$houses = array_slice(brandList(), 0, 6, true);

include('includes/header.php');
?>

<!-- Hero: asymmetric split, copy against a four-bottle mosaic -->
<section class="shell hero">
    <div class="hero__copy">
        <h1 class="hero__title">The bottle people <em>remember</em> you by.</h1>
        <div class="hero__rule" aria-hidden="true"></div>
        <p class="hero__sub">
            Designer and niche fragrance, sourced sealed and delivered anywhere in Bangladesh.
        </p>
        <div class="hero__cta">
            <a class="btn btn--primary btn--lg" href="perfumes.php">Browse the collection</a>
            <a class="btn btn--ghost btn--lg" href="#houses">Shop by house</a>
        </div>
    </div>

    <div class="hero__art" aria-hidden="true">
        <?php
        $plate = 0;
        if ($heroShots && mysqli_num_rows($heroShots) > 0) {
            foreach ($heroShots as $shot) {
                if (++$plate > 3) break;   // the plate holds three
        ?>
                <figure class="hero__plate">
                    <img src="images/<?= e($shot['image_path']) ?>" alt=""
                         width="600" height="750" decoding="async"
                         <?= $plate === 1 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                </figure>
        <?php }
        }
        ?>
    </div>
</section>

<!-- Trending: horizontal rail, panned by drag or arrows -->
<section class="section section--sunken">
    <div class="shell">
        <div class="section-head">
            <div>
                <h2>Moving fastest right now</h2>
                <p>The bottles our customers keep coming back for.</p>
            </div>
            <div class="rail-nav">
                <button class="rail-btn" type="button" data-rail-prev="trending-rail" aria-label="Scroll left">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </button>
                <button class="rail-btn" type="button" data-rail-next="trending-rail" aria-label="Scroll right">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="shell">
        <?php if ($trending && mysqli_num_rows($trending) > 0) { ?>
            <div class="rail" id="trending-rail" data-rail tabindex="0" aria-label="Trending fragrances">
                <?php foreach ($trending as $p) { include('includes/product-card.php'); } ?>
            </div>
        <?php } else { ?>
            <div class="empty">
                <span class="empty__icon"><i class="fa-solid fa-wind" aria-hidden="true"></i></span>
                <h3 style="font-size:var(--t-h3)">No featured bottles yet</h3>
                <p>Nothing is flagged as trending at the moment. The full collection is still open.</p>
                <a class="btn btn--primary" href="perfumes.php">View the collection</a>
            </div>
        <?php } ?>
    </div>
</section>

<!-- Houses: image-backed tiles, one cell per house -->
<section class="section shell" id="houses">
    <div class="section-head">
        <div>
            <h2>Houses worth knowing</h2>
            <p>From the reference designer signatures to the niche bottles that outlast them.</p>
        </div>
    </div>

    <div class="brand-grid">
        <?php foreach ($houses as $slug => $house) { ?>
            <a class="brand-tile" href="brands/<?= $slug ?>.php" data-reveal="wipe">
                <img src="images/<?= e($house['shot']) ?>" alt="" loading="lazy" decoding="async">
                <span>
                    <span class="brand-tile__name"><?= e($house['label']) ?></span><br>
                    <span class="brand-tile__count"><?= countBrandProducts($slug) ?> in stock</span>
                </span>
            </a>
        <?php } ?>
    </div>
</section>

<!-- Editorial: the only image-and-text split on this page -->
<section class="section section--sunken">
    <div class="shell editorial">
        <div class="editorial__media" data-reveal="settle">
            <img src="assets/images/coco_noir.jpg" alt="Fragrance bottles arranged on a dark surface"
                 loading="lazy" decoding="async" width="900" height="720">
        </div>
        <div class="editorial__body" data-reveal="settle">
            <h2 style="font-size:var(--t-h2)">Fragrance is the one thing you wear that nobody sees.</h2>
            <p>
                We stock what we would actually wear. Every bottle arrives sealed, is checked against
                its batch code, and ships the same week. No decants sold as full bottles, no grey-market
                guesswork.
            </p>
            <p>
                If a fragrance is on this site, it is in the room with us. That is the whole standard.
            </p>

            <div class="stat-row">
                <div>
                    <div class="stat__n"><?= $publishedCount ?></div>
                    <div class="stat__l">Bottles in stock</div>
                </div>
                <div>
                    <div class="stat__n"><?= count(brandList()) ?></div>
                    <div class="stat__l">Houses carried</div>
                </div>
                <div>
                    <div class="stat__n">64</div>
                    <div class="stat__l">Districts delivered</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Full catalogue entry point -->
<section class="section shell">
    <div class="section-head">
        <div>
            <h2>The full shelf</h2>
            <p>Everything currently in stock, from the entry-level workhorses to the niche heavyweights.</p>
        </div>
        <a class="btn btn--ghost" href="perfumes.php">See all <?= $publishedCount ?></a>
    </div>

    <?php
    $shelf = mysqli_query($con,
        "SELECT * FROM perfumes WHERE status = 1 ORDER BY price DESC LIMIT 8");
    if ($shelf && mysqli_num_rows($shelf) > 0) { ?>
        <div class="card-grid card-grid--4">
            <?php foreach ($shelf as $p) { include('includes/product-card.php'); } ?>
        </div>
    <?php } ?>
</section>

<?php
include('includes/outro.php');
include('includes/footer.php');
?>
