-- =============================================
-- Ahsan Electronic Shop - Advanced Database
-- PHP + MySQL Catalog with Admin Panel
-- XAMPP MySQL Import Ready
-- =============================================

-- Create database
CREATE DATABASE IF NOT EXISTS `ahsanelectronic_shop`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `ahsanelectronic_shop`;

-- =============================================
-- Categories Table
-- Stores product categories with optional icons
-- =============================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(255) DEFAULT NULL COMMENT 'Category icon image filename',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Products Table
-- Stores all product information with view tracking
-- =============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image` VARCHAR(255) DEFAULT NULL,
  `category_id` INT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Page view counter',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_category` (`category_id`),
  INDEX `idx_views` (`views`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Settings Table (Key-Value Store)
-- Stores all site configuration options
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Admin Users Table
-- Stores admin credentials with login attempt tracking
-- =============================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed password',
  `login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_attempt` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Analytics Table
-- Tracks product page views and user activity
-- =============================================
CREATE TABLE IF NOT EXISTS `analytics` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IPv4/IPv6 address',
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product` (`product_id`),
  INDEX `idx_viewed` (`viewed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Default Data
-- =============================================

-- Default admin user (username: admin, password: admin123)
-- IMPORTANT: Change password in production!
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Default settings (includes theme, social, logo)
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title', 'Ahsan Electronic Shop'),
('whatsapp_number', '+8801768870308'),
('site_description', 'Best Quality Electronic Products - Your Trusted Address'),
('site_address', 'Dhaka, Bangladesh'),
('site_email', 'info@ahsanelectronic.com'),
('site_logo', ''),
('theme_primary_color', '#16a34a'),
('theme_secondary_color', '#166534'),
('social_facebook', ''),
('social_instagram', ''),
('social_youtube', '');

-- Default categories
INSERT INTO `categories` (`name`) VALUES
('Mobile Phones'),
('Laptops'),
('Televisions'),
('Headphones & Earphones'),
('Chargers & Cables');
