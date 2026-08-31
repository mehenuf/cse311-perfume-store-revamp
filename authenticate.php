<?php
 if (!(isset($_SESSION['auth']))) {
  $_SESSION['message'] = "You have to login to use shopping cart!";
  header('login.php');
 }
?>