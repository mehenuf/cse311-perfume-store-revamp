# Perfume Store — database

The original `.sql` dump was lost. Everything here was reconstructed by reading
the PHP source: every `SELECT` / `INSERT` / `UPDATE` / `DELETE` string, and every
`$row['column']` access across all 42 PHP files.

```
database/
├── mysql/01_schema.sql      MySQL / MariaDB DDL   (XAMPP + managed MySQL)
├── mysql/02_seed.sql        MySQL / MariaDB data
├── postgres/01_schema.sql   PostgreSQL DDL        (Neon / Supabase / Render)
├── postgres/02_seed.sql     PostgreSQL data
├── _seed_body.sql           the shared INSERT block both seeds embed
└── verify.py                executable proof the reconstruction is consistent
```

---

## The model

```
customer ──1:N─→ cart ←─N:1── perfumes
    │                             ▲
    └──1:N─→ orders ──1:N─→ order_item ──N:1─┘
```

| Table | PK | Unique keys | Foreign keys |
|---|---|---|---|
| `customer` | `id` | `username`, `email` | — |
| `perfumes` | `id` | `name` | — |
| `cart` | `id` | `(user_id, perfume_id)` | `user_id`→`customer` **CASCADE**, `perfume_id`→`perfumes` **CASCADE** |
| `orders` | `id` | `tracking_no` | `user_id`→`customer` **RESTRICT** |
| `order_item` | `id` | `(order_id, perfume_id)` | `order_id`→`orders` **CASCADE**, `perfume_id`→`perfumes` **RESTRICT** |

Why those delete rules:

- **cart → CASCADE.** A basket line is meaningless without its customer or its
  product, so it should disappear with either.
- **orders.user_id → RESTRICT.** Order history is a financial record. A customer
  who has ordered cannot be hard-deleted; deactivate them instead.
- **order_item.order_id → CASCADE.** Lines belong to their header.
- **order_item.perfume_id → RESTRICT.** A perfume that has ever been sold cannot
  be hard-deleted, or past invoices would lose their product. **Unpublish it
  instead** (`status = 0`) — the admin panel already renders that as
  "Unpublished". The admin Delete button still works for products never ordered.

Two unique keys were inferred rather than copied:

- `perfumes.name` — `display-perfume.php?name=…` resolves a product by name and
  reads a single row, so names must be unique.
- `cart (user_id, perfume_id)` — `cart-function.php` checks for an existing row
  before inserting; the constraint enforces that in the database too.

Status codes, taken from `order-details.php` and `admin/order-history.php`:
`0` Processing · `1` Completed · `2` Shipped · `3` Delivered · `4` Cancelled.

## The data

39 products, matching all 39 files already in `/images` — Dior (7), Chanel (5),
Tom Ford (5), Mancera (5), Hugo Boss (4), Lattafa (4), plus Armaf, Rasasi,
Givenchy, JPG, Kilian, Ferragamo, Luxodor, Spectre and one unpublished row so
the published/unpublished split is exercised.

Product names deliberately start with the brand, because the brand pages filter
with `name LIKE '%dior%'`, `'%tom%ford%'` and so on.

**Prices are in Bangladeshi Taka**, converted from each product's international
USD retail price at **1 USD = 120 BDT**, rounded to the nearest 10 Tk. The
storefront prints `Tk. <price>`.

Also seeded: 6 customers (2 admins), 6 live cart rows, and 5 orders covering
every status code, whose `total_price` equals the sum of their line items.

### Logins

| Username | Password | Role |
|---|---|---|
| `mehenuf` | `admin123` | admin |
| `shopadmin` | `admin123` | admin |
| `arif` / `nusrat` / `tanvir` / `sadia` | `<username>1234` | customer |

> ⚠️ Passwords are stored in **plain text** because `functions/authcode.php`
> logs in with `WHERE username = ? AND password = ?`. That is how the original
> app worked and the seed matches it, but it is the single most important thing
> to fix before this is public — see *Known issues* below.

## Verifying

```bash
python database/verify.py
```

Loads the schema and seed into SQLite with foreign keys enforced and runs 31
assertions: primary keys, the exact foreign key set, orphan scans, order totals
against their line items, brand-page filters returning products, every
`image_path` resolving to a real file, and the CASCADE/RESTRICT rules actually
behaving. All 31 currently pass.

---

## Installing

### MySQL / MariaDB — including XAMPP

```bash
mysql -u root -e "CREATE DATABASE perfumestore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root perfumestore < database/mysql/01_schema.sql
mysql -u root perfumestore < database/mysql/02_seed.sql
```

In phpMyAdmin: create the `perfumestore` database, then Import each file in order.

### PostgreSQL

```bash
psql "$DATABASE_URL" -f database/postgres/01_schema.sql
psql "$DATABASE_URL" -f database/postgres/02_seed.sql
```

---

## Hosting it for free

**Vercel does not run PHP.** It serves static files and Node/Python/Go/Ruby
functions; the only PHP support is an unofficial community runtime, and even
that breaks this app because the admin panel writes uploaded images to
`/images`, and serverless filesystems are read-only. Deploying this app as-is to
Vercel is not a path worth taking.

Two paths that do work, both free:

### Path A — keep MySQL (recommended, no code changes)

The app is written against `mysqli`. Point it at a managed MySQL and nothing
else has to change.

| Piece | Free option |
|---|---|
| Database | **TiDB Cloud Serverless** (MySQL wire-compatible, 25 GiB free) or **Aiven for MySQL** free plan |
| PHP hosting | **InfinityFree** (PHP + persistent disk), or **Render** / **Koyeb** with a small PHP Docker image |

Set these environment variables on the host — `config/dbcon.php` now reads them,
and falls back to the old XAMPP defaults when they are absent, so local
development is unaffected:

```
DB_HOST=gateway01.eu-central-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_USER=your_user
DB_PASS=your_password
DB_NAME=perfumestore
DB_SSL=1
```

Load `mysql/01_schema.sql` then `mysql/02_seed.sql` into it and you are live.

### Path B — PostgreSQL (Neon / Supabase)

`postgres/01_schema.sql` is ready, but the **PHP is not** — it calls `mysqli_*`
throughout, which cannot talk to Postgres. Choosing this path means swapping the
data layer to PDO (`pgsql`). It is a contained job — only 8 distinct functions
are used across the codebase (`mysqli_query`, `mysqli_num_rows`,
`mysqli_fetch_array`, `mysqli_real_escape_string`, `mysqli_escape_string`,
`mysqli_insert_id`, `mysqli_connect`, `mysqli_connect_error`) — but it is a real
change, so it is not done here. Two extra things it needs:

1. **`LIKE` is case-sensitive in Postgres.** The six brand functions in
   `functions/brandsearchfunctions.php` use `name LIKE '%dior%'` and would return
   nothing. Change `LIKE` to `ILIKE` — the `idx_perfumes_name_lower` index in the
   schema is already there to support it.
2. `status` and `trending` are `SMALLINT`, not `BOOLEAN`, on purpose, so the
   existing `== 1` and `== '1'` comparisons in the PHP keep working.

---

## Fixes applied while reconstructing

Three genuine bugs surfaced during the analysis and were corrected:

1. **`admin/perfume.php`** read `$item['Status']` while every SQL statement
   writes lowercase `status`. On MySQL the key lookup returned nothing, so the
   admin product list showed **every** perfume as "Unpublished". Now `status`.
2. **`admin/edit-perfume.php`** read `$data['Trending']` and `$data['Status']`
   for the two checkboxes, so the edit form always rendered them unchecked —
   silently clearing both flags on save. Now lowercase.
3. **`admin/Includes/code.php`** read `$perfume_data['image']`, which is not a
   column. The column is `image_path`.

Also consolidated: `functions/functions.php` and
`functions/brandsearchfunctions.php` each opened their own hardcoded
`localhost` / `root` connection. Both now `require_once` `config/dbcon.php`, so
there is exactly one place to configure credentials.

## Known issues (not fixed — they are app changes, not schema changes)

- **Plain-text passwords.** Move to `password_hash()` / `password_verify()`.
  Doing so means re-seeding the accounts above with hashes.
- **SQL injection.** Values are interpolated into query strings throughout;
  several paths (`$_GET['id']`, `$_GET['name']`, `$_GET['trackid']`) are not
  escaped at all. Prepared statements are the fix.
- **`dob` is a `DATE`** but the registration form is a free-text input. An empty
  or malformed date makes the insert fail under MySQL strict mode, which the app
  reports only as "Something went wrong". Validate before inserting, or store
  `NULL`.
- **Stock can go negative.** `placeorder.php` subtracts the ordered quantity
  without checking availability first.
