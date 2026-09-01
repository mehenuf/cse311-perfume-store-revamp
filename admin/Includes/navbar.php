<?php
/** Admin topbar. Expects $adminTitle and $adminUser from header.php. */
?>
<header class="topbar">
    <button class="topbar__burger" type="button" data-rail-toggle
            aria-label="Toggle navigation" aria-expanded="false">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>

    <h1 class="topbar__title"><?= e($adminTitle) ?></h1>

    <div class="topbar__actions">
        <span class="topbar__who">Signed in as <?= e($adminUser) ?></span>

        <!-- View store lives here as well as in the rail. On a phone the rail
             is off-canvas, and leaving the workspace should never require
             opening a menu first. -->
        <a class="topbar__act" href="../index.php">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            <span class="topbar__act-label">View store</span>
        </a>

        <button class="topbar__act" type="button" data-theme-toggle
                aria-label="Switch colour theme">
            <svg class="theme-toggle__sun" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.3"/>
                <path d="M12 2.6v2.2M12 19.2v2.2M2.6 12h2.2M19.2 12h2.2M5.4 5.4l1.6 1.6M17 17l1.6 1.6M18.6 5.4 17 7M7 17l-1.6 1.6"
                      stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
            </svg>
            <svg class="theme-toggle__moon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M20.4 14.6A8.6 8.6 0 0 1 9.4 3.6a8.6 8.6 0 1 0 11 11Z"
                      stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
            </svg>
        </button>

        <a class="topbar__act" href="logout.php" aria-label="Sign out">
            <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
            <span class="topbar__act-label">Sign out</span>
        </a>
    </div>
</header>
