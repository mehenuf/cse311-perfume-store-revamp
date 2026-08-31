<?php
/**
 * Guard for pages that require a logged-in customer.
 *
 * The previous version called header('login.php'), which is not a redirect at
 * all: it emits a malformed header, execution continues, and the page renders
 * for anonymous visitors before failing on $_SESSION['auth_user'].
 */
if (!isset($_SESSION['auth'])) {
    $_SESSION['message'] = 'Please log in to continue.';
    header('Location: login.php');
    exit;
}
