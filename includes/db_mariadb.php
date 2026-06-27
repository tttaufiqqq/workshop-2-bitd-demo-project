<?php
// includes/db_mariadb.php
// Connection helper for Node A (MariaDB)
// MariaDB uses the same driver as MySQL (PDO mysql or mysqli)

require_once __DIR__ . '/../config.php';

function getMariaDBConnection(): PDO {
    // Throws PDOException on failure — callers must catch it.
    // die() must NOT be used here: pages catch this per-node so one offline
    // node doesn't crash the entire page (core distributed-systems lesson).
    $dsn = "mysql:host=" . DB_A_HOST . ";port=" . DB_A_PORT . ";dbname=" . DB_A_NAME . ";charset=utf8mb4";
    return new PDO($dsn, DB_A_USER, DB_A_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
}
