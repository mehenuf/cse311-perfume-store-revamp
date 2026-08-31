<?php
session_start();
include('../config/dbcon.php');

if (isset($_SESSION['auth'])) {
    if (isset($_POST['scope'])) {
        $scope = $_POST['scope'];
        switch ($scope) {
            case 'add':
                $perfume_id = $_POST['perfume_id'];
                $perfume_qty = $_POST['perfume_qty'];

                $user_id = $_SESSION['auth_user']['user_id'];

                $check_cart_query = "SELECT * FROM cart WHERE user_id = '$user_id' AND perfume_id = '$perfume_id';";
                $check_cart_query_run = mysqli_query($con, $check_cart_query);

                if (mysqli_num_rows($check_cart_query_run) > 0) {
                    echo 69;
                } else {

                    $add_to_cart_query = "INSERT INTO cart (user_id, perfume_id, perfume_qty) VALUES ('$user_id', '$perfume_id', '$perfume_qty');";
                    $add_to_cart_query_run = mysqli_query($con, $add_to_cart_query);
                    if ($add_to_cart_query_run) {
                        echo 201;
                    } else {
                        echo 500;
                    }
                }
                break;
            case 'update':
                $perfume_id = $_POST['perfume_id'];
                $perfume_qty = $_POST['perfume_qty'];

                $user_id = $_SESSION['auth_user']['user_id'];
                $check_cart_query = "SELECT * FROM cart WHERE user_id = '$user_id' AND perfume_id = '$perfume_id';";
                $check_cart_query_run = mysqli_query($con, $check_cart_query);

                if (mysqli_num_rows($check_cart_query_run) > 0) {
                    $update_cart_query =   "UPDATE cart
                                            SET perfume_qty = '$perfume_qty'
                                            WHERE perfume_id = '$perfume_id'
                                            AND user_id = '$user_id';";
                    $update_cart_query_run = mysqli_query($con, $update_cart_query);
                    if ($update_cart_query_run) {
                        echo 200;
                    } else {
                        echo 500;
                    }
                } else {
                    echo 'Something went wrong';
                }
                break;
            case 'delete':
                $cart_id = $_POST['cart_id'];
                //$perfume_qty = $_POST['perfume_qty'];

                $user_id = $_SESSION['auth_user']['user_id'];
                $check_cart_query = "SELECT * FROM cart WHERE id = '$cart_id' AND user_id = '$user_id';";
                $check_cart_query_run = mysqli_query($con, $check_cart_query);

                if (mysqli_num_rows($check_cart_query_run) > 0) {
                    $delete_query = "DELETE FROM cart WHERE id = '$cart_id';";
                    $delete_query_run = mysqli_query($con, $delete_query);

                    if ($delete_query_run) {
                        echo 200;
                    } else {
                        echo 500;
                    }
                    
                } else {
                    echo 'Something went wrong';
                }
                break;
            default:
                echo 500;
                break;
        }
    }
} else {
    // User is not authenticated, so return an error response
    echo 401;
}
