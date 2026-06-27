# Spec 03 — Register Page (pages/register.php)

## Goal

Form that writes a new user to Node A (MariaDB). Demonstrates a single-node
INSERT with prepared statements and duplicate key handling.

## Design

POST handling at top of file. Re-render form with preserved values on error.
Show full users table below form so students see the write confirmed immediately.

## Implementation

**POST handler:**
1. Trim all inputs
2. Validate: all fields non-empty → else $error
3. Validate: email format via `filter_var(FILTER_VALIDATE_EMAIL)` → else $error
4. `getMariaDBConnection()`
5. Prepared INSERT with `:name`, `:email`, `:student_id`
6. On success: set `$message = "User registered! ID = {$pdo->lastInsertId()}"`
7. On PDOException code `23000`: set `$error = "Email or Student ID already exists."`
8. On other PDOException: set `$error = "Database error: " . $e->getMessage()`

**HTML:**
- Node badge: `node-a` (blue)
- Success card (green) if `$message` set
- Error card (red) if `$error` set
- Form with method=POST, fields: name (text), email (email), student_id (text)
- Preserve submitted values in input `value` attributes via `htmlspecialchars($_POST[...])`
- Table of all users from Node A below form (separate try/catch query)
- Footer callout: "This page writes to Node A (MariaDB at DB_A_HOST)."

## Pages

- `pages/register.php`

## DB Objects

- `node_a_users.users` — INSERT (name, email, student_id)
- `node_a_users.users` — SELECT * ORDER BY created_at DESC (for the table)

## Files Changed

- `pages/register.php`

## Verify

- [ ] Submitting empty form shows "All fields are required."
- [ ] Submitting invalid email shows "Invalid email format."
- [ ] Submitting valid new user shows success message with the new ID
- [ ] New user appears in the table below immediately after registration
- [ ] Submitting duplicate email shows "Email or Student ID already exists."
- [ ] Input values are preserved in form fields after a failed submission
- [ ] All echoed values go through htmlspecialchars()
- [ ] File is under 150 lines
