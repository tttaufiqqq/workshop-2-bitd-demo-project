# Spec 01 — Config and Connection Helpers

## Goal

Produce `config.php` and the three `includes/db_*.php` helpers.
These are the foundation — everything else depends on them.

## Design

`config.php` uses `define()` constants (not variables). Constants are
globally readable without passing `$config` arrays around. Students can
edit one file to change any setting.

Each helper exposes exactly one function returning a `PDO` instance.
No singleton, no global variable — just a function. Simple to read, simple
to call, simple to understand.

## Implementation

### config.php

- 5-second `PDO::ATTR_TIMEOUT` on all connections
- `APP_DEBUG = true` in template; students must set false for any public demo
- Placeholder IPs `100.x.x.x` — never real IPs in the committed file
- Comment above each node explaining how to find the Tailscale IP

### includes/db_mariadb.php

- Function name: `getMariaDBConnection()`
- DSN: `mysql:host=DB_A_HOST;port=DB_A_PORT;dbname=DB_A_NAME;charset=utf8mb4`
- On `PDOException`: if `APP_DEBUG` show full message; else generic message
- `require_once __DIR__ . '/../config.php'`

### includes/db_mysql.php

- Function name: `getMySQLConnection()`
- DSN: same pattern as MariaDB (same driver)
- Uses `DB_B_*` constants

### includes/db_postgres.php

- Function name: `getPostgresConnection()`
- DSN: `pgsql:host=DB_C_HOST;port=DB_C_PORT;dbname=DB_C_NAME`
- Note: NO `charset=utf8mb4` in pgsql DSN — that's MySQL-only
- Uses `DB_C_*` constants

## Dependencies

None. This is the base layer.

## Files Changed

- `config.php`
- `includes/db_mariadb.php`
- `includes/db_mysql.php`
- `includes/db_postgres.php`

## Verify

- [ ] `php -l config.php` exits with no errors
- [ ] `php -l includes/db_mariadb.php` exits with no errors
- [ ] `php -l includes/db_mysql.php` exits with no errors
- [ ] `php -l includes/db_postgres.php` exits with no errors
- [ ] Each helper has exactly one public function
- [ ] No `$variables` used for constants — all are `define()`
- [ ] pgsql DSN has no `charset` parameter
