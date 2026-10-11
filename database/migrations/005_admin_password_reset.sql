-- Required for administrator password-reset links.
-- Run after the earlier DigiResult security migrations.

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
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
