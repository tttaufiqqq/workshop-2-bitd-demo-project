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
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,  -- References users.id on Node A (MariaDB)
    item_name   VARCHAR(200) NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    price       DECIMAL(8,2) NOT NULL,
    status      ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    ordered_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- IMPORTANT NOTE FOR STUDENTS:
-- Notice that user_id here refers to the id in Node A's users table.
-- We CANNOT use a foreign key constraint across different databases/servers.
-- In a distributed system, referential integrity must be enforced
-- in your application code (PHP), not at the database level.

-- Step 4: Seed some sample data
INSERT INTO orders (user_id, item_name, quantity, price, status) VALUES
    (1, 'Laptop Stand',       1, 45.00, 'confirmed'),
    (1, 'USB-C Hub',          2, 35.00, 'pending'),
    (2, 'Mechanical Keyboard', 1, 199.00, 'confirmed'),
    (3, 'Monitor Light Bar',  1, 89.00, 'pending');

-- ============================================================
-- Verify:
-- SHOW TABLES;
-- SELECT * FROM orders;
