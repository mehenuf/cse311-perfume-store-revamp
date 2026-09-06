<?php
session_start();
include('../config/dbcon.php');

if (isset($_SESSION['auth'])) {
    if (isset($_POST['scope'])) {
        $scope = $_POST['scope'];
        $user_id = (int) $_SESSION['auth_user']['user_id'];

        switch ($scope) {
            case 'add':
                $perfume_id = (int) $_POST['perfume_id'];
                $perfume_qty = (int) $_POST['perfume_qty'];

                $check_stmt = mysqli_prepare($con, "SELECT id FROM cart WHERE user_id = ? AND perfume_id = ?");
                mysqli_stmt_bind_param($check_stmt, 'ii', $user_id, $perfume_id);
                mysqli_stmt_execute($check_stmt);
                $check_cart_query_run = mysqli_stmt_get_result($check_stmt);

                if (mysqli_num_rows($check_cart_query_run) > 0) {
                    echo 69;
                } else {
                    $add_stmt = mysqli_prepare($con,
                        "INSERT INTO cart (user_id, perfume_id, perfume_qty) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($add_stmt, 'iii', $user_id, $perfume_id, $perfume_qty);
                    $add_to_cart_query_run = mysqli_stmt_execute($add_stmt);
                    if ($add_to_cart_query_run) {
                        echo 201;
                    } else {
                        echo 500;
                    }
                }
                break;
            case 'update':
                $perfume_id = (int) $_POST['perfume_id'];
                $perfume_qty = (int) $_POST['perfume_qty'];

                $check_stmt = mysqli_prepare($con, "SELECT id FROM cart WHERE user_id = ? AND perfume_id = ?");
                mysqli_stmt_bind_param($check_stmt, 'ii', $user_id, $perfume_id);
                mysqli_stmt_execute($check_stmt);
                $check_cart_query_run = mysqli_stmt_get_result($check_stmt);

                if (mysqli_num_rows($check_cart_query_run) > 0) {
                    $update_stmt = mysqli_prepare($con,
                        "UPDATE cart SET perfume_qty = ? WHERE perfume_id = ? AND user_id = ?");
                    mysqli_stmt_bind_param($update_stmt, 'iii', $perfume_qty, $perfume_id, $user_id);
                    $update_cart_query_run = mysqli_stmt_execute($update_stmt);
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
                $cart_id = (int) $_POST['cart_id'];

                // Scoped to the current user in both the check and the delete
                // itself, so one account can never remove another's cart row
                // by guessing or iterating ids.
                $check_stmt = mysqli_prepare($con, "SELECT id FROM cart WHERE id = ? AND user_id = ?");
                mysqli_stmt_bind_param($check_stmt, 'ii', $cart_id, $user_id);
                mysqli_stmt_execute($check_stmt);
                $check_cart_query_run = mysqli_stmt_get_result($check_stmt);

                if (mysqli_num_rows($check_cart_query_run) > 0) {
                    $delete_stmt = mysqli_prepare($con, "DELETE FROM cart WHERE id = ? AND user_id = ?");
                    mysqli_stmt_bind_param($delete_stmt, 'ii', $cart_id, $user_id);
                    $delete_query_run = mysqli_stmt_execute($delete_stmt);

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
