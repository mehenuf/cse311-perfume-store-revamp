<?php
session_start();
include('functions/functions.php');

$pageTitle       = 'For her';
$pageDescription = 'Fragrances for her at Perfume Store, from designer signatures to niche releases.';
include('includes/header.php');

$collectionTitle  = 'For her';
$collectionLede   = 'Designer and niche fragrance for her, plus the unisex bottles that wear just as well.';
$collectionCrumbs = ['Home' => 'index.php', 'For her' => null];
$collectionRows   = getByGender('perfumes', 'women');
$collectionShowGenderFilter = false; // the page is already the gender filter

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
