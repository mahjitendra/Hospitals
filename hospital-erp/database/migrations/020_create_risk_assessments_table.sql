CREATE TABLE IF NOT EXISTS `risk_assessments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assessment_date` DATE NOT NULL,
  `area_assessed` VARCHAR(255) NOT NULL,
  `risk_description` TEXT NOT NULL,
  `likelihood` ENUM('Low', 'Medium', 'High') NOT NULL,
  `impact` ENUM('Low', 'Medium', 'High') NOT NULL,
  `mitigation_plan` TEXT NULL,
  `status` ENUM('Open', 'In Progress', 'Mitigated', 'Closed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
