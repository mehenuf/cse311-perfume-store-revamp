<?php
require_once(__DIR__ . '/../config/dbcon.php');
require_once(__DIR__ . '/../includes/helpers.php');



function getAllPublished($table){
    global $con;
    $query = "SELECT * FROM $table
    WHERE status = 1
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}
function getAllTrending($table){
    global $con;
    $query = "SELECT * FROM $table
    WHERE status = 1
    AND trending = 1
    ORDER BY name ASC;";
    return $query_run = mysqli_query($con, $query);
}

/**
 * Published perfumes marketed for a given gender, for the nav's "For Him" /
 * "For Her" shortcuts. Each also includes 'unisex' rows -- a unisex bottle
 * genuinely suits either shelf, and without it "For Her" alone would be a
 * thin 19 products against the catalogue's 79 men's + 30 unisex.
 */
function getByGender($table, $gender)
{
    global $con;
    $stmt = mysqli_prepare($con, "SELECT * FROM $table
        WHERE status = 1 AND gender IN (?, 'unisex')
        ORDER BY name ASC");
    mysqli_stmt_bind_param($stmt, 's', $gender);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/**
 * Published perfumes with a currently-active discount. "Active" is computed
 * from discount_starts_at/discount_ends_at against the database's own clock
 * on every call, the same window perfumePricing() checks in PHP -- so this
 * list is always correct the instant a discount starts or ends, with no
 * separate flag to keep in sync and no scheduled job required to do it
 * (useful on hosts, like a typical free plan, with no cron support).
 */
function getActiveDiscounts($table){
    global $con;
    // The current time is computed here and bound as a parameter, the same
    // way forgotpassword.php/resetpassword.php check a token's expiry,
    // rather than relying on the database's own NOW() -- one clock to
    // reason about instead of two, and it keeps this portable to a database
    // that has no NOW() of its own.
    $now = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($con, "SELECT * FROM $table
        WHERE status = 1
        AND discount_percent > 0
        AND (discount_starts_at IS NULL OR discount_starts_at <= ?)
        AND (discount_ends_at IS NULL OR discount_ends_at > ?)
        ORDER BY discount_percent DESC, name ASC");
    mysqli_stmt_bind_param($stmt, 'ss', $now, $now);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
function getData($table){
    global $con;
    $query = "SELECT * FROM $table;";
    return $query_run = mysqli_query($con, $query);
}

function getViaID($table, $id){
    global $con;
    $stmt = mysqli_prepare($con, "SELECT * FROM perfumes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function redirect($url, $message){
    $_SESSION['message'] = $message;
    header('Location:'. $url);
    exit();
}

function getViaNameActive($table, $name){
    global $con;
    $stmt = mysqli_prepare($con, "SELECT * FROM $table WHERE name = ? AND status = 1");
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/**
 * The current cart -- a customer's DB-backed cart when logged in, or the
 * guest session cart when not. Always a plain array of rows shaped the
 * same way either way: cart_id, perfume_id, perfume_quantity,
 * perfume_name, image_path, price, original_price, discount_percent,
 * discount_active. price is always the price actually charged right now
 * (discounted if a discount is running), so every total computed from it
 * -- the cart summary, the order placed from it -- is correct without
 * having to know about discounts itself; original_price/discount_percent/
 * discount_active are there only so a template can show the struck-through
 * price the way the product pages do.
 */
function displayCart() {
    global $con;

    if (!isset($_SESSION['auth_user']['user_id'])) {
        return guestCartRows();
    }

    $userid = (int) $_SESSION['auth_user']['user_id'];
    $stmt = mysqli_prepare($con,
        "SELECT c.id as cart_id, c.perfume_id as perfume_id, c.perfume_qty as perfume_quantity,
                p.name as perfume_name, p.image_path, p.price,
                p.discount_percent, p.discount_starts_at, p.discount_ends_at
         FROM cart c, perfumes p
         WHERE c.perfume_id = p.id
         AND c.user_id = ?
         ORDER BY c.id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userid);
    mysqli_stmt_execute($stmt);

    $rows = [];
    foreach (mysqli_stmt_get_result($stmt) as $row) {
        $rows[] = applyCartPricing($row);
    }
    return $rows;
}

/**
 * The guest session cart, joined against the live catalogue.
 *
 * There is no cart table row for a guest line, so cart_id doubles as the
 * perfume_id -- that is fine because it is only ever used to identify
 * which line to update or remove within the guest's own session cart,
 * never as a foreign key or an ownership check across users the way the
 * real cart_id is.
 */
function guestCartRows() {
    global $con;
    $cart = isset($_SESSION['guest_cart']) ? $_SESSION['guest_cart'] : [];
    if (!$cart) {
        return [];
    }

    $stmt = mysqli_prepare($con,
        "SELECT name, image_path, price, discount_percent, discount_starts_at, discount_ends_at
         FROM perfumes WHERE id = ? AND status = 1");

    $rows = [];
    foreach ($cart as $perfumeId => $qty) {
        $perfumeId = (int) $perfumeId;
        mysqli_stmt_bind_param($stmt, 'i', $perfumeId);
        mysqli_stmt_execute($stmt);
        $perfume = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$perfume) {
            // Unpublished or removed since it was added to this guest's cart.
            unset($_SESSION['guest_cart'][$perfumeId]);
            continue;
        }

        $rows[] = applyCartPricing([
            'cart_id' => $perfumeId,
            'perfume_id' => $perfumeId,
            'perfume_quantity' => (int) $qty,
            'perfume_name' => $perfume['name'],
            'image_path' => $perfume['image_path'],
            'price' => $perfume['price'],
            'discount_percent' => $perfume['discount_percent'],
            'discount_starts_at' => $perfume['discount_starts_at'],
            'discount_ends_at' => $perfume['discount_ends_at'],
        ]);
    }
    return $rows;
}

/** Resolves a cart row's raw price + discount columns down to what is charged now. */
function applyCartPricing($row) {
    $pricing = perfumePricing(['price' => $row['price'],
        'discount_percent' => $row['discount_percent'] ?? 0,
        'discount_starts_at' => $row['discount_starts_at'] ?? null,
        'discount_ends_at' => $row['discount_ends_at'] ?? null]);

    $row['original_price'] = $pricing['original'];
    $row['price'] = $pricing['final'];
    $row['discount_percent'] = $pricing['percent'];
    $row['discount_active'] = $pricing['active'];
    unset($row['discount_starts_at'], $row['discount_ends_at']);
    return $row;
}

function getOrderHistory() {
    global $con;
    $userid = (int) $_SESSION['auth_user']['user_id'];
    $stmt = mysqli_prepare($con,
        "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userid);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/**
 * One order by tracking number, scoped so it can never cross accounts:
 * logged in, it must belong to the session's own user_id; signed out, it
 * must be a guest order (user_id IS NULL). A guest can never see an
 * account's order this way, and a logged-in customer can never see a
 * guest order or another account's order, even knowing the exact
 * tracking number.
 */
function validateTrackID($tracking_no){
    global $con;

    if (isset($_SESSION['auth_user']['user_id'])) {
        $userid = (int) $_SESSION['auth_user']['user_id'];
        $stmt = mysqli_prepare($con,
            "SELECT * FROM orders WHERE tracking_no = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $tracking_no, $userid);
    } else {
        $stmt = mysqli_prepare($con,
            "SELECT * FROM orders WHERE tracking_no = ? AND user_id IS NULL");
        mysqli_stmt_bind_param($stmt, 's', $tracking_no);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
?>