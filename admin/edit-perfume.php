<?php
include('../middleware/adminmiddleware.php');
include('../admin/Includes/header.php');

include('../functions/myfunctions.php')
?>


<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $perfumes = getViaID("perfumes", $id);
                if (mysqli_num_rows($perfumes) > 0) {
                    $data = mysqli_fetch_array($perfumes);
            ?>
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Panel</h4>
                        </div>
                        <div class="card-body">
                            <form action="Includes/code.php" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="hidden" name="get_id" value="<?= $data['id'] ?>">
                                        <label for="">Name</label><br>
                                        <input type="text" value="<?= $data['name'] ?>" name="name" placeholder="Enter perfume name" class="form-control">
                                    </div>
                                    <div class="col-md-8">
                                        <label for="">Perfume Notes</label><br>
                                        <textarea rows="2" name="perfume_notes" placeholder="Enter perfume notes" class="form-control"><?= $data['perfume_notes'] ?></textarea>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="">Description</label><br>
                                        <textarea rows="3" name="description" placeholder="Enter description of the perfume" class="form-control"><?= $data['description'] ?></textarea>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="">volume</label><br>
                                        <input type="text" value="<?= $data['volume'] ?>" name="volume" placeholder="Enter bottle's volume (ml)" class="form-control">
                                    </div>
                                    <div class="col-md-8">
                                        <label for="">Image</label><br>
                                        <input type="file" name="image_path" placeholder="Insert perfume image" class="form-control">
                                        <label for="">Current Image</label><br>
                                        <input type="hidden" name="old_image" value="<?= $data['image_path'] ?>">
                                        <img src="../images/<?= $data['image_path'] ?>" alt="<?= $data['name'] ?>" width="150px" height="150px">
                                    </div>
                                    <div class="col-md-8">
                                        <label for="">Price</label><br>
                                        <input type="text" value="<?= $data['price'] ?>" name="price" placeholder="Enter perfume price" class="form-control">
                                    </div>
                                    <div class="col-md-8">
                                        <label for="">Quantity</label><br>
                                        <input type="text" value="<?= $data['qty'] ?>" name="quantity" placeholder="Enter perfume quantity." class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="">Trending</label>
                                        <input type="checkbox" <?= $data['trending'] ? "checked" : "" ?> name="trending">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="">Status</label>
                                        <input type="checkbox" <?= $data['status'] ? "checked" : "" ?> name="status">
                                    </div>
                                    <div class="col-md-8">
                                        <button type="submit" class="btn btn-success" name="save_edit_btn">Save</button>
                                        <form action="Includes/code.php" method="post">
                                            <input type="hidden" name="delete_id" value="<?= $data['id'] ?>">
                                            <button type="submit" class="btn btn-danger" name="dlt_perfume_btn">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>


            <?php
                } else {
                    echo "Perfume wasn't found in our database.";
                }
            } else {
                $_SESSION['message'] = "Error! Unable to fetch ID from URL.";
            }
            ?>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
?>