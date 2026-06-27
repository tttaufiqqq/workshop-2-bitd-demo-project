-- ============================================================
-- Node C: PostgreSQL Mockup Data (node_c_reports database)
-- Run this AFTER node_c_postgres.sql has been executed.
-- Run on the machine hosting PostgreSQL (Node C)
-- ============================================================
--
-- IMPORTANT: These rows must match the orders in seed_b_mysql.sql.
-- total_orders = number of orders placed by that student
-- total_spent  = sum of price values from Node B orders
--                (matches how pages/orders.php upserts this table)
--
-- In normal use this table is populated automatically when orders
-- are placed via pages/orders.php. This seed file exists so the
-- Reports page has data to show during an offline demo.
-- ============================================================

-- Connect to the database first: \c node_c_reports

INSERT INTO order_summary (student_id, user_name, total_orders, total_spent) VALUES
    --  student_id     name              orders  spent (sum of price per order)
    ('B032310001', 'Ahmad Syazwan',   3,  229.00),  -- 45 + 35 + 149
    ('B032310002', 'Nurul Aina',      2,  234.00),  -- 199 + 35
    ('B032310003', 'Tan Wei Ming',    2,  111.00),  -- 89 + 22
    ('B032310004', 'Priya Darshini',  2,  108.00),  -- 79 + 29
    ('B032310005', 'Muhammad Hafiz',  1,   25.00)   -- 25
ON CONFLICT (student_id) DO UPDATE
    SET total_orders = EXCLUDED.total_orders,
        total_spent  = EXCLUDED.total_spent,
        user_name    = EXCLUDED.user_name,
        last_updated = CURRENT_TIMESTAMP;

-- ============================================================
-- Verify:
-- SELECT * FROM order_summary ORDER BY total_spent DESC;
-- Expected: 5 rows
-- ============================================================
