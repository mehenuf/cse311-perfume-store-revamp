<?php
include('../middleware/adminmiddleware.php');
include('../admin/Includes/header.php');

include('../functions/myfunctions.php');
?>


<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Perfumes
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Notes</th>
                                    <th>Description</th>
                                    <th>Volume</th>
                                    <th>Image</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $perfume = getData("perfumes");
                                if (mysqli_num_rows($perfume) > 0) {
                                    foreach ($perfume as $item) {
                                ?>
                                        <tr>
                                            <td><?= $item['id']; ?></td>
                                            <td><?= $item['name']; ?></td>
                                            <td style="white-space: pre-wrap;"><?= $item['perfume_notes']; ?></td>
                                            <td style="white-space: pre-wrap;"><?= $item['description']; ?></td>
                                            <td><?= $item['volume']; ?></td>
                                            <td>
                                                <img src="../images/<?= $item['image_path']; ?>" alt="<?= $item['name']; ?>" width="150px" height="150px">
                                            </td>
                                            <td><?= "Tk." . $item['price']; ?></td>
                                            <td><?= $item['qty'] ?></td>
                                            <td><?= $item['status'] == '1' ? "Published" : "Unpublished"; ?></td>
                                            <td>
                                                <a href="edit-perfume.php?id=<?= $item['id']; ?>" class="btn btn-outline-danger">Edit</a>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    $_SESSION['message'] = "No perfumes found :(";
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('Includes/footer.php');
?>