<?php
/**
 * Admin Settings Page
 * Manage site title, logo, WhatsApp, theme colors, social links, admin password
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../db.php';

$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'Ahsan Electronic Shop';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_settings') {
        $fields = [
            'site_title' => trim($_POST['site_title'] ?? ''),
            'site_description' => trim($_POST['site_description'] ?? ''),
            'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'primary_color' => trim($_POST['primary_color'] ?? '#16a34a'),
            'secondary_color' => trim($_POST['secondary_color'] ?? '#15803d'),
            'facebook_url' => trim($_POST['facebook_url'] ?? ''),
            'instagram_url' => trim($_POST['instagram_url'] ?? ''),
            'youtube_url' => trim($_POST['youtube_url'] ?? ''),
        ];

        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleImageUpload($_FILES['site_logo'], 'logo');
            if (is_array($upload_result) && isset($upload_result['error'])) {
                $error = $upload_result['error'];
            } elseif ($upload_result) {
                if (!empty($settings['site_logo'])) {
                    deleteUploadedImage($settings['site_logo']);
                }
                $fields['site_logo'] = $upload_result;
            }
        }

        if (empty($error)) {
            foreach ($fields as $key => $value) {
                $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->bind_param("ss", $value, $key);
                $stmt->execute();
                $stmt->close();
            }
            header("Location: settings.php?msg=settings_updated");
            exit;
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            $error = 'Please fill in all password fields.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $admin_id = $_SESSION['admin_id'];
            $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            $stmt->close();

            if ($admin && password_verify($current_password, $admin['password'])) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed, $admin_id);
                $stmt->execute();
                $stmt->close();
                $success = 'Password changed successfully!';
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }

    // Reload settings after update
    $settings = getSettings($conn);
}

$msg = $_GET['msg'] ?? '';
if ($msg === 'settings_updated') {
    $success = 'Settings updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo htmlspecialchars($site_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-gray-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <span class="text-xl font-bold">Admin Panel</span>
                    <button onclick="document.getElementById('adminMenu').classList.toggle('hidden')" class="md:hidden ml-4 p-1 rounded hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="dashboard.php" class="hover:text-green-400 transition text-sm">Dashboard</a>
                    <a href="add-product.php" class="hover:text-green-400 transition text-sm">Add Product</a>
                    <a href="add-category.php" class="hover:text-green-400 transition text-sm">Add Category</a>
                    <a href="settings.php" class="text-green-400 text-sm font-semibold">Settings</a>
                    <a href="login.php?logout=1" class="bg-red-500 px-3 py-1 rounded text-sm hover:bg-red-600 transition">Logout</a>
                </div>
            </div>
            <div id="adminMenu" class="hidden md:hidden mt-3 pb-2 border-t border-gray-700 pt-3 space-y-2">
                <a href="dashboard.php" class="block hover:text-green-400 text-sm py-1">Dashboard</a>
                <a href="add-product.php" class="block hover:text-green-400 text-sm py-1">Add Product</a>
                <a href="add-category.php" class="block hover:text-green-400 text-sm py-1">Add Category</a>
                <a href="settings.php" class="block text-green-400 text-sm font-semibold py-1">Settings</a>
                <a href="login.php?logout=1" class="block text-red-400 text-sm py-1">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">

        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-lg mb-6"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg mb-6"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Site Settings -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Site Settings</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_settings">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Site Title</label>
                        <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="+880XXXXXXXXXX">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Site Description</label>
                    <textarea name="site_description" rows="2"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Contact Email</label>
                        <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Site Logo</label>
                    <?php if (!empty($settings['site_logo']) && file_exists('../uploads/' . $settings['site_logo'])): ?>
                        <div class="mb-3">
                            <img src="../uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>" class="w-24 h-24 object-contain rounded-lg border">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="site_logo" accept="image/*"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <h3 class="text-lg font-bold text-gray-800 mb-3 mt-6">Theme Colors</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Primary Color</label>
                        <input type="color" name="primary_color" value="<?php echo htmlspecialchars($settings['primary_color'] ?? '#16a34a'); ?>"
                               class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Secondary Color</label>
                        <input type="color" name="secondary_color" value="<?php echo htmlspecialchars($settings['secondary_color'] ?? '#15803d'); ?>"
                               class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer">
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 mb-3 mt-6">Social Media Links</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Facebook URL</label>
                        <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="https://facebook.com/...">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Instagram URL</label>
                        <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="https://instagram.com/...">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">YouTube URL</label>
                        <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="https://youtube.com/...">
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow">
                    Save Settings
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Change Admin Password</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Current Password</label>
                    <input type="password" name="current_password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">New Password</label>
                        <input type="password" name="new_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow">
                    Change Password
                </button>
            </form>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>
