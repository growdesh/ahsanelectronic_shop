-- =============================================
-- আহসান ইলেকট্রনিক শপ - ডাটাবেস স্ক্রিপ্ট
-- Ahsan Electronic Shop - Database Script
-- XAMPP MySQL এ ইম্পোর্ট করুন
-- =============================================

-- ডাটাবেস তৈরি
CREATE DATABASE IF NOT EXISTS `ahsanelectronic_shop`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `ahsanelectronic_shop`;

-- =============================================
-- ক্যাটাগরি টেবিল (Categories Table)
-- =============================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- প্রোডাক্ট টেবিল (Products Table)
-- =============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image` VARCHAR(255) DEFAULT NULL,
  `category_id` INT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- সেটিংস টেবিল (Settings Table)
-- সাইটের বিভিন্ন সেটিংস সংরক্ষণ করা হয়
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- অ্যাডমিন ইউজার টেবিল (Admin Users Table)
-- =============================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ডিফল্ট ডেটা ইনসার্ট (Default Data Insert)
-- =============================================

-- ডিফল্ট অ্যাডমিন ইউজার (ইউজারনেম: admin, পাসওয়ার্ড: admin123)
-- প্রোডাকশনে অবশ্যই পাসওয়ার্ড পরিবর্তন করুন!
-- নোট: install.php ব্যবহার করলে সঠিক bcrypt হ্যাশ স্বয়ংক্রিয়ভাবে তৈরি হবে।
-- ম্যানুয়াল ইম্পোর্টের জন্য নিচের হ্যাশটি 'admin123' এর জন্য:
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ডিফল্ট সেটিংস
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title', 'আহসান ইলেকট্রনিক শপ'),
('whatsapp_number', '+8801768870308'),
('site_description', 'সেরা মানের ইলেকট্রনিক পণ্যের বিশ্বস্ত ঠিকানা'),
('site_address', 'ঢাকা, বাংলাদেশ'),
('site_email', 'info@ahsanelectronic.com');

-- কিছু ডিফল্ট ক্যাটাগরি
INSERT INTO `categories` (`name`) VALUES
('মোবাইল ফোন'),
('ল্যাপটপ'),
('টেলিভিশন'),
('হেডফোন ও ইয়ারফোন'),
('চার্জার ও ক্যাবল');
