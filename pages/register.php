<?php
// pages/register.php
// Writes a new user to Node A (MariaDB)
// Demonstrates: INSERT with PDO prepared statements + cross-node data flow

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_mariadb.php';
require_once __DIR__ . '/../includes/styles.php';

$message = null;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $student_id = trim($_POST['student_id'] ?? '');

    if (empty($name) || empty($email) || empty($student_id)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            $pdo  = getMariaDBConnection();
            $stmt = $pdo->prepare("INSERT INTO users (name, email, student_id) VALUES (:name, :email, :student_id)");
            $stmt->execute([':name' => $name, ':email' => $email, ':student_id' => $student_id]);
            $message = "User registered! ID = " . $pdo->lastInsertId() . " (Node A — MariaDB at " . DB_A_HOST . ")";
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000'
                ? "Email or Student ID already exists."
                : "Database error: " . $e->getMessage();
        }
    }
}

$users = [];
try {
    $pdo   = getMariaDBConnection();
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $error = $error ?? "Cannot load users: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — <?= APP_NAME ?></title>
    <?php renderStyles(); ?>
</head>
<body>
<?php renderNav('register'); ?>
<div class="page narrow">

<div class="page-header">
    <div class="badges"><span class="badge badge-a">Node A — MariaDB</span></div>
    <h1>Register New User</h1>
    <p>Creates a student record on Node A (<?= DB_A_HOST ?>). This node stores all user identity data.</p>
</div>

<?php if ($message): ?><div class="alert alert-ok">✅ <?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input class="form-input" type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input class="form-input" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Student ID</label>
            <input class="form-input" type="text" name="student_id" placeholder="e.g. B032310099" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
        </div>
        <button class="btn btn-primary" type="submit">Register →</button>
    </form>
</div>

<h2 class="section-title">All Registered Users</h2>
<?php if (empty($users)): ?>
    <p class="empty">No users yet.</p>
<?php else: ?>
    <div class="card card-table">
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Student ID</th><th>Registered</th></tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['student_id']) ?></td>
                <td><?= $u['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

</div>
</body>
</html>
