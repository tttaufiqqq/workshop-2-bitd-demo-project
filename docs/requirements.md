# Requirements — Distributed DB Demo

---

## 1. Functional Requirements

### FR-01 — User Registration
The system shall allow a Student to register by submitting name, email,
and student ID. The record shall be persisted to Node A (MariaDB). The
system shall reject duplicate email or student ID with an inline error.

### FR-02 — User Listing
The system shall display all registered users from Node A on the Register
page below the form, ordered by registration date descending.

### FR-03 — Order Placement
The system shall allow a Student to place an order by selecting a user ID,
entering an item name, quantity, and price. Before inserting the order on
Node B (MySQL), the system shall verify the user exists on Node A (MariaDB).
If the user is not found, the order shall be rejected.

### FR-04 — Order Listing
The system shall display the 10 most recent orders from Node B on the
Orders page, ordered by order date descending.

### FR-05 — Summary Sync
When an order is confirmed on Node B, the system shall upsert the matching
row on Node C (PostgreSQL) to increment `total_orders` by 1 and add the
order price to `total_spent`.

### FR-06 — Reports Page
The system shall display the full `order_summary` table from Node C,
ordered by `total_spent` descending, with each row showing user name,
total orders, and total spent.

### FR-07 — Dashboard
The system shall display a three-column dashboard showing the 5 most
recent users (Node A), 5 most recent orders (Node B), and full order
summary (Node C) simultaneously. Each column shall show an inline error
if its node is unreachable, without affecting the other columns.

### FR-08 — Connection Test Page
The system shall provide a `/setup/test_connections.php` page that
attempts to connect to all three nodes, reports success or failure per
node with latency in milliseconds, and shows the currently configured
host/port/database values.

---

## 2. Non-Functional Requirements

### NFR-01 — Learnability (Primary)
**Requirement:** A student with no prior framework experience must be able
to read and understand any PHP file in the project without needing to look
up framework documentation.

**Measure:** No Composer dependencies. No framework classes. Every function
called is either a PHP built-in or defined within the project's own files.

**Architecture link:** Plain PHP + `config.php` pattern (see `context/architecture.md` §4).

### NFR-02 — Fault Tolerance
**Requirement:** The dashboard and report pages must remain functional when
one database node is offline. The offline node shall display an error card;
the other nodes shall render normally.

**Measure:** With any single node's DB service stopped, the other two
columns on the dashboard must load within 10 seconds (including connection
timeout).

**Architecture link:** Per-node try/catch isolation (`context/architecture.md` §3).
Timeout set to 5 seconds (`PDO::ATTR_TIMEOUT => 5`) in all helpers.

### NFR-03 — Security (Input Handling)
**Requirement:** No user-supplied input shall be interpolated directly into
any SQL query. No database value shall be echoed to the browser without
`htmlspecialchars()`.

**Measure:** Zero occurrences of `"... $variable ..."` in SQL strings.
Zero occurrences of `echo $row[...]` without sanitisation.

**Architecture link:** PDO prepared statements only (`context/code-standards.md`).

### NFR-04 — Cross-Engine Compatibility (Heterogeneous Support)
**Requirement:** The same PDO interface shall be used across all three engines in this
heterogeneous setup. Engine-specific differences (DSN prefix, SERIAL vs AUTO_INCREMENT,
NUMERIC vs DECIMAL) shall be isolated to the SQL schema files and clearly commented.

**Measure:** No `mysqli_*` or `pg_*` function calls anywhere in `pages/`
or `includes/`.

### NFR-05 — Setup Time
**Requirement:** A student who has never used the project must be able to
go from a fresh clone to a working dashboard in under 30 minutes, following
only the README.

**Measure:** README walkthrough tested by at least one student not involved
in building the demo.

---

## 3. Domain Requirements

### DR-01 — No Cross-Server Foreign Keys
Database engines cannot enforce referential integrity across separate server
instances. The application must treat cross-node relationships as logical
references only, validated in PHP before writes.

### DR-02 — Tailscale IP Addressing
All inter-node connections must use Tailscale-assigned IPs (`100.x.x.x`).
Physical LAN IPs or localhost must not be used, as the topology depends on
which machine the PHP server runs on.

### DR-03 — Remote DB User Permissions
All database users must be created with the `'%'` wildcard host to permit
connections from any Tailscale node. This is a requirement of the distributed
topology, not a security choice — students must understand this tradeoff.

### DR-04 — Synchronous Cross-Node Writes
In this demo, writes to Node C (order summary sync) happen synchronously
within the same PHP request as the Node B write. There is no retry
mechanism. If Node C is down, the sync is skipped and an inline warning
is shown — the order on Node B is still committed.

### DR-05 — Engine Differences Are Teaching Material
Differences between MySQL/MariaDB and PostgreSQL (SERIAL, NUMERIC, pg_hba.conf,
different `pgsql:` DSN) are not abstractions to hide. They are intentionally
exposed and commented in both code and SQL files.
