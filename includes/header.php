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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES) ?>">
    <meta name="theme-color" content="#0a0a0c" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f7f5f1" media="(prefers-color-scheme: light)">

    <title><?= htmlspecialchars($title, ENT_QUOTES) ?></title>

    <!--
        Typography: Cormorant Garamond carries the voice, Jost does the work.
        Loaded from Google Fonts because this project has no build step to
        self-host with; preconnect + display=swap keeps it off the critical path.
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap">

    <!-- One icon stylesheet. The previous build requested Font Awesome 14 times. -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="<?= $basePath ?>assets/css/store.css?v=<?= @filemtime(__DIR__ . '/../assets/css/store.css') ?>">
</head>

<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="scroll-line" aria-hidden="true"></div>

    <?php include(__DIR__ . '/navbar.php'); ?>

    <main id="main">
