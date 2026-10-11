-- DigiResult modernized fresh-install schema.
-- Select/create your target database in phpMyAdmin before importing this file.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admincreds (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS batch (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Batch VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_batch (Batch)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS department (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Department VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_department (Department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS semester (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Semester VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_semester (Semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS year (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Year INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_year (Year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ien_number VARCHAR(20) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  date_of_birth DATE NOT NULL,
  gender ENUM('Male','Female','Other') NOT NULL,
  email VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  student_number VARCHAR(20) NOT NULL,
  year INT NOT NULL,
  department VARCHAR(100) NOT NULL,
  batch VARCHAR(50) NOT NULL,
  semester VARCHAR(50) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_student_ien (ien_number),
  KEY idx_student_email (email),
  KEY idx_student_active (is_active),
  KEY idx_student_context (year,batch,semester,department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Subject VARCHAR(150) NOT NULL,
  tw_checked VARCHAR(1) NOT NULL DEFAULT '',
  pr_checked VARCHAR(1) NOT NULL DEFAULT '',
  ese_checked VARCHAR(1) NOT NULL DEFAULT '',
  Batch VARCHAR(50) NOT NULL,
  Semester VARCHAR(50) NOT NULL,
  Department VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_subject_context (Subject,Batch,Semester,Department),
  KEY idx_subject_context (Batch,Semester,Department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS results (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ien_number VARCHAR(20) DEFAULT NULL,
  subject VARCHAR(150) DEFAULT NULL,
  TW_marks INT DEFAULT NULL,
  PR_marks INT DEFAULT NULL,
  ESE_marks INT DEFAULT NULL,
  year VARCHAR(10) DEFAULT NULL,
  batch VARCHAR(50) DEFAULT NULL,
  semester VARCHAR(50) DEFAULT NULL,
  department VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_result_context (ien_number,subject,year,semester,batch,department),
  KEY idx_result_context (year,batch,semester,department),
  KEY idx_result_student (ien_number,year,semester),
  CONSTRAINT fk_results_student_ien FOREIGN KEY (ien_number) REFERENCES students (ien_number) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_otps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ien_number VARCHAR(20) NOT NULL,
  email VARCHAR(255) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  consumed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  request_ip VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_otp_lookup (ien_number,email,consumed_at),
  KEY idx_otp_rate (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_password_resets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id INT UNSIGNED NOT NULL,
  email VARCHAR(255) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  request_ip VARCHAR(45) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_reset_token (token_hash),
  KEY idx_admin_reset_admin (admin_id),
  KEY idx_admin_reset_email (email),
  KEY idx_admin_reset_expires (expires_at),
  CONSTRAINT fk_admin_reset_admin
    FOREIGN KEY (admin_id) REFERENCES admincreds (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS result_publications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  year VARCHAR(10) NOT NULL,
  batch VARCHAR(50) NOT NULL,
  semester VARCHAR(50) NOT NULL,
  department VARCHAR(100) NOT NULL,
  is_live TINYINT(1) NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  published_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_publication_context (year,batch,semester,department),
  KEY idx_publication_live (is_live,published_at),
  CONSTRAINT fk_publication_admin FOREIGN KEY (published_by) REFERENCES admincreds (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id INT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(500) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_created (created_at),
  KEY idx_audit_admin (admin_id),
  CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES admincreds (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
