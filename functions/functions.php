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
    $stmt = mysqli_prepare($con, "SELECT * FROM perfumes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function redirect($url, $message){
    $_SESSION['message'] = $message;
    header('Location:'. $url);
    exit();
}

function getViaNameActive($table, $name){
    global $con;
    $stmt = mysqli_prepare($con, "SELECT * FROM $table WHERE name = ? AND status = 1");
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function displayCart() {
    global $con;
    $userid = (int) $_SESSION['auth_user']['user_id'];
    $stmt = mysqli_prepare($con,
        "SELECT c.id as cart_id, c.perfume_id as perfume_id, c.perfume_qty as perfume_quantity, p.id, p.name as perfume_name, p.image_path, p.price
         FROM cart c, perfumes p
         WHERE c.perfume_id = p.id
         AND c.user_id = ?
         ORDER BY c.id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userid);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function getOrderHistory() {
    global $con;
    $userid = (int) $_SESSION['auth_user']['user_id'];
    $stmt = mysqli_prepare($con,
        "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userid);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function validateTrackID($tracking_no){
    global $con;
    $userid = (int) $_SESSION['auth_user']['user_id'];
    $stmt = mysqli_prepare($con,
        "SELECT * FROM orders WHERE tracking_no = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $tracking_no, $userid);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
?>