CREATE TABLE IF NOT EXISTS `lab_results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lab_order_id` INT UNSIGNED NOT NULL,
  `result_value` VARCHAR(255) NOT NULL COMMENT 'The quantitative or qualitative result',
  `result_unit` VARCHAR(50) NULL COMMENT 'e.g., mg/dL, %, positive/negative',
  `is_abnormal` BOOLEAN NOT NULL DEFAULT FALSE,
  `result_date` DATETIME NOT NULL,
  `notes` TEXT NULL COMMENT 'Notes from the lab technician',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`lab_order_id`) REFERENCES `lab_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
