<?php
session_start();
include('functions/brandsearchfunctions.php');

$pageTitle       = 'Hugo Boss';
$pageDescription = 'Hugo Boss fragrances in stock at Perfume Store, delivered across Bangladesh.';
include('includes/header.php');

$collectionTitle  = 'Hugo Boss';
$collectionLede   = 'The dependable office wardrobe. Bottled has been quietly working since 1998.';
$collectionCrumbs = ['Home' => 'index.php', 'Brands' => 'perfumes.php', 'Hugo Boss' => null];
$collectionRows   = getHugoBoss();

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
