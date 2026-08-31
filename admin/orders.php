<?php
include('../middleware/adminmiddleware.php');
include('Includes/header.php');
include('../functions/myfunctions.php')
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4 class="fw-bolder">Order History</h4><hr>
            <div class="card">
                <div class="card-header text-white fw-bold bg-dark">
                    Orders
                    
                    <a href="orders.php" class="btn btn-primary btn-sm float-end"><i class="fa-solid fa-reply"></i></a>
            
                    <a href="previous-orders.php" class="btn btn-primary me-3 btn-sm float-end">Previous Orders</a>
                    <a href="active-orders.php" class="btn btn-primary me-3 btn-sm float-end">Active Orders</a>
                </div>
                <div class="card-body bg-primary" id="order_table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-dark table-responsive">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Tracking ID</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $displayOrders = getOrders();

                                if (mysqli_num_rows($displayOrders) > 0) {
                                    foreach ($displayOrders as $key) {
                                ?>
                                        <tr>
                                            <td><?= $key['id'] ?></td>
                                            <td><?= $key['name'] ?></td>
                                            <td><?= $key['tracking_no']; ?></td>
                                            <td><?= $key['total_price']; ?></td>
                                            <td><?= $key['created_at']; ?></td>
                                            <td><a href="order-history.php?trackid=<?= $key['tracking_no']; ?>" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye"></i> View Details</a></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="5">No Orders Yet</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php
    include('includes/footer.php');
    ?>