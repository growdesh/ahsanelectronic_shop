<?php
/**
 * ইনস্টলেশন স্ক্রিপ্ট (Installation Script)
 * এই ফাইলটি প্রথমবার চালালে ডাটাবেস এবং টেবিল তৈরি হবে।
 * ইনস্টলেশন শেষে এই ফাইলটি মুছে ফেলুন।
 * 
 * ব্যবহার: XAMPP চালু করে ব্রাউজারে যান: http://localhost/ahsanelectronic_shop/install.php
 */

// ডাটাবেস কনফিগারেশন
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'ahsanelectronic_shop';

$messages = [];
$errors = [];

try {
    // MySQL সংযোগ (ডাটাবেস ছাড়া)
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        die("MySQL সংযোগ ব্যর্থ: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // ডাটাবেস তৈরি
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "ডাটাবেস '$dbname' তৈরি হয়েছে।";

    // ডাটাবেস নির্বাচন
    $conn->select_db($dbname);

    // ক্যাটাগরি টেবিল
    $conn->query("
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "categories টেবিল তৈরি হয়েছে।";

    // প্রোডাক্ট টেবিল
    $conn->query("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "products টেবিল তৈরি হয়েছে।";

    // সেটিংস টেবিল
    $conn->query("
        CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "settings টেবিল তৈরি হয়েছে।";

    // অ্যাডমিন টেবিল
    $conn->query("
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = "admins টেবিল তৈরি হয়েছে।";

    // ডিফল্ট অ্যাডমিন ইউজার
    $check = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
    if ($check->num_rows === 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        $username = 'admin';
        $stmt->execute();
        $messages[] = "ডিফল্ট অ্যাডমিন তৈরি হয়েছে (admin / admin123)।";
    } else {
        $messages[] = "অ্যাডমিন ইউজার আগে থেকেই আছে।";
    }

    // ডিফল্ট সেটিংস
    $default_settings = [
        'site_title' => 'আহসান ইলেকট্রনিক শপ',
        'whatsapp_number' => '+8801768870308',
        'site_description' => 'সেরা মানের ইলেকট্রনিক পণ্যের বিশ্বস্ত ঠিকানা',
        'site_address' => 'ঢাকা, বাংলাদেশ',
        'site_email' => 'info@ahsanelectronic.com',
    ];

    foreach ($default_settings as $key => $value) {
        $check = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");
        if ($check->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
    }
    $messages[] = "ডিফল্ট সেটিংস যোগ হয়েছে।";

    // ডিফল্ট ক্যাটাগরি
    $check = $conn->query("SELECT id FROM categories LIMIT 1");
    if ($check->num_rows === 0) {
        $cats = ['মোবাইল ফোন', 'ল্যাপটপ', 'টেলিভিশন', 'হেডফোন ও ইয়ারফোন', 'চার্জার ও ক্যাবল'];
        foreach ($cats as $cat) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $cat);
            $stmt->execute();
        }
        $messages[] = "ডিফল্ট ক্যাটাগরি যোগ হয়েছে।";
    }

    $conn->close();

} catch (Exception $e) {
    $errors[] = "ত্রুটি: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইনস্টলেশন - আহসান ইলেকট্রনিক শপ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-6">
                <div class="text-5xl mb-4">🛠️</div>
                <h1 class="text-2xl font-bold text-gray-800">ইনস্টলেশন সম্পন্ন!</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-red-700 mb-2">ত্রুটি সমূহ:</h3>
                    <?php foreach ($errors as $err): ?>
                        <p class="text-red-600">- <?php echo htmlspecialchars($err); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($messages)): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-green-700 mb-2">সফল:</h3>
                    <?php foreach ($messages as $msg): ?>
                        <p class="text-green-600">- <?php echo htmlspecialchars($msg); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-700 font-semibold">⚠️ নিরাপত্তার জন্য এই ফাইলটি মুছে ফেলুন!</p>
                <p class="text-yellow-600 text-sm mt-1">install.php ফাইলটি ডিলিট করুন।</p>
            </div>

            <div class="space-y-3">
                <a href="index.php" class="block w-full bg-green-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    🌐 ওয়েবসাইট দেখুন
                </a>
                <a href="admin/login.php" class="block w-full bg-gray-800 text-white text-center py-3 rounded-lg font-semibold hover:bg-gray-900 transition">
                    🔐 অ্যাডমিন প্যানেল
                </a>
            </div>

            <div class="mt-6 text-sm text-gray-500 text-center">
                <p>ডিফল্ট অ্যাডমিন: <strong>admin</strong> / <strong>admin123</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
