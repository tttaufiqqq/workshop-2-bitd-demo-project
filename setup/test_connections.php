<?php
// setup/test_connections.php
// ============================================================
// Run this page FIRST before anything else.
// It checks whether your app can reach all 3 database nodes.
// Open in browser: http://localhost/distributed-db-demo/setup/test_connections.php
// ============================================================

require_once __DIR__ . '/../config.php';

function testConnection(string $label, string $dsn, string $user, string $pass): array {
    try {
        $start = microtime(true);
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE  => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT  => 5,
        ]);
        $latency = round((microtime(true) - $start) * 1000, 1);
        $pdo->query("SELECT 1"); // Simple ping
        return ['ok' => true, 'msg' => "Connected successfully ({$latency}ms)"];
    } catch (PDOException $e) {
        return ['ok' => false, 'msg' => $e->getMessage()];
    }
}

$tests = [
    'Node A — MariaDB' => testConnection(
        'Node A',
        "mysql:host=" . DB_A_HOST . ";port=" . DB_A_PORT . ";dbname=" . DB_A_NAME . ";charset=utf8mb4",
        DB_A_USER, DB_A_PASS
    ),
    'Node B — MySQL' => testConnection(
        'Node B',
        "mysql:host=" . DB_B_HOST . ";port=" . DB_B_PORT . ";dbname=" . DB_B_NAME . ";charset=utf8mb4",
        DB_B_USER, DB_B_PASS
    ),
    'Node C — PostgreSQL' => testConnection(
        'Node C',
        "pgsql:host=" . DB_C_HOST . ";port=" . DB_C_PORT . ";dbname=" . DB_C_NAME,
        DB_C_USER, DB_C_PASS
    ),
];

$allOk = array_reduce($tests, fn($carry, $t) => $carry && $t['ok'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Connection Test — <?= APP_NAME ?></title>
    <style>
        body { font-family: monospace; max-width: 700px; margin: 40px auto; padding: 0 20px; background: #0f0f0f; color: #eee; }
        h1   { color: #7dd3fc; }
        .box { border: 1px solid #333; border-radius: 6px; padding: 16px; margin: 12px 0; }
        .ok  { border-color: #4ade80; }
        .err { border-color: #f87171; }
        .ok  .status { color: #4ade80; }
        .err .status { color: #f87171; }
        .label { font-size: 1.1em; font-weight: bold; margin-bottom: 6px; }
        .msg   { color: #aaa; font-size: 0.9em; margin-top: 4px; }
        .config { background: #1a1a1a; padding: 12px; border-radius: 4px; margin: 4px 0; font-size: 0.85em; color: #facc15; }
        .summary { padding: 16px; border-radius: 6px; text-align: center; font-size: 1.2em; margin-top: 20px; }
        .all-ok  { background: #052e16; color: #4ade80; }
        .has-err { background: #2d0a0a; color: #f87171; }
        a { color: #7dd3fc; }
    </style>
</head>
<body>
<h1>🔌 <?= APP_NAME ?> — Connection Test</h1>
<p>Testing all 3 database nodes. Check <code>config.php</code> if any fail.</p>

<?php foreach ($tests as $label => $result): ?>
<div class="box <?= $result['ok'] ? 'ok' : 'err' ?>">
    <div class="label"><?= htmlspecialchars($label) ?></div>
    <div class="status"><?= $result['ok'] ? '✅ OK' : '❌ FAILED' ?></div>
    <div class="msg"><?= htmlspecialchars($result['msg']) ?></div>
</div>
<?php endforeach; ?>

<div class="summary <?= $allOk ? 'all-ok' : 'has-err' ?>">
    <?php if ($allOk): ?>
        ✅ All nodes connected! <a href="../pages/index.php">→ Go to Dashboard</a>
    <?php else: ?>
        ❌ Some nodes failed. Fix config.php, then refresh this page.
    <?php endif; ?>
</div>

<h2 style="margin-top:30px">Current config.php values</h2>
<div class="config">
    Node A: <?= DB_A_USER ?>@<?= DB_A_HOST ?>:<?= DB_A_PORT ?>/<?= DB_A_NAME ?><br>
    Node B: <?= DB_B_USER ?>@<?= DB_B_HOST ?>:<?= DB_B_PORT ?>/<?= DB_B_NAME ?><br>
    Node C: <?= DB_C_USER ?>@<?= DB_C_HOST ?>:<?= DB_C_PORT ?>/<?= DB_C_NAME ?>
</div>

<p style="color:#555;margin-top:30px;font-size:0.8em">
    If a node fails: (1) check <code>config.php</code> IPs match Tailscale IPs,
    (2) ping the host from terminal, (3) check DB service is running,
    (4) check the DB user has remote access (see README troubleshooting).
</p>
</body>
</html>
