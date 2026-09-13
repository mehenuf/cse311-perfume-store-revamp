-- ============================================================
--  Perfume Store - Migration 14: Give the last mismatched products
--  a real photo instead of the "Photography pending" placeholder
--
--  Migration 13 blanked image_path for 7 products whose photo was
--  visually confirmed wrong, and a follow-up audit of the site's own
--  "st_*.jpg" mood-photography files (used for houses whose official
--  product photography isn't freely licensed) found an 8th: id 45
--  (Rasasi La Yuqawam Pour Homme) was showing st_amber_bottles_bokeh.jpg,
--  which is actually a real Lancome/Dior/Jean Paul Gaultier bottle shot,
--  not Rasasi.
--
--  None of these 8 products has real, correctly-branded photography
--  available (see 13's header for why), so each gets a new, distinct,
--  freely-licensed (Pexels), unbranded mood photo instead of leaving it
--  blank -- same convention as the site's existing st_*.jpg files.
--
--  Idempotent: safe to run more than once, and safe to run whether or
--  not migration 13 ever ran on this database (it does not depend on
--  image_path being blank first).
-- ============================================================

UPDATE perfumes SET image_path = CASE id
    WHEN 36  THEN 'st_amber_dappled_light.jpg'
    WHEN 45  THEN 'st_peach_marble_minimal.jpg'
    WHEN 47  THEN 'st_amber_trio_gradient.jpg'
    WHEN 59  THEN 'st_clear_sand_blank.jpg'
    WHEN 87  THEN 'st_ruby_wool_eucalyptus.jpg'
    WHEN 108 THEN 'st_gold_velvet_attar.jpg'
    WHEN 111 THEN 'st_frosted_stones_ice.jpg'
    WHEN 113 THEN 'st_sage_dune.jpg'
    ELSE image_path
END
WHERE id IN (36, 45, 47, 59, 87, 108, 111, 113);
