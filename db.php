<?php
/**
 * ডাটাবেস কানেকশন ফাইল (Database Connection File)
 * এই ফাইলটি MySQL ডাটাবেসের সাথে সংযোগ স্থাপন করে।
 * XAMPP এ ব্যবহারের জন্য তৈরি।
 */

// ডাটাবেস কনফিগারেশন
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP এ ডিফল্ট পাসওয়ার্ড খালি থাকে
define('DB_NAME', 'ahsanelectronic_shop');

// ডাটাবেস সংযোগ স্থাপন
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // সংযোগ যাচাই
    if ($conn->connect_error) {
        error_log("ডাটাবেস সংযোগ ব্যর্থ: " . $conn->connect_error);
        die("দুঃখিত, সার্ভারে সমস্যা হয়েছে। অনুগ্রহ করে পরে আবার চেষ্টা করুন।");
    }

    // বাংলা ভাষার জন্য UTF-8 সেট করা
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("ডাটাবেস ত্রুটি: " . $e->getMessage());
    die("দুঃখিত, সার্ভারে সমস্যা হয়েছে।");
}

// PHP এরর ডিসপ্লে বন্ধ (প্রোডাকশনে নিরাপত্তার জন্য)
ini_set('display_errors', 0);
error_reporting(E_ALL);

/**
 * সাইট সেটিংস লোড করার ফাংশন
 * settings টেবিল থেকে সব সেটিংস নিয়ে আসে
 */
function getSettings($conn) {
    $settings = [];
    $result = $conn->query("SELECT setting_key, setting_value FROM settings");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

/**
 * ইনপুট স্যানিটাইজ করার ফাংশন
 * XSS আক্রমণ প্রতিরোধে ব্যবহৃত
 */
function sanitize($conn, $input) {
    return htmlspecialchars($conn->real_escape_string(trim($input)), ENT_QUOTES, 'UTF-8');
}
?>
