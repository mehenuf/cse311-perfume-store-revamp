CREATE TABLE customer (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    contacts     VARCHAR(30),
    address      VARCHAR(255),
    dob          DATE,
    admin_check  INTEGER NOT NULL DEFAULT 0,
    reset_token_hash    CHAR(64),
    reset_token_expires TIMESTAMP
);

CREATE TABLE perfumes (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          VARCHAR(150) NOT NULL UNIQUE,
    perfume_notes TEXT,
    description   TEXT,
    volume        VARCHAR(30),
    qty           INTEGER NOT NULL DEFAULT 0,
    image_path    VARCHAR(255),
    price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    trending      INTEGER NOT NULL DEFAULT 0,
    status        INTEGER NOT NULL DEFAULT 1,
    discount_percent   INTEGER NOT NULL DEFAULT 0,
    discount_starts_at TIMESTAMP,
    discount_ends_at   TIMESTAMP
);

CREATE TABLE cart (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES customer(id),
    perfume_id  INTEGER NOT NULL REFERENCES perfumes(id),
    perfume_qty INTEGER NOT NULL DEFAULT 1,
    UNIQUE (user_id, perfume_id)
);

CREATE TABLE orders (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    tracking_no  VARCHAR(40) NOT NULL UNIQUE,
    user_id      INTEGER REFERENCES customer(id), -- NULL for a guest checkout
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    contacts     VARCHAR(30),
    address      VARCHAR(255),
    zipcode      VARCHAR(15),
    total_price  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_mode VARCHAR(30) NOT NULL DEFAULT 'COD',
    payment_id   VARCHAR(50),
    status       INTEGER NOT NULL DEFAULT 0,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_item (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id    INTEGER NOT NULL REFERENCES orders(id),
    perfume_id  INTEGER NOT NULL REFERENCES perfumes(id),
    perfume_qty INTEGER NOT NULL DEFAULT 1,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00
);
