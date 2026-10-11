-- Optional integrity constraints for an existing migrated DigiResult database.
-- BEFORE running this file, verify: students.ien_number is unique, results contain no
-- orphan IEN values, and published_by/admin_id values refer to admincreds ids.
-- This migration is intentionally separate because legacy data may contain inconsistencies.

ALTER TABLE results
  ADD CONSTRAINT fk_results_student_ien FOREIGN KEY (ien_number) REFERENCES students (ien_number) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE result_publications
  ADD CONSTRAINT fk_publication_admin FOREIGN KEY (published_by) REFERENCES admincreds (id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE audit_logs
  ADD CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES admincreds (id) ON DELETE SET NULL ON UPDATE CASCADE;
