<?php
/**
 * A discount percentage is set, but its window already ended. The full
 * price must be charged -- discount_percent alone is not enough, the
 * window (when one is set) has to actually be current.
 */
$ROOT = dirname(__DIR__, 2);
require __DIR__ . '/../support/mysqli_shim.php';

$pdo = shim_boot(__DIR__ . '/../.tmp_discount_expired.sqlite', file_get_contents(__DIR__ . '/../support/schema.sql'));
$pastStart = date('Y-m-d H:i:s', time() - 3600 * 24 * 10);
$pastEnd   = date('Y-m-d H:i:s', time() - 3600 * 24 * 3);
$stmt = $pdo->prepare("INSERT INTO perfumes (name, qty, price, status, discount_percent, discount_starts_at, discount_ends_at)
                        VALUES ('Dior Sauvage', 10, 10000, 1, 20, ?, ?)");
$stmt->execute([$pastStart, $pastEnd]);

require $ROOT . '/includes/helpers.php';

shim_report(function () use ($pdo) {
    $row = $pdo->query("SELECT * FROM perfumes")->fetch(PDO::FETCH_ASSOC);
    $pricing = perfumePricing($row);
    return [
        'not_active' => $pricing['active'] === false,
        'full_price_charged' => (float) $pricing['final'] === 10000.0,
    ];
});
