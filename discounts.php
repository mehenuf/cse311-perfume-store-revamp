<?php
session_start();
include('functions/functions.php');

$pageTitle       = 'On sale now';
$pageDescription = 'Every fragrance currently discounted at Perfume Store, for a limited time.';
include('includes/header.php');

$collectionTitle  = 'On sale now';
$collectionLede   = 'Time-boxed prices on bottles moving fast -- while the discount lasts.';
$collectionCrumbs = ['Home' => 'index.php', 'On sale' => null];
$collectionRows   = getActiveDiscounts('perfumes');

include('includes/collection.php');
include('includes/outro.php');
include('includes/footer.php');
