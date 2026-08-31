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
            <a class="text-white" href="checkout.php" style="text-decoration: none;">
                Checkout
            </a>
        </h6>
    </div>
</div>
<div class="py-5">
    <div class="container">
        <div class="card shadow">
            <div class="card-header">
                <h3 class="fw-bolder">Checkout</h3>
            </div>
            <div class="card-body">
                <form action="functions/placeorder.php" method="post">
                    <div class="row">
                        <div class="col-md-7">
                            <h4 class="fw-bolder">Basic Details</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Full Name</label><br>
                                    <input type="text" name="name" required placeholder="Enter your full legal name." class="form-control w-100">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Email</label><br>
                                    <input type="email" name="email" required placeholder="Enter your e-mail." class="form-control w-100">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Contact</label><br>
                                    <input type="text" name="contact" required placeholder="Enter your contact no." class="form-control w-100">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Zip Code</label><br>
                                    <input type="text" name="zipcode" required placeholder="Enter your area zip code." class="form-control w-100">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Address</label><br>
                                    <textarea name="address" required class="form-control" placeholder="Provide your address." rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h4>Your Cart</h4>
                            <hr>

                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6>Product</h6>
                                </div>
                                <div class="col-md-3">
                                    <h6>Price</h6>
                                </div>
                                <div class="col-md-3">
                                    <h6>Quantity</h6>
                                </div>
                            </div>
                            <?php
                            $cart_items = displayCart();
                            $totalcost = 0;
                            foreach ($cart_items as $key) {
                            ?>
                                <div class="mb-1 border">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="images/<?= $key['image_path'] ?>" alt="<?= $key['perfume_name'] ?>" class="w-75">
                                        </div>
                                        <div class="col-md-4">
                                            <h5><?= $key['perfume_name'] ?></h5>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Tk. <?= $key['price'] * $key['perfume_quantity'] ?></h5>
                                        </div>
                                        <div class="col-md-3">
                                            <h5><?= $key['perfume_quantity'] ?></h5>
                                        </div>
                                    </div>
                                </div>
                            <?php
                                $totalcost += $key['price'] * $key['perfume_quantity'];
                            }
                            ?>
                            <hr>
                            <h5 class="fw-bold">Total bill: <span class="float-start">Tk. <?= $totalcost ?></span></h5>
                            <button type="submit" name="placeorder" class="btn btn-outline-success btn-sm float-sm-start w-100">Confirm Order</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
include('includes/outro.php');
include('includes/footer.php');
?>