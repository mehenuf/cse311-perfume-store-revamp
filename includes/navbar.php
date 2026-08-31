<nav class="navbar navbar-expand-lg navbar-dark sticky-top bg-dark shadow">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Perfume Store</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item ">
                    <a class="nav-link active" aria-current="page" href="index.php">Home <i class="fa-solid fa-house fa-bounce"></i></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Perfumes
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="perfumes.php">All Perfumes</a></li>
                        <li><a class="dropdown-item" href="#">Male</a></li>
                        <li><a class="dropdown-item" href="#">Female</a></li>
                        <li><a class="dropdown-item" href="#">Unisex</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Brands
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="dior.php">Dior</a></li>
                        <li><a class="dropdown-item" href="chanel.php">Chanel</a></li>
                        <li><a class="dropdown-item" href="tomford.php">Tom Ford</a></li>
                        <li><a class="dropdown-item" href="mancera.php">Mancera</a></li>
                        <li><a class="dropdown-item" href="lattafa.php">Lattafa</a></li>
                        <li><a class="dropdown-item" href="hugoboss.php">Hugo Boss</a></li>
                    </ul>
                </li>



                <?php
                if (isset($_SESSION['auth'])) {
                ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= $_SESSION['auth_user']['email'] ?> <i class="fa-solid fa-user"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="shoppingcart.php"><i class="fa-solid fa-cart-shopping"></i> My Cart</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fa-solid fa-cart-flatbed"></i> My Orders</a></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                        </ul>
                    </li>
                    <?php
                    if ($_SESSION['admin_check'] == 1) {
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">Admin Panel <i class="fa-solid fa-user-tie fa-flip"></i></a>
                        </li>
                    <?php
                    }
                } else {
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Sign up</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Log in</a>
                    </li>
                <?php
                }

                ?>


            </ul>
        </div>
    </div>
</nav>