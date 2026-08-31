<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'In progress';
include('Includes/header.php');
include('../functions/myfunctions.php');

$listTitle = 'In progress';
$listLede  = 'Confirmed or already on the way to the customer.';
$listRows  = getActiveOrders();
$emptyText = 'No orders are in progress right now.';

include('Includes/order-table.php');
include('Includes/footer.php');
