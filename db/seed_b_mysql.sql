-- ============================================================
-- Node B: MySQL Mockup Data (node_b_orders database)
-- Run this AFTER node_b_mysql.sql has been executed.
-- Run on the machine hosting MySQL (Node B)
-- ============================================================
--
-- IMPORTANT: The student_id values here must already exist in
-- Node A's students table. Run seed_a_mariadb.sql on Node A first.
-- In production, PHP enforces this check (see pages/orders.php).
-- ============================================================

USE node_b_orders;

-- 10 sample orders spread across all 5 students.
-- Statuses are mixed (pending / confirmed) to make the dashboard
-- more realistic during the demo.

INSERT INTO orders (student_id, item_name, quantity, price, status) VALUES
    -- Ahmad Syazwan: 3 orders
    ('B032310001', 'Laptop Stand',         1,  45.00, 'confirmed'),
    ('B032310001', 'USB-C Hub',            2,  35.00, 'pending'),
    ('B032310001', 'Webcam HD 1080p',      1, 149.00, 'confirmed'),

    -- Nurul Aina: 2 orders
    ('B032310002', 'Mechanical Keyboard',  1, 199.00, 'confirmed'),
    ('B032310002', 'Desk Organizer',       1,  35.00, 'confirmed'),

    -- Tan Wei Ming: 2 orders
    ('B032310003', 'Monitor Light Bar',    1,  89.00, 'pending'),
    ('B032310003', 'USB Flash Drive 64GB', 3,  22.00, 'confirmed'),

    -- Priya Darshini: 2 orders
    ('B032310004', 'Wireless Mouse',       1,  79.00, 'confirmed'),
    ('B032310004', 'Phone Stand',          1,  29.00, 'pending'),

    -- Muhammad Hafiz: 1 order
    ('B032310005', 'HDMI Cable 2m',        2,  25.00, 'confirmed');

-- ============================================================
-- Verify:
-- SELECT * FROM orders;
-- Expected: 10 rows
--
-- SELECT student_id, COUNT(*) AS total_orders, SUM(price) AS total_spent
-- FROM orders GROUP BY student_id;
-- ============================================================
