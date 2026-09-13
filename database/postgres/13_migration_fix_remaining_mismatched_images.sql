-- ============================================================
--  Perfume Store - Migration 13: Fix remaining mismatched photography
--
--  THE INCIDENT: a full visual audit of every product photo against its
--  product name (every image actually opened and compared, not just its
--  filename) found 7 products still showing the wrong photo -- some left
--  over from the original migration-07 scraping incident that migrations
--  08/09 believed they had fully restored, plus 2 that predate that
--  incident entirely and were never audited before:
--
--    - id 36  (Salvatore Ferragamo Black, seeded from the start) shows a
--      real "F by Ferragamo Pour Homme" bottle -- a different Ferragamo
--      product, identical to the correct photo already used for id 125.
--    - id 47  (Rayhaan Bariq) shows a bottle labelled "Sunset Woods".
--    - id 59  (Giorgio Armani Acqua Di Gio Profumo, from
--      03_seed_expansion.sql, never touched by migrations 08/09) shows
--      the original Acqua Di Gio -- a real but different Armani product.
--    - id 87  (Afnan Historic Olmeda) shows an amber soap dispenser,
--      dropper bottle and reed diffuser -- not a perfume at all.
--    - id 108 (Creed Millesime Imperial) shows a bottle labelled
--      "Sunset Woods".
--    - id 111 (Creed Himalaya) shows a bottle labelled "Wilde".
--    - id 113 (Rasasi Fattan) shows three bottles labelled "Wilde",
--      "Botaniste" and "June" -- an unrelated fragrance line.
--
--  THE FIX: same remedy as migration 08 -- blank image_path so the site's
--  existing "Photography pending" tile renders instead of a wrong photo.
--  Real photography can be added per product afterward from the admin
--  panel.
--
--  Idempotent: safe to run more than once, and safe to run regardless of
--  which earlier migrations have already run on this database.
-- ============================================================

BEGIN;

UPDATE perfumes SET image_path = '' WHERE id IN (36, 47, 59, 87, 108, 111, 113);

COMMIT;
