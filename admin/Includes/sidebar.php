<?php
/**
 * Admin rail. Expects $adminPage from header.php.
 *
 * Counts are live so the operator sees workload before clicking anything;
 * a nav that shows nothing is a nav you have to guess at.
 */
$pendingOrders = 0;
$activeOrders  = 0;
$liveProducts  = 0;

if (isset($con)) {
    if ($r = mysqli_query($con, "SELECT COUNT(*) n FROM orders WHERE status = 0")) {
        $row = mysqli_fetch_assoc($r); $pendingOrders = (int) $row['n'];
    }
    if ($r = mysqli_query($con, "SELECT COUNT(*) n FROM orders WHERE status IN (1,2)")) {
        $row = mysqli_fetch_assoc($r); $activeOrders = (int) $row['n'];
    }
    if ($r = mysqli_query($con, "SELECT COUNT(*) n FROM perfumes WHERE status = 1")) {
        $row = mysqli_fetch_assoc($r); $liveProducts = (int) $row['n'];
    }
}

/** Active-state attribute for a rail link. */
function railCurrent($file, $adminPage)
{
    return $file === $adminPage ? ' aria-current="page"' : '';
}
?>
<div class="rail__scrim" data-rail-scrim></div>

<aside class="rail" data-rail aria-label="Admin sections">

    <a class="rail__brand" href="index.php">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9.6 2.5h4.8v2.9H9.6z" stroke="currentColor" stroke-width="1.1"/>
            <path d="M8 5.4h8l1.6 3.1v11.1a1.9 1.9 0 0 1-1.9 1.9H8.3a1.9 1.9 0 0 1-1.9-1.9V8.5L8 5.4Z"
                  stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/>
            <path d="M9.4 11.6h5.2" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
        </svg>
        <span>Admin</span>
    </a>

    <nav class="rail__nav">

        <div class="rail__group">
            <p class="rail__label">Overview</p>
            <ul>
                <li>
                    <a class="rail__link" href="index.php"<?= railCurrent('index.php', $adminPage) ?>>
                        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard
                    </a>
                </li>
            </ul>
        </div>

        <div class="rail__group">
            <p class="rail__label">Catalogue</p>
            <ul>
                <li>
                    <a class="rail__link" href="perfume.php"<?= railCurrent('perfume.php', $adminPage) ?>>
                        <i class="fa-solid fa-bottle-droplet" aria-hidden="true"></i> Products
                        <span class="rail__count"><?= $liveProducts ?></span>
                    </a>
                </li>
                <li>
                    <a class="rail__link" href="add.php"<?= railCurrent('add.php', $adminPage) ?>>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> Add product
                    </a>
                </li>
            </ul>
        </div>

        <div class="rail__group">
            <p class="rail__label">Orders</p>
            <ul>
                <li>
                    <a class="rail__link" href="orders.php"<?= railCurrent('orders.php', $adminPage) ?>>
                        <i class="fa-solid fa-inbox" aria-hidden="true"></i> New
                        <span class="rail__count"><?= $pendingOrders ?></span>
                    </a>
                </li>
                <li>
                    <a class="rail__link" href="active-orders.php"<?= railCurrent('active-orders.php', $adminPage) ?>>
                        <i class="fa-solid fa-truck" aria-hidden="true"></i> In progress
                        <span class="rail__count"><?= $activeOrders ?></span>
                    </a>
                </li>
                <li>
                    <a class="rail__link" href="previous-orders.php"<?= railCurrent('previous-orders.php', $adminPage) ?>>
                        <i class="fa-solid fa-box-archive" aria-hidden="true"></i> Closed
                    </a>
                </li>
            </ul>
        </div>

    </nav>

    <div class="rail__foot">
        <a class="btn btn--ghost btn--sm btn--block" href="../index.php">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View store
        </a>
    </div>
</aside>
