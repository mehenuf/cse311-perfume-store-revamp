-- ============================================================
--  Perfume Store - migration: product gender
--
--  Adds perfumes.gender so the collection filter can offer a Men /
--  Women / Unisex toggle. Safe to run on a live database: every
--  existing row gets 'unisex' first (the same as "unfiltered" on the
--  storefront), then the UPDATE below backfills the real classification
--  for every product already seeded by 02_seed.sql, 03_seed_expansion.sql
--  and 07_migration_more_perfumes.sql. A product you added yourself
--  keeps 'unisex' until you set it from the edit page.
-- ============================================================

ALTER TABLE perfumes
    ADD COLUMN gender VARCHAR(10) NOT NULL DEFAULT 'unisex',
    ADD CONSTRAINT ck_perfumes_gender CHECK (gender IN ('men', 'women', 'unisex'));

UPDATE perfumes SET gender = 'men' WHERE id IN (1,2,3,4,5,6,7,8,9,10,18,19,20,21,23,31,32,33,34,36,40,42,43,44,45,46,47,48,50,51,52,53,54,55,56,57,59,60,61,62,63,64,66,67,68,70,71,72,74,76,77,78,79,80,81,83,87,88,89,90,92,93,94,95,98,100,102,110,111,113,114,116,118,120,122,123,124,125,127);

UPDATE perfumes SET gender = 'women' WHERE id IN (11,12,58,65,73,75,82,91,96,97,99,101,103,112,115,117,119,121,126);

UPDATE perfumes SET gender = 'unisex' WHERE id IN (13,14,15,16,17,22,24,25,26,27,28,29,30,35,37,38,39,41,49,69,84,85,86,104,105,106,107,108,109,128);
