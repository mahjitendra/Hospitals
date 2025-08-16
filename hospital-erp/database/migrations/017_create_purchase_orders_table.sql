CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_id` INT UNSIGNED NOT NULL,
  `order_date` DATE NOT NULL,
  `expected_delivery_date` DATE NULL,
  `status` ENUM('Pending', 'Ordered', 'Shipped', 'Received', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
