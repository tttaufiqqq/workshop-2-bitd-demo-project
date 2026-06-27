# Flow of Events — Distributed DB Demo

---

## UC-01: Register a New User

**Actor:** Student
**Description:** Student submits a registration form. Data is written to Node A (MariaDB).

**Pre-conditions:**
1. Node A (MariaDB) is reachable via Tailscale.
2. The `node_a_users` database and `users` table exist on Node A.
3. `config.php` contains the correct Tailscale IP for Node A.

**Post-conditions:**
1. A new row exists in `node_a_users.users` with the submitted data.
2. The page re-renders showing a success message and the new user in the table.

**Normal Flow:**
- R01-1: Student navigates to `pages/register.php`.
- R01-2: Student fills in Name, Email, and Student ID fields.
- R01-3: Student submits the form (POST).
- R01-4: System validates that all fields are non-empty.
- R01-5: System validates that email is a valid format.
- R01-6: System calls `getMariaDBConnection()` to open a PDO connection to Node A.
- R01-7: System executes `INSERT INTO users (name, email, student_id) VALUES (?, ?, ?)` as a prepared statement.
- R01-8: System displays success message: "User registered! ID = {id}".
- R01-9: System queries and renders the updated users table below the form.

**Alternative Flow:**
- R01-4a: If any field is empty, system displays "All fields are required." Resumes at R01-2.
- R01-5a: If email format is invalid, system displays "Invalid email format." Resumes at R01-2.

**Exception Flow:**
- R01-6a: If Node A is unreachable, PDO throws `PDOException`. System displays "Node A (MariaDB) Connection Failed: {message}". Page does not crash.
- R01-7a: If email or student_id already exists (PDO error code `23000`), system displays "Email or Student ID already exists." Resumes at R01-2.

---

## UC-02: Place an Order

**Actor:** Student
**Description:** Student places an order for an item. System verifies the user on Node A,
writes the order to Node B (MySQL), and syncs a summary to Node C (PostgreSQL).

**Pre-conditions:**
1. At least one user exists on Node A.
2. Node B (MySQL) is reachable via Tailscale.
3. Node C (PostgreSQL) is reachable via Tailscale (preferred but not blocking).
4. `config.php` contains correct Tailscale IPs for all three nodes.

**Post-conditions:**
1. A new row exists in `node_b_orders.orders`.
2. The matching row in `node_c_reports.order_summary` is updated (if Node C was reachable).
3. The page re-renders with a success message.

**Normal Flow:**
- R02-1: Student navigates to `pages/orders.php`.
- R02-2: Student enters User ID, Item Name, Quantity, and Price.
- R02-3: Student submits the form (POST).
- R02-4: System validates all fields are non-empty and quantity/price are positive numbers.
- R02-5: System calls `getMariaDBConnection()` and executes `SELECT id, name FROM users WHERE id = :id`.
- R02-6: System confirms the user exists. Stores `user_name` for Node C sync.
- R02-7: System calls `getMySQLConnection()` and executes INSERT into `orders`.
- R02-8: System calls `getPostgresConnection()` and executes UPSERT on `order_summary`.
- R02-9: System displays success message referencing all three nodes.
- R02-10: System re-renders the recent orders table from Node B.

**Alternative Flow:**
- R02-4a: Validation fails → inline error message. Resumes at R02-2.
- R02-8a: Node C is unreachable → system logs a warning inline: "Order saved, but summary sync to Node C failed." Order on Node B is still committed. Resumes at R02-9.

**Exception Flow:**
- R02-5a: Node A unreachable → system displays "Cannot verify user — Node A is down." Order is not placed.
- R02-6a: User ID not found on Node A → system displays "User ID {id} does not exist." Order is not placed.
- R02-7a: Node B unreachable → system displays "Cannot save order — Node B is down."

---

## UC-03: View Dashboard

**Actor:** Student / Instructor
**Description:** Viewer opens the dashboard and sees live data from all three nodes simultaneously.

**Pre-conditions:**
1. `config.php` is configured with Tailscale IPs.
2. At least one node is reachable (page degrades gracefully if others are down).

**Post-conditions:**
1. Dashboard renders with data from all reachable nodes.
2. Unreachable nodes show an inline error card — other columns are unaffected.

**Normal Flow:**
- R03-1: User navigates to `pages/index.php`.
- R03-2: System independently queries Node A for 5 most recent users.
- R03-3: System independently queries Node B for 5 most recent orders.
- R03-4: System independently queries Node C for order summary.
- R03-5: System renders a three-column layout with data from each node.

**Exception Flow:**
- R03-2a through R03-4a: If any node query fails, that column renders an error card. Other columns render normally. This is the core distributed systems teaching moment.

---

## UC-04: Check Connection Health

**Actor:** Student (during setup)
**Description:** Student runs the connection test page to verify all three nodes
are reachable before starting development.

**Pre-conditions:**
1. `config.php` has been updated with actual Tailscale IPs.

**Post-conditions:**
1. Each node shows either "Connected (Xms)" or a specific error message.

**Normal Flow:**
- R04-1: Student navigates to `setup/test_connections.php`.
- R04-2: System attempts PDO connection to each node with 5-second timeout.
- R04-3: System measures latency for each successful connection.
- R04-4: System renders a result card per node (green = ok, red = failed).
- R04-5: If all pass, system shows a link to the Dashboard.

**Exception Flow:**
- R04-2a: Any node fails → error card shows the raw PDO exception message (debug mode on). Student uses this message + troubleshooting guide to diagnose.
