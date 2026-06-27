# Database — Distributed DB Demo

## Overview

Three separate database servers. No shared schema. No cross-server foreign keys.
Referential integrity is enforced entirely in PHP application code.

---

## Node A — MariaDB (`node_a_users`)

### Engine notes
MariaDB uses the **same PDO DSN as MySQL** (`mysql:host=...`). The `pdo_mysql`
PHP extension handles both. This is intentional — students see that MariaDB
and MySQL are interchangeable at the driver level.

### Table: `users`

| Column     | Type                        | Constraints             | Notes |
|------------|-----------------------------|-------------------------|-------|
| id         | INT AUTO_INCREMENT          | PRIMARY KEY             | Referenced by orders.user_id on Node B |
| name       | VARCHAR(100)                | NOT NULL                | Full name |
| email      | VARCHAR(150)                | NOT NULL, UNIQUE        | |
| student_id | VARCHAR(20)                 | NOT NULL, UNIQUE        | e.g. B032310001 |
| created_at | DATETIME                    | DEFAULT CURRENT_TIMESTAMP | |

### Indexes
- PRIMARY KEY on `id`
- UNIQUE on `email`
- UNIQUE on `student_id`

### Remote Access Requirement
The DB user must be created with `'demo_user'@'%'` (wildcard host) so that
PHP running on the web server machine can connect via Tailscale IP.

```sql
CREATE USER IF NOT EXISTS 'demo_user'@'%' IDENTIFIED BY 'demo_pass';
GRANT ALL PRIVILEGES ON node_a_users.* TO 'demo_user'@'%';
```

---

## Node B — MySQL (`node_b_orders`)

### Engine notes
MySQL on Windows. Same `mysql:` PDO DSN as Node A. The key teaching point
is that `user_id` here is a **logical** reference to `users.id` on Node A —
there is no FK constraint enforcing it.

### Table: `orders`

| Column     | Type                                    | Constraints             | Notes |
|------------|-----------------------------------------|-------------------------|-------|
| id         | INT AUTO_INCREMENT                      | PRIMARY KEY             | |
| user_id    | INT                                     | NOT NULL                | Logical ref to node_a_users.users.id — no FK |
| item_name  | VARCHAR(200)                            | NOT NULL                | |
| quantity   | INT                                     | NOT NULL, DEFAULT 1     | |
| price      | DECIMAL(8,2)                            | NOT NULL                | |
| status     | ENUM('pending','confirmed','cancelled') | DEFAULT 'pending'       | |
| ordered_at | DATETIME                                | DEFAULT CURRENT_TIMESTAMP | |

### Indexes
- PRIMARY KEY on `id`
- Index on `user_id` (for lookups by user)
- Index on `status` (for filtering by status)

### Integrity Rule
Before inserting an order, PHP **must** verify `user_id` exists on Node A.
If Node A is unreachable, the order must be rejected with an error — orphan
records are not acceptable even in a demo.

---

## Node C — PostgreSQL (`node_c_reports`)

### Engine notes
PostgreSQL uses a **different DSN prefix**: `pgsql:host=...`. It also uses
`SERIAL` instead of `AUTO_INCREMENT`, and `NUMERIC` instead of `DECIMAL`.
These differences are the core teaching moment for this node.

The `pg_hba.conf` and `postgresql.conf` changes required for remote access
are documented in `setup/tailscale_guide.md`.

### Table: `order_summary`

| Column        | Type              | Constraints              | Notes |
|---------------|-------------------|--------------------------|-------|
| id            | SERIAL            | PRIMARY KEY              | PostgreSQL auto-increment |
| user_id       | INTEGER           | NOT NULL                 | Logical ref to node_a_users.users.id |
| user_name     | VARCHAR(100)      |                          | Denormalised for fast reporting — avoids cross-node join |
| total_orders  | INTEGER           | DEFAULT 0                | Running count |
| total_spent   | NUMERIC(10,2)     | DEFAULT 0.00             | NUMERIC = PostgreSQL's DECIMAL |
| last_updated  | TIMESTAMP         | DEFAULT CURRENT_TIMESTAMP | |

### Indexes
- PRIMARY KEY on `id`
- UNIQUE on `user_id` (one summary row per user)

### Why Denormalise user_name?
Joining across servers requires a PHP-level join (fetch from both, merge in
code). For a summary/analytics table, storing `user_name` directly avoids
hitting Node A on every report read. This is a real-world analytics pattern.

---

## Cross-Node Data Flow

```
Register (Node A) ──► users.id = 1, name = "Ahmad"

Place Order (Node B) ──► orders: user_id=1, item="Laptop Stand", price=45.00
     │
     └──► Sync (Node C) ──► order_summary: user_id=1, user_name="Ahmad",
                             total_orders += 1, total_spent += 45.00
```

The sync to Node C happens in the same PHP request as the Node B insert.
There is no message queue or background job in this demo — it is synchronous.
This is a simplification: in production, you would use an event queue to
decouple the writes and handle partial failures.

---

## SQL Files Location

| File | Purpose |
|---|---|
| `db/node_a_mariadb.sql` | Full setup script for Node A |
| `db/node_b_mysql.sql` | Full setup script for Node B |
| `db/node_c_postgres.sql` | Full setup script for Node C |

Each file is self-contained: run it top-to-bottom on a fresh install.
