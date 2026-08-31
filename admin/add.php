<?php
include('../middleware/adminmiddleware.php');
include('../admin/Includes/header.php');

?>


<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Add Perfume</h4>
                </div>
                <div class="card-body">
                    <form action="Includes/code.php" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="">Name</label><br>
                                <input type="text" name="name" placeholder="Enter perfume name" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label for="">Perfume Notes</label><br>
                                <textarea rows="2" name="perfume_notes" placeholder="Enter perfume notes" class="form-control"></textarea>
                            </div>
                            <div class="col-md-8">
                                <label for="">Description</label><br>
                                <textarea rows="3" name="description" placeholder="Enter description of the perfume" class="form-control"></textarea>
                            </div>
                            <div class="col-md-8">
                                <label for="">volume</label><br>
                                <input type="text" name="volume" placeholder="Enter bottle's volume (ml)" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label for="">Quantity</label><br>
                                <input type="text" name="qty" placeholder="Enter perfume stock" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label for="">Image</label><br>
                                <input type="file" name="image_path" placeholder="Insert perfume image" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label for="">Price</label><br>
                                <input type="text" name="price" placeholder="Enter perfume price" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="">Trending</label>
                                <input type="checkbox" name="trending">
                            </div>
                            <div class="col-md-6">
                                <label for="">Status</label>
                                <input type="checkbox" name="status">
                            </div>
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-success" name="addperfume_btn">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
?>