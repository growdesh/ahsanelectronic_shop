<?php
/**
 * Installation Script
 * Run this file once to set up the database and tables.
 * Delete this file after installation for security.
 * 
 * Usage: Start XAMPP, visit: http://localhost/ahsanelectronic_shop/install.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'ahsanelectronic_shop';

$messages = [];
$errors = [];

try {
    // Connect to MySQL (without database)
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        die("MySQL connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // Create database
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "Database '$dbname' created.";

    // Select database
    $conn->select_db($dbname);

    // Categories table (with icon column)
    $conn->query("
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `icon` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "categories table created.";

    // Products table (with views column)
    $conn->query("
        CREATE TABLE IF NOT EXISTS `products` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `image` VARCHAR(255) DEFAULT NULL,
            `category_id` INT DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `views` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "products table created.";

    // Settings table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "settings table created.";

    // Admins table (with login attempt tracking)
    $conn->query("
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
            `last_attempt` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "admins table created.";

    // Analytics table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `analytics` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` VARCHAR(500) DEFAULT NULL,
            `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "analytics table created.";

    // Default admin user
    $check = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
    if ($check->num_rows === 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $username = 'admin';
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();
        $messages[] = "Default admin created (admin / admin123).";
    } else {
        $messages[] = "Admin user already exists.";
    }

    // Default settings
    $default_settings = [
        'site_title' => 'Ahsan Electronic Shop',
        'whatsapp_number' => '+8801768870308',
        'site_description' => 'Best Quality Electronic Products - Your Trusted Address',
        'site_address' => 'Dhaka, Bangladesh',
        'site_email' => 'info@ahsanelectronic.com',
        'site_logo' => '',
        'theme_primary_color' => '#16a34a',
        'theme_secondary_color' => '#166534',
        'social_facebook' => '',
        'social_instagram' => '',
        'social_youtube' => '',
    ];

    foreach ($default_settings as $key => $value) {
        $check = $conn->query("SELECT id FROM settings WHERE setting_key = '" . $conn->real_escape_string($key) . "'");
        if ($check->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
    }
    $messages[] = "Default settings added.";

    // Default categories
    $check = $conn->query("SELECT id FROM categories LIMIT 1");
    if ($check->num_rows === 0) {
        $cats = ['Mobile Phones', 'Laptops', 'Televisions', 'Headphones & Earphones', 'Chargers & Cables'];
        foreach ($cats as $cat) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $cat);
            $stmt->execute();
        }
        $messages[] = "Default categories added.";
    }

    // Create uploads directory if it doesn't exist
    $uploads_dir = __DIR__ . '/uploads';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
        $messages[] = "uploads/ directory created.";
    }

    $conn->close();

} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Ahsan Electronic Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-6">
                <div class="text-5xl mb-4">&#x1F6E0;&#xFE0F;</div>
                <h1 class="text-2xl font-bold text-gray-800">Installation Complete!</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-red-700 mb-2">Errors:</h3>
                    <?php foreach ($errors as $err): ?>
                        <p class="text-red-600">- <?php echo htmlspecialchars($err); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($messages)): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-green-700 mb-2">Success:</h3>
                    <?php foreach ($messages as $msg): ?>
                        <p class="text-green-600">- <?php echo htmlspecialchars($msg); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-700 font-semibold">Warning: Delete this file for security!</p>
                <p class="text-yellow-600 text-sm mt-1">Remove install.php after setup is complete.</p>
            </div>

            <div class="space-y-3">
                <a href="index.php" class="block w-full bg-green-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    View Website
                </a>
                <a href="admin/login.php" class="block w-full bg-gray-800 text-white text-center py-3 rounded-lg font-semibold hover:bg-gray-900 transition">
                    Admin Panel
                </a>
            </div>

            <div class="mt-6 text-sm text-gray-500 text-center">
                <p>Default Admin: <strong>admin</strong> / <strong>admin123</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
