-- ============================================================
--  Perfume Store - Migration 09: Restore verified catalogue photography
--
--  Migration 08 blanked image_path for 60 products whose photography
--  turned out wrong (see that file's header for the full incident),
--  which made them render the "Photography pending" tile. Real
--  photography has since been sourced and verified for all 60:
--
--    - Official, verified product photography for houses where that is
--      freely available (Givenchy, Jean Paul Gaultier, Giorgio Armani,
--      Paco Rabanne, Prada, Mancera, Yves Saint Laurent, Salvatore
--      Ferragamo, Kilian).
--    - Tasteful, unbranded editorial/stock photography -- never the
--      specific branded bottle -- for Creed, Rasasi, Rayhaan and Afnan,
--      whose official product photography is not freely licensed for
--      reuse (same convention as the site's existing st_*.jpg stock
--      photos used elsewhere in the catalogue).
--
--  Run this AFTER uploading the corresponding image files to images/.
--  Idempotent: safe to run more than once, and safe to run whether or
--  not migration 08 ever ran on this database.
-- ============================================================

UPDATE perfumes SET image_path = CASE id
    WHEN 40  THEN 'creed_aventus.jpg'
    WHEN 47  THEN 'rayhaan_bariq.jpg'
    WHEN 48  THEN 'rayhaan_roberto_rayhaan_noir.jpg'
    WHEN 72  THEN 'givenchy_gentleman_reserve_privee.jpg'
    WHEN 73  THEN 'givenchy_linterdit.jpg'
    WHEN 74  THEN 'jean_paul_gaultier_le_male_le_parfum.jpg'
    WHEN 75  THEN 'jean_paul_gaultier_la_belle.jpg'
    WHEN 76  THEN 'prada_lhomme_intense.jpg'
    WHEN 77  THEN 'prada_amber_pour_homme.jpg'
    WHEN 78  THEN 'giorgio_armani_acqua_di_gio_profondo.jpg'
    WHEN 79  THEN 'giorgio_armani_code_absolu.jpg'
    WHEN 80  THEN 'paco_rabanne_1_million_elixir.jpg'
    WHEN 81  THEN 'paco_rabanne_phantom.jpg'
    WHEN 82  THEN 'kilian_good_girl_gone_bad.jpg'
    WHEN 83  THEN 'afnan_supremacy_silver.jpg'
    WHEN 84  THEN 'afnan_supremacy_in_heaven.jpg'
    WHEN 85  THEN 'afnan_modest_une.jpg'
    WHEN 86  THEN 'afnan_9am_dive.jpg'
    WHEN 87  THEN 'afnan_historic_olmeda.jpg'
    WHEN 88  THEN 'jean_paul_gaultier_le_male.jpg'
    WHEN 89  THEN 'jean_paul_gaultier_scandal_pour_homme_le_parfum.jpg'
    WHEN 90  THEN 'jean_paul_gaultier_le_beau.jpg'
    WHEN 91  THEN 'jean_paul_gaultier_classique.jpg'
    WHEN 92  THEN 'jean_paul_gaultier_le_male_elixir.jpg'
    WHEN 93  THEN 'givenchy_pi.jpg'
    WHEN 94  THEN 'givenchy_gentleman_eau_de_parfum.jpg'
    WHEN 95  THEN 'givenchy_xeryus_rouge.jpg'
    WHEN 96  THEN 'givenchy_amarige.jpg'
    WHEN 97  THEN 'givenchy_l_interdit_rouge.jpg'
    WHEN 98  THEN 'paco_rabanne_black_xs.jpg'
    WHEN 99  THEN 'paco_rabanne_olympea.jpg'
    WHEN 100 THEN 'paco_rabanne_pure_xs.jpg'
    WHEN 101 THEN 'paco_rabanne_lady_million.jpg'
    WHEN 102 THEN 'paco_rabanne_1_million_parfum.jpg'
    WHEN 103 THEN 'mancera_roses_vanille.jpg'
    WHEN 104 THEN 'mancera_aoud_lemon_mint.jpg'
    WHEN 105 THEN 'mancera_holidays.jpg'
    WHEN 106 THEN 'mancera_coco_vanille.jpg'
    WHEN 107 THEN 'mancera_black_gold.jpg'
    WHEN 108 THEN 'creed_millesime_imperial.jpg'
    WHEN 109 THEN 'creed_virgin_island_water.jpg'
    WHEN 110 THEN 'creed_royal_oud.jpg'
    WHEN 111 THEN 'creed_himalaya.jpg'
    WHEN 112 THEN 'creed_love_in_white.jpg'
    WHEN 113 THEN 'rasasi_fattan.jpg'
    WHEN 114 THEN 'rasasi_shuhrah_pour_homme.jpg'
    WHEN 115 THEN 'rasasi_la_yuqawam_jasmine_wisp.jpg'
    WHEN 116 THEN 'rasasi_al_wisam_day.jpg'
    WHEN 117 THEN 'rasasi_hawas_for_her.jpg'
    WHEN 118 THEN 'yves_saint_laurent_l_homme.jpg'
    WHEN 119 THEN 'yves_saint_laurent_libre.jpg'
    WHEN 120 THEN 'yves_saint_laurent_y_le_parfum.jpg'
    WHEN 121 THEN 'yves_saint_laurent_opium.jpg'
    WHEN 122 THEN 'yves_saint_laurent_tuxedo.jpg'
    WHEN 123 THEN 'salvatore_ferragamo_uomo.jpg'
    WHEN 124 THEN 'salvatore_ferragamo_uomo_signature.jpg'
    WHEN 125 THEN 'salvatore_ferragamo_f_by_ferragamo_pour_homme.jpg'
    WHEN 126 THEN 'salvatore_ferragamo_signorina.jpg'
    WHEN 127 THEN 'salvatore_ferragamo_acqua_essenziale_blu.jpg'
    WHEN 128 THEN 'salvatore_ferragamo_ferragamo_intense_leather.jpg'
    ELSE image_path
END
WHERE id IN (40, 47, 48, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84,
             85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99,
             100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111,
             112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123,
             124, 125, 126, 127, 128);
