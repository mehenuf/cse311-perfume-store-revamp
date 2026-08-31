<?php
session_start();

$_SESSION = [];
session_destroy();

session_start();
$_SESSION['message'] = 'You have been signed out.';

header('Location: ../index.php');
exit;
