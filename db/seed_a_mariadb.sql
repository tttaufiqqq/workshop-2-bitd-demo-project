-- ============================================================
-- Node A: MariaDB Mockup Data (node_a_users database)
-- Run this AFTER node_a_mariadb.sql has been executed.
-- Run on the machine hosting MariaDB (Node A)
-- ============================================================

USE node_a_users;

-- 5 sample students with realistic Malaysian university details.
-- student_id format: B03231XXXX (UTM-style matriculation number)

INSERT INTO students (student_id, name, email) VALUES
    ('B032310001', 'Ahmad Syazwan',  'syazwan@student.utm.my'),
    ('B032310002', 'Nurul Aina',     'aina@student.utm.my'),
    ('B032310003', 'Tan Wei Ming',   'weiming@student.utm.my'),
    ('B032310004', 'Priya Darshini', 'priya@student.utm.my'),
    ('B032310005', 'Muhammad Hafiz', 'hafiz@student.utm.my')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ============================================================
-- Verify:
-- SELECT * FROM students;
-- Expected: 5 rows
-- ============================================================
