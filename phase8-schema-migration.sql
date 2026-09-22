ALTER TABLE admissions
  ADD COLUMN exam_code VARCHAR(10) NULL AFTER status,
  ADD COLUMN exam_scheduled_at DATETIME NULL AFTER exam_code,
  ADD COLUMN exam_expires_at DATETIME NULL AFTER exam_scheduled_at,
  ADD COLUMN cbt_exam_id INT UNSIGNED NULL AFTER exam_expires_at,
  MODIFY status ENUM('pending', 'under_review', 'cbt_scheduled', 'cbt_completed', 'approved', 'rejected') DEFAULT 'pending',
  ADD KEY admissions_cbt_exam_idx (cbt_exam_id),
  ADD CONSTRAINT admissions_cbt_exam_fk FOREIGN KEY (cbt_exam_id) REFERENCES cbt_exams (id) ON DELETE SET NULL;

ALTER TABLE notifications
  MODIFY member_id INT UNSIGNED NULL,
  ADD COLUMN admission_id INT UNSIGNED NULL AFTER member_id,
  ADD KEY notifications_admission_idx (admission_id),
  ADD CONSTRAINT notifications_admission_fk FOREIGN KEY (admission_id) REFERENCES admissions (id) ON DELETE CASCADE;

ALTER TABLE cbt_results
  MODIFY member_id INT UNSIGNED NULL,
  ADD COLUMN admission_id INT UNSIGNED NULL AFTER member_id,
  ADD KEY cbt_results_admission_idx (admission_id),
  ADD CONSTRAINT cbt_results_admission_fk FOREIGN KEY (admission_id) REFERENCES admissions (id) ON DELETE CASCADE;
