<?php
/**
 * Admin catalogue/order write endpoint.
 *
 * This used to be reachable with no admin check at all: a direct POST here
 * could add, edit or delete any product, or change any order's status,
 * without ever logging in. The middleware include below is the fix -- it
 * must run before anything else in this file.
 */
$basePath = '../../';
include(__DIR__ . '/../../middleware/adminmiddleware.php');

include(__DIR__ . '/../../config/dbcon.php');
include(__DIR__ . '/../../functions/myfunctions.php');
require_once(__DIR__ . '/../../includes/helpers.php');

/**
 * Reduce an uploaded filename to something safe to place on disk:
 * strip any directory part, keep only sane characters, and force a
 * known image extension.
 */
function safeUploadName($name)
{
    $name = basename((string) $name);
    if ($name === '') {
        return '';
    }
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
        return '';
    }
    $stem = pathinfo($name, PATHINFO_FILENAME);
    $stem = preg_replace('/[^A-Za-z0-9_-]+/', '_', $stem);
    $stem = trim($stem, '_');
    if ($stem === '') {
        $stem = 'perfume';
    }
    return substr($stem, 0, 80) . '.' . $ext;
}

/**
 * True when the upload actually decodes as one of the accepted image types.
 * The extension whitelist in safeUploadName() only checks the filename --
 * this checks the bytes, so a renamed non-image file cannot be saved as one.
 */
function isRealImage($tmpPath)
{
    if (!is_uploaded_file($tmpPath)) {
        return false;
    }
    $info = @getimagesize($tmpPath);
    return $info !== false && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true);
}

/**
 * The two discount fields from a request, sanitised and made mutually
 * consistent: a percentage outside 0-90 is clamped rather than rejected
 * (the schema's CHECK constraint would otherwise fail the whole save), and
 * an end date that isn't strictly after the start date is dropped instead
 * of left to violate the schema's window constraint.
 */
function readDiscountFields()
{
    $percent = isset($_POST['discount_percent']) ? (int) $_POST['discount_percent'] : 0;
    $percent = max(0, min(90, $percent));

    $starts = fromDatetimeLocal($_POST['discount_starts_at'] ?? '');
    $ends   = fromDatetimeLocal($_POST['discount_ends_at'] ?? '');
    if ($starts !== null && $ends !== null && $ends <= $starts) {
        $ends = null;
    }

    return [$percent, $starts, $ends];
}

/** The gender field, constrained to the same set as the schema's CHECK constraint. */
function readGenderField()
{
    $gender = $_POST['gender'] ?? 'unisex';
    return in_array($gender, ['men', 'women', 'unisex'], true) ? $gender : 'unisex';
}

if (isset($_POST['addperfume_btn'])) {
    $name          = $_POST['name'];
    $perfume_notes = $_POST['perfume_notes'];
    $description   = $_POST['description'];
    $volume        = $_POST['volume'];
    $qty           = (int) $_POST['qty'];
    $price         = (float) $_POST['price'];
    $trending      = isset($_POST['trending']) ? 1 : 0;
    $status        = isset($_POST['status']) ? 1 : 0;
    list($discount_percent, $discount_starts, $discount_ends) = readDiscountFields();
    $gender = readGenderField();

    // Absolute upload directory, so it resolves no matter what the current
    // working directory is on the host.
    $path     = __DIR__ . '/../../images';
    $img_path = isRealImage($_FILES['image_path']['tmp_name'] ?? '')
        ? safeUploadName($_FILES['image_path']['name'])
        : '';

    $stmt = mysqli_prepare($con,
        "INSERT INTO perfumes
            (name, perfume_notes, description, volume, qty, image_path, price, trending, status,
             discount_percent, discount_starts_at, discount_ends_at, gender)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssisdiiisss',
        $name, $perfume_notes, $description, $volume, $qty, $img_path, $price, $trending, $status,
        $discount_percent, $discount_starts, $discount_ends, $gender);
    $add_query_run = mysqli_stmt_execute($stmt);

    if ($add_query_run) {
        if ($img_path !== '') {
            move_uploaded_file($_FILES['image_path']['tmp_name'], $path . '/' . $img_path);
        }
        redirect("../add.php", "The perfume was successfully added!!");
    } else {
        redirect("../add.php", "There was an error adding the perfume  :( ");
    }
} else if (isset($_POST['save_edit_btn'])) {
    $get_id        = (int) $_POST['get_id'];
    $name          = $_POST['name'];
    $perfume_notes = $_POST['perfume_notes'];
    $description   = $_POST['description'];
    $volume        = $_POST['volume'];
    $price         = (float) $_POST['price'];
    $qty           = (int) $_POST['quantity'];
    $old_image     = $_POST['old_image'];
    $trending      = isset($_POST['trending']) ? 1 : 0;
    $status        = isset($_POST['status']) ? 1 : 0;
    list($discount_percent, $discount_starts, $discount_ends) = readDiscountFields();
    $gender = readGenderField();

    $path        = __DIR__ . '/../../images';
    $hasNewImage = isRealImage($_FILES['image_path']['tmp_name'] ?? '');
    $new_image   = $hasNewImage ? safeUploadName($_FILES['image_path']['name']) : '';
    $re_image    = ($new_image !== '') ? $new_image : $old_image;

    $stmt = mysqli_prepare($con,
        "UPDATE perfumes SET
            name = ?, perfume_notes = ?, description = ?, volume = ?,
            image_path = ?, price = ?, qty = ?, trending = ?, status = ?,
            discount_percent = ?, discount_starts_at = ?, discount_ends_at = ?, gender = ?
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sssssdiiiisssi',
        $name, $perfume_notes, $description, $volume, $re_image, $price, $qty, $trending, $status,
        $discount_percent, $discount_starts, $discount_ends, $gender, $get_id);
    $update_query_run = mysqli_stmt_execute($stmt);

    if ($update_query_run) {
        if ($hasNewImage && $new_image !== '') {
            move_uploaded_file($_FILES['image_path']['tmp_name'], $path . '/' . $new_image);
            if ($old_image !== '' && $old_image !== $new_image
                && file_exists($path . '/' . $old_image)) {
                unlink($path . '/' . $old_image);
            }
        }
        redirect("../perfume.php?updated=$get_id#perfume-$get_id", "The edit was saved successfully");
    } else {
        redirect("../edit-perfume.php?id=$get_id", "An error was occured!");
    }
} else if (isset($_POST['dlt_perfume_btn'])) {
    $delete_id = (int) $_POST['delete_id'];

    $stmt = mysqli_prepare($con, "SELECT image_path FROM perfumes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    $perfume_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $image        = $perfume_data ? $perfume_data['image_path'] : '';

    $stmt = mysqli_prepare($con, "DELETE FROM perfumes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    $remove_query_run = mysqli_stmt_execute($stmt);

    if ($remove_query_run) {
        // Delete the file first: redirect() ends the request, so anything
        // after it never ran.
        $imageFile = __DIR__ . '/../../images/' . $image;
        if ($image !== '' && file_exists($imageFile)) {
            unlink($imageFile);
        }
        redirect("../perfume.php", "Perfume was successfully deleted!");
    } else {
        // A perfume that has already been ordered cannot be deleted, because
        // order_item.perfume_id is ON DELETE RESTRICT. Unpublish it instead.
        redirect("../perfume.php", "This perfume appears in past orders, so it cannot be deleted. Untick Status to unpublish it instead.");
    }
} elseif (isset($_POST['updateOrder_btn'])) {
    $tracking_no  = $_POST['tracking_no'];
    $order_status = (int) $_POST['order_status'];

    $stmt = mysqli_prepare($con, "UPDATE orders SET status = ? WHERE tracking_no = ?");
    mysqli_stmt_bind_param($stmt, 'is', $order_status, $tracking_no);
    mysqli_stmt_execute($stmt);

    redirect("../order-history.php?trackid=" . urlencode($tracking_no), "Order Status has been updated");
}
