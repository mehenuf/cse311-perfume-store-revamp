<?php
session_start();
include('includes/header.php');
include('functions/functions.php');


if (isset($_GET['name'])) {
    $name = $_GET['name'];
    $displaybyid = getViaNameActive("perfumes", $name);
    $display_perfume = mysqli_fetch_array($displaybyid);

    if ($display_perfume) {
?>
        <div class="py-3 bg-secondary">
            <div class="container">
                <h6 class="text-white">
                    <a class="text-white" href="perfumes.php" style="text-decoration: none;">
                        Perfumes /
                    </a>
                    <a class="text-white" href="perfumes.php" style="text-decoration: none;">
                        All Perfumes /
                    </a>
                    <a class="text-white" href="perfumes.php" style="text-decoration: none;">
                    </a>
                    <?= $display_perfume['name']; ?>
                </h6>
            </div>
        </div>
        <div class="bg-light py-4">
            <div class="container perfume_data mt-5" style="text-decoration: none;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="shadow">
                            <img src="images/<?= $display_perfume['image_path']; ?>" alt="<?= $display_perfume['name']; ?>">
                        </div>
                    </div>
                    <div class="col-md-8 mb-8 mt-2">
                        <h4 class="fw-bolder"><?= $display_perfume['name']; ?></h4>
                        <hr>

                        <p><?= $display_perfume['description']; ?></p>
                        <div class="card shadow-blur">
                            <div class="card-body">
                                <h6><?= $display_perfume['perfume_notes'] ?></h6>
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h5>Tk. <span class="text-success fw-bold"><?= $display_perfume['price'] ?></span></h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group mb-3" style="width: 125px;">
                                    <button class="input-group-text decrement-btn">-</button>
                                    <input type="text" class="form-control text-center perfume-qty bg-white" value="1" disabled>
                                    <button class="input-group-text increment-btn">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button class="btn btn-danger px-4 add_to_cart" value="<?= $display_perfume['id'] ?>"><i class="fa-solid fa-cart-shopping fa-beat me-2"></i> Add to Cart</button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary px-4"><i class="fa-regular fa-heart fa-beat-fade me-2"></i> Add to Wishlist</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




<?php
    } else {
        echo "Invalid Product ID";
        $_SESSION['message'] = "Product wasn't found in our archive.";
    }
} else {
    echo "Something went wrong";
}


include('includes/outro.php');
include('includes/footer.php');
?>