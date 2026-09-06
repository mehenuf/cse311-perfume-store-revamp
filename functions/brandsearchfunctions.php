<?php
/**
 * Brand queries.
 *
 * Previously six near-identical functions with hardcoded LIKE patterns. Now a
 * single function driven by the registry in includes/helpers.php, so adding a
 * house needs no new code here.
 */
require_once(__DIR__ . '/../config/dbcon.php');
require_once(__DIR__ . '/../includes/helpers.php');

/** Published products for one brand slug, dearest first. */
function getBrandProducts($slug)
{
    global $con;
    $brand = brandBySlug($slug);
    if (!$brand) {
        return false;
    }
    $pattern = $brand['pattern'];
    $stmt = mysqli_prepare($con,
        "SELECT * FROM perfumes
         WHERE status = 1 AND name LIKE ?
         ORDER BY price DESC, name ASC");
    mysqli_stmt_bind_param($stmt, 's', $pattern);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/** How many published products a brand has. Used for the homepage tiles. */
function countBrandProducts($slug)
{
    global $con;
    $brand = brandBySlug($slug);
    if (!$brand) {
        return 0;
    }
    $pattern = $brand['pattern'];
    $stmt = mysqli_prepare($con,
        "SELECT COUNT(*) AS n FROM perfumes WHERE status = 1 AND name LIKE ?");
    mysqli_stmt_bind_param($stmt, 's', $pattern);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return (int) $row['n'];
}
