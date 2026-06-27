-- ============================================================
-- Node A: MariaDB Schema (node_a_users database)
-- Run this on the machine hosting MariaDB (Node A)
-- ============================================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS node_a_users
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE node_a_users;

-- Step 2: Create a dedicated user for the app
--         Run this as root/admin
CREATE USER IF NOT EXISTS 'demo_user'@'%' IDENTIFIED BY 'demo_pass';
GRANT ALL PRIVILEGES ON node_a_users.* TO 'demo_user'@'%';
FLUSH PRIVILEGES;

-- NOTE: '%' means allow connections from ANY IP address.
--       This is needed because other team members will connect
--       from different machines via Tailscale.
--       In a real production system, you would restrict this
--       to specific IPs only.

-- Step 3: Create tables

CREATE TABLE IF NOT EXISTS students (
    student_id  VARCHAR(20)  NOT NULL PRIMARY KEY,  -- e.g. B032310001
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- NOTE: student_id is the primary key — it is the university-issued
--       ID (e.g. B032310001) and is unique by definition.
--       No separate auto-increment id is needed.
--       Other nodes (B and C) reference this student_id directly.

-- ============================================================
-- Verify everything is set up:
-- ============================================================
-- SHOW TABLES;
-- SELECT * FROM students;
-- To load mockup data run: db/seed_a_mariadb.sql
