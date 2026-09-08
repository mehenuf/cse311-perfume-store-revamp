-- ============================================================
--  Perfume Store - migration: online payment gateways
--
--  See mysql/12_migration_payment_gateways.sql for the full explanation.
--  This is the same migration for PostgreSQL.
-- ============================================================

BEGIN;

ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'cod';
ALTER TABLE orders ADD COLUMN gateway_ref    VARCHAR(120);
ALTER TABLE orders ADD COLUMN currency       VARCHAR(10)  NOT NULL DEFAULT 'BDT';
ALTER TABLE orders ADD COLUMN paid_at        TIMESTAMP;

-- payment_id was VARCHAR(50), sized for COD's placeholder random int.
-- A Stripe Checkout Session id alone commonly runs 60-70+ characters, so
-- this must widen before any gateway ever calls attachGatewayReference().
ALTER TABLE orders ALTER COLUMN payment_id TYPE VARCHAR(191);

ALTER TABLE orders
    ADD CONSTRAINT ck_orders_payment_status
        CHECK (payment_status IN ('cod', 'pending', 'paid', 'failed', 'refunded'));

UPDATE orders SET payment_status = 'cod' WHERE payment_mode = 'COD';

CREATE TABLE payment_events (
    id           SERIAL       PRIMARY KEY,
    order_id     INTEGER      NOT NULL REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
    gateway      VARCHAR(20)  NOT NULL,
    event_id     VARCHAR(150) NOT NULL,
    event_type   VARCHAR(60)  NOT NULL,
    payload      TEXT,
    received_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP,

    CONSTRAINT uq_payment_events_event UNIQUE (gateway, event_id)
);

CREATE INDEX idx_payment_events_order ON payment_events (order_id);

COMMIT;
