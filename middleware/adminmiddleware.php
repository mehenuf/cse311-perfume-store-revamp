<?php
session_start();

    if (isset($_SESSION['auth'])) {
        if ($_SESSION['admin_check'] !=1) {
            $_SESSION['message'] == 'Unauthorized Access. Only for admins.';
            header('Location: ../index.php');
        } 
        
    } else {
        $_SESSION['message'] == 'Login to get access';
            header('Location: ../login.php');
    }
    
?>