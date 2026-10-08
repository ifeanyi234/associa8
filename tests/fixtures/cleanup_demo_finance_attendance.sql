-- Removes only the clearly labeled demo payment and attendance records.
START TRANSACTION;

DELETE FROM attendance_logs
WHERE member_id IN (
  SELECT id FROM members
  WHERE org_id = 1 AND member_code IN ('DEMO-FIN-001', 'DEMO-FIN-002', 'DEMO-FIN-003')
);

DELETE FROM finance_transactions
WHERE reference IN (
  'DEMO-FIN-PAY-001',
  'DEMO-FIN-PAY-002',
  'DEMO-FIN-PAY-003',
  'DEMO-FIN-PAY-004'
);

DELETE FROM members
WHERE org_id = 1
  AND member_code IN ('DEMO-FIN-001', 'DEMO-FIN-002', 'DEMO-FIN-003')
  AND email IN (
    'demo-finance-001@example.invalid',
    'demo-finance-002@example.invalid',
    'demo-finance-003@example.invalid'
  );

COMMIT;
