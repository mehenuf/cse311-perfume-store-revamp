<?php
session_start();
include('functions/brandsearchfunctions.php');

$pageTitle       = 'Dior';
$pageDescription = 'Dior fragrances in stock at Perfume Store, delivered across Bangladesh.';
include('includes/header.php');

$collectionTitle  = 'Dior';
$collectionLede   = 'The house that gave us Sauvage, Fahrenheit and the iris of Dior Homme.';
$collectionCrumbs = ['Home' => 'index.php', 'Brands' => 'perfumes.php', 'Dior' => null];
$collectionRows   = getDior();

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
