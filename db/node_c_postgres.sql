-- ============================================================
-- Node C: PostgreSQL Schema (node_c_reports database)
-- Run this on the machine hosting PostgreSQL (Node C)
-- ============================================================

-- Step 1: Create the database
-- Run this in psql as the postgres superuser:
--   CREATE DATABASE node_c_reports;
--   \c node_c_reports

-- Step 2: Create a dedicated user
CREATE USER demo_user WITH PASSWORD 'demo_pass';
GRANT ALL PRIVILEGES ON DATABASE node_c_reports TO demo_user;

-- Step 3: Connect to the database before running the rest
-- \c node_c_reports

-- Step 4: Grant schema permissions (PostgreSQL requires this separately)
GRANT ALL ON SCHEMA public TO demo_user;

-- Step 5: Create tables

CREATE TABLE IF NOT EXISTS order_summary (
    id              SERIAL PRIMARY KEY,       -- SERIAL = PostgreSQL auto-increment
    user_id         INTEGER NOT NULL UNIQUE,  -- mirrors Node A users.id; UNIQUE required for ON CONFLICT (user_id)
    user_name       VARCHAR(100),             -- denormalized for reporting speed (DR-04)
    total_orders    INTEGER DEFAULT 0,
    total_spent     NUMERIC(10,2) DEFAULT 0.00,
    last_updated    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- NOTE: In PostgreSQL, SERIAL and AUTO_INCREMENT are the same concept,
-- but PostgreSQL uses SERIAL (or BIGSERIAL for big tables).
-- NUMERIC is PostgreSQL's equivalent of MySQL's DECIMAL.

-- Step 6: Allow connections from any IP
-- Edit pg_hba.conf and add:
--   host  all  all  0.0.0.0/0  md5
-- Then in postgresql.conf, set:
--   listen_addresses = '*'
-- Restart PostgreSQL after these changes.

-- Step 7: Seed sample data
INSERT INTO order_summary (user_id, user_name, total_orders, total_spent) VALUES
    (1, 'Ahmad Syazwan', 2, 115.00),
    (2, 'Nurul Aina',    1, 199.00),
    (3, 'Tan Wei Ming',  1, 89.00)
ON CONFLICT (id) DO NOTHING;

-- ============================================================
-- Verify:
-- \dt
-- SELECT * FROM order_summary;
-- ============================================================
