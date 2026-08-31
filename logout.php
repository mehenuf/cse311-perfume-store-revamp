<?php
session_start();
if ($_SESSION['auth']) {
    unset($_SESSION['auth']);
    unset($_SESSION['auth_user']);
    $_SESSION['message'] = 'You have logged out successfully!';
}
header('Location: index.php');
?>