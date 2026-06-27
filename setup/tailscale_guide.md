# Tailscale Setup Guide

Tailscale connects all your team's machines into one private network so they
can reach each other's databases — even on different Wi-Fi networks or across
Linux and Windows.

---

## Step 1: Install Tailscale on Every Machine

All team members do this on their own machine.

**Linux (Ubuntu/Debian):**
```bash
curl -fsSL https://tailscale.com/install.sh | sh
```

**Windows:**
Download and install from: https://tailscale.com/download/windows

---

## Step 2: Create One Shared Tailscale Account

One team member signs up at https://tailscale.com (free tier supports up
to 3 devices for the free plan, 100 devices on the free personal plan).

Use the same account / login across all devices, OR invite team members
to a shared tailnet.

> **For a class:** Your instructor may provide a shared login.
> Everyone logs in to the same Tailscale account so all machines appear
> on the same network.

---

## Step 3: Connect Each Machine

**Linux:**
```bash
sudo tailscale up
# Opens a browser link — log in with the shared account
```

If the machine has no browser:
```bash
sudo tailscale up --qr
# Shows a QR code — scan with phone to authenticate
```

**Windows:**
Click the Tailscale system tray icon → Log in → authenticate in browser.

---

## Step 4: Find Each Machine's Tailscale IP

**Linux:**
```bash
tailscale ip -4
# Output: 100.x.x.x
```

**Windows:**
```
ipconfig
# Look for the Tailscale adapter — copy the IPv4 Address (100.x.x.x)
```

**Or:** Open https://login.tailscale.com/admin/machines — you can see all
connected devices and their IPs from a browser.

Write down all three IPs:
```
Node A (MariaDB) machine:  100.___.___.___
Node B (MySQL) machine:    100.___.___.___
Node C (PostgreSQL) machine: 100.___.___.___
```

---

## Step 5: Verify Machines Can Ping Each Other

From the PHP web server machine, test connectivity to each DB machine:

```bash
# Ping Node A
ping 100.x.x.1

# Ping Node B
ping 100.x.x.2

# Ping Node C
ping 100.x.x.3
```

All three should respond. If one doesn't:
- Check Tailscale is running on that machine (`tailscale status`)
- Check the machine isn't in sleep mode

---

## Step 6: Update config.php

Open `config.php` and replace the placeholder IPs:

```php
define('DB_A_HOST', '100.x.x.1');   // <-- Actual Node A Tailscale IP
define('DB_B_HOST', '100.x.x.2');   // <-- Actual Node B Tailscale IP
define('DB_C_HOST', '100.x.x.3');   // <-- Actual Node C Tailscale IP
```

---

## Step 7: Run the Connection Test

Open in your browser:
```
http://localhost/distributed-db-demo/setup/test_connections.php
```

All three nodes should show green. If any shows red, see
`docs/troubleshooting.md`.

---

## Common Issues

### `tailscale up` hangs on Linux
```bash
# Force it to run in the foreground
sudo tailscale up --accept-routes
```

### Machines show as connected in Tailscale but can't ping each other
Your institution's network may block Tailscale's relay servers. Try:
```bash
tailscale ping 100.x.x.x
# Shows "pong from ..." if direct or relayed connection works
```

### "This device is not authorized"
The machine needs to be approved in the Tailscale admin panel.
Log in to https://login.tailscale.com/admin/machines and approve the device.
