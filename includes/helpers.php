<?php
/**
 * Small view helpers shared by the storefront templates.
 * Presentation only, no database access.
 */

if (!function_exists('e')) {
    /** Escape for HTML output. */
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('taka')) {
    /** Format a price in Bangladeshi Taka the way the storefront prints it. */
    function taka($amount)
    {
        return 'Tk. ' . number_format((float) $amount, 0);
    }
}

if (!function_exists('stockLabel')) {
    /** Human stock state, plus whether it should read as a warning. */
    function stockLabel($qty)
    {
        $qty = (int) $qty;
        if ($qty <= 0)  return ['Sold out', true];
        if ($qty <= 10) return ['Only ' . $qty . ' left', true];
        return ['In stock', false];
    }
}

if (!function_exists('orderStatus')) {
    /**
     * Order status code to label.
     * Codes come from order-details.php and admin/order-history.php.
     */
    function orderStatus($code)
    {
        $map = [
            0 => 'Processing',
            1 => 'Completed',
            2 => 'Shipped',
            3 => 'Delivered',
            4 => 'Cancelled',
        ];
        return isset($map[(int) $code]) ? $map[(int) $code] : 'Unknown';
    }
}

if (!function_exists('crumb')) {
    /**
     * Breadcrumb trail.
     * $items is an ordered map of label => href, with href null for the
     * current page.
     */
    function crumb(array $items)
    {
        $chevron = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
                 . 'stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
                 . '<path d="m9 18 6-6-6-6"/></svg>';

        $out = '<nav class="shell crumb" aria-label="Breadcrumb">';
        $i = 0;
        $last = count($items) - 1;
        foreach ($items as $label => $href) {
            if ($i > 0) $out .= $chevron;
            $out .= $href !== null && $i !== $last
                ? '<a href="' . e($href) . '">' . e($label) . '</a>'
                : '<span aria-current="page">' . e($label) . '</span>';
            $i++;
        }
        return $out . '</nav>';
    }
}
