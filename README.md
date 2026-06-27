# Distributed DB Demo — Student Order System

A PHP demo project showing how to build a system that reads and writes across
**three separate database engines** (MariaDB, MySQL, PostgreSQL) running on
**different machines** connected via **Tailscale**.

---

## What This Demo Shows

| Page | What it teaches |
|---|---|
| `setup/test_connections.php` | How to verify remote DB connectivity |
| `pages/index.php` (Dashboard) | How distributed nodes fail independently |
| `pages/register.php` | Write to a remote MariaDB from PHP |
| `pages/orders.php` | Cross-node write: verify on Node A → insert on Node B → sync to Node C |
| `pages/reports.php` | Application-level join across 3 separate servers |

---

## Architecture

```
Node A (Linux)    Node B (Windows)    Node C (any OS)
MariaDB           MySQL               PostgreSQL
node_a_users      node_b_orders       node_c_reports
    ▲                  ▲                   ▲
    └──────────────────┼───────────────────┘
                       │
              PHP web server
              (runs on one machine)
              connects to all 3 via Tailscale
```

All three machines must be on **Tailscale** — a free VPN that gives each
device a stable `100.x.x.x` IP regardless of which Wi-Fi network it's on.

---

## Setup — Read This In Order

### 1. Install and Connect Tailscale

Follow the full guide: [`setup/tailscale_guide.md`](setup/tailscale_guide.md)

After setup, every machine should be able to ping the others:
```bash
ping 100.x.x.x   # Tailscale IP of teammate's machine
```

### 2. Set Up Each Database Node

Run the SQL file for each node **on the machine that hosts that DB**:

**Node A — MariaDB (Linux machine):**
```bash
mysql -u root -p < db/node_a_mariadb.sql
```

**Node B — MySQL (Windows machine):**
```bash
# In Command Prompt:
mysql -u root -p < db\node_b_mysql.sql
```

**Node C — PostgreSQL (any machine):**
```bash
psql -U postgres -f db/node_c_postgres.sql
```

> **PostgreSQL extra step:** You must also configure remote access.
> See [`docs/environment.md`](docs/environment.md) — "PostgreSQL Remote Access Configuration".

### 3. Edit config.php

Open `config.php` and replace the placeholder IPs with your actual Tailscale IPs:

```php
define('DB_A_HOST', '100.x.x.1');   // ← Node A's Tailscale IP
define('DB_B_HOST', '100.x.x.2');   // ← Node B's Tailscale IP
define('DB_C_HOST', '100.x.x.3');   // ← Node C's Tailscale IP
```

> `config.php` is listed in `.gitignore`. Never commit it with real IPs.

### 4. Enable PHP Extensions

Check that your PHP installation has the required extensions:
```bash
php -m | grep pdo
# Must show: pdo, pdo_mysql, pdo_pgsql
```

If `pdo_pgsql` is missing:
```bash
# Ubuntu/Debian
sudo apt install php-pgsql
sudo systemctl restart apache2
```

See [`docs/environment.md`](docs/environment.md) for Windows instructions.

### 5. Run the Connection Test

Open this page in your browser:
```
http://localhost/distributed-db-demo/setup/test_connections.php
```

All three nodes must show **✅ Connected**. If any show red, see
[`docs/troubleshooting.md`](docs/troubleshooting.md).

### 6. Open the Dashboard

```
http://localhost/distributed-db-demo/pages/index.php
```

---

## PHP Version and Extensions Required

| Requirement | Details |
|---|---|
| PHP | 8.0 or higher |
| `pdo_mysql` | For Node A (MariaDB) and Node B (MySQL) |
| `pdo_pgsql` | For Node C (PostgreSQL) |
| Web server | Apache, Nginx, or `php -S localhost:8080` |

---

## Project Structure

```
config.php          ← Edit this first: put your Tailscale IPs here
pages/              ← The demo pages (open these in browser)
includes/           ← DB connection helpers
db/                 ← SQL setup scripts (run once per node)
setup/              ← Connection test + Tailscale guide
docs/               ← Technical reference
context/            ← Agent context for Claude Code (skip if not using AI)
```

Full structure: [`docs/folder-structure.md`](docs/folder-structure.md)

---

## Key Things to Understand

**Why can't we use foreign keys across nodes?**
Database engines only enforce constraints within their own server. A foreign
key in MySQL on Node B cannot reference a table in MariaDB on Node A —
they're on different machines. Your PHP code must enforce this manually.
See how `pages/orders.php` verifies the user on Node A before writing to Node B.

**Why does the dashboard still work when one node is down?**
Each node query is wrapped in its own `try/catch`. A failure on Node B only
affects the Orders column — it doesn't crash the page. This is the core
concept of distributed system fault tolerance.

**Why is PostgreSQL different?**
PostgreSQL uses a different PDO driver (`pgsql:` DSN vs `mysql:`), different
auto-increment syntax (`SERIAL` vs `AUTO_INCREMENT`), and requires explicit
config changes to accept remote connections (`pg_hba.conf`).
These differences are intentionally visible in the code.

---

## Troubleshooting

See [`docs/troubleshooting.md`](docs/troubleshooting.md) for solutions to
all common connection and PHP errors.

If stuck, copy the exact error message from `setup/test_connections.php`
and bring it to your instructor.

---

## Technical Docs

| Document | What it covers |
|---|---|
| [`docs/database.md`](docs/database.md) | Schema for all 3 nodes |
| [`docs/architecture.md`](context/architecture.md) | How the system fits together |
| [`docs/requirements.md`](docs/requirements.md) | Functional and non-functional requirements |
| [`docs/domain-rules.md`](docs/domain-rules.md) | Business rules (e.g. no cross-server FKs) |
| [`docs/environment.md`](docs/environment.md) | config.php reference, PHP extensions |
| [`docs/troubleshooting.md`](docs/troubleshooting.md) | Common errors and fixes |
