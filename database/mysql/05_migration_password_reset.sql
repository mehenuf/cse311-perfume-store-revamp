-- ============================================================
--  Perfume Store - migration: password reset
--
--  Adds the columns "Forgot password?" needs. Run this once. Safe to run
--  on a live database: it only adds nullable columns, so every existing
--  row is unaffected (both new columns start NULL, meaning "no reset
--  pending").
--
--  Only a SHA-256 hash of the reset token is ever stored, the same way
--  the password itself is never stored in reversible form -- a stolen
--  copy of this table cannot be used to reset anyone's password.
-- ============================================================

ALTER TABLE customer
    ADD COLUMN reset_token_hash    CHAR(64)  NULL,
    ADD COLUMN reset_token_expires TIMESTAMP NULL;
