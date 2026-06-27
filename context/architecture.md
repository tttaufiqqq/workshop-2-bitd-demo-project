# Architecture — Heterogeneous Distributed DB Demo

## Overview

Three physical machines, each hosting a **different** database engine, connected via Tailscale.
This makes it a **heterogeneous distributed database system** — no two nodes run the same DBMS.
One machine also runs the PHP web server. The app connects to all three nodes from a single
`config.php`.

```
┌─────────────────────────────────────────────────────────┐
│                    Tailscale Network                    │
│                   (100.x.x.x range)                    │
│                                                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Node A     │  │   Node B     │  │   Node C     │  │
│  │   Linux      │  │   Windows    │  │   Any OS     │  │
│  │             │  │             │  │             │  │
│  │  MariaDB    │  │   MySQL     │  │ PostgreSQL  │  │
│  │  port 3306  │  │  port 3306  │  │  port 5432  │  │
│  │             │  │             │  │             │  │
│  │  node_a_    │  │  node_b_    │  │  node_c_    │  │
│  │  users DB   │  │  orders DB  │  │  reports DB │  │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘  │
│         │                │                │          │
│         └────────────────┼────────────────┘          │
│                          │                            │
│              ┌───────────▼───────────┐               │
│              │   PHP Web Server      │               │
│              │   Apache / php -S     │               │
│              │                       │               │
│              │   pages/index.php     │               │
│              │   pages/register.php  │               │
│              │   pages/orders.php    │               │
│              │   pages/reports.php   │               │
│              └───────────────────────┘               │
└─────────────────────────────────────────────────────────┘
```

## Node Responsibilities

| Node | Engine    | Database        | Stores          | Why This Engine |
|------|-----------|-----------------|-----------------|-----------------|
| A    | MariaDB   | node_a_users    | Users / identity| MySQL-compatible; shows same driver works for both |
| B    | MySQL     | node_b_orders   | Orders / txns   | Native Windows install; most students familiar |
| C    | PostgreSQL| node_c_reports  | Analytics summary| Different DSN, SERIAL vs AUTO_INCREMENT, NUMERIC vs DECIMAL |

## Key Architectural Decisions

### 1. No Cross-DB Foreign Keys
**Decision:** Referential integrity is enforced in PHP, not at DB level.

**Why:** You cannot define a foreign key that crosses server boundaries.
`orders.user_id` references `users.id` on a different machine — no DB
engine supports this natively.

**NFR addressed:** Correctness under distributed constraints.

**Tradeoff:** App code must validate user existence before inserting an order.
If Node A is down, order creation must be blocked (not silently create
orphan records).

### 2. PDO as the Abstraction Layer
**Decision:** All three nodes are accessed via PDO, not `mysqli` or `pg_*`.

**Why:** PDO works for MySQL-family (MariaDB, MySQL) and PostgreSQL via
different DSN prefixes (`mysql:` vs `pgsql:`). Students learn one API,
not three. The DSN difference is the key teaching moment.

**NFR addressed:** Maintainability, learnability.

**Tradeoff:** PDO hides some engine-specific features. For a demo, this
is correct.

### 3. Per-Node Error Isolation
**Decision:** Each node connection is wrapped in its own try/catch. A
failed node shows an inline error card — it does not crash the page.

**Why:** This is the central distributed systems lesson: partial failure
is the normal case. Students must see that the dashboard still works with
2/3 nodes healthy.

**NFR addressed:** Fault tolerance, reliability.

**Tradeoff:** More verbose page code. Acceptable for a teaching context.

### 4. config.php Over .env
**Decision:** All credentials in `config.php` using `define()` constants.

**Why:** Students don't know Composer or `vlucas/phpdotenv`. A single
file they can open in Notepad is a lower barrier to entry. Constants are
globally accessible without injection.

**NFR addressed:** Learnability, simplicity.

**Tradeoff:** Credentials must never be committed with real values. The
`.gitignore` and README make this explicit.

### 5. No Session / Auth
**Decision:** No login system in the demo.

**Why:** Auth adds a full layer of complexity that distracts from the
distributed DB concept. Students can extend the demo after understanding
the core architecture.

## Data Flow: Place an Order

```
Browser POST /pages/orders.php
    │
    ├─► getMariaDBConnection()        [Node A — verify user exists]
    │       SELECT id FROM users WHERE id = :user_id
    │       → if not found: show error, stop
    │
    ├─► getMySQLConnection()          [Node B — insert order]
    │       INSERT INTO orders (user_id, item_name, ...) VALUES (...)
    │       → get lastInsertId()
    │
    └─► getPostgresConnection()       [Node C — update summary]
            UPDATE order_summary SET total_orders = total_orders + 1,
            total_spent = total_spent + :price WHERE user_id = :user_id
```

This flow explicitly demonstrates the cross-node write pattern that is
the core concept of the project.

## NFR to Architecture Mapping

| NFR | Component | Decision | Tradeoff |
|---|---|---|---|
| Fault tolerance | pages/* try/catch | Per-node isolation | More verbose code |
| Learnability | config.php | Single file, `define()` | Not 12-factor app |
| Security | PDO everywhere | Prepared statements only | Slight verbosity |
| Cross-engine compat | PDO DSN pattern | Same API, different prefix | Can't use engine-specific types in shared code |
| Debuggability | APP_DEBUG flag | Full errors in dev | Must be false in prod |
