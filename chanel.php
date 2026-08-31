<?php
session_start();
include('functions/brandsearchfunctions.php');

$pageTitle       = 'Chanel';
$pageDescription = 'Chanel fragrances in stock at Perfume Store, delivered across Bangladesh.';
include('includes/header.php');

$collectionTitle  = 'Chanel';
$collectionLede   = 'From No 5 to Bleu de Chanel, the reference point almost every other house is measured against.';
$collectionCrumbs = ['Home' => 'index.php', 'Brands' => 'perfumes.php', 'Chanel' => null];
$collectionRows   = getChanel();

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
