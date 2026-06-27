# Domain Rules — Distributed DB Demo

These are the business and architectural rules that govern how the system
behaves. They are not just conventions — violating them produces incorrect
or misleading results for students.

---

## DR-01 — No Cross-Server Foreign Keys

**Rule:** No SQL file may define a `FOREIGN KEY` that references a table on
a different Node.

**Reason:** Database engines cannot enforce referential integrity across
separate server instances. Attempting to define such a constraint will fail
at the SQL level.

**Enforcement:** Integrity is enforced in PHP (see DR-02).

---

## DR-02 — Verify User Before Writing an Order

**Rule:** Before inserting any row into `node_b_orders.orders`, PHP must
execute a `SELECT` on Node A to confirm the `user_id` exists.

**Reason:** Without FK constraints, orphan orders (pointing to non-existent
users) can be created. This is the primary correctness risk of a distributed
write.

**Implementation:** `pages/orders.php` must SELECT from Node A, check the
result, and abort the Node B INSERT if no user is found.

---

## DR-03 — Node C Sync Is Best-Effort

**Rule:** If the Node C (PostgreSQL) summary sync fails after a successful
Node B order INSERT, the order must still be committed. A warning must be
shown to the user, but the page must not roll back the Node B transaction.

**Reason:** Distributed transactions (2-phase commit) are out of scope for
this demo. The teaching point is that distributed writes have partial failure
modes — both the order being committed and the sync failing is a valid
outcome to show students.

---

## DR-04 — user_name Is Denormalised Into Node C

**Rule:** `order_summary.user_name` is populated from the Node A user record
at the time of order placement. It is not re-fetched from Node A on each
report read.

**Reason:** Avoids a live cross-node join on the Reports page, which would
require Node A to be up for Node C to render. Denormalisation is a real-world
distributed DB pattern.

**Implication:** If a user's name changes on Node A, the denormalised copy
on Node C becomes stale. This inconsistency is intentional and should be
noted in code comments as a teaching point.

---

## DR-05 — Tailscale IPs Are the Only Valid Host Values

**Rule:** `config.php` must use Tailscale-assigned IPs (`100.x.x.x`) for
all `DB_*_HOST` values. `localhost` and physical LAN IPs must not be used
for remote nodes.

**Reason:** The system depends on Tailscale mesh networking. Physical IPs
will fail when team members move to different networks. `localhost` only
works when the web server and DB are on the same machine.

---

## DR-06 — `demo_user` Must Have Wildcard Host Access

**Rule:** All three DB setup scripts must create the user with `@'%'`
(wildcard host), not `@'localhost'`.

```sql
-- Correct
CREATE USER 'demo_user'@'%' IDENTIFIED BY 'demo_pass';

-- Wrong — only allows local connections
CREATE USER 'demo_user'@'localhost' IDENTIFIED BY 'demo_pass';
```

**Reason:** PHP running on the web server machine connects to the DB via
Tailscale IP. From the DB's perspective, this is a remote connection, not
localhost.

---

## DR-07 — config.php Must Not Be Committed With Real Credentials

**Rule:** `config.php` must only ever be committed with placeholder values
(`100.x.x.x`, `demo_user`, `demo_pass`). Real Tailscale IPs and any changed
passwords must only exist on the local machine.

**Reason:** The repo is shared across all team members and potentially public
on GitHub. Committing real IPs exposes the network topology.

**Enforcement:** `config.php` is in `.gitignore`. The committed version in
the repo is the placeholder template only.

---

## DR-08 — Engine Differences Must Be Visible, Not Abstracted Away

**Rule:** The code must NOT create a database abstraction layer that hides
the difference between MySQL and PostgreSQL. The different DSN strings,
different SQL types (`SERIAL`, `NUMERIC`), and different config requirements
(`pg_hba.conf`) must all be visible and commented.

**Reason:** This is a teaching demo. The point is that students understand
what is different between engines, not that they learn how to hide those
differences.
