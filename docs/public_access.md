# Public Access via Cloudflare Tunnel

Host the project on Ubuntu Server (`workshop-postgres`, `100.113.234.24`) and expose it
to the public internet — **no domain purchase required**.

**Status: Live and verified as of 2026-06-27.**
Current public URL: `https://stretch-billing-bye-hormone.trycloudflare.com`
(URL changes on every cloudflared restart — see Step 7 for how to get the new one.)

---

## How It Works

```
Browser (anyone on internet)
        │
        ▼
  *.trycloudflare.com   ← Cloudflare's free subdomain (auto-assigned)
        │
        ▼
  Cloudflare Edge (kul01 — Kuala Lumpur)
        │  (encrypted tunnel, outbound only — no port forwarding needed)
        ▼
  cloudflared daemon  ← running as systemd service on Ubuntu Server
        │
        ▼
  Apache 2.4 (localhost:80)
        │
        ▼
  PHP 8.3 app  →  /var/www/distributed-db-demo/pages/
```

`cloudflared` opens an outbound connection to Cloudflare — no port forwarding,
no router config, no firewall rules needed on the server or router.

---

## Prerequisites

- Ubuntu Server 24.04 LTS
- Tailscale active on the server (used to SSH in)
- SSH key configured: `ssh workshop-postgres@100.113.234.24`
- Project pushed to GitHub (used for deployment)

---

## Step 1 — Install Apache and PHP

SSH into the server:

```bash
ssh workshop-postgres@100.113.234.24
```

Update packages and install Apache + PHP 8.3 with PDO extensions for all three DB engines:

```bash
sudo apt update && sudo apt upgrade -y
```

```bash
sudo apt install -y apache2 php8.3 php8.3-pdo php8.3-mysql php8.3-pgsql libapache2-mod-php8.3
```

Verify both installed:

```bash
apache2 -v
php -v
```

Enable and start Apache:

```bash
sudo systemctl enable apache2
sudo systemctl start apache2
```

Confirm Apache is serving locally:

```bash
curl -s -o /dev/null -w '%{http_code}' http://localhost
```

Expected output: `200`

---

## Step 2 — Create Web Root and Configure Apache

Create the project directory with correct ownership:

```bash
sudo mkdir -p /var/www/distributed-db-demo
sudo chown -R workshop-postgres:www-data /var/www/distributed-db-demo
sudo chmod -R 755 /var/www/distributed-db-demo
```

Create a virtual host config:

```bash
sudo nano /etc/apache2/sites-available/distributed-db-demo.conf
```

Paste this content (DocumentRoot points to `pages/` because that is where all PHP entry
points live):

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/distributed-db-demo/pages

    <Directory /var/www/distributed-db-demo/pages>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/demo-error.log
    CustomLog ${APACHE_LOG_DIR}/demo-access.log combined
</VirtualHost>
```

Enable the new site, disable the default placeholder, enable URL rewriting, reload:

```bash
sudo a2ensite distributed-db-demo.conf
sudo a2dissite 000-default.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

## Step 3 — Deploy Project Files

Clone the repository into the web root:

```bash
cd /var/www/distributed-db-demo
git clone https://github.com/tttaufiqqq/workshop-2-bitd-demo-project.git .
```

`config.php` is excluded from git (it contains real credentials). Copy it from your local
machine using `scp` — run this command **on your local Windows machine**, not the server:

```bash
scp /c/Users/taufi/Documents/Dev/distributed-db-demo/config.php \
  workshop-postgres@100.113.234.24:/var/www/distributed-db-demo/config.php
```

Confirm the app loads locally on the server:

```bash
curl -s -o /dev/null -w '%{http_code}' http://localhost/index.php
```

Expected output: `200`

If you get `500`, check Apache error logs:

```bash
sudo tail -30 /var/log/apache2/demo-error.log
```

---

## Step 4 — Install cloudflared

Download and install the latest cloudflared binary:

```bash
curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb \
  -o /tmp/cloudflared.deb
sudo dpkg -i /tmp/cloudflared.deb
cloudflared --version
```

---

## Step 5 — Create Systemd Service for the Tunnel

Running cloudflared as a systemd service keeps the tunnel alive after SSH sessions end
and restarts it automatically on failure.

Create the service file:

```bash
sudo nano /etc/systemd/system/cloudflared-quick.service
```

Paste this content:

```ini
[Unit]
Description=Cloudflare Quick Tunnel for distributed-db-demo
After=network.target

[Service]
Type=simple
User=workshop-postgres
ExecStart=/usr/bin/cloudflared tunnel --url http://localhost:80
Restart=on-failure
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Enable and start the service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable cloudflared-quick
sudo systemctl start cloudflared-quick
```

---

## Step 6 — Get the Public URL

The tunnel takes a few seconds to negotiate. Read the assigned URL from logs:

```bash
sudo journalctl -u cloudflared-quick --no-pager | grep trycloudflare
```

You will see a line like:

```
INF |  https://random-words-here.trycloudflare.com
```

That is your live public URL. It works immediately — share it with anyone.

To confirm it is reachable from outside (run on any machine):

```bash
curl -s -o /dev/null -w '%{http_code}' https://random-words-here.trycloudflare.com/index.php
```

Expected output: `200`

---

## Notes on Connectivity Warnings

When cloudflared starts, it runs a precheck and may show warnings like:

```
FAIL    QUIC connection failed (region2)
FAIL    HTTP/2 connection is blocked or unreachable (region2)
```

These are non-fatal. The tunnel connects through region1 (Kuala Lumpur for this server's
location). The app will be live even with these warnings. You will see a line confirming
the connection:

```
INF Registered tunnel connection connIndex=0 ... location=kul01 protocol=quic
```

---

## Updating the App

When you push new commits to GitHub, pull them on the server:

```bash
ssh workshop-postgres@100.113.234.24
cd /var/www/distributed-db-demo
git pull origin main
```

If you changed `config.php` locally, scp it again:

```bash
scp /c/Users/taufi/Documents/Dev/distributed-db-demo/config.php \
  workshop-postgres@100.113.234.24:/var/www/distributed-db-demo/config.php
```

No Apache restart needed for PHP file changes — Apache picks them up immediately.

---

## Useful Commands

| Task | Command (run on server) |
|------|------------------------|
| Check Apache status | `sudo systemctl status apache2` |
| Check tunnel service | `sudo systemctl status cloudflared-quick` |
| View tunnel logs live | `sudo journalctl -u cloudflared-quick -f` |
| Get current public URL | `sudo journalctl -u cloudflared-quick --no-pager \| grep trycloudflare` |
| Restart tunnel (new URL) | `sudo systemctl restart cloudflared-quick` |
| Stop tunnel | `sudo systemctl stop cloudflared-quick` |
| View Apache error logs | `sudo tail -f /var/log/apache2/demo-error.log` |
| Reload Apache config | `sudo systemctl reload apache2` |
| Pull latest code | `cd /var/www/distributed-db-demo && git pull origin main` |

---

## Persistent URL — Named Tunnel (Needs Free Cloudflare Account + Any Domain)

The quick tunnel URL changes every restart. For a fixed URL:

1. Sign up free at `cloudflare.com`
2. Add any domain you own to Cloudflare (just DNS management — free)
3. Run on the server:

```bash
cloudflared tunnel login
cloudflared tunnel create demo-tunnel
cloudflared tunnel route dns demo-tunnel demo.yourdomain.com
```

Update the service `ExecStart` line to:

```ini
ExecStart=/usr/bin/cloudflared tunnel run demo-tunnel
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl restart cloudflared-quick
```

This gives a fixed `https://demo.yourdomain.com` that survives restarts.

---

## Access Summary

| Method | URL | Who can access |
|--------|-----|----------------|
| Direct Tailscale IP | `http://100.113.234.24` | Tailscale members only |
| MagicDNS hostname | `http://workshop-postgres.taile932d8.ts.net` | Tailscale members only |
| Cloudflare Quick Tunnel | `https://*.trycloudflare.com` | Anyone on the internet |
