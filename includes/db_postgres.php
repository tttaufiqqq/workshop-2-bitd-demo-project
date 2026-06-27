<?php
// includes/db_postgres.php
// Connection helper for Node C (PostgreSQL)
// Note: PostgreSQL uses a DIFFERENT DSN format than MySQL/MariaDB

require_once __DIR__ . '/../config.php';

function getPostgresConnection(): PDO {
    // PostgreSQL DSN uses "pgsql:" prefix — different from MySQL family's "mysql:"
    // Throws PDOException on failure — callers must catch it.
    // See db_mariadb.php for the reason die() is not used here.
    $dsn = "pgsql:host=" . DB_C_HOST . ";port=" . DB_C_PORT . ";dbname=" . DB_C_NAME;
    return new PDO($dsn, DB_C_USER, DB_C_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
}
