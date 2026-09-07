<?php
session_start();
include('functions/functions.php');

$pageTitle       = 'Featured';
$pageDescription = 'Every fragrance currently flagged as a favourite at Perfume Store.';
include('includes/header.php');

$collectionTitle  = 'Featured';
$collectionLede   = 'The bottles our customers keep coming back for -- hand-picked, not algorithmic.';
$collectionCrumbs = ['Home' => 'index.php', 'Featured' => null];
$collectionRows   = getAllTrending('perfumes');

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
