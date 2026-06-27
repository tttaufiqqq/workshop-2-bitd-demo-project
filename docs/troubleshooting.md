# Troubleshooting — Distributed DB Demo

Start with `setup/test_connections.php`. The error message it shows is your
primary diagnostic tool. Find your error below.

---

## Connection Errors

### `SQLSTATE[HY000] [2002] Connection refused`

The DB service is not running on the target machine.

**Fix:**
```bash
# Linux — MariaDB
sudo systemctl start mariadb
sudo systemctl status mariadb

# Linux — MySQL
sudo systemctl start mysql

# Linux — PostgreSQL
sudo systemctl start postgresql

# Windows — check Services panel
# Search "Services" → find MySQL / MariaDB / PostgreSQL → Start
```

---

### `SQLSTATE[HY000] [2002] No route to host` or `Network unreachable`

PHP cannot reach the target machine at all. Either Tailscale is down or
the IP in `config.php` is wrong.

**Diagnosis steps:**
1. On the web server machine, run: `ping 100.x.x.x` (the IP in config.php)
2. If ping fails: check Tailscale is running on both machines (`tailscale status`)
3. If ping succeeds but connection still fails: firewall is blocking port 3306/5432

**Fix (Tailscale not running):**
```bash
# Linux
sudo tailscale up

# Windows
# Open Tailscale from system tray → Connect
```

**Fix (firewall blocking):**
```bash
# Linux — allow MySQL/MariaDB
sudo ufw allow 3306/tcp

# Linux — allow PostgreSQL
sudo ufw allow 5432/tcp
```
Windows: Add inbound rule in Windows Defender Firewall for port 3306 or 5432.

---

### `SQLSTATE[HY000] [1045] Access denied for user 'demo_user'@'100.x.x.x'`

The DB user exists but is not allowed to connect from this IP.

**Cause:** User was created with `@'localhost'` instead of `@'%'`.

**Fix:** On the DB machine, run:
```sql
-- Drop the restricted user
DROP USER IF EXISTS 'demo_user'@'localhost';

-- Recreate with wildcard host
CREATE USER 'demo_user'@'%' IDENTIFIED BY 'demo_pass';
GRANT ALL PRIVILEGES ON node_a_users.* TO 'demo_user'@'%';
FLUSH PRIVILEGES;
```
Replace `node_a_users` with the correct database name for that node.

---

### `SQLSTATE[HY000] [1049] Unknown database 'node_a_users'`

The database hasn't been created yet, or the name in `config.php` doesn't
match what was created.

**Fix:** Run the matching SQL file on that machine:
```bash
# Node A
mysql -u root -p < db/node_a_mariadb.sql

# Node B
mysql -u root -p < db/node_b_mysql.sql

# Node C
psql -U postgres -f db/node_c_postgres.sql
```

---

### PostgreSQL: `FATAL: password authentication failed`

The password in `config.php` doesn't match what was set in PostgreSQL.

**Fix:**
```sql
-- In psql as postgres superuser
ALTER USER demo_user WITH PASSWORD 'demo_pass';
```

---

### PostgreSQL: `FATAL: no pg_hba.conf entry for host "100.x.x.x"`

PostgreSQL doesn't allow connections from your Tailscale IP.

**Fix:** Edit `pg_hba.conf` (location: run `SHOW hba_file;` in psql):
```
# Add this line:
host  all  all  0.0.0.0/0  md5
```
Then restart PostgreSQL:
```bash
sudo systemctl restart postgresql
```

---

### PostgreSQL: `could not connect to server: Connection refused` on port 5432

PostgreSQL is not listening on the Tailscale interface.

**Fix:** Edit `postgresql.conf` (location: run `SHOW config_file;` in psql):
```
listen_addresses = '*'
```
Restart PostgreSQL after saving.

---

## PHP Errors

### `Call to undefined function getMariaDBConnection()`

The include for `db_mariadb.php` is missing at the top of the page.

**Fix:** Add at the top of the page:
```php
require_once __DIR__ . '/../includes/db_mariadb.php';
```

---

### `could not find driver` (PDO)

The required PDO extension for that engine is not enabled.

**Fix:**
```bash
# Check which extensions are active
php -m | grep pdo

# Install missing extensions (Ubuntu/Debian)
sudo apt install php-mysql php-pgsql
sudo systemctl restart apache2
```

Windows (XAMPP/WAMP): Uncomment `extension=pdo_pgsql` in `php.ini`, restart Apache.

---

### Page shows blank white screen

PHP has a fatal error and `APP_DEBUG` may be false, suppressing it.

**Fix:** Temporarily set in `config.php`:
```php
define('APP_DEBUG', true);
```
Or enable PHP error display:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
Put this at the very top of the failing file, before any `require_once`.

---

## Tailscale Setup Issues

### Devices can't ping each other

1. Verify both devices show as online: `tailscale status`
2. If a device shows as offline, run `tailscale up` on it
3. Check if your institution blocks Tailscale UDP traffic — use `tailscale ping 100.x.x.x` to diagnose

### `tailscale up` asks to log in on a machine with no browser

```bash
tailscale up --qr
# Shows a QR code — scan with phone to authenticate
```

Or use an auth key from the Tailscale admin panel:
```bash
tailscale up --authkey=tskey-auth-xxxxxx
```

---

## Still Stuck?

1. Run `setup/test_connections.php` and copy the full error message
2. Check `docs/environment.md` for PHP extension requirements
3. Check `docs/domain-rules.md` DR-06 for the `@'%'` user requirement
4. Ask your instructor, showing the exact error from the test page
