-- ============================================================
--  Perfume Store - MySQL / MariaDB schema
--  Reconstructed from the PHP source (mysqli queries).
--  Works with XAMPP, and with free MySQL-compatible clouds
--  (TiDB Cloud Serverless, Aiven for MySQL, Clever Cloud).
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_item;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS perfumes;
DROP TABLE IF EXISTS customer;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- customer : site users. admin_check = 1 unlocks /admin.
-- Referenced by cart.user_id and orders.user_id.
-- ------------------------------------------------------------
CREATE TABLE customer (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username     VARCHAR(50)  NOT NULL,
    password     VARCHAR(255) NOT NULL,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    contacts     VARCHAR(30)      NULL,
    address      VARCHAR(255)     NULL,
    dob          DATE             NULL,
    admin_check  TINYINT(1)   NOT NULL DEFAULT 0,
    -- Password reset. Only a SHA-256 hash of the reset token is ever stored,
    -- the same way the password itself is never stored in reversible form --
    -- a stolen copy of this table cannot be used to reset anyone's password.
    reset_token_hash    CHAR(64)  NULL,
    reset_token_expires TIMESTAMP NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_customer          PRIMARY KEY (id),
    CONSTRAINT uq_customer_username UNIQUE (username),
    CONSTRAINT uq_customer_email    UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- perfumes : product catalogue.
--   status   1 = published (visible on the storefront)
--   trending 1 = shown in the home-page carousel
-- name is UNIQUE because display-perfume.php?name=... resolves
-- a single product by its name.
-- ------------------------------------------------------------
CREATE TABLE perfumes (
    id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    name          VARCHAR(150)   NOT NULL,
    perfume_notes TEXT               NULL,
    description   TEXT               NULL,
    volume        VARCHAR(30)        NULL,
    qty           INT UNSIGNED   NOT NULL DEFAULT 0,
    image_path    VARCHAR(255)       NULL,
    price         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    trending      TINYINT(1)     NOT NULL DEFAULT 0,
    status        TINYINT(1)     NOT NULL DEFAULT 1,
    -- A time-boxed discount. discount_percent = 0 means no discount at all,
    -- regardless of the dates -- the window only matters once a percentage
    -- is actually set. price itself never changes: it is always the
    -- original price, and the discounted price is computed from it.
    discount_percent   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    discount_starts_at TIMESTAMP            NULL,
    discount_ends_at   TIMESTAMP            NULL,
    -- Who the product is marketed for -- the collection page's gender
    -- filter reads this directly. 'unisex' is the safe default for a
    -- product added without picking one.
    gender        VARCHAR(10)    NOT NULL DEFAULT 'unisex',
    created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_perfumes       PRIMARY KEY (id),
    CONSTRAINT uq_perfumes_name  UNIQUE (name),
    CONSTRAINT ck_perfumes_price CHECK (price >= 0),
    CONSTRAINT ck_perfumes_discount_percent CHECK (discount_percent BETWEEN 0 AND 90),
    CONSTRAINT ck_perfumes_discount_window CHECK (
        discount_ends_at IS NULL OR discount_starts_at IS NULL OR discount_ends_at > discount_starts_at
    ),
    CONSTRAINT ck_perfumes_gender CHECK (gender IN ('men', 'women', 'unisex'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_perfumes_status   ON perfumes (status);
CREATE INDEX idx_perfumes_trending ON perfumes (trending, status);

-- ------------------------------------------------------------
-- cart : one live row per (customer, perfume).
-- The UNIQUE pair enforces at DB level what cart-function.php
-- checks in PHP before inserting.
-- Both parents CASCADE: a cart line has no meaning without them.
-- ------------------------------------------------------------
CREATE TABLE cart (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    perfume_id  INT UNSIGNED NOT NULL,
    perfume_qty INT UNSIGNED NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_cart              PRIMARY KEY (id),
    CONSTRAINT uq_cart_user_perfume UNIQUE (user_id, perfume_id),
    CONSTRAINT fk_cart_user         FOREIGN KEY (user_id)
        REFERENCES customer (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_perfume      FOREIGN KEY (perfume_id)
        REFERENCES perfumes (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT ck_cart_qty          CHECK (perfume_qty > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- orders : order header. tracking_no is the public handle used
-- by order-details.php?trackid=... so it is UNIQUE.
--   status 0=Processing 1=Completed 2=Shipped 3=Delivered 4=Cancelled
-- user_id is NULL for a guest checkout (no account was created) and
-- RESTRICT for a real customer: order history must survive, so a
-- customer with orders cannot be hard-deleted. name/email/contacts/
-- address are stored directly on the row either way, so a guest order
-- is fully self-contained without a customer row.
-- ------------------------------------------------------------
CREATE TABLE orders (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    tracking_no  VARCHAR(40)   NOT NULL,
    user_id      INT UNSIGNED      NULL,
    name         VARCHAR(100)  NOT NULL,
    email        VARCHAR(150)  NOT NULL,
    contacts     VARCHAR(30)       NULL,
    address      VARCHAR(255)      NULL,
    zipcode      VARCHAR(15)       NULL,
    total_price  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_mode VARCHAR(30)   NOT NULL DEFAULT 'COD',
    payment_id   VARCHAR(50)       NULL,
    status       TINYINT       NOT NULL DEFAULT 0,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_orders          PRIMARY KEY (id),
    CONSTRAINT uq_orders_tracking UNIQUE (tracking_no),
    CONSTRAINT fk_orders_user     FOREIGN KEY (user_id)
        REFERENCES customer (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_orders_status   CHECK (status BETWEEN 0 AND 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_orders_user    ON orders (user_id, created_at);
CREATE INDEX idx_orders_status  ON orders (status);

-- ------------------------------------------------------------
-- order_item : order lines. price is copied at purchase time so
-- later catalogue price edits do not rewrite history.
--   order_id   CASCADE  - lines belong to their header
--   perfume_id RESTRICT - a perfume that was ever sold cannot be
--                         hard-deleted; unpublish it (status = 0)
-- ------------------------------------------------------------
CREATE TABLE order_item (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    order_id    INT UNSIGNED  NOT NULL,
    perfume_id  INT UNSIGNED  NOT NULL,
    perfume_qty INT UNSIGNED  NOT NULL DEFAULT 1,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT pk_order_item          PRIMARY KEY (id),
    CONSTRAINT uq_order_item_line     UNIQUE (order_id, perfume_id),
    CONSTRAINT fk_order_item_order    FOREIGN KEY (order_id)
        REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_item_perfume  FOREIGN KEY (perfume_id)
        REFERENCES perfumes (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_order_item_qty      CHECK (perfume_qty > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_order_item_order   ON order_item (order_id);
CREATE INDEX idx_order_item_perfume ON order_item (perfume_id);
