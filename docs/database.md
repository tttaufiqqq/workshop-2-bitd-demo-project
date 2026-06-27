# Database — Distributed DB Demo

## Overview

Three separate **heterogeneous** database servers — each node runs a different engine
(MariaDB, MySQL, PostgreSQL). No shared schema. No cross-server foreign keys.
Referential integrity is enforced entirely in PHP application code.

---

## Node A — MariaDB (`node_a_students`)

### Engine notes
MariaDB uses the **same PDO DSN as MySQL** (`mysql:host=...`). The `pdo_mysql`
PHP extension handles both. This is intentional — students see that MariaDB
and MySQL are interchangeable at the driver level.

### Table: `students`

| Column     | Type                        | Constraints             | Notes |
|------------|-----------------------------|-------------------------|-------|
| student_id | VARCHAR(20)                 | PRIMARY KEY             | e.g. B032310001 — referenced by orders.student_id on Node B |
| name       | VARCHAR(100)                | NOT NULL                | Full name |
| email      | VARCHAR(150)                | NOT NULL, UNIQUE        | |
| created_at | DATETIME                    | DEFAULT CURRENT_TIMESTAMP | |

### Indexes
- PRIMARY KEY on `student_id`
- UNIQUE on `email`

### Remote Access Requirement
The DB user must be created with `'demo_user'@'%'` (wildcard host) so that
PHP running on the web server machine can connect via Tailscale IP.

```sql
CREATE USER IF NOT EXISTS 'demo_user'@'%' IDENTIFIED BY 'demo_pass';
GRANT ALL PRIVILEGES ON node_a_students.* TO 'demo_user'@'%';
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
| order_id   | INT AUTO_INCREMENT                      | PRIMARY KEY             | |
| student_id | VARCHAR(20)                             | NOT NULL                | Logical ref to node_a_students.students.student_id — no FK |
| item_name  | VARCHAR(200)                            | NOT NULL                | |
| quantity   | INT                                     | NOT NULL, DEFAULT 1     | |
| price      | DECIMAL(8,2)                            | NOT NULL                | |
| status     | ENUM('pending','confirmed','cancelled') | DEFAULT 'pending'       | |
| ordered_at | DATETIME                                | DEFAULT CURRENT_TIMESTAMP | |

### Indexes
- PRIMARY KEY on `order_id`
- Index on `student_id` (for lookups by student)
- Index on `status` (for filtering by status)

### Integrity Rule
Before inserting an order, PHP **must** verify `student_id` exists on Node A.
If Node A is unreachable, the order must be rejected with an error — orphan
records are not acceptable even in a demo.

---

## Node C — PostgreSQL (`node_c_reports`)

### Engine notes
PostgreSQL uses a **different DSN prefix**: `pgsql:host=...`. It also uses
`SERIAL` instead of `AUTO_INCREMENT`, and `NUMERIC` instead of `DECIMAL`.
These differences are the core teaching moment for this node.

The `pg_hba.conf` and `postgresql.conf` changes required for remote access
are documented in `docs/tailscale_guide.md`.

### Table: `order_summary`

| Column        | Type              | Constraints              | Notes |
|---------------|-------------------|--------------------------|-------|
| summary_id    | SERIAL            | PRIMARY KEY              | PostgreSQL auto-increment |
| student_id    | VARCHAR(20)       | NOT NULL, UNIQUE         | Logical ref to node_a_students.students.student_id |
| user_name     | VARCHAR(100)      |                          | Denormalised for fast reporting — avoids cross-node join |
| total_orders  | INTEGER           | DEFAULT 0                | Running count |
| total_spent   | NUMERIC(10,2)     | DEFAULT 0.00             | NUMERIC = PostgreSQL's DECIMAL |
| last_updated  | TIMESTAMP         | DEFAULT CURRENT_TIMESTAMP | |

### Indexes
- PRIMARY KEY on `summary_id`
- UNIQUE on `student_id` (one summary row per student)

### Why Denormalise user_name?
Joining across servers requires a PHP-level join (fetch from both, merge in
code). For a summary/analytics table, storing `user_name` directly avoids
hitting Node A on every report read. This is a real-world analytics pattern.

---

## Cross-Node Data Flow

```
Register (Node A) ──► students: student_id = "B032310001", name = "Ahmad"

Place Order (Node B) ──► orders: student_id="B032310001", item="Laptop Stand", price=45.00
     │
     └──► Sync (Node C) ──► order_summary: student_id="B032310001", user_name="Ahmad",
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
