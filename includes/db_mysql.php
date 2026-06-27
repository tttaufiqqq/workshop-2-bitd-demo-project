<?php
// includes/db_mysql.php
// Connection helper for Node B (MySQL)

require_once __DIR__ . '/../config.php';

function getMySQLConnection(): PDO {
    // Throws PDOException on failure — callers must catch it.
    // See db_mariadb.php for the reason die() is not used here.
    $dsn = "mysql:host=" . DB_B_HOST . ";port=" . DB_B_PORT . ";dbname=" . DB_B_NAME . ";charset=utf8mb4";
    return new PDO($dsn, DB_B_USER, DB_B_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
}
