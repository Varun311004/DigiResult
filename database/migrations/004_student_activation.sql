-- Required when migrating an existing DigiResult database.
-- Adds a soft-deactivation flag so admin can disable a student without deleting history.
ALTER TABLE students ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER semester;
CREATE INDEX idx_student_active ON students (is_active);
