<?php
session_start();

$_SESSION = [];
session_destroy();

session_start();
$_SESSION['message'] = 'You have been logged out.';

header('Location: index.php');
exit;
