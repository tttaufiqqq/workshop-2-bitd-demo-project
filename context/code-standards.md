# Code Standards — Distributed DB Demo

## PHP Version

PHP 8.0 or higher. No deprecated functions. No `mysql_*` functions (removed in PHP 7).

## File Size

No single PHP file exceeds **150 lines**. If a page grows beyond this,
extract a helper function into `includes/`.

## Requires

Always use `require_once` with `__DIR__` for reliable relative paths:

```php
// Correct
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_mariadb.php';

// Wrong — breaks if PHP is run from a different working directory
require_once '../config.php';
```

## Database Queries

**Always use prepared statements.** Never interpolate variables into SQL.

```php
// Correct
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);

// Wrong — SQL injection risk
$result = $pdo->query("SELECT * FROM users WHERE id = $id");
```

Named placeholders (`:name`) are preferred over positional (`?`) for readability.

## HTML Output

Every value from user input or database must go through `htmlspecialchars()`:

```php
// Correct
echo htmlspecialchars($user['name']);

// Wrong — XSS risk
echo $user['name'];
```

## Error Handling Strategy

Catch exceptions per node. Never let one node failure bubble up to a fatal error:

```php
$users = [];
$usersError = null;

try {
    $pdo = getMariaDBConnection();
    $users = $pdo->query("SELECT * FROM users")->fetchAll();
} catch (Exception $e) {
    $usersError = $e->getMessage();
}
```

Then in HTML:
```php
if ($usersError) {
    echo '<div class="error">' . htmlspecialchars($usersError) . '</div>';
} else {
    // render table
}
```

## HTML Structure Per Page

Every page follows this structure:

```
<?php // 1. PHP logic block (POST handling + DB reads)  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Title — APP_NAME</title>
    <style>/* inline styles only — no external CSS files */</style>
</head>
<body>
    <!-- nav / back link -->
    <!-- node tag badge -->
    <!-- page heading -->
    <!-- success / error messages -->
    <!-- main content -->
    <!-- footer note explaining what this page demonstrates -->
</body>
</html>
```

## CSS

All CSS is inline in `<style>` blocks. No external stylesheets. No CDN links.
Students should not need internet access to view the demo.

Color system:
- Node A (MariaDB): blue tones — `#dbeafe` bg, `#1e40af` text
- Node B (MySQL): green tones — `#dcfce7` bg, `#166534` text
- Node C (PostgreSQL): amber tones — `#fef3c7` bg, `#92400e` text
- Errors: `#fef2f2` bg, `#dc2626` text
- Success: `#f0fdf4` bg, `#166534` text

## Function Naming

| Type | Convention | Example |
|---|---|---|
| DB connection helper | `get{Engine}Connection()` | `getMariaDBConnection()` |
| Page helper (if extracted) | camelCase verb-noun | `renderNodeError()` |
| Validation | `validate{Thing}()` returning bool | `validateEmail()` |

## SQL Files

Each `db/*.sql` file must be runnable top-to-bottom as a single script.
Include `CREATE DATABASE IF NOT EXISTS` and `CREATE USER IF NOT EXISTS`
so students can run it fresh on any machine.

Always include a commented `-- Verify:` section at the bottom with the
queries students should run to confirm the setup worked.

## Comments

Comment the *why*, not the *what*:

```php
// Correct — explains a non-obvious decision
// We catch the exception here and not in the helper so that
// one node failure doesn't take down the whole dashboard.

// Wrong — restates the code
// Get the MariaDB connection
$pdo = getMariaDBConnection();
```

All teaching-specific notes (e.g. "In a distributed system, you cannot
use FK constraints across servers") belong as inline comments in the
relevant file, not just in the docs.
