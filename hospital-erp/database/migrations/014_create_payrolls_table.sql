CREATE TABLE IF NOT EXISTS `payrolls` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` INT UNSIGNED NOT NULL,
  `pay_period_start` DATE NOT NULL,
  `pay_period_end` DATE NOT NULL,
  `gross_salary` DECIMAL(10, 2) NOT NULL,
  `deductions` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `net_salary` DECIMAL(10, 2) NOT NULL,
  `pay_date` DATE NOT NULL,
  `status` ENUM('Pending', 'Paid', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`staff_id`) REFERENCES `staff`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
