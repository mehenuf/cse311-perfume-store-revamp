<?php
/**
 * AJAX endpoint behind the inline price/stock editor on admin/perfume.php.
 * Same admin-only guard as every other admin write path, and the same
 * prepared-statement discipline -- this is a second way to reach the
 * catalogue's price/qty columns, so it gets no less scrutiny than
 * admin/Includes/code.php's full edit form.
 */
$basePath = '../../';
include(__DIR__ . '/../../middleware/adminmiddleware.php');
include(__DIR__ . '/../../config/dbcon.php');

header('Content-Type: application/json');

$perfumeId = isset($_POST['perfume_id']) ? (int) $_POST['perfume_id'] : 0;
$price     = isset($_POST['price']) && is_numeric($_POST['price']) ? (float) $_POST['price'] : null;
$qty       = isset($_POST['qty']) && is_numeric($_POST['qty']) ? (int) $_POST['qty'] : null;

if ($perfumeId <= 0 || $price === null || $qty === null || $price < 0 || $qty < 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Enter a valid price and stock quantity.']);
    exit;
}

$stmt = mysqli_prepare($con, "UPDATE perfumes SET price = ?, qty = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'dii', $price, $qty, $perfumeId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['ok' => true, 'price' => $price, 'qty' => $qty]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not save changes.']);
}
