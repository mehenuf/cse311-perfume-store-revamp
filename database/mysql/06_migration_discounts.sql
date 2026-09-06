-- ============================================================
--  Perfume Store - migration: time-boxed discounts
--
--  Adds the columns the admin discount feature needs. Run this once.
--  Safe to run on a live database: every existing row gets
--  discount_percent = 0 (no discount), which is the same price behaviour
--  as before this migration existed. price itself is untouched -- it is
--  always the original price, and the discounted price is computed from
--  it on the fly, never stored.
-- ============================================================

ALTER TABLE perfumes
    ADD COLUMN discount_percent   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN discount_starts_at TIMESTAMP            NULL,
    ADD COLUMN discount_ends_at   TIMESTAMP            NULL,
    ADD CONSTRAINT ck_perfumes_discount_percent CHECK (discount_percent BETWEEN 0 AND 90),
    ADD CONSTRAINT ck_perfumes_discount_window CHECK (
        discount_ends_at IS NULL OR discount_starts_at IS NULL OR discount_ends_at > discount_starts_at
    );
