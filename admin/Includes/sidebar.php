<?php
//echo substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], "/")+1);
$page = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], "/") + 1);
//echo $page;
?>
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-primary" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 <?= $page == "add.php" ? 'active-bg-gradient-primary' : '' ?>" href="add.php" target="_blank">
            <span class="ms-1 font-weight-bold text-white">Admin Control</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto  max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white active-bg-gradient-primary" href="../index.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-house fa-bounce"></i>
                    </div>
                    <span class="nav-link-text ms-1">Homepage </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= $page == "index.php" ? 'active-bg-gradient-primary' : '' ?>" href="index.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Admin Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= $page == "perfume.php" ? 'active-bg-gradient-primary' : '' ?>" href="perfume.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">list_alt</i>
                    </div>
                    <span class="nav-link-text ms-1">Perfume Listing</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= $page == "orders.php" ? 'active-bg-gradient-secondary' : '' ?>" href="orders.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">shopping_bag</i>
                    </div>
                    <span class="nav-link-text ms-1">Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= $page == "add.php" ? 'active-bg-gradient-secondary' : '' ?>" href="add.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">add</i>
                    </div>
                    <span class="nav-link-text ms-1">Add Perfume</span>
                </a>
            </li>
            <li class="nav-item mt-3 justify-content-center align-bottom text-center">
                <a href="logout.php" class="btn btn-dark text-white <?= $page == "logout.php" ? 'active-bg-gradient-primary' : '' ?>">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">logout</i>
                        <span class="nav-link-text ms-1">Logout</span>
                    </div>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">
    </div>
</aside>