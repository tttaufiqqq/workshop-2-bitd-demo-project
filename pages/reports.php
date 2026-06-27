<?php
// pages/reports.php
// Cross-node read: fetch order_summary from Node C, then enrich each row
// with email from Node A and most recent order from Node B.
// This is an application-level JOIN across three separate database servers.

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_mariadb.php';
require_once __DIR__ . '/../includes/db_mysql.php';
require_once __DIR__ . '/../includes/db_postgres.php';
require_once __DIR__ . '/../includes/styles.php';

$summary      = [];
$summaryError = null;
$nodeAError   = null;
$nodeBError   = null;

// Fetch summary from Node C — required; nothing useful to show without it
try {
    $pdoC    = getPostgresConnection();
    $summary = $pdoC->query("SELECT * FROM order_summary ORDER BY total_spent DESC")->fetchAll();
} catch (Exception $e) { $summaryError = $e->getMessage(); }

// Enrich each summary row with Node A email + Node B last order (app-level join)
if (!$summaryError) {
    $pdoA = null; $pdoB = null;
    try { $pdoA = getMariaDBConnection(); } catch (Exception $e) { $nodeAError = $e->getMessage(); }
    try { $pdoB = getMySQLConnection();   } catch (Exception $e) { $nodeBError = $e->getMessage(); }

    foreach ($summary as &$row) {
        // Node A: look up email by user_id
        if ($pdoA) {
            $stmt = $pdoA->prepare("SELECT email FROM users WHERE id = :id");
            $stmt->execute([':id' => $row['user_id']]);
            $userRow       = $stmt->fetch();
            $row['email']  = $userRow ? $userRow['email'] : '(not found)';
        } else {
            $row['email'] = '(Node A unavailable)';
        }

        // Node B: look up most recent order by user_id
        if ($pdoB) {
            $stmt = $pdoB->prepare(
                "SELECT item_name, ordered_at FROM orders WHERE user_id = :id ORDER BY ordered_at DESC LIMIT 1"
            );
            $stmt->execute([':id' => $row['user_id']]);
            $orderRow             = $stmt->fetch();
            $row['last_item']     = $orderRow ? $orderRow['item_name']  : 'No orders';
            $row['last_order_at'] = $orderRow ? $orderRow['ordered_at'] : '-';
        } else {
            $row['last_item']     = '(Node B unavailable)';
            $row['last_order_at'] = '-';
        }
    }
    unset($row); // break reference after foreach
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports — <?= APP_NAME ?></title>
    <?php renderStyles(); ?>
</head>
<body>
<?php renderNav('reports'); ?>
<div class="page">

<div class="page-header">
    <div class="badges">
        <span class="badge badge-a">Node A — MariaDB</span>
        <span class="badge badge-b">Node B — MySQL</span>
        <span class="badge badge-c">Node C — PostgreSQL</span>
    </div>
    <h1>Order Summary Report</h1>
    <p>An application-level join — Node C supplies the aggregates; PHP enriches each row from Node A and Node B.</p>
</div>

<?php if ($summaryError): ?>
    <div class="alert alert-err">❌ Cannot load summary — Node C is required for this page: <?= htmlspecialchars($summaryError) ?></div>
<?php elseif (empty($summary)): ?>
    <p class="empty">No summary data yet. <a href="orders.php">Place an order →</a></p>
<?php else: ?>
    <?php if ($nodeAError): ?><div class="alert alert-warn">⚠️ Node A unavailable — email column shows placeholder values.</div><?php endif; ?>
    <?php if ($nodeBError): ?><div class="alert alert-warn">⚠️ Node B unavailable — last order column shows placeholder values.</div><?php endif; ?>
    <div class="card card-table">
        <table>
            <tr>
                <th>User Name</th><th>Email <small>(Node A)</small></th>
                <th>Total Orders</th><th>Total Spent</th>
                <th>Last Item <small>(Node B)</small></th><th>Last Order</th>
            </tr>
            <?php foreach ($summary as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['user_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['total_orders'] ?></td>
                <td>RM <?= number_format((float)$row['total_spent'], 2) ?></td>
                <td><?= htmlspecialchars($row['last_item']) ?></td>
                <td><?= htmlspecialchars($row['last_order_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<div class="callout">
    <strong>App-level JOIN:</strong> This page does what a SQL JOIN does — but across three servers.
    Node C provides the pre-aggregated summary, then PHP loops through each row to fetch the matching
    email from Node A and the most recent order from Node B by <code>user_id</code>.
    Notice that <code>order_summary</code> also stores <code>user_name</code> directly: if Node A is
    down, the name column is still readable from that denormalised copy — a deliberate tradeoff between
    consistency and availability.
</div>

</div>
</body>
</html>
