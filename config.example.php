<?php
// ============================================================
// config.example.php — Template for Distributed DB Demo
// ============================================================
// HOW TO USE:
//   1. Copy this file and rename the copy to config.php
//      Windows:  copy config.example.php config.php
//      Linux:    cp config.example.php config.php
//   2. Open config.php and replace every 100.x.x.x value
//      with the actual Tailscale IP of the machine running
//      that database node.
//   3. config.php is listed in .gitignore — never commit it.
//      This file (config.example.php) is what gets committed.
// ============================================================
// Find your Tailscale IP:
//   Windows: ipconfig        (look for the Tailscale adapter)
//   Linux:   tailscale ip -4
// ============================================================

// --- NODE A: MariaDB (stores Students) ---
// The machine in your group running MariaDB (usually Linux)
define('DB_A_HOST', '100.x.x.1');       // <-- Replace with Node A Tailscale IP
define('DB_A_PORT', 3306);
define('DB_A_NAME', 'node_a_users');
define('DB_A_USER', 'demo_user');
define('DB_A_PASS', 'demo_pass');

// --- NODE B: MySQL (stores Orders) ---
// The machine in your group running MySQL (usually Windows)
define('DB_B_HOST', '100.x.x.2');       // <-- Replace with Node B Tailscale IP
define('DB_B_PORT', 3306);
define('DB_B_NAME', 'node_b_orders');
define('DB_B_USER', 'demo_user');
define('DB_B_PASS', 'demo_pass');

// --- NODE C: PostgreSQL (stores Order Summary / Reports) ---
// The machine in your group running PostgreSQL (any OS)
define('DB_C_HOST', '100.x.x.3');       // <-- Replace with Node C Tailscale IP
define('DB_C_PORT', 5432);
define('DB_C_NAME', 'node_c_reports');
define('DB_C_USER', 'demo_user');
define('DB_C_PASS', 'demo_pass');

// ============================================================
// APP SETTINGS
// ============================================================
define('APP_NAME', 'Distributed DB Demo');
define('APP_DEBUG', true); // Set to false in production
