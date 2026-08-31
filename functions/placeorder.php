<?php
session_start();
include('../config/dbcon.php');
include('../functions/functions.php');


if (isset($_SESSION['auth'])) {
    if (isset($_POST['placeorder'])) {
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $contact = mysqli_real_escape_string($con, $_POST['contact']);
        $zipcode = mysqli_real_escape_string($con, $_POST['zipcode']);
        $address = mysqli_real_escape_string($con, $_POST['address']);

        if (($name == null) || $email == null || $contact == null || $zipcode == null || $address == null) {
            echo "null checked";
            $_SESSION['message'] = "You need to fill every fields.";
            header('Location: ../checkout.php');
            exit(0);
        }


        $cart_items = displayCart();
        $totalcost = 0;
        foreach ($cart_items as $key) {
            $totalcost += $key['price'] * $key['perfume_quantity'];
        }


        $username = $_SESSION['auth_user']['username'];
        $tracking_no = "TRK" . rand(1000000, 999999999999) . substr($username,0,4);
        $user_id = $_SESSION['auth_user']['user_id'];
        $payment_id = rand(1, 9999999999);


        $orderinsert_query =    "INSERT INTO orders
                        (tracking_no, user_id, name, email, contacts, address, zipcode, total_price, payment_mode, payment_id)
                        VALUES (
                            '$tracking_no',
                            '$user_id',
                            '$name',
                            '$email',
                            '$contact',
                            '$address',
                            '$zipcode',
                            '$totalcost',
                            'COD',
                            '$payment_id');";
        $orderinsert_query_run = mysqli_query($con, $orderinsert_query);


        if ($orderinsert_query_run) {
            $order_id = mysqli_insert_id($con);


            foreach ($cart_items as $key) {

                $perfume_id = $key['perfume_id'];
                $perfume_qty = $key['perfume_quantity'];
                $price = $key['price'];

                $insertitem_query = "INSERT INTO order_item
                                    (order_id, perfume_id, perfume_qty, price) 
                                    VALUES 
                                    ('$order_id',
                                    '$perfume_id',
                                    '$perfume_qty',
                                    '$price');";
                $insertitem_query_run = mysqli_query($con, $insertitem_query);


                $perfumeFetch_query = "SELECT * FROM perfumes WHERE id = '$perfume_id';";
                $perfumeFetch_query_run = mysqli_query($con, $perfumeFetch_query);
                
                $perfume_data = mysqli_fetch_array($perfumeFetch_query_run);
                $current_qty = $perfume_data['qty'];

                $new_qty = ($current_qty - $perfume_qty);
                $update_qty_query = "UPDATE perfumes
                                    SET qty = '$new_qty'
                                    WHERE id = '$perfume_id';";
                $update_qty_query_run = mysqli_query($con, $update_qty_query);
            }

            $reset_cart_query = "DELETE FROM cart WHERE user_id = '$user_id' ;";
            $reset_cart_query_run = mysqli_query($con, $reset_cart_query);

            
            $_SESSION['message'] = "Order placed successfully";
            header('Location: ../orders.php');
        }
    }
} else {
    header('Location: ../index.php');
}
