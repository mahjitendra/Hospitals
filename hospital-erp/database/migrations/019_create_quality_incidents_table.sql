CREATE TABLE IF NOT EXISTS `quality_incidents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `incident_date` DATETIME NOT NULL,
  `description` TEXT NOT NULL,
  `department` VARCHAR(100) NULL,
  `reported_by` INT UNSIGNED NULL,
  `severity` ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
  `status` ENUM('Open', 'Under Investigation', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`reported_by`) REFERENCES `staff`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
