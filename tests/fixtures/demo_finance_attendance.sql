-- Local-only demo data for the Associa8 database.
-- Uses org_id 1 and synthetic .invalid email addresses.
-- This script is safe to rerun; it does not overwrite existing rows.
START TRANSACTION;

INSERT IGNORE INTO members
  (org_id, member_code, first_name, last_name, email, code, status, joined_date)
VALUES
  (1, 'DEMO-FIN-001', 'Demo', 'Finance Member One', 'demo-finance-001@example.invalid', 'DEMO-FIN-001', 'active', '2026-10-01'),
  (1, 'DEMO-FIN-002', 'Demo', 'Finance Member Two', 'demo-finance-002@example.invalid', 'DEMO-FIN-002', 'active', '2026-10-01'),
  (1, 'DEMO-FIN-003', 'Demo', 'Finance Member Three', 'demo-finance-003@example.invalid', 'DEMO-FIN-003', 'active', '2026-10-01');

INSERT IGNORE INTO finance_transactions
  (reference, member_id, amount, type, status, paid_at, created_at)
SELECT 'DEMO-FIN-PAY-001', id, 45000.00, 'membership_dues', 'successful', '2026-10-08 08:00:00', '2026-10-08 08:00:00'
FROM members WHERE org_id = 1 AND member_code = 'DEMO-FIN-001';

INSERT IGNORE INTO finance_transactions
  (reference, member_id, amount, type, status, paid_at, created_at)
SELECT 'DEMO-FIN-PAY-002', id, 15000.00, 'other', 'successful', '2026-09-12 09:30:00', '2026-09-12 09:30:00'
FROM members WHERE org_id = 1 AND member_code = 'DEMO-FIN-002';

INSERT IGNORE INTO finance_transactions
  (reference, member_id, amount, type, status, paid_at, created_at)
SELECT 'DEMO-FIN-PAY-003', id, 25000.00, 'membership_dues', 'pending', NULL, '2026-10-07 10:15:00'
FROM members WHERE org_id = 1 AND member_code = 'DEMO-FIN-003';

INSERT IGNORE INTO finance_transactions
  (reference, member_id, amount, type, status, paid_at, created_at)
SELECT 'DEMO-FIN-PAY-004', id, 5000.00, 'other', 'failed', NULL, '2026-10-06 11:45:00'
FROM members WHERE org_id = 1 AND member_code = 'DEMO-FIN-001';

INSERT IGNORE INTO attendance_logs (member_id, check_in, status, created_at)
SELECT id, '2026-10-08 08:00:00', 'present', '2026-10-08 08:00:00'
FROM members
WHERE org_id = 1 AND member_code = 'DEMO-FIN-001'
  AND NOT EXISTS (
    SELECT 1 FROM attendance_logs
    WHERE member_id = members.id AND check_in = '2026-10-08 08:00:00'
  );

INSERT IGNORE INTO attendance_logs (member_id, check_in, status, created_at)
SELECT id, '2026-10-07 08:30:00', 'late', '2026-10-07 08:30:00'
FROM members
WHERE org_id = 1 AND member_code = 'DEMO-FIN-002'
  AND NOT EXISTS (
    SELECT 1 FROM attendance_logs
    WHERE member_id = members.id AND check_in = '2026-10-07 08:30:00'
  );

INSERT IGNORE INTO attendance_logs (member_id, check_in, status, created_at)
SELECT id, '2026-10-06 08:15:00', 'absent', '2026-10-06 08:15:00'
FROM members
WHERE org_id = 1 AND member_code = 'DEMO-FIN-003'
  AND NOT EXISTS (
    SELECT 1 FROM attendance_logs
    WHERE member_id = members.id AND check_in = '2026-10-06 08:15:00'
  );

COMMIT;
