CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_order_id` INT UNSIGNED NOT NULL,
  `inventory_item_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
