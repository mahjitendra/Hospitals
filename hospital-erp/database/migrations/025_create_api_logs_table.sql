CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_method` VARCHAR(10) NOT NULL,
  `request_uri` VARCHAR(255) NOT NULL,
  `request_payload` TEXT NULL,
  `response_code` INT NULL,
  `response_payload` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
