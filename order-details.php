<?php
session_start();
include('authenticate.php');
include('functions/functions.php');
include('includes/header.php');

if (isset($_GET['trackid'])) {
    $tracking_no = $_GET['trackid'];
    //echo $tracking_no;
    $validation = validateTrackID($tracking_no);
    if (mysqli_num_rows($validation) <= 0) {
?>
        <h4>Something is wrong.</h4>
    <?php
        die();
    }
} else {
    ?>
    <h4>Unable to fetch tracking ID.</h4>
<?php
    die();
}
$orderData = mysqli_fetch_array($validation);
?>
<div class="py-3 bg-secondary">
    <div class="container">
        <h6 class="text-white">
            <a class="text-white" href="index.php" style="text-decoration: none;">
                Home /
            </a>
            <a class="text-white" href="orders.php" style="text-decoration: none;">
                My Orders /
            </a>
            <a class="text-white" href="order-details.php" style="text-decoration: none;">
                Order Details /
            </a>
        </h6>
    </div>
</div>
<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-white bg-dark fw-bolder">
                        Order Details
                        <a href="orders.php" class="btn btn-outline-danger btn-sm float-start"><i class="fa-solid fa-reply"></i></a>
                    </div>
                    <div class="card-body bg-dark-subtle">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Delivery Details</h4>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label for="" class="fw-bold">Name</label>
                                        <div class="border p-1">
                                            <?= $orderData['name']; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label for="" class="fw-bold">Email</label>
                                        <div class="border p-1">
                                            <?= $orderData['email']; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label for="" class="fw-bold">Contact</label>
                                        <div class="border p-1">
                                            <?= $orderData['contacts']; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label for="" class="fw-bold">Tracking No.</label>
                                        <div class="border p-1">
                                            <?= $orderData['tracking_no']; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label for="" class="fw-bold">Address</label>
                                        <div class="border p-1">
                                            <?= $orderData['address'] . ", Zipcode - " . $orderData['zipcode'] . "."; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <h4>Item List</h4>
                                <hr>

                                <table class="table table-bordered table-striped table-responsive table-dark">
                                    <thead>
                                        <tr>
                                            <th colspan="2">Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Net Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>

                                        </tr>
                                            <?php
                                                $userid = $_SESSION['auth_user']['user_id'];


                                                $order_query = "SELECT o.id as OrderID, o.tracking_no, oi.*, p.*
                                                            FROM orders o, order_item oi, perfumes p
                                                            WHERE o.user_id = '$userid' 
                                                            AND oi.order_id = o.id
                                                            AND p.id = oi.perfume_id
                                                            AND o.tracking_no = '$tracking_no';";
                                                $order_query_run = mysqli_query($con, $order_query);

                                                if (mysqli_num_rows($order_query_run) > 0) {
                                                    foreach ($order_query_run as $key) {
                                                        ?>
                                                            <tr>
                                                                <td class="align-middle">
                                                                    <img src="images/<?= $key['image_path']; ?> " alt="<?= $key['name']; ?>" width="100px" height="100px">
                                                                    
                                                                </td>
                                                                <td><?= $key['name']; ?></td>
                                                                <td class="align-middle">
                                                                    Tk. <?= $key['price']; ?>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <?= $key['perfume_qty']; ?>
                                                                </td>
                                                                <td class="align-middle">
                                                                    Tk. <?= $key['perfume_qty'] * $key['price']; ?>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                    }
                                                }

                                            ?>
                                    </tbody>
                                </table>
                                <hr>
                                <h4 class="fw-bold">Total Price: <span class="float-start text-danger"><?= $orderData['total_price']; ?></span></h4>
                                <hr>
                                <div class="border p-1 mb-3">
                                    <label for="" class="fw-semibold">Payment Mode: </label>
                                    <?= $orderData['payment_mode']; ?>
                                </div>
                                <div class="border p-1 mb-3">
                                    <label for="" class="fw-semibold">Status: </label>
                                    <?php
                                        if ($orderData['status'] == 0) {
                                            echo "Processing.";
                                        } elseif ($orderData['status'] == 1) {
                                            echo "Completed.";
                                        } elseif ($orderData['status'] == 2) {
                                            echo "Shipped.";
                                        } elseif ($orderData['status'] == 3) {
                                            echo "Delivered.";
                                        } elseif ($orderData['status'] == 4) {
                                            echo "Cancelled.";
                                        }
                                        
                                     
                                     ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
include('includes/outro.php');
include('includes/footer.php');
?>