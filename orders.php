<?php
session_start();
include('authenticate.php');
include('functions/functions.php');
include('includes/header.php');
?>
<div class="py-3 bg-secondary">
    <div class="container">
        <h6 class="text-white">
            <a class="text-white" href="index.php" style="text-decoration: none;">
                Home /
            </a>
            <a class="text-white" href="orders.php" style="text-decoration: none;">
                My Orders
            </a>
        </h6>
    </div>
</div>
<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered table-responsive table-striped table-dark">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tracking ID</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $displayOrders = getOrderHistory();

                        if (mysqli_num_rows($displayOrders) > 0) {
                            foreach ($displayOrders as $key) {
                        ?>
                                <tr>
                                    <td><?= $key['id'] ?></td>
                                    <td><?= $key['tracking_no']; ?></td>
                                    <td><?= $key['total_price']; ?></td>
                                    <td><?= $key['created_at']; ?></td>
                                    <td>
                                        <?php
                                            if ($key['status'] == 0) {
                                                echo "Processing";
                                            } elseif ($key['status'] == 1) {
                                                echo "Completed";
                                            } elseif ($key['status'] == 2) {
                                                echo "Shipped";
                                            } elseif ($key['status'] == 3) {
                                                echo "Delivered";
                                            } elseif ($key['status'] == 4) {
                                                echo "Cancelled";
                                            }
                                        ?>
                                    </td>
                                    <td><a href="order-details.php?trackid=<?= $key['tracking_no']; ?>" class="btn btn-info btn-sm"><i class="fa-solid fa-eye"></i> View Details</a></td>
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


<?php
include('includes/outro.php');
include('includes/footer.php');
?>