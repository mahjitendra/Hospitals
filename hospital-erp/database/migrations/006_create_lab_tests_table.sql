CREATE TABLE IF NOT EXISTS `lab_tests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `department` VARCHAR(100) NOT NULL COMMENT 'e.g., Hematology, Chemistry, Microbiology',
  `specimen_type` VARCHAR(100) NOT NULL COMMENT 'e.g., Blood, Urine, Saliva',
  `reference_range` VARCHAR(255) NULL COMMENT 'e.g., 4.5-5.5 x 10^12/L',
  `cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
