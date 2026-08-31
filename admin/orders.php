<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'New orders';
include('Includes/header.php');
include('../functions/myfunctions.php');

$listTitle = 'New orders';
$listLede  = 'Placed and waiting to be processed.';
$listRows  = getOrders();
$emptyText = 'Nothing is waiting. Every order has been picked up.';

include('Includes/order-table.php');
include('Includes/footer.php');
