<?php
/**
 * Admin document head and shell.
 *
 * Pages set $pageTitle before including this file.
 * Opens: .app > (rail, .main > topbar > .content)
 * Closed by Includes/footer.php.
 */
require_once(__DIR__ . '/../../includes/helpers.php');
require_once(__DIR__ . '/../../config/dbcon.php');

$adminPage  = basename($_SERVER['PHP_SELF']);
$adminTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Dashboard';
$adminUser  = isset($_SESSION['auth_user']['username']) ? $_SESSION['auth_user']['username'] : 'admin';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#141518" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f4f5f7" media="(prefers-color-scheme: light)">

    <title><?= e($adminTitle) ?> | Perfume Store Admin</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='5' fill='%230a0a0c'/%3E%3Cpath d='M9.6 2.5h4.8v2.9H9.6z' stroke='%23d4af6a' stroke-width='1.1' fill='none'/%3E%3Cpath d='M8 5.4h8l1.6 3.1v11.1a1.9 1.9 0 0 1-1.9 1.9H8.3a1.9 1.9 0 0 1-1.9-1.9V8.5L8 5.4Z' stroke='%23d4af6a' stroke-width='1.1' stroke-linejoin='round' fill='none'/%3E%3Cpath d='M9.4 11.6h5.2' stroke='%23d4af6a' stroke-width='1.1' stroke-linecap='round'/%3E%3C/svg%3E">

    <?php include(__DIR__ . '/../../includes/theme-boot.php'); ?>

    <!-- Ground colours. Mirrors --bg / --fg in admin/assets/css/admin.css. -->
    <style>
        html { background: #141518; color-scheme: dark; }
        body { background: #141518; color: #e9eaec; margin: 0;
               font-family: 'Jost', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        @media (prefers-color-scheme: light) {
            html:not([data-theme='dark']) { background: #f4f5f7; color-scheme: light; }
            html:not([data-theme='dark']) body { background: #f4f5f7; color: #16181c; }
        }
        html[data-theme='light'] { background: #f4f5f7; color-scheme: light; }
        html[data-theme='light'] body { background: #f4f5f7; color: #16181c; }
    </style>

    <link rel="stylesheet" id="store-css"
          href="assets/css/admin.css?v=<?= @filemtime(__DIR__ . '/../assets/css/admin.css') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" media="print" onload="this.media='all'"
          href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Jost:wght@400;500&display=swap">

    <link rel="stylesheet" media="print" onload="this.media='all'"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <noscript>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Jost:wght@400;500&display=swap">
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </noscript>

    <?php $cssProbe = '--rail'; include(__DIR__ . '/../../includes/stylesheet-guard.php'); ?>
</head>

<body>
    <a class="skip-link" href="#content">Skip to content</a>

    <div class="app">
        <?php include(__DIR__ . '/sidebar.php'); ?>

        <div class="main">
            <?php include(__DIR__ . '/navbar.php'); ?>
            <main class="content" id="content">
