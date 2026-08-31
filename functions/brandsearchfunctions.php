<?php
require_once(__DIR__ . '/../config/dbcon.php');


function getDior() {
    global $con;
    $query = "SELECT * FROM perfumes 
    WHERE status = 1 AND name LIKE '%dior%' 
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}

function getChanel() {
    global $con;
    $query = "SELECT * FROM perfumes 
    WHERE status = 1 AND name LIKE '%chanel%' 
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}

function getlattafa() {
    global $con;
    $query = "SELECT * FROM perfumes 
    WHERE status = 1 AND name LIKE '%lattafa%' 
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}

function getMancera() {
    global $con;
    $query = "SELECT * FROM perfumes 
    WHERE status = 1 AND name LIKE '%Mancera%' 
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}

function getTomFord() {
    global $con;
    $query = "SELECT * FROM perfumes 
    WHERE status = 1 AND name LIKE '%Tom%Ford%' 
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}

function getHugoBoss() {
    global $con;
    $query = "SELECT * FROM perfumes 
    WHERE status = 1 AND name LIKE '%Hugo%Boss%'
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}