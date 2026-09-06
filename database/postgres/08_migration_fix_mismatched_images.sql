-- ============================================================
--  Perfume Store - Migration 08: Fix mismatched catalogue photography
--
--  THE INCIDENT: the catalogue expansion in migration 07 (ids 72-128) and
--  part of the earlier expansion in 03_seed_expansion.sql (ids 40-71) had
--  their product photography sourced by an automated image scraper. A
--  visual audit of every image afterward found:
--
--    - All 57 products added by migration 07 had a wrong image: either a
--      duplicate of another unrelated photo (an Armani bottle, an Afnan
--      bottle, or a Fragrantica webpage screenshot -- each reused across
--      a dozen or more unrelated products), or something with no
--      connection to perfumery at all (screenshots of apps and websites,
--      unrelated product photography, charts, diagrams, logos, memes).
--    - 2 products from the earlier batch (ids 47, 48) showed a real photo
--      of the correct BRAND but a different, specific product.
--    - 1 product from the earlier batch (id 40, Creed Aventus) showed an
--      unrelated image entirely.
--    - id 35 (Kilian Angels' Share, an original catalogue product, not
--      part of either expansion) had its previously-correct photograph
--      overwritten with the same Fragrantica screenshot -- that one was
--      restored from git history rather than blanked, so it is not part
--      of this migration.
--
--  THE FIX: rather than guess at replacement photography and risk
--  repeating the same mistake, every affected id is set to an empty
--  image_path, which renders the site's existing designed "Photography
--  pending" tile (includes/helpers.php::pendingMedia()) instead of a
--  wrong photo. Real photography can be added per product afterward from
--  the admin panel, same as the original 25 unphotographed products from
--  the first catalogue expansion.
--
--  Idempotent: safe to run more than once, and safe to run whether or not
--  migration 07 already ran on this database (UPDATE is a no-op on rows
--  that don't exist or are already empty).
-- ============================================================

BEGIN;

UPDATE perfumes SET image_path = '' WHERE id IN (
    -- earlier expansion (03_seed_expansion.sql): wrong or non-specific photo
    40, 47, 48,
    -- migration 07: every one of the 57 added products had a wrong image
    72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89,
    90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105,
    106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119,
    120, 121, 122, 123, 124, 125, 126, 127, 128
);

COMMIT;
