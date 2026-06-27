# Environment — Distributed DB Demo

There is no `.env` file in this project. All environment configuration lives
in `config.php` at the project root. This is intentional — see `docs/domain-rules.md` DR-07.

---

## config.php Reference

```php
// Node A — MariaDB
define('DB_A_HOST', '100.x.x.1');   // Tailscale IP of Node A machine
define('DB_A_PORT', 3306);           // Default MariaDB/MySQL port
define('DB_A_NAME', 'node_a_users');
define('DB_A_USER', 'demo_user');
define('DB_A_PASS', 'demo_pass');

// Node B — MySQL
define('DB_B_HOST', '100.x.x.2');   // Tailscale IP of Node B machine
define('DB_B_PORT', 3306);
define('DB_B_NAME', 'node_b_orders');
define('DB_B_USER', 'demo_user');
define('DB_B_PASS', 'demo_pass');

// Node C — PostgreSQL
define('DB_C_HOST', '100.x.x.3');   // Tailscale IP of Node C machine
define('DB_C_PORT', 5432);           // Default PostgreSQL port
define('DB_C_NAME', 'node_c_reports');
define('DB_C_USER', 'demo_user');
define('DB_C_PASS', 'demo_pass');

// App
define('APP_NAME', 'Distributed DB Demo');
define('APP_DEBUG', true);           // Set false before any public demo
```

---

## How to Find Your Tailscale IP

**Linux:**
```bash
tailscale ip -4
# OR
ip addr show tailscale0
```

**Windows (PowerShell or CMD):**
```
ipconfig
# Look for the "Tailscale" adapter — the IPv4 Address is your Tailscale IP
```

Each machine will have a unique `100.x.x.x` address. These are stable —
they don't change when you reconnect to Tailscale.

---

## PHP Runtime Requirements

| Requirement | Version / Extension |
|---|---|
| PHP | 8.0 or higher |
| PDO extension | enabled (default in most installs) |
| pdo_mysql extension | enabled — for Node A (MariaDB) + Node B (MySQL) |
| pdo_pgsql extension | enabled — for Node C (PostgreSQL) |

**Verify extensions are enabled:**
```bash
php -m | grep pdo
# Should show: pdo, pdo_mysql, pdo_pgsql
```

**Enable missing extensions (Linux/Ubuntu):**
```bash
sudo apt install php-mysql php-pgsql
sudo service apache2 restart
```

**Enable missing extensions (Windows — XAMPP/WAMP):**
Uncomment these lines in `php.ini`:
```
extension=pdo_mysql
extension=pdo_pgsql
```
Then restart Apache.

---

## Running the PHP Server

**Option 1 — Apache (recommended for lab machines):**
Place the project folder in your web root:
- Linux: `/var/www/html/distributed-db-demo/`
- Windows (XAMPP): `C:\xampp\htdocs\distributed-db-demo\`

Open: `http://localhost/distributed-db-demo/setup/test_connections.php`

**Option 2 — PHP built-in server (for quick testing):**
```bash
cd distributed-db-demo
php -S localhost:8080
```
Open: `http://localhost:8080/setup/test_connections.php`

---

## Database Server Ports and Firewall

Each machine hosting a DB must allow incoming connections on the DB port
through the system firewall.

**Linux (ufw):**
```bash
# MariaDB / MySQL
sudo ufw allow 3306/tcp

# PostgreSQL
sudo ufw allow 5432/tcp
```

**Windows (Defender Firewall):**
Add an inbound rule for port 3306 (MySQL) or 5432 (PostgreSQL) in
Windows Defender Firewall → Advanced Settings → Inbound Rules → New Rule.

**Tailscale note:** Tailscale traffic uses the `tailscale0` network
interface. If your firewall rules are interface-specific, ensure port
3306/5432 is allowed on that interface.

---

## PostgreSQL Remote Access Configuration

PostgreSQL requires two additional config changes to accept remote connections:

**1. postgresql.conf** — allow listening on all interfaces:
```
listen_addresses = '*'
```
Location: `/etc/postgresql/{version}/main/postgresql.conf` (Linux)
or `C:\Program Files\PostgreSQL\{version}\data\postgresql.conf` (Windows)

**2. pg_hba.conf** — allow password auth from any IP:
```
host  all  all  0.0.0.0/0  md5
```
Location: same directory as `postgresql.conf`.

Restart PostgreSQL after both changes:
```bash
sudo systemctl restart postgresql   # Linux
# OR via Services panel on Windows
```
