<?php
require_once(__DIR__ . '/../config/dbcon.php');

/**
 * $basePath is '' for pages at the web root and '../' for pages one level
 * down (brands/). Every link and asset URL below goes through it.
 */
$basePath = isset($basePath) ? $basePath : '';

$currentPage = basename($_SERVER['PHP_SELF']);
$isAuthed    = isset($_SESSION['auth']);
$isAdmin     = $isAuthed && isset($_SESSION['admin_check']) && $_SESSION['admin_check'] == 1;
$userName    = $isAuthed && isset($_SESSION['auth_user']['username'])
    ? $_SESSION['auth_user']['username'] : '';

// Live cart count for the header badge.
$cartCount = 0;
if ($isAuthed && isset($_SESSION['auth_user']['user_id'])) {
    $uid = (int) $_SESSION['auth_user']['user_id'];
    $res = mysqli_query($con, "SELECT COALESCE(SUM(perfume_qty), 0) AS n FROM cart WHERE user_id = $uid");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $cartCount = (int) $row['n'];
    }
}

$navBrands = brandList();

/** Marks the active link for both styling and assistive tech. */
function navCurrent($page, $currentPage)
{
    return $page === $currentPage ? ' aria-current="page"' : '';
}
?>
<nav class="nav" data-nav aria-label="Primary">
    <div class="shell nav__inner">

        <a class="nav__brand" href="<?= $basePath ?>index.php">
            <!-- Authored mark: a stopper and flacon, drawn in the world's own
                 hairline grammar rather than borrowed from an icon set. -->
            <svg class="nav__mark" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M9.6 2.5h4.8v2.9H9.6z" stroke="currentColor" stroke-width="1.1"/>
                <path d="M8 5.4h8l1.6 3.1v11.1a1.9 1.9 0 0 1-1.9 1.9H8.3a1.9 1.9 0 0 1-1.9-1.9V8.5L8 5.4Z"
                      stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/>
                <path d="M9.4 11.6h5.2" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
            </svg>
            <span>Perfume<b>Store</b></span>
        </a>

        <ul class="nav__links">
            <li><a class="nav__link" href="<?= $basePath ?>index.php"<?= navCurrent('index.php', $currentPage) ?>>Home</a></li>
            <li><a class="nav__link" href="<?= $basePath ?>perfumes.php"<?= navCurrent('perfumes.php', $currentPage) ?>>Collection</a></li>

            <li class="nav__group" data-nav-group data-open="false">
                <a class="nav__link" href="<?= $basePath ?>perfumes.php" data-nav-trigger
                   aria-expanded="false" aria-haspopup="true">Houses</a>
                <ul class="nav__menu nav__menu--cols">
                    <?php foreach ($navBrands as $slug => $b) { ?>
                        <li><a href="<?= $basePath ?>brands/<?= $slug ?>.php"><?= e($b['label']) ?></a></li>
                    <?php } ?>
                </ul>
            </li>
        </ul>

        <div class="nav__actions">
            <?php if ($isAuthed) { ?>

                <a class="nav__icon" href="<?= $basePath ?>shoppingcart.php" aria-label="Your cart">
                    <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                    <span class="nav__cart-count" data-cart-count<?= $cartCount === 0 ? ' hidden' : '' ?>><?= $cartCount ?></span>
                </a>

                <!-- Account menu. The name opens this; it never signs you out on
                     its own, which is what the previous single logout link did. -->
                <div class="nav__group nav__group--end" data-nav-group data-open="false">
                    <button class="btn btn--ghost btn--sm" type="button" data-nav-trigger
                            aria-expanded="false" aria-haspopup="true">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        <span class="nav__who"><?= e($userName) ?></span>
                        <i class="fa-solid fa-chevron-down nav__chev" aria-hidden="true"></i>
                    </button>
                    <ul class="nav__menu nav__menu--right">
                        <li class="nav__menu-head">Signed in as <b><?= e($userName) ?></b></li>
                        <li><a href="<?= $basePath ?>orders.php"><i class="fa-solid fa-receipt" aria-hidden="true"></i> My orders</a></li>
                        <li><a href="<?= $basePath ?>shoppingcart.php"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> My cart<?= $cartCount ? ' (' . $cartCount . ')' : '' ?></a></li>
                        <?php if ($isAdmin) { ?>
                            <li class="nav__menu-sep"></li>
                            <li><a href="<?= $basePath ?>admin/index.php"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Admin dashboard</a></li>
                        <?php } ?>
                        <li class="nav__menu-sep"></li>
                        <li><a href="<?= $basePath ?>logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out</a></li>
                    </ul>
                </div>

            <?php } else { ?>
                <a class="btn btn--quiet btn--sm" href="<?= $basePath ?>login.php">Log in</a>
                <a class="btn btn--primary btn--sm" href="<?= $basePath ?>register.php">Create account</a>
            <?php } ?>

            <button class="nav__toggle" type="button" data-nav-toggle aria-expanded="false"
                    aria-controls="nav-drawer" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <div class="nav__drawer" id="nav-drawer" data-nav-drawer data-open="false">
        <div>
            <div class="shell nav__drawer-inner">
                <ul>
                    <li><a href="<?= $basePath ?>index.php">Home</a></li>
                    <li><a href="<?= $basePath ?>perfumes.php">Collection</a></li>
                    <?php if ($isAuthed) { ?>
                        <li><a href="<?= $basePath ?>shoppingcart.php">Cart<?= $cartCount ? ' (' . $cartCount . ')' : '' ?></a></li>
                        <li><a href="<?= $basePath ?>orders.php">My orders</a></li>
                        <?php if ($isAdmin) { ?>
                            <li><a href="<?= $basePath ?>admin/index.php">Admin dashboard</a></li>
                        <?php } ?>
                        <li><a href="<?= $basePath ?>logout.php">Sign out</a></li>
                    <?php } else { ?>
                        <li><a href="<?= $basePath ?>login.php">Log in</a></li>
                        <li><a href="<?= $basePath ?>register.php">Create account</a></li>
                    <?php } ?>
                </ul>
                <p class="nav__drawer-label">Houses</p>
                <ul>
                    <?php foreach ($navBrands as $slug => $b) { ?>
                        <li><a href="<?= $basePath ?>brands/<?= $slug ?>.php"><?= e($b['label']) ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</nav>
