<?php
include('../middleware/adminmiddleware.php');
include('Includes/header.php');
include('../functions/myfunctions.php');



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

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4 class="fw-bolder">Order Details</h4>
            <hr>
            <div class="card">
                <div class="card-header text-white bg-dark fw-bolder">
                    Order Details
                    
                    <a href="orders.php" class="btn btn-primary btn-sm float-end"><i class="fa-solid fa-reply"></i></a>
                </div>
                <div class="card-body bg-gray-100">
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
                                    <label for="" class="fw-bold">Username</label>
                                    <div class="border p-1">
                                        <?= $orderData['username']; ?>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label for="" class="fw-bold">Account Name</label>
                                    <div class="border p-1">
                                        <?= $orderData['id_name']; ?>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label for="" class="fw-bold">Account Email</label>
                                    <div class="border p-1">
                                        <?= $orderData['id_email']; ?>
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
                            <div class="table-responsive">
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
                                        $order_query = "SELECT o.id as OrderID, o.tracking_no, oi.*, p.*
                                                            FROM orders o, order_item oi, perfumes p
                                                            WHERE oi.order_id = o.id
                                                            AND p.id = oi.perfume_id
                                                            AND o.tracking_no = '$tracking_no';";
                                        $order_query_run = mysqli_query($con, $order_query);

                                        if (mysqli_num_rows($order_query_run) > 0) {
                                            foreach ($order_query_run as $key) {
                                        ?>
                                                <tr>
                                                    <td class="align-middle">
                                                        <img src="../images/<?= $key['image_path']; ?> " alt="<?= $key['name']; ?>" width="100px" height="100px">

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
                            </div>
                            <hr>
                            <h4 class="fw-bold">Total Price: <span class="float-end text-danger"><?= $orderData['total_price']; ?></span></h4>
                            <hr>
                            <div class="border p-1 mb-3">
                                <label for="" class="fw-bold">Payment Mode: </label>
                                <?= $orderData['payment_mode']; ?>
                            </div>
                            <div>
                                <label for="" class="fw-bold">Status: </label>
                                <form action="Includes/code.php" method="post">
                                    <input type="hidden" name="tracking_no" value="<?= $orderData['tracking_no']; ?>">
                                    echo <?= $orderData['tracking_no']; ?>;
                                    <select name="order_status" id="" class="form-select">
                                        <option value="0" <?= $orderData['status'] == 0 ? "selected" : "" ?>>Processing</option>
                                        <option value="1" <?= $orderData['status'] == 1 ? "selected" : "" ?>>Completed</option>
                                        <option value="2" <?= $orderData['status'] == 2 ? "selected" : "" ?>>Shipped</option>
                                        <option value="3" <?= $orderData['status'] == 3 ? "selected" : "" ?>>Delivered</option>
                                        <option value="4" <?= $orderData['status'] == 4 ? "selected" : "" ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="updateOrder_btn" class="btn btn-primary float-end w-100 mt-2">Update</button>
                                </form>
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
include('includes/footer.php');
?>