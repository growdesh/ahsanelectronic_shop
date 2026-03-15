<?php
/**
 * Database Connection File
 * Secure mysqli connection with prepared statements support
 * Configured for XAMPP (localhost)
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default empty for XAMPP
define('DB_NAME', 'ahsanelectronic_shop');

// Disable error display in production
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Establish database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Sorry, a server error occurred. Please try again later.");
    }

    // Set UTF-8 for multi-language support
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("Sorry, a server error occurred.");
}

/**
 * Load all site settings from the settings table
 * Returns associative array of setting_key => setting_value
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
 * Sanitize user input to prevent XSS attacks
 */
function sanitize($conn, $input) {
    return htmlspecialchars($conn->real_escape_string(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Track product page view and log analytics data
 */
function trackProductView($conn, $product_id) {
    // Increment the view counter
    $stmt = $conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    // Log to analytics table
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    $stmt = $conn->prepare("INSERT INTO analytics (product_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $product_id, $ip, $ua);
    $stmt->execute();
    $stmt->close();
}

/**
 * Check if admin login is rate-limited
 * Returns true if too many failed attempts (5 attempts, 15 min lockout)
 */
function isLoginRateLimited($conn, $username) {
    $stmt = $conn->prepare("SELECT login_attempts, last_attempt FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if (!$admin) {
        return false;
    }

    if ($admin['login_attempts'] >= 5 && $admin['last_attempt']) {
        $last = strtotime($admin['last_attempt']);
        if (time() - $last < 900) {
            return true;
        }
        // Reset attempts after lockout period
        $reset_stmt = $conn->prepare("UPDATE admins SET login_attempts = 0 WHERE username = ?");
        $reset_stmt->bind_param("s", $username);
        $reset_stmt->execute();
        $reset_stmt->close();
    }

    return false;
}

/**
 * Record a failed login attempt
 */
function recordFailedLogin($conn, $username) {
    $stmt = $conn->prepare("UPDATE admins SET login_attempts = login_attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

/**
 * Reset login attempts on successful login
 */
function resetLoginAttempts($conn, $username) {
    $stmt = $conn->prepare("UPDATE admins SET login_attempts = 0, last_attempt = NULL WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

/**
 * Handle file upload for images (products, categories, logo)
 * Returns filename on success, array with 'error' key on failure, false if no file
 */
function handleImageUpload($file, $prefix = 'img') {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Only JPG, PNG, GIF, and WebP images are allowed.'];
    }

    if ($file['size'] > $max_size) {
        return ['error' => 'Image size must not exceed 5MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
        return $new_filename;
    }

    return ['error' => 'Failed to upload image.'];
}

/**
 * Delete an uploaded image file
 */
function deleteUploadedImage($filename) {
    if (!empty($filename)) {
        $path = __DIR__ . '/uploads/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
?>
