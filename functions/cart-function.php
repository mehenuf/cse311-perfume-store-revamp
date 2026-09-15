<?php
session_start();
include('../config/dbcon.php');

if (!isset($_POST['scope'])) {
    echo 500;
    exit;
}

$scope = $_POST['scope'];

if (isset($_SESSION['auth'])) {
    $user_id = (int) $_SESSION['auth_user']['user_id'];

    switch ($scope) {
        case 'add':
            $perfume_id = (int) $_POST['perfume_id'];
            $perfume_qty = (int) $_POST['perfume_qty'];

            // The stepper on display-perfume.php caps at min(10, stock), but
            // that is a client-side convenience, not a guarantee -- a direct
            // POST could otherwise put an arbitrary quantity in the cart,
            // which would then overcharge the order total for units that
            // were never actually reserved (placeorder.php's stock decrement
            // is separately guarded against going negative, but the price
            // charged is read straight from the cart row).
            $stock_stmt = mysqli_prepare($con, "SELECT qty FROM perfumes WHERE id = ? AND status = 1");
            mysqli_stmt_bind_param($stock_stmt, 'i', $perfume_id);
            mysqli_stmt_execute($stock_stmt);
            $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stock_stmt));
            if (!$stock_row) {
                echo 500;
                break;
            }
            $perfume_qty = max(1, min(10, $perfume_qty, (int) $stock_row['qty']));

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

            $stock_stmt = mysqli_prepare($con, "SELECT qty FROM perfumes WHERE id = ? AND status = 1");
            mysqli_stmt_bind_param($stock_stmt, 'i', $perfume_id);
            mysqli_stmt_execute($stock_stmt);
            $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stock_stmt));
            if (!$stock_row) {
                echo 500;
                break;
            }
            $perfume_qty = max(1, min(10, $perfume_qty, (int) $stock_row['qty']));

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
    exit;
}

// No session: a guest's cart lives entirely in $_SESSION, keyed by
// perfume id. There is no `cart` table row and no FK to lean on, so the
// product itself is validated here instead.
switch ($scope) {
    case 'add':
        $perfume_id = (int) $_POST['perfume_id'];
        $perfume_qty = (int) $_POST['perfume_qty'];

        $stmt = mysqli_prepare($con, "SELECT qty FROM perfumes WHERE id = ? AND status = 1");
        mysqli_stmt_bind_param($stmt, 'i', $perfume_id);
        mysqli_stmt_execute($stmt);
        $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$stock_row) {
            echo 500;
            break;
        }
        $perfume_qty = max(1, min(10, $perfume_qty, (int) $stock_row['qty']));

        if (isset($_SESSION['guest_cart'][$perfume_id])) {
            echo 69;
        } else {
            $_SESSION['guest_cart'][$perfume_id] = $perfume_qty;
            echo 201;
        }
        break;
    case 'update':
        $perfume_id = (int) $_POST['perfume_id'];
        $perfume_qty = (int) $_POST['perfume_qty'];

        if (isset($_SESSION['guest_cart'][$perfume_id])) {
            $stmt = mysqli_prepare($con, "SELECT qty FROM perfumes WHERE id = ? AND status = 1");
            mysqli_stmt_bind_param($stmt, 'i', $perfume_id);
            mysqli_stmt_execute($stmt);
            $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            $perfume_qty = max(1, min(10, $perfume_qty, $stock_row ? (int) $stock_row['qty'] : $perfume_qty));

            $_SESSION['guest_cart'][$perfume_id] = $perfume_qty;
            echo 200;
        } else {
            echo 'Something went wrong';
        }
        break;
    case 'delete':
        // A guest line has no DB row, so cart_id here is the perfume_id
        // (see guestCartRows() in functions/functions.php) -- scoped to
        // $_SESSION['guest_cart'], which already belongs to this visitor
        // alone.
        $cart_id = (int) $_POST['cart_id'];

        if (isset($_SESSION['guest_cart'][$cart_id])) {
            unset($_SESSION['guest_cart'][$cart_id]);
            echo 200;
        } else {
            echo 'Something went wrong';
        }
        break;
    default:
        echo 500;
        break;
}
