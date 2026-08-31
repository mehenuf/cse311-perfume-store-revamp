<?php
include('../middleware/adminmiddleware.php');
$pageTitle = 'Closed orders';
include('Includes/header.php');
include('../functions/myfunctions.php');

$listTitle = 'Closed orders';
$listLede  = 'Delivered or cancelled. Kept for the record.';
$listRows  = getPreviousOrders();
$emptyText = 'No orders have been closed yet.';

include('Includes/order-table.php');
include('Includes/footer.php');
