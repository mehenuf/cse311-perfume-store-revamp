<?php
session_start();
require_once('includes/helpers.php');
require_once('functions/brandsearchfunctions.php');

$pageTitle       = 'All houses';
$pageDescription = 'Every fragrance house carried at Perfume Store, from designer signatures to niche releases.';
include('includes/header.php');

$houses = brandList();
?>

<?= crumb(['Home' => 'index.php', 'Houses' => null]) ?>

<section class="section shell">
    <header class="section-head">
        <div>
            <h1 style="font-size:var(--t-h1)">Houses worth knowing</h1>
            <p>From the reference designer signatures to the niche bottles that outlast them.</p>
        </div>
        <p style="color:var(--fg-faint);font-size:var(--t-sm);margin:0"><?= count($houses) ?> houses</p>
    </header>

    <div class="brand-grid brand-grid--all">
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

<?php
include('includes/outro.php');
include('includes/footer.php');
