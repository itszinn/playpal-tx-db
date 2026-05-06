CREATE DATABASE IF NOT EXISTS `playpal_tx_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `playpal_tx_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','member','guest') NOT NULL DEFAULT 'guest',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `supplier_product_id` VARCHAR(191) NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `supplier_price` DECIMAL(12,2) NOT NULL,
  `price_guest` DECIMAL(12,2) NOT NULL,
  `price_silver` DECIMAL(12,2) NOT NULL,
  `price_gold` DECIMAL(12,2) NOT NULL,
  `price_platinum` DECIMAL(12,2) NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `supplier_product_id_unique` (`supplier_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `product_id` INT DEFAULT NULL,
  `status` ENUM('Success','Pending','Refund','Paid','Waiting for Approval') NOT NULL DEFAULT 'Pending',
  `payment_method` VARCHAR(100) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `total_amount` DECIMAL(14,2) NOT NULL,
  `cost_amount` DECIMAL(14,2) NOT NULL,
  `admin_fee` DECIMAL(12,2) NOT NULL,
  `profit` DECIMAL(14,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `admin_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
