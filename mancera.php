<?php
session_start();
include('functions/brandsearchfunctions.php');

$pageTitle       = 'Mancera';
$pageDescription = 'Mancera fragrances in stock at Perfume Store, delivered across Bangladesh.';
include('includes/header.php');

$collectionTitle  = 'Mancera';
$collectionLede   = 'Paris niche with enormous performance. Red Tobacco and Cedrat Boise are the ones people stop you for.';
$collectionCrumbs = ['Home' => 'index.php', 'Brands' => 'perfumes.php', 'Mancera' => null];
$collectionRows   = getMancera();

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
