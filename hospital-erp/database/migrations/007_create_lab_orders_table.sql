CREATE TABLE IF NOT EXISTS `lab_orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id` INT UNSIGNED NOT NULL,
  `doctor_id` INT UNSIGNED NOT NULL COMMENT 'The user ID of the ordering physician',
  `lab_test_id` INT UNSIGNED NOT NULL,
  `order_date` DATETIME NOT NULL,
  `status` ENUM('Ordered', 'Specimen Collected', 'In Progress', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Ordered',
  `notes` TEXT NULL COMMENT 'Clinical notes or reasons for the test',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`lab_test_id`) REFERENCES `lab_tests`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
