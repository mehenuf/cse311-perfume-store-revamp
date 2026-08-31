<?php
session_start();
include('functions/brandsearchfunctions.php');

$pageTitle       = 'Tom Ford';
$pageDescription = 'Tom Ford fragrances in stock at Perfume Store, delivered across Bangladesh.';
include('includes/header.php');

$collectionTitle  = 'Tom Ford';
$collectionLede   = 'Loud, expensive and completely unapologetic. Black Orchid and Tuscan Leather lead the line.';
$collectionCrumbs = ['Home' => 'index.php', 'Brands' => 'perfumes.php', 'Tom Ford' => null];
$collectionRows   = getTomFord();

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
