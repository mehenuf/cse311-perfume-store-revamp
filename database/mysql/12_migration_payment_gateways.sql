-- ============================================================
--  Perfume Store - migration: online payment gateways
--
--  Adds the columns and table needed for SSLCommerz (bKash, Rocket,
--  Nagad, Bangla QR and local cards, all through SSLCommerz's own
--  hosted checkout), Stripe (cards, Google Pay, Apple Pay) and Coinbase
--  Commerce (crypto) alongside the existing Cash on Delivery flow.
--
--  orders.payment_mode already exists (COD/'...') and now also takes
--  'SSLCOMMERZ', 'STRIPE', 'COINBASE'. orders.payment_id already exists
--  and is repurposed to hold the real gateway session/charge id for
--  online payments instead of the placeholder random int COD used.
--
--  payment_status tracks the money separately from the existing
--  fulfilment `status` column (Processing/Shipped/Delivered/...): a
--  COD order is 'cod' until collected, an online order is 'pending'
--  until its webhook confirms it, then 'paid' or 'failed'.
--
--  payment_events is the idempotency guard: SSLCommerz, Stripe and
--  Coinbase all retry a webhook on anything other than a 2xx response,
--  so the same event can arrive more than once. The unique constraint
--  on (gateway, event_id) is what makes a retry a no-op instead of a
--  second stock decrement / second confirmation email.
--
--  Two separate statements for the ALTER TABLE, same reasoning as
--  10_migration_add_gender.sql: some MySQL/MariaDB builds do not parse
--  a single ALTER TABLE that combines ADD COLUMN and ADD CONSTRAINT
--  with a comma. If a column already exists from a previous partial
--  run, only that one statement errors (1060 Duplicate column) --
--  skip it and run the rest.
-- ============================================================

ALTER TABLE orders
    ADD COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'cod',
    ADD COLUMN gateway_ref    VARCHAR(120)     NULL,
    ADD COLUMN currency       VARCHAR(10)  NOT NULL DEFAULT 'BDT',
    ADD COLUMN paid_at        TIMESTAMP        NULL;

-- payment_id was VARCHAR(50), sized for COD's placeholder random int.
-- A Stripe Checkout Session id alone commonly runs 60-70+ characters, so
-- this must widen before any gateway ever calls attachGatewayReference().
ALTER TABLE orders MODIFY COLUMN payment_id VARCHAR(191) NULL;

ALTER TABLE orders
    ADD CONSTRAINT ck_orders_payment_status
        CHECK (payment_status IN ('cod', 'pending', 'paid', 'failed', 'refunded'));

-- Every order placed before this migration was COD, so its money side is
-- already settled the same way COD orders always have been.
UPDATE orders SET payment_status = 'cod' WHERE payment_mode = 'COD';

CREATE TABLE payment_events (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    order_id     INT UNSIGNED  NOT NULL,
    gateway      VARCHAR(20)   NOT NULL,
    event_id     VARCHAR(150)  NOT NULL,
    event_type   VARCHAR(60)   NOT NULL,
    payload      TEXT              NULL,
    received_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP         NULL,

    CONSTRAINT pk_payment_events         PRIMARY KEY (id),
    CONSTRAINT uq_payment_events_event   UNIQUE (gateway, event_id),
    CONSTRAINT fk_payment_events_order   FOREIGN KEY (order_id)
        REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_payment_events_order ON payment_events (order_id);
