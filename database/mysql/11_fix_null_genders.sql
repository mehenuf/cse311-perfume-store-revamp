-- ============================================================
--  Perfume Store - repair: backfill perfumes.gender
--
--  Standalone and idempotent -- run this any time perfumes.gender is
--  NULL, empty, or just wrong for some rows (for example: the column
--  was added by hand, or 10_migration_add_gender.sql's ALTER TABLE ran
--  but its own UPDATE statements were never reached). It does not touch
--  the column definition at all, only the values, so it is safe to run
--  before or after 10_migration_add_gender.sql, and safe to run more
--  than once.
--
--  Covers every one of the 128 products seeded by 02_seed.sql,
--  03_seed_expansion.sql and 07_migration_more_perfumes.sql, classified
--  by how each fragrance is actually marketed. A product you added
--  yourself is untouched here -- set its gender from the edit page.
-- ============================================================

UPDATE perfumes SET gender = 'men' WHERE id IN (1,2,3,4,5,6,7,8,9,10,18,19,20,21,23,31,32,33,34,36,40,42,43,44,45,46,47,48,50,51,52,53,54,55,56,57,59,60,61,62,63,64,66,67,68,70,71,72,74,76,77,78,79,80,81,83,87,88,89,90,92,93,94,95,98,100,102,110,111,113,114,116,118,120,122,123,124,125,127);

UPDATE perfumes SET gender = 'women' WHERE id IN (11,12,58,65,73,75,82,91,96,97,99,101,103,112,115,117,119,121,126);

UPDATE perfumes SET gender = 'unisex' WHERE id IN (13,14,15,16,17,22,24,25,26,27,28,29,30,35,37,38,39,41,49,69,84,85,86,104,105,106,107,108,109,128);

-- Anything outside that id range (a product you added yourself) that is
-- still NULL or empty falls back to 'unisex' -- the same default a fresh
-- ADD COLUMN gives, rather than being left unset.
UPDATE perfumes SET gender = 'unisex' WHERE gender IS NULL OR gender = '';
