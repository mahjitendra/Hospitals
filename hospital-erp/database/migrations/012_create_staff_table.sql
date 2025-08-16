CREATE TABLE IF NOT EXISTS `staff` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `job_title` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `hire_date` DATE NOT NULL,
  `phone_number` VARCHAR(50) NULL,
  `email` VARCHAR(255) NOT NULL,
  `status` ENUM('Active', 'On Leave', 'Terminated') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_user_id_unique` (`user_id`),
  UNIQUE KEY `staff_email_unique` (`email`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
