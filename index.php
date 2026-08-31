<?php
session_start();
include('includes/header.php');
include('functions/functions.php');
include('includes/carousel.php')
?>

<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="fw-bold">Trending</h2>
                <div class="underline2 mb-2"></div>
                <hr>
                <div class="row">
                    <div class="owl-carousel owl-theme">
                        <?php
                        $displayperfume = getAllTrending("perfumes");
                        if (mysqli_num_rows($displayperfume) > 0) {
                            foreach ($displayperfume as $key) {
                        ?>
                                <div class="item">
                                    <a href="display-perfume.php?name=<?= $key['name'] ?>">
                                        <div class="card shadow-sm">
                                            <div class="card-body">
                                                <img src="images/<?= $key['image_path']; ?>" alt="<?= $key['name']; ?>" class="w-100">
                                                <h6 class="text-center text-black" id="problematic-header"><?= $key['name']; ?></h6><br>
                                                <label for="" class="text-black"><b>Volume: </b><?= $key['volume']; ?></label><br>
                                                <label for="" class="text-danger"><b class="text-black">Price : </b><?= $key['price']; ?></label>
                                            </div>
                                        </div>
                                    </a>
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
</div>

<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <?php
                if (isset($_SESSION['message'])) {
                ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Congrats!</strong> <?= $_SESSION['message']; ?>.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php
                    unset($_SESSION['message']);
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="py-5 bg-awhite">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h4>About Us</h4>
                <div class="underline mb-2"></div>
                <p>
                    Welcome to Perfume Store, a realm of exquisite fragrances where passion for perfumery takes center stage. Our collection is a symphony of scents, a tribute to timeless elegance, and a celebration of individuality.
                </p><br>
                <p>
                    At Perfume Store, we believe in the transformative power of fragrance. Our curated selection of perfumes represents a dedication to quality, creativity, and sophistication. Whether you're enchanted by floral, oriental, or niche fragrances, our range is a sensory journey like no other.
                </p><br>
                <p>
                    Perfume is more than a scent; it's an expression of self. Each bottle in our collection tells a unique story, inviting you to explore your own. Discover the magic of Perfume Store, where every drop of perfume is a brushstroke in your personal olfactory masterpiece.
                </p>
            </div>
        </div>
    </div>
</div>


<div class="py-5 bg-dark">
    <div class="container text-white ">
        <div class="row">
            <div class="col-md-3">
                <h4 class="fw-bold">Perfume Store</h4>
                <div class="underline3 mb-2"></div>
                <a href="index.php" class="text-white text-decoration-none"><i class="fa-solid fa-angle-right"></i> Home</a><br>
                <a href="shoppingcart.php" class="text-white text-decoration-none"><i class="fa-solid fa-angle-right"></i> Your Cart</a><br>
                <a href="checkout.php" class="text-white text-decoration-none"><i class="fa-solid fa-angle-right"></i> Checkout</a><br>
                <a href="orders.php" class="text-white text-decoration-none"><i class="fa-solid fa-angle-right"></i> Your Orders</a><br>
                <a href="perfumes.php" class="text-white text-decoration-none"><i class="fa-solid fa-angle-right"></i> Our Collection</a><br>
            </div>
            <div class="col-md-3 text-white">
                <h4>
                    Address
                </h4>
                <div class="underline mb-2"></div>
                <p>
                    #69, 10th Floor,<br>
                    420 Street,<br>
                    City, Country.
                </p>
                <a href="tel:+8801000000000" class="text-white"><i class="fa-solid fa-phone-volume" style="color: #ffffff;"></i> +880 10 000 00000 </a><br>
                <a href="mailto:mehenuf@gmail.com" class="text-white"><i class="fa-solid fa-at" style="color: #ffffff;"></i> mehenuf@gmail.com </a>
            </div>
            <div class="col-md-6">
            <iframe src="https://www.google.com/maps/embed?pb=!1m24!1m12!1m3!1d464.93576586811685!2d-6.580082579971572!3d55.210813652992364!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m9!3e6!4m3!3m2!1d55.2092197!2d-6.580910599999999!4m3!3m2!1d55.2106924!2d-6.5796063!5e0!3m2!1sen!2sbd!4v1698828551214!5m2!1sen!2sbd" class="w-100" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>
<div class="py-1 bg-crimson">
    <div class="text-center">
        <p class="mb-0 text-white">All rights reserved. Copyright @ Mehenuf Hossain Bhuiyan - <?= date('Y') ?></p>
    </div>
</div>

<?php
include('includes/footer.php');
?>
<script>
    $(document).ready(function() {
        var owl = $('.owl-carousel');
        owl.owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 4
                }
            },
            autoplay: true, // Enable autoplay
            autoplayTimeout: 1000, // Set the autoplay interval in milliseconds (e.g., 3000ms = 3 seconds)
            autoplayHoverPause: true // Pause autoplay when hovering over the carousel
        });
        $('.play').on('click', function() {
            owl.trigger('play.owl.autoplay', [1000])
        })
        $('.stop').on('click', function() {
            owl.trigger('stop.owl.autoplay')
        })
    });
</script>