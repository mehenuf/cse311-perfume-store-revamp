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
            <a class="text-white" href="shoppingcart.php" style="text-decoration: none;">
                Shopping Cart
            </a>
        </h6>
    </div>
</div>
<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div id="shopcart">
                    <?php
                    $cart_items = displayCart();
                    if (mysqli_num_rows($cart_items) > 0) {
                    ?>
                        <div class="card mx-auto shadow">
                            <div class="card-header">
                                <h4>Your Cart</h4>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <h6>Product</h6>
                                    </div>
                                    <div class="col-md-3">
                                        <h6>Price</h6>
                                    </div>
                                    <div class="col-md-2">
                                        <h6>Quantity</h6>
                                    </div>
                                    <div class="col-md-2">
                                        <h6>Action</h6>
                                    </div>
                                </div>
                                <?php
                                foreach ($cart_items as $key) {
                                ?>
                                    <div class="card perfume_data shadow-sm mb-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <img src="images/<?= $key['image_path'] ?>" alt="<?= $key['perfume_name'] ?>" class="w-75">
                                            </div>
                                            <div class="col-md-3">
                                                <h5><?= $key['perfume_name'] ?></h5>
                                            </div>
                                            <div class="col-md-3">
                                                <h5><?= $key['price'] ?></h5>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="hidden" class="perfumeID" value="<?= $key['perfume_id'] ?>">
                                                <div class="input-group mb-3" style="width: 125px;">
                                                    <button class="input-group-text decrement-btn updateQty">-</button>
                                                    <input type="text" class="form-control text-center perfume-qty bg-white" value="<?= $key['perfume_quantity'] ?>" disabled>
                                                    <button class="input-group-text increment-btn updateQty">+</button>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-outline-danger btn-sm delete_cart_item" value="<?= $key['cart_id'] ?>"><i class="fa-solid fa-trash-can me-1"></i> Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="float-start">
                                <a href="checkout.php" class="btn btn-outline-primary w-100">Checkout</a>
                            </div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="card card-body shadow-blur text-center text-danger">
                            <h4 class="py-3">
                                Your cart is empty!!!
                            </h4>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
include('includes/outro.php');
include('includes/footer.php');
?>