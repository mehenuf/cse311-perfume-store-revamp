
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        Admin Dashboard
    </title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />

    <!-- Font Awesomes Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- CSS Files -->
    <link id="pagestyle" href="assets/css/material-dashboard.min.css" rel="stylesheet" />

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/brands.min.css" integrity="sha512-W/zrbCncQnky/EzL+/AYwTtosvrM+YG/V6piQLSe2HuKS6cmbw89kjYkp3tWFn1dkWV7L1ruvJyKbLz73Vlgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/fontawesome.min.css" integrity="sha512-siarrzI1u3pCqFG2LEzi87McrBmq6Tp7juVsdmGY1Dr8Saw+ZBAzDzrGwX3vgxX1NkioYNCFOVC0GpDPss10zQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/regular.min.css" integrity="sha512-rOTVxSYNb4+/vo9pLIcNAv9yQVpC8DNcFDWPoc+gTv9SLu5H8W0Dn7QA4ffLHKA0wysdo6C5iqc+2LEO1miAow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/solid.min.css" integrity="sha512-P9pgMgcSNlLb4Z2WAB2sH5KBKGnBfyJnq+bhcfLCFusrRc4XdXrhfDluBl/usq75NF5gTDIMcwI1GaG5gju+Mw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/svg-with-js.min.css" integrity="sha512-4oP5WpLD1feGamTDxyKyYjJj9a15AlPfKOt78LpGmZ+XfrUcuC7hjHVTuzhJhO4pPvi3RHL6CI2Tyjdoik3AnA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/v4-font-face.min.css" integrity="sha512-sx5f0oBw5bYJXpvIAFXqCP3p5heQFrIDUJEUu2ja7WbmFHBHKY565OjQK2PQM+8PruMwcDR18WEIOse4qEBJ8Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/v4-shims.min.css" integrity="sha512-fWfO/7eGDprvp7/UATnfhpPDgF33fetj94tDv9q0z/WN4PDYiTP97+QcV1QWgpbkb+rUp76g6glID5mdf/K+SQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/v5-font-face.min.css" integrity="sha512-5zrDFDfoCqAqA6xPCDWHHy8cOXlRGgCy+/wh4eUwzJXAwyvBeBzCJ4AqVlasi+Dxr5b9qabJw67ocz+qsax4eQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script rel="stylesheet" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" integrity="sha512-uKQ39gEGiyUJl4AI6L+ekBdGKpGw4xJ55+xyJG7YFlJokPNYegn9KwQ3P8A7aFQAUtUsAQHep+d/lrGqrbPIDQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script rel="stylesheet" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/brands.min.js" integrity="sha512-IZeK0c+nwCpZdoWLUoguVYEnBOwOnS3eTyS5Eg57YCk41x2NphG1E/vSa886whDSXG7vGauI8mmbP5PI/VC5LQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script rel="stylesheet" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/conflict-detection.min.js" integrity="sha512-tOK04OMrEtOhHdTJSiClQ6wlAHB4BizsjbKbt8KRLLfN1xeCl84CdOW0B++kTNYPp+VDlgJg+jrWX6FuCDx7kg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script rel="stylesheet" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/fontawesome.min.js" integrity="sha512-64O4TSvYybbO2u06YzKDmZfLj/Tcr9+oorWhxzE3yDnmBRf7wvDgQweCzUf5pm2xYTgHMMyk5tW8kWU92JENng==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script rel="stylesheet" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/regular.min.js" integrity="sha512-kSAGSlODsZwG7bMv/Hydyvybjk+WOz4oEqQiWYwpCxQ7/7yXMFynr2QrvNc2myZW/7wyi0IF2TXZZWMeg8AGhw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script rel="stylesheet" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/solid.min.js" integrity="sha512-s6yNeC6faUgveCQocceGXVia7ciAebyTH7hRNazwZa2FHhnxX22qaGeb9d3a8PUKdnoHo3T3bYI/0ZOjmgWkNg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script rel="stylesheet" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/v4-shims.min.js" integrity="sha512-6n3X18GAJomziz6HVPbmyBOEZeoB8+unwEBTRXFs3IZTRYYCkbXNAWNV68uHujamEvDBqaGe2FTW19o81h1/RA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    


    <!-- For showing alert in add perfume -->
    <!-- CSS -->
    <!--
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    -->
    <link rel="stylesheet" href="assets/css/alertify.min.css"/>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"/>
    <style>
        .form-control {
            border: 1px solid #b3a1a1 !important;
            padding: 8px 10px;
        }
    </style>
</head>

<body class="g-sidenav-show  bg-gray-200">
    <?php
    include('sidebar.php');
    ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <?php
        include('navbar.php');
        ?>