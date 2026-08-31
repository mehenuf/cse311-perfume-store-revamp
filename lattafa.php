<?php
session_start();
include('functions/brandsearchfunctions.php');

$pageTitle       = 'Lattafa';
$pageDescription = 'Lattafa fragrances in stock at Perfume Store, delivered across Bangladesh.';
include('includes/header.php');

$collectionTitle  = 'Lattafa';
$collectionLede   = 'The Emirati house that changed what a budget bottle is allowed to smell like.';
$collectionCrumbs = ['Home' => 'index.php', 'Brands' => 'perfumes.php', 'Lattafa' => null];
$collectionRows   = getlattafa();

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
