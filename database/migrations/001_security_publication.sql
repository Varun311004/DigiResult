-- Run this on an existing DigiResult database after importing the original srms.sql.
-- It intentionally keeps the original core tables/columns for compatibility.
SET NAMES utf8mb4;

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
  KEY idx_publication_live (is_live,published_at)
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
  KEY idx_audit_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
