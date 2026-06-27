-- ============================================================
-- Node B: MySQL Schema (node_b_orders database)
-- Run this on the machine hosting MySQL (Node B)
-- ============================================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS node_b_orders
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE node_b_orders;

-- Step 2: Create a dedicated user for the app
CREATE USER IF NOT EXISTS 'demo_user'@'%' IDENTIFIED BY 'demo_pass';
GRANT ALL PRIVILEGES ON node_b_orders.* TO 'demo_user'@'%';
FLUSH PRIVILEGES;

-- Step 3: Create tables

CREATE TABLE IF NOT EXISTS orders (
    order_id    INT AUTO_INCREMENT PRIMARY KEY,
    student_id  VARCHAR(20)  NOT NULL,  -- References students.student_id on Node A (MariaDB)
    item_name   VARCHAR(200) NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    price       DECIMAL(8,2) NOT NULL,
    status      ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    ordered_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- IMPORTANT NOTE FOR STUDENTS:
-- Notice that student_id here refers to the primary key in Node A's students table.
-- We CANNOT use a foreign key constraint across different databases/servers.
-- In a distributed system, referential integrity must be enforced
-- in your application code (PHP), not at the database level.
-- See pages/orders.php — it queries Node A first to verify the student exists.

-- ============================================================
-- Verify:
-- SHOW TABLES;
-- SELECT * FROM orders;
-- To load mockup data run: db/seed_b_mysql.sql
