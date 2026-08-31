<?php
session_start();

include('../../config/dbcon.php');
include('../../functions/myfunctions.php');

if (isset($_POST['addperfume_btn'])) {
    $name = $_POST['name'];
    $perfume_notes = $_POST['perfume_notes'];
    $description = $_POST['description'];
    $volume = $_POST['volume'];
    $qty = $_POST['qty'];
    $image = $_FILES['image_path']['name'];
    $path = "../../images";
    //$extension = pathinfo($image, PATHINFO_EXTENSION);
    $img_path = $image;
    $price = $_POST['price'];
    if (isset($_POST['trending'])) {
        $trending = 1;
    } else {
        $trending = 0;
    }
    if (isset($_POST['status'])) {
        $status = 1;
    } else {
        $status = 0;
    }
    //echo $img_path;

    $add_query = "INSERT INTO perfumes (name, perfume_notes, description, volume, qty, image_path, price, trending, status) VALUES 
    ('$name', '$perfume_notes', '$description', '$volume', '$qty', '$img_path', '$price', '$trending', '$status');";

    $add_query_run = mysqli_query($con, $add_query);

    if ($add_query_run) {
        move_uploaded_file($_FILES['image']['tmp_name'], $path . '/' . $img_path);
        redirect("../add.php", "The perfume was successfully added!!");
    } else {
        redirect("../add.php", "There was an error adding the perfume  :( ");
    }
} else if (isset($_POST['save_edit_btn'])) {
    $get_id = $_POST['get_id'];
    $name = $_POST['name'];
    $perfume_notes = $_POST['perfume_notes'];
    $description = $_POST['description'];
    $volume = $_POST['volume'];
    $price = $_POST['price'];
    $qty = $_POST['quantity'];
    $new_image = $_FILES['image_path']['name'];
    $old_image = $_POST['old_image'];
    if (isset($_POST['trending'])) {
        $trending = 1;
    } else {
        $trending = 0;
    }
    if (isset($_POST['status'])) {
        $status = 1;
    } else {
        $status = 0;
    }


    if (($new_image != null) || $old_image==null) {
        $re_image = $new_image;
    } else {
        $re_image = $old_image;
    }


    $update_query = "UPDATE perfumes
                        SET
                        name = '$name', 
                        perfume_notes = '$perfume_notes', 
                        description = '$description', 
                        volume = '$volume', 
                        image_path = '$re_image', 
                        price = '$price', 
                        qty = '$qty',
                        trending = '$trending', 
                        status = '$status' 
                        WHERE id = '$get_id';";

    $update_query_run = mysqli_query($con, $update_query);
    if ($update_query_run == true) {

        if ($_FILES['image_path']['name'] != "") {
            move_uploaded_file($_FILES['image_path']['tmp_name'], $path . '/' . $new_image);
            if (file_exists("../../images/" . $old_image)) {
                unlink("../../images/" . $old_image);
            }
            //echo "before redirect";
            //redirect_func("../edit-perfume.php", "The edit was saved successfully!");
            //echo"after redirect" ;
        }
        redirect("../edit-perfume.php?id=$get_id", "The edit was saved successfully");
    } else {
        //redirect_func("../edit-perfume.php", "An error was occured!");
        redirect("../edit-perfume.php?id=$get_id", "An error was occured!");
    }
} else if (isset($_POST['dlt_perfume_btn'])) {
    $delete_id = mysqli_escape_string($con, $_POST['delete_id']);
    $perfume_query = "SELECT * FROM perfumes WHERE id = $delete_id;";
    $perfume_query_run = mysqli_query($con, $perfume_query);
    $perfume_data = mysqli_fetch_array($perfume_query_run);
    $image = $perfume_data['image_path'];

    $remove_query = "DELETE FROM perfumes WHERE id = $delete_id;";
    $remove_query_run = mysqli_query($con, $remove_query);
    if ($remove_query_run == true) {
        redirect("../perfume.php", "Perfume was successfully deleted!");
        if (file_exists("../../images/" . $old_image)) {
            unlink("../../images/" . $old_image);
        } else {
            redirect("../perfume.php", "There's being a fucking error and i don't know what's wrong");
        }
    }
} elseif (isset($_POST['updateOrder_btn'])) {
    //echo "button clicked";
    $tracking_no = $_POST['tracking_no'];
    $order_status = $_POST['order_status'];

    $update_order_query = "UPDATE orders SET status = '$order_status' WHERE tracking_no = '$tracking_no';";
    $update_order_query_run = mysqli_query($con, $update_order_query);

    redirect("../order-history.php?trackid=$tracking_no", "Order Status has been updated");
}
