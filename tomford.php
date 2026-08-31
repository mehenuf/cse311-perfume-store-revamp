<?php
session_start();
include('includes/header.php');
include('functions/brandsearchfunctions.php');
/*

*/
?>
<div class="py-3 bg-secondary" >
    <div class="container">
        <h6 class="text-white">
            <a class="text-white" href="perfumes.php"  style="text-decoration: none;">
                Brands /
            </a>
            <a class="text-white" href="tomford.php"  style="text-decoration: none;">
                Tom Ford
            </a>
        </h6>
    </div>
</div>
<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="fw-bold">Tom Ford Collections</h1>
                <hr>
                <div class="row">
                    <?php
                    $displayperfume = getTomFord();
                    if (mysqli_num_rows($displayperfume) > 0) {
                        foreach ($displayperfume as $key) {
                    ?>
                            <div class="col-md-3 mb-4">
                                <a href="display-perfume.php?name=<?= $key['name'] ?>">
                                    <div class="card shadow">
                                        <div class="card-body">
                                            <img src="images/<?= $key['image_path']; ?>" alt="<?= $key['name']; ?>" class="w-100">
                                            <h6 class="text-center text-black" id="problematic-header"><?= $key['name']; ?></h6><br>
                                            <label for="" class="text-black"><b>Volume: </b><?= $key['volume']; ?></label><br>
                                            <label for="" class="text-danger"><b class="text-black">Price : </b><?= $key['price']; ?></label>
                                        </div>
                                </a>
                            </div>
                </div>
        <?php
                        }
                    } else {
                        echo "No perfumes found";
                    }
        ?>
            </div>
        </div>
    </div>
</div>

</div>



<?php
include('includes/footer.php');
?>