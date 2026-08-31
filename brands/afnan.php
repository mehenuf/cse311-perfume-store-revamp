<?php
/**
 * Afnan brand page.
 *
 * Lives one level down, so $basePath tells the shared partials to reach back
 * up for assets and links. Everything else comes from the brand registry in
 * includes/helpers.php.
 */
session_start();
$basePath = '../';
require_once(__DIR__ . '/../includes/helpers.php');
require_once(__DIR__ . '/../functions/brandsearchfunctions.php');

$brandSlug = 'afnan';
$brand     = brandBySlug($brandSlug);

$pageTitle       = $brand['label'];
$pageDescription = $brand['label'] . ' fragrances in stock at Perfume Store, delivered across Bangladesh.';
include(__DIR__ . '/../includes/header.php');

$collectionTitle  = $brand['label'];
$collectionLede   = $brand['lede'];
$collectionCrumbs = [
    'Home'   => '../index.php',
    'Houses' => '../perfumes.php',
    $brand['label'] => null,
];
$collectionRows   = getBrandProducts($brandSlug);

include(__DIR__ . '/../includes/collection.php');
include(__DIR__ . '/../includes/outro.php');
include(__DIR__ . '/../includes/footer.php');
