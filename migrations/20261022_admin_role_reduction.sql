-- Apply after 20261021_scoped_admin_invitations.sql.
UPDATE `admin-info`
SET role = 'admin'
WHERE role IN ('manager', 'staff');

UPDATE staff_invitations
SET role = 'admin', zone_id = NULL, subzone_id = NULL
WHERE role IN ('manager', 'staff');

UPDATE users
SET role = 'admin'
WHERE role IN ('manager', 'staff');

ALTER TABLE users
  MODIFY COLUMN role enum('super_admin','admin') NOT NULL DEFAULT 'admin';
