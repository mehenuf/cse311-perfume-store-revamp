<?php
session_start();
include('includes/header.php');

if (isset($_SESSION['auth'])) {
    header('Location: index.php');
}



?>
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                <div class="card mx-auto" style="width: 50%;">
                    <div class="card-header">
                        <h3>Registration Form</h3>
                    </div>
                    <div class="card-body">
                        <form action="functions/authcode.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Write your full name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Enter an username">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter a valid email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" placeholder="Enter your address">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" name="contacts" class="form-control" placeholder="Provide your contact number">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="text" name="dob" class="form-control" placeholder="Birth date (YYYY-MM-DD)">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter a valid password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="repassword" class="form-control" placeholder="Repeat your password">
                            </div>
                            <button type="submit" name="signup_btn" class="btn btn-outline-primary">Submit</button>
                        </form>
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