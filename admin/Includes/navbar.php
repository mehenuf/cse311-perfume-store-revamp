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
        <a class="btn btn--quiet btn--sm" href="logout.php">
            <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out
        </a>
    </div>
</header>
