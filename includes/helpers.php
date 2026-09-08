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

if (!function_exists('csrfToken')) {
    /**
     * One CSRF token per session, reused across requests (not per-form) so
     * a customer with two tabs open doesn't get logged out of one by
     * submitting the other. Needs session_start() already called by the
     * including page, same as every other $_SESSION use in this app.
     */
    function csrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrfVerify')) {
    /** Timing-safe check of a submitted token against the session's own. */
    function csrfVerify($submittedToken)
    {
        return isset($_SESSION['csrf_token'])
            && is_string($submittedToken)
            && hash_equals($_SESSION['csrf_token'], $submittedToken);
    }
}

if (!function_exists('taka')) {
    /** Format a price in Bangladeshi Taka the way the storefront prints it. */
    function taka($amount)
    {
        return 'Tk. ' . number_format((float) $amount, 0);
    }
}

if (!function_exists('perfumePricing')) {
    /**
     * The price actually charged for a perfume right now.
     *
     * $p needs price, discount_percent, discount_starts_at, discount_ends_at
     * -- every `SELECT *` on perfumes already has them; a query that lists
     * columns by name has to ask for these explicitly.
     *
     * price itself is never touched or overwritten by a discount: it is
     * always the original price, and 'final' is computed from it fresh
     * every time this is called, so nothing ever needs "restoring" when a
     * discount ends.
     *
     * A percentage with no dates on either side reads as "on until turned
     * off" -- the dates are an optional window, not a requirement.
     *
     * Returns ['original', 'final', 'percent', 'active'] -- when there is no
     * running discount, 'final' equals 'original' and 'active' is false.
     */
    function perfumePricing($p)
    {
        $price   = (float) $p['price'];
        $percent = isset($p['discount_percent']) ? (int) $p['discount_percent'] : 0;

        $active = false;
        if ($percent > 0) {
            $now      = time();
            $starts   = !empty($p['discount_starts_at']) ? strtotime($p['discount_starts_at']) : null;
            $ends     = !empty($p['discount_ends_at'])   ? strtotime($p['discount_ends_at'])   : null;
            $active   = ($starts === null || $starts <= $now) && ($ends === null || $ends > $now);
        }

        $final = $active ? round($price * (100 - $percent) / 100, 2) : $price;

        return [
            'original' => $price,
            'final'    => $final,
            'percent'  => $percent,
            'active'   => $active,
        ];
    }
}

if (!function_exists('toDatetimeLocal')) {
    /** A DB datetime as the value an <input type="datetime-local"> expects, or '' when unset. */
    function toDatetimeLocal($value)
    {
        if (empty($value)) {
            return '';
        }
        $ts = strtotime($value);
        return $ts === false ? '' : date('Y-m-d\TH:i', $ts);
    }
}

if (!function_exists('fromDatetimeLocal')) {
    /** An <input type="datetime-local"> value back to a DB datetime, or null when empty/unparsable. */
    function fromDatetimeLocal($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        return $ts === false ? null : date('Y-m-d H:i:s', $ts);
    }
}

if (!function_exists('priceMarkup')) {
    /**
     * The price block markup shared by the product card, the product
     * detail page and the cart -- the original price struck through above
     * the discounted one when a discount is running, or just the plain
     * price when it isn't. $extraClass lets a caller size it differently
     * (the detail page's price reads larger than a grid card's).
     */
    function priceMarkup($p, $extraClass = '')
    {
        $pricing = perfumePricing($p);
        $class   = trim('price-block ' . $extraClass);

        if (!$pricing['active']) {
            return '<span class="' . e($class) . '"><span class="price-block__final">'
                 . e(taka($pricing['final'])) . '</span></span>';
        }

        return '<span class="' . e($class) . '" data-discount>'
             . '<span class="price-block__was">' . e(taka($pricing['original'])) . '</span>'
             . '<span class="price-block__row">'
             . '<span class="price-block__final">' . e(taka($pricing['final'])) . '</span>'
             . '<span class="price-block__badge">-' . $pricing['percent'] . '%</span>'
             . '</span>'
             . '</span>';
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

if (!function_exists('brandList')) {
    /**
     * The one place brands are defined.
     *
     * Every brand page, the nav, the homepage tiles and the brand queries all
     * read from here, so adding a house means editing this array and nothing
     * else. 'pattern' is the SQL LIKE used to match products by name, which is
     * why product names must start with their house.
     */
    function brandList()
    {
        return [
        'dior' => ['label' => 'Dior', 'pattern' => '%dior%',
            'shot' => 'dior_sauvage.jpg',
            'lede' => 'The house that gave us Sauvage, Fahrenheit and the iris of Dior Homme.'],
        'chanel' => ['label' => 'Chanel', 'pattern' => '%chanel%',
            'shot' => 'bleu_de_chanel.jpg',
            'lede' => 'From No 5 to Bleu de Chanel, the reference point almost every other house is measured against.'],
        'tomford' => ['label' => 'Tom Ford', 'pattern' => '%tom ford%',
            'shot' => 'tomford_black_orchid.jpg',
            'lede' => 'Loud, expensive and completely unapologetic. Black Orchid and Tuscan Leather lead the line.'],
        'creed' => ['label' => 'Creed', 'pattern' => '%creed%',
            'shot' => 'creed_aventus.jpg',
            'lede' => 'The Anglo-French house behind Aventus. Expensive, imitated everywhere, still unmatched.'],
        'mancera' => ['label' => 'Mancera', 'pattern' => '%mancera%',
            'shot' => 'mancera_redtobacco.jpg',
            'lede' => 'Paris niche with enormous performance. Red Tobacco and Cedrat Boise are the ones people stop you for.'],
        'lattafa' => ['label' => 'Lattafa', 'pattern' => '%lattafa%',
            'shot' => 'lattafa_khamrah.jpg',
            'lede' => 'The Emirati house that changed what a budget bottle is allowed to smell like.'],
        'hugoboss' => ['label' => 'Hugo Boss', 'pattern' => '%hugo boss%',
            'shot' => 'hugoboss_bossbottled.jpg',
            'lede' => 'The dependable office wardrobe. Bottled has been quietly working since 1998.'],
        'rasasi' => ['label' => 'Rasasi', 'pattern' => '%rasasi%',
            'shot' => 'rasasi_hawas.jpg',
            'lede' => 'Dubai since 1979. Hawas made its name in the West; the back catalogue is deeper than most realise.'],
        'rayhaan' => ['label' => 'Rayhaan', 'pattern' => '%rayhaan%',
            'shot' => 'rayhaan_bariq.jpg',
            'lede' => 'The sister label to Rasasi. Gulf opulence at a price that does not ask you to think about it.'],
        'afnan' => ['label' => 'Afnan', 'pattern' => '%afnan%',
            'shot' => 'afnan_9pm.png',
            'lede' => 'The house behind 9PM. Sweet, loud and impossible to ignore on a night out.'],
        'armaf' => ['label' => 'Armaf', 'pattern' => '%armaf%',
            'shot' => 'armaf_cdnim.jpg',
            'lede' => 'Club de Nuit built this house. Enormous performance for a fraction of its inspiration.'],
        'versace' => ['label' => 'Versace', 'pattern' => '%versace%',
            'shot' => 'versace_dylan_blue.jpeg',
            'lede' => 'Eros and the Dylan line. Mediterranean, bright and built to be noticed.'],
        'ysl' => ['label' => 'Yves Saint Laurent', 'pattern' => '%saint laurent%',
            'shot' => 'yves_saint_laurent_y_eau_de_parfum.png',
            'lede' => 'Y, La Nuit and Black Opium. Parisian, sharp, and never quite polite.'],
        'armani' => ['label' => 'Giorgio Armani', 'pattern' => '%armani%',
            'shot' => 'giorgio_armani_code_parfum.png',
            'lede' => 'Acqua di Gio defined the modern aquatic. Code took the same tailoring somewhere warmer.'],
        'pacorabanne' => ['label' => 'Paco Rabanne', 'pattern' => '%paco rabanne%',
            'shot' => 'paco_rabanne_1_million.jpg',
            'lede' => '1 Million and Invictus. Unsubtle by design, and very good at what they set out to do.'],
        'prada' => ['label' => 'Prada', 'pattern' => '%prada%',
            'shot' => 'prada_l_homme.jpg',
            'lede' => 'Luna Rossa and L Homme. Restrained, architectural, and quietly expensive.'],
        'kilian' => ['label' => 'Kilian', 'pattern' => '%kilian%',
            'shot' => 'kilian_angels_share.jpg',
            'lede' => 'The house of Kilian Hennessy. Gourmands made with the seriousness of a cognac cellar.'],
        'givenchy' => ['label' => 'Givenchy', 'pattern' => '%givenchy%',
            'shot' => 'givenchy_gentlemen.jpg',
            'lede' => 'Gentleman, reworked for a generation that wears iris without apology.'],
        'jpg' => ['label' => 'Jean Paul Gaultier', 'pattern' => '%jean paul gaultier%',
            'shot' => 'jpg_le_bleu_le_parfum.jpg',
            'lede' => 'The torso bottle. Vanilla, warmth and a complete lack of restraint.'],
        'ferragamo' => ['label' => 'Salvatore Ferragamo', 'pattern' => '%ferragamo%',
            'shot' => 'salvatore_ferregamo_ferregamo_black.jpg',
            'lede' => 'Italian tailoring in fragrance form. Understated where its neighbours shout.'],
        ];
    }
}

if (!function_exists('brandListAlphabetical')) {
    /**
     * brandList(), sorted by label rather than by curation order.
     *
     * brandList() itself stays in curation order on purpose -- the
     * homepage's 6-tile teaser deliberately leads with the best-known
     * houses. Every actual *directory* of houses (the nav's Houses menu,
     * its mobile drawer, the footer, and the all-houses page) is something
     * a visitor scans to find one name, which alphabetical order serves
     * far better than "whichever six were picked for the homepage".
     */
    function brandListAlphabetical()
    {
        $all = brandList();
        uasort($all, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });
        return $all;
    }
}

if (!function_exists('brandBySlug')) {
    /** One brand, or null when the slug is unknown. */
    function brandBySlug($slug)
    {
        $all = brandList();
        return isset($all[$slug]) ? $all[$slug] : null;
    }
}

if (!function_exists('houseOf')) {
    /**
     * The house a product belongs to, matched against the registry.
     * Falls back to the first word of the name.
     */
    function houseOf($productName)
    {
        $needle = ' ' . strtolower($productName) . ' ';
        foreach (brandList() as $brand) {
            $pattern = str_replace('%', '', strtolower($brand['pattern']));
            if ($pattern !== '' && strpos($needle, $pattern) !== false) {
                return $brand['label'];
            }
        }
        $first = strtok($productName, ' ');
        return $first !== false ? $first : $productName;
    }
}

if (!function_exists('brandSlugOf')) {
    /**
     * The registry slug a product belongs to (e.g. 'dior'), or null when no
     * pattern matches. Same matching rule as houseOf(), just returning the
     * key instead of the label -- used to tag each product card with its
     * house for the collection filter's brand checklist.
     */
    function brandSlugOf($productName)
    {
        $needle = ' ' . strtolower($productName) . ' ';
        foreach (brandList() as $slug => $brand) {
            $pattern = str_replace('%', '', strtolower($brand['pattern']));
            if ($pattern !== '' && strpos($needle, $pattern) !== false) {
                return $slug;
            }
        }
        return null;
    }
}

if (!function_exists('genderLabel')) {
    /** The display label for a perfumes.gender value, falling back safely. */
    function genderLabel($gender)
    {
        $map = ['men' => 'Men', 'women' => 'Women', 'unisex' => 'Unisex'];
        return isset($map[$gender]) ? $map[$gender] : 'Unisex';
    }
}

if (!function_exists('pendingMedia')) {
    /** The designed stand-in for a product with no photograph yet. */
    function pendingMedia($productName)
    {
        $house = houseOf($productName);
        return '<div class="media-pending">'
             . '<span class="media-pending__mark">' . e($house) . '</span>'
             . '<span class="media-pending__note">Photography pending</span>'
             . '</div>';
    }
}
