<?php
/**
 * getActiveDiscounts() must return exactly the published perfumes with a
 * currently-active discount window -- not a zero-percent row, not an
 * unpublished row even with a live discount, and not a row whose window
 * has not started yet or has already ended.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_discount_listing.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));

$pastStart   = date('Y-m-d H:i:s', time() - 3600 * 24 * 5);
$futureEnd   = date('Y-m-d H:i:s', time() + 3600 * 24 * 5);
$futureStart = date('Y-m-d H:i:s', time() + 3600 * 24 * 2);
$pastEnd     = date('Y-m-d H:i:s', time() - 3600 * 24 * 1);

function insertPerfume($pdo, $name, $status, $percent, $starts = null, $ends = null)
{
    $stmt = $pdo->prepare("INSERT INTO perfumes
        (name, qty, price, status, discount_percent, discount_starts_at, discount_ends_at)
        VALUES (?, 10, 10000, ?, ?, ?, ?)");
    $stmt->execute([$name, $status, $percent, $starts, $ends]);
}

insertPerfume($pdo, 'Active No Window', 1, 15, null, null);
insertPerfume($pdo, 'Active In Window', 1, 25, $pastStart, $futureEnd);
insertPerfume($pdo, 'Not Started Yet', 1, 30, $futureStart, $futureEnd);
insertPerfume($pdo, 'Already Ended', 1, 30, $pastStart, $pastEnd);
insertPerfume($pdo, 'No Discount', 1, 0, null, null);
insertPerfume($pdo, 'Unpublished Discount', 0, 40, null, null);

$con = new stdClass();
require $ROOT . '/functions/functions.php';

shim_report(function () {
    $rows = [];
    foreach (getActiveDiscounts('perfumes') as $r) {
        $rows[] = $r['name'];
    }
    sort($rows);
    return [
        'exactly_two_active' => count($rows) === 2,
        'includes_open_ended_window' => in_array('Active No Window', $rows, true),
        'includes_in_window' => in_array('Active In Window', $rows, true),
        'excludes_not_started' => !in_array('Not Started Yet', $rows, true),
        'excludes_already_ended' => !in_array('Already Ended', $rows, true),
        'excludes_zero_percent' => !in_array('No Discount', $rows, true),
        'excludes_unpublished' => !in_array('Unpublished Discount', $rows, true),
    ];
});
