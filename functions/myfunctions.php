<?php
include(__DIR__ . '/../config/dbcon.php');

function getData($table)
{
    global $con;
    $query = "SELECT * FROM $table;";
    return $query_run = mysqli_query($con, $query);
}

function getViaID($table, $id)
{
    global $con;
    $stmt = mysqli_prepare($con, "SELECT * FROM perfumes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function redirect($url, $message)
{
    $_SESSION['message'] = $message;
    header('Location:' . $url);
    exit();
}

function getOrders()
{
    global $con;
    $query =    "SELECT * FROM orders
                WHERE status = '0';";
    return $query_run = mysqli_query($con, $query);
}

function getPreviousOrders()
{
    global $con;
    $query =    "SELECT * FROM orders
                WHERE status = '3' OR status = '4';";
    return $query_run = mysqli_query($con, $query);
}

function getActiveOrders() {
    global $con;
    $query =    "SELECT * FROM orders
                WHERE status = '1' OR status = '2';";
    return $query_run = mysqli_query($con, $query);
}

function validateTrackID($tracking_no){
    global $con;

    $stmt = mysqli_prepare($con,
        "SELECT o.*, c.username as username, c.email as id_email, c.name as id_name
         FROM orders o, customer c
         WHERE o.user_id = c.id AND o.tracking_no = ?
         ORDER BY o.created_at ASC");
    mysqli_stmt_bind_param($stmt, 's', $tracking_no);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}