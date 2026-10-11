-- Optional performance/index improvements for a cleaned-up database.
-- Review existing indexes before running against a heavily customized production DB.
ALTER TABLE students ADD INDEX idx_student_context (year,batch,semester,department);
ALTER TABLE results ADD INDEX idx_result_context (year,batch,semester,department);
ALTER TABLE results ADD INDEX idx_result_student (ien_number,year,semester);
ALTER TABLE subjects ADD INDEX idx_subject_context (Batch,Semester,Department);
