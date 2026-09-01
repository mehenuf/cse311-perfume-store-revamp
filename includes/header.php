<?php
/**
 * Storefront document head + navigation.
 *
 * Pages may set these before including this file:
 *   $pageTitle       string  appended to the site name
 *   $pageDescription string  meta description
 */
require_once(__DIR__ . '/helpers.php');

// '' at the web root, '../' for pages one level down (brands/).
$basePath = isset($basePath) ? $basePath : '';

$siteName = 'Perfume Store';
$title    = isset($pageTitle) && $pageTitle !== ''
    ? $pageTitle . ' | ' . $siteName
    : $siteName . ' | Designer and niche fragrances in Bangladesh';
$description = isset($pageDescription) && $pageDescription !== ''
    ? $pageDescription
    : 'A curated house of designer and niche fragrances, delivered across Bangladesh. Dior, Chanel, Tom Ford, Mancera, Lattafa and Hugo Boss.';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES) ?>">
    <meta name="theme-color" content="#0a0a0c" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f7f5f1" media="(prefers-color-scheme: light)">

    <title><?= htmlspecialchars($title, ENT_QUOTES) ?></title>

    <?php include(__DIR__ . '/theme-boot.php'); ?>

    <!--
        Ground colours, inline.

        The rest of the design lives in store.css. This block exists only so
        that a page can never be painted as raw black-on-white HTML if that
        one request is slow, cached wrong, or intercepted by a host's
        interstitial. It is the floor, not the design.
        Values mirror --ink / --fg in assets/css/store.css; the frontend audit
        asserts they still match.
    -->
    <style>
        html { background: #0a0a0c; color-scheme: dark; }
        body { background: #0a0a0c; color: #f1ede5; margin: 0;
               font-family: 'Jost', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        @media (prefers-color-scheme: light) {
            html:not([data-theme='dark']) { background: #f7f5f1; color-scheme: light; }
            html:not([data-theme='dark']) body { background: #f7f5f1; color: #14140f; }
        }
        html[data-theme='light'] { background: #f7f5f1; color-scheme: light; }
        html[data-theme='light'] body { background: #f7f5f1; color: #14140f; }
    </style>

    <!-- The one stylesheet that matters is the only render-blocking request. -->
    <link rel="stylesheet" id="store-css"
          href="<?= $basePath ?>assets/css/store.css?v=<?= @filemtime(__DIR__ . '/../assets/css/store.css') ?>">

    <!--
        Typography: Cormorant Garamond carries the voice, Jost does the work.
        Loaded print-first and promoted on load, so a slow font CDN delays a
        typeface but never the page. font-display: swap does the rest.
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" media="print" onload="this.media='all'"
          href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap">

    <!-- One icon stylesheet. The previous build requested Font Awesome 14 times. -->
    <link rel="stylesheet" media="print" onload="this.media='all'"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <noscript>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap">
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </noscript>

    <?php include(__DIR__ . '/stylesheet-guard.php'); ?>
</head>

<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="scroll-line" aria-hidden="true"></div>

    <?php include(__DIR__ . '/navbar.php'); ?>

    <main id="main">
