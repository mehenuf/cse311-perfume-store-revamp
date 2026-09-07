# Tests

A lightweight regression suite for the request handlers that matter most if
they break: login/registration, the admin access-control guard, and the
catalogue/cart write endpoints.

```
php tests/run.php
```

Needs only a PHP CLI with `pdo_sqlite` enabled — no MySQL, no XAMPP, no
Composer. Almost every PHP build ships `pdo_sqlite` enabled by default
(including XAMPP's), so this is usually a zero-setup `php tests/run.php`.

## What this is, and isn't

Each scenario in `scenarios/` runs the real application file — e.g.
`functions/authcode.php` — completely unmodified, against a throwaway
SQLite database instead of MySQL. `support/mysqli_shim.php` makes that
possible: it defines the handful of `mysqli_*` procedural functions this
project actually calls, backed by SQLite via PDO, so the app's own
mysqli-based code runs as-is and gets asserted on for real rather than only
read by eye.

That also means it is **not** a substitute for testing against real MySQL —
column types, collation, and MySQL-specific SQL are not exercised. Do the
manual pass in `README.md` ("Test the full path once") before a real
launch. What this suite is good at catching:

- an admin endpoint losing its auth check (this is exactly how
  `admin/Includes/code.php` was found to have none at all)
- the login/registration flow regressing on password hashing
- SQL built by string concatenation creeping back in — the injection and
  apostrophe fixtures in `scenarios/` fail loudly against concatenated SQL,
  whether or not the specific payload is exploitable
- cross-user access on cart rows (IDOR)

## Previewing the whole app without MySQL

`support/localserver_bootstrap.php` reuses the same shim to run the *entire*
site -- storefront and admin -- against a throwaway SQLite database, seeded
from `database/_seed_body.sql`. Useful for a quick visual pass when a real
MySQL server isn't handy:

```
php -d auto_prepend_file=tests/support/localserver_bootstrap.php -S localhost:8000
```

If your PHP build loads the real `mysqli` extension, it will conflict with
the shim's `mysqli_*` function definitions ("Cannot redeclare"); run with an
ini that has `extension=mysqli` disabled. Log in with any seed account, e.g.
`mehenuf` / `admin123` (admin) or `arif` / `arif1234` (customer). This is a
dev convenience only, like `tools/build_preview.py` -- not part of the
deployed app, and not a substitute for the real MySQL pass in the top-level
`README.md`.

## Adding a scenario

Copy the shape of an existing file in `scenarios/`: boot a fresh SQLite
database from `support/schema.sql`, seed whatever rows the scenario needs,
set `$_SESSION`/`$_POST`/`$_FILES`, call `shim_report()` with the
assertions to check, then `include` the real application file. Use
`shim_report()` (not a plain `echo`) even for files that don't call
`exit()` — it runs on shutdown, so it reports correctly either way.
