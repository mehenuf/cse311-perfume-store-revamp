    <footer class="footer pt-5">
        <div class="container-fluid">
            <div class="row align-items-center justify-content-lg-between">
                <div class="col-lg-12">
                    <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                        <li class="nav-item">
                            <a href="../index.php" class="nav-link text-muted" target="_blank">Perfume Store</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-muted" target="_blank">About Us <i class="fa-solid fa-circle-info fa-flip" style="color: #000000;"></i></a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-muted" target="_blank">Contacts <i class="fa-solid fa-address-card fa-flip" style="color: #000000;"></i></a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-muted" target="_blank">Services <i class="fa-solid fa-bars-progress" style="color: #000000;"></i></a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </footer>
    </main>
    <script src="assets/js/jquery-3.6.0.min.js"></script> <!-- jQuery should come first -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="assets/js/alertify.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script src="assets/js/mybootstrap.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script>
        <?php
        if (isset($_SESSION['message'])) {
        ?>
            alertify.set('notifier', 'position', 'top-right');
            alertify.error('<?= $_SESSION['message']; ?>');
        <?php
            unset($_SESSION['message']);
        }
        ?>
    </script>

    </body>

    </html>