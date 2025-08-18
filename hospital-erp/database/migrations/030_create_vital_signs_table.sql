CREATE TABLE IF NOT EXISTS `vital_signs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id` INT UNSIGNED NOT NULL,
  `record_date` DATETIME NOT NULL,
  `temperature` DECIMAL(4, 1) NULL,
  `heart_rate` INT NULL,
  `blood_pressure` VARCHAR(20) NULL,
  `respiratory_rate` INT NULL,
  `oxygen_saturation` INT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
