<?php
session_start();

/**
 * Guard for pages that require a logged-in admin.
 *
 * $basePath lets this be included from different depths: '../' for pages
 * directly under admin/ (the default), '../../' for admin/Includes/code.php.
 *
 * Every branch below MUST end in exit. A header('Location: ...') without it
 * is not a redirect: PHP keeps executing and the including page's full
 * output -- dashboard figures, customer orders, admin form handlers -- still
 * gets sent in the response body, which any client that does not follow
 * redirects (curl, fetch with redirect:'manual') can simply read.
 */
$basePath = isset($basePath) ? $basePath : '../';

if (!isset($_SESSION['auth'])) {
    $_SESSION['message'] = 'Login to get access';
    header('Location: ' . $basePath . 'login.php');
    exit;
}

if (!isset($_SESSION['admin_check']) || $_SESSION['admin_check'] != 1) {
    $_SESSION['message'] = 'Unauthorized access. Only for admins.';
    header('Location: ' . $basePath . 'index.php');
    exit;
}
