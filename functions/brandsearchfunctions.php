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
    $pattern = mysqli_real_escape_string($con, $brand['pattern']);
    return mysqli_query($con,
        "SELECT * FROM perfumes
         WHERE status = 1 AND name LIKE '" . $pattern . "'
         ORDER BY price DESC, name ASC");
}

/** How many published products a brand has. Used for the homepage tiles. */
function countBrandProducts($slug)
{
    global $con;
    $brand = brandBySlug($slug);
    if (!$brand) {
        return 0;
    }
    $pattern = mysqli_real_escape_string($con, $brand['pattern']);
    $res = mysqli_query($con,
        "SELECT COUNT(*) AS n FROM perfumes
         WHERE status = 1 AND name LIKE '" . $pattern . "'");
    if (!$res) {
        return 0;
    }
    $row = mysqli_fetch_assoc($res);
    return (int) $row['n'];
}
