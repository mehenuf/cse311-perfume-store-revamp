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

    // LEFT JOIN, not an inner join: a guest checkout's order has
    // user_id IS NULL, which an inner join can never match against
    // customer.id, so admin/order-history.php's "Open" link -- reachable
    // for any guest order, COD or online-gateway -- would otherwise always
    // report "Order not found" for one. username/id_email/id_name are
    // simply NULL for a guest order, same as $order['user_id'] itself.
    $stmt = mysqli_prepare($con,
        "SELECT o.*, c.username as username, c.email as id_email, c.name as id_name
         FROM orders o LEFT JOIN customer c ON o.user_id = c.id
         WHERE o.tracking_no = ?
         ORDER BY o.created_at ASC");
    mysqli_stmt_bind_param($stmt, 's', $tracking_no);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}