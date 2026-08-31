<?php
session_start();
include('functions/functions.php');

$pageTitle       = 'The collection';
$pageDescription = 'Every fragrance currently in stock at Perfume Store, from designer signatures to niche releases.';
include('includes/header.php');

$collectionTitle  = 'The collection';
$collectionLede   = 'Everything on the shelf right now, sealed and ready to ship.';
$collectionCrumbs = ['Home' => 'index.php', 'Collection' => null];
$collectionRows   = getAllPublished('perfumes');

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
