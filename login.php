<?php
session_start();
include('includes/header.php');

if (isset($_SESSION['auth'])) {
    header('Location: index.php');
}


?>
<div class="py-5 ">
    <div class="container container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <?php
                if (isset($_SESSION['message'])) {


                ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>⚠︎ Warning!</strong> <?= $_SESSION['message']; ?>.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php
                    unset($_SESSION['message']);
                }
                ?>


                <div class="card mx-auto">
                    <div class="card-header">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <form action="functions/authcode.php" method="post">

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="var_username" class="form-control" placeholder="Enter an username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="var_password" class="form-control" placeholder="Enter a valid password">
                            </div>

                            <button type="submit" name="login_btn" class="btn btn-outline-success">Login</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



<?php
include('includes/footer.php');
?>