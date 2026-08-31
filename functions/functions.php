<?php
require_once(__DIR__ . '/../config/dbcon.php');



function getAllPublished($table){
    global $con;
    $query = "SELECT * FROM $table 
    WHERE status = 1 
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}
function getAllTrending($table){
    global $con;
    $query = "SELECT * FROM $table 
    WHERE status = 1 
    AND trending = 1
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}
function getData($table){
    global $con;
    $query = "SELECT * FROM $table;";
    return $query_run = mysqli_query($con, $query);
}

function getViaID($table, $id){
    global $con;
    $query = "SELECT * FROM perfumes WHERE id = '$id';";
    return $query_run = mysqli_query($con, $query);
}

function redirect($url, $message){
    $_SESSION['message'] = $message;
    header('Location:'. $url);
    exit();
}

function getViaNameActive($table, $name){
    global $con;
    $query = "SELECT * FROM $table WHERE name = '$name' AND status = 1;";
    return $query_run = mysqli_query($con, $query);
}

function displayCart() {
    global $con;
    $userid = $_SESSION['auth_user']['user_id'];
    $query =   "SELECT c.id as cart_id, c.perfume_id as perfume_id, c.perfume_qty as perfume_quantity, p.id, p.name as perfume_name, p.image_path, p.price
                FROM cart c, perfumes p
                WHERE c.perfume_id = p.id
                AND c.user_id = '$userid'
                ORDER BY c.id DESC;";
    return $query_run = mysqli_query($con, $query);
}

function getOrderHistory() {
    global $con;
    $userid = $_SESSION['auth_user']['user_id'];
    $query =    "SELECT * FROM orders WHERE user_id = '$userid' 
                ORDER BY created_at DESC;";
    return $query_run = mysqli_query($con, $query);
}

function validateTrackID($tracking_no){
    global $con;
    $userid = $_SESSION['auth_user']['user_id'];
    $query =    "SELECT * FROM orders
                WHERE
                tracking_no = '$tracking_no' AND
                user_id = '$userid';";
    return $query_run = mysqli_query($con, $query);
}
?>