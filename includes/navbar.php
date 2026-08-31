<?php
require_once(__DIR__ . '/../config/dbcon.php');

$currentPage = basename($_SERVER['PHP_SELF']);
$isAuthed    = isset($_SESSION['auth']);
$isAdmin     = $isAuthed && isset($_SESSION['admin_check']) && $_SESSION['admin_check'] == 1;

// Live cart count for the header badge.
$cartCount = 0;
if ($isAuthed && isset($_SESSION['auth_user']['user_id'])) {
    $uid  = (int) $_SESSION['auth_user']['user_id'];
    $res  = mysqli_query($con, "SELECT COALESCE(SUM(perfume_qty), 0) AS n FROM cart WHERE user_id = $uid");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $cartCount = (int) $row['n'];
    }
}

$brands = [
    'dior.php'      => 'Dior',
    'chanel.php'    => 'Chanel',
    'tomford.php'   => 'Tom Ford',
    'mancera.php'   => 'Mancera',
    'lattafa.php'   => 'Lattafa',
    'hugoboss.php'  => 'Hugo Boss',
];

/** Marks the active link for both styling and assistive tech. */
function navCurrent($page, $currentPage)
{
    return $page === $currentPage ? ' aria-current="page"' : '';
}
?>
<nav class="nav" data-nav aria-label="Primary">
    <div class="shell nav__inner">

        <a class="nav__brand" href="index.php">
            <span class="nav__mark" aria-hidden="true">PS</span>
            <span>Perfume Store</span>
        </a>

        <ul class="nav__links">
            <li><a class="nav__link" href="index.php"<?= navCurrent('index.php', $currentPage) ?>>Home</a></li>
            <li><a class="nav__link" href="perfumes.php"<?= navCurrent('perfumes.php', $currentPage) ?>>Collection</a></li>

            <li class="nav__group" data-nav-group data-open="false">
                <a class="nav__link" href="perfumes.php" data-nav-trigger aria-expanded="false" aria-haspopup="true">
                    Brands <i class="fa-solid fa-chevron-down" style="font-size:.62em;opacity:.6" aria-hidden="true"></i>
                </a>
                <ul class="nav__menu">
                    <?php foreach ($brands as $file => $label) { ?>
                        <li><a href="<?= $file ?>"><?= $label ?></a></li>
                    <?php } ?>
                </ul>
            </li>

            <?php if ($isAuthed) { ?>
                <li><a class="nav__link" href="orders.php"<?= navCurrent('orders.php', $currentPage) ?>>Orders</a></li>
            <?php } ?>
            <?php if ($isAdmin) { ?>
                <li><a class="nav__link" href="admin/index.php">Admin</a></li>
            <?php } ?>
        </ul>

        <div class="nav__actions">
            <?php if ($isAuthed) { ?>
                <a class="btn btn--quiet nav__cart" href="shoppingcart.php" aria-label="Your cart">
                    <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                    <span class="nav__cart-count" data-cart-count<?= $cartCount === 0 ? ' hidden' : '' ?>><?= $cartCount ?></span>
                </a>

                <a class="btn btn--ghost btn--sm" href="logout.php">
                    <span class="visually-hidden">Log out of </span><?= htmlspecialchars($_SESSION['auth_user']['username'], ENT_QUOTES) ?>
                </a>
            <?php } else { ?>
                <a class="btn btn--quiet btn--sm" href="login.php">Log in</a>
                <a class="btn btn--primary btn--sm" href="register.php">Create account</a>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="perfumes.php">Collection</a></li>
                    <?php if ($isAuthed) { ?>
                        <li><a href="shoppingcart.php">Cart<?= $cartCount ? ' (' . $cartCount . ')' : '' ?></a></li>
                        <li><a href="orders.php">Orders</a></li>
                    <?php } ?>
                    <?php if ($isAdmin) { ?>
                        <li><a href="admin/index.php">Admin panel</a></li>
                    <?php } ?>
                </ul>
                <p class="nav__drawer-label">Brands</p>
                <ul>
                    <?php foreach ($brands as $file => $label) { ?>
                        <li><a href="<?= $file ?>"><?= $label ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</nav>
