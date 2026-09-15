<?php
session_start();
include('../config/dbcon.php');
include('../functions/functions.php');

if (!isset($_POST['placeorder'])) {
    header('Location: ../index.php');
    exit;
}

if (!csrfVerify($_POST['csrf_token'] ?? null)) {
    $_SESSION['message'] = 'Your session expired. Please try again.';
    $_SESSION['message_kind'] = 'error';
    header('Location: ../checkout.php');
    exit;
}

$name = $_POST['name'];
$email = $_POST['email'];
$contact = $_POST['contact'];
$zipcode = $_POST['zipcode'];
$address = $_POST['address'];

if (($name == null) || $email == null || $contact == null || $zipcode == null || $address == null) {
    $_SESSION['message'] = "Please fill in every field.";
    $_SESSION['message_kind'] = 'error';
    header('Location: ../checkout.php');
    exit(0);
}

$cart_items = displayCart();
if (!$cart_items) {
    header('Location: ../checkout.php');
    exit;
}

$totalcost = 0;
foreach ($cart_items as $key) {
    $totalcost += $key['price'] * $key['perfume_quantity'];
}

// A logged-in customer's order is attached to their account; a guest
// checkout's order stands on its own -- user_id is NULL, and name/email/
// contacts/address (already captured above) are everything the order
// needs, exactly as an account's order also stores them directly on the
// row rather than only through the FK.
$isAuthed = isset($_SESSION['auth']);
$user_id  = $isAuthed ? (int) $_SESSION['auth_user']['user_id'] : null;
$prefix   = $isAuthed ? $_SESSION['auth_user']['username'] : $name;

$tracking_no = "TRK" . random_int(1000000, 999999999999) . substr($prefix, 0, 4);
$payment_id  = random_int(1, 999999999);

$orderinsert_stmt = mysqli_prepare($con,
    "INSERT INTO orders
        (tracking_no, user_id, name, email, contacts, address, zipcode, total_price, payment_mode, payment_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'COD', ?)");
mysqli_stmt_bind_param($orderinsert_stmt, 'sisssssdi',
    $tracking_no, $user_id, $name, $email, $contact, $address, $zipcode, $totalcost, $payment_id);
$orderinsert_query_run = mysqli_stmt_execute($orderinsert_stmt);

if ($orderinsert_query_run) {
    $order_id = mysqli_insert_id($con);

    $insertitem_stmt = mysqli_prepare($con,
        "INSERT INTO order_item (order_id, perfume_id, perfume_qty, price) VALUES (?, ?, ?, ?)");
    // Atomic, conditional decrement -- qty is UNSIGNED, so a plain
    // "SELECT current, then UPDATE SET qty = current - n" can drive it
    // negative (a fatal DB error under strict mode) whenever the cart's
    // remembered quantity has since exceeded real stock, whether from a
    // concurrent order or stock the admin lowered after the item was added
    // to the cart. "AND qty >= ?" makes the same guarantee the online
    // gateway path already has in functions/payments/common.php: the row
    // either decrements cleanly or is left untouched, never negative.
    $updateqty_stmt = mysqli_prepare($con,
        "UPDATE perfumes SET qty = qty - ? WHERE id = ? AND qty >= ?");

    foreach ($cart_items as $key) {

        $perfume_id = (int) $key['perfume_id'];
        $perfume_qty = (int) $key['perfume_quantity'];
        $price = (float) $key['price'];

        mysqli_stmt_bind_param($insertitem_stmt, 'iiid', $order_id, $perfume_id, $perfume_qty, $price);
        mysqli_stmt_execute($insertitem_stmt);

        mysqli_stmt_bind_param($updateqty_stmt, 'iii', $perfume_qty, $perfume_id, $perfume_qty);
        mysqli_stmt_execute($updateqty_stmt);
    }

    if ($isAuthed) {
        $resetcart_stmt = mysqli_prepare($con, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($resetcart_stmt, 'i', $user_id);
        mysqli_stmt_execute($resetcart_stmt);

        $_SESSION['message'] = "Order placed successfully";
        header('Location: ../orders.php');
    } else {
        unset($_SESSION['guest_cart']);

        $_SESSION['message'] = "Order placed successfully. Save your tracking number to check its status later.";
        header('Location: ../order-details.php?trackid=' . urlencode($tracking_no));
    }
}
