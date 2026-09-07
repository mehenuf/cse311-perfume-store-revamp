<?php
session_start();
include('functions/functions.php');

$pageTitle       = 'For him';
$pageDescription = 'Fragrances for him at Perfume Store, from designer signatures to niche releases.';
include('includes/header.php');

$collectionTitle  = 'For him';
$collectionLede   = "Men's fragrances, plus every unisex bottle in the catalogue -- it suits this shelf too.";
$collectionCrumbs = ['Home' => 'index.php', 'For him' => null];
$collectionRows   = getByGender('perfumes', 'men');
$collectionShowGenderFilter = false; // the page is already the gender filter

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
