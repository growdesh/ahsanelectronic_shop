<?php
/**
 * সাইট সেটিংস পেজ (Site Settings Page)
 * WhatsApp নম্বর, সাইটের শিরোনাম ইত্যাদি পরিবর্তন করা যায়
 */
session_start();

// লগইন যাচাই
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../db.php';

$error = '';
$success = false;

// ফর্ম সাবমিট হলে সেটিংস আপডেট করা
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_update = [
        'site_title' => trim($_POST['site_title'] ?? ''),
        'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
        'site_description' => trim($_POST['site_description'] ?? ''),
        'site_address' => trim($_POST['site_address'] ?? ''),
        'site_email' => trim($_POST['site_email'] ?? ''),
    ];

    // ভ্যালিডেশন
    if (empty($settings_to_update['site_title'])) {
        $error = 'সাইটের শিরোনাম দিন।';
    } elseif (empty($settings_to_update['whatsapp_number'])) {
        $error = 'WhatsApp নম্বর দিন।';
    } else {
        // পাসওয়ার্ড পরিবর্তন
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $error = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।';
            } elseif ($new_password !== $confirm_password) {
                $error = 'পাসওয়ার্ড মিলছে না!';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $pwd_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $pwd_stmt->bind_param("si", $hashed, $_SESSION['admin_id']);
                $pwd_stmt->execute();
            }
        }

        if (empty($error)) {
            // সেটিংস আপডেট করা
            foreach ($settings_to_update as $key => $value) {
                $stmt = $conn->prepare("
                    INSERT INTO settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->bind_param("sss", $key, $value, $value);
                $stmt->execute();
            }

            header("Location: dashboard.php?msg=settings_updated");
            exit;
        }
    }
}

// বর্তমান সেটিংস লোড
$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'আহসান ইলেকট্রনিক শপ';
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সেটিংস - <?php echo htmlspecialchars($site_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Noto Sans Bengali', 'Kalpurush', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- অ্যাডমিন নেভিগেশন -->
    <nav class="bg-gray-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-6">
                    <span class="text-xl font-bold">⚙️ অ্যাডমিন প্যানেল</span>
                    <a href="dashboard.php" class="hover:text-green-400 transition text-sm">📊 ড্যাশবোর্ড</a>
                    <a href="add-product.php" class="hover:text-green-400 transition text-sm">➕ পণ্য যোগ</a>
                    <a href="add-category.php" class="hover:text-green-400 transition text-sm">📁 ক্যাটাগরি যোগ</a>
                    <a href="settings.php" class="hover:text-green-400 transition text-sm text-green-400">⚙️ সেটিংস</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" target="_blank" class="text-sm hover:text-green-400 transition">🌐 সাইট দেখুন</a>
                    <a href="login.php?logout=1" class="bg-red-500 px-3 py-1 rounded text-sm hover:bg-red-600 transition">🚪 লগআউট</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">⚙️ সাইট সেটিংস</h1>

            <!-- এরর মেসেজ -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- সাইটের শিরোনাম -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">🏪 সাইটের শিরোনাম *</label>
                    <input type="text" name="site_title" 
                           value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="সাইটের শিরোনাম" required>
                </div>

                <!-- WhatsApp নম্বর -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">📱 WhatsApp নম্বর *</label>
                    <input type="text" name="whatsapp_number" 
                           value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="+8801XXXXXXXXX" required>
                    <p class="text-sm text-gray-500 mt-1">দেশের কোড সহ লিখুন, যেমন: +8801768870308</p>
                </div>

                <!-- সাইটের বিবরণ -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">📝 সাইটের বিবরণ</label>
                    <textarea name="site_description" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="সাইটের সংক্ষিপ্ত বিবরণ"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                </div>

                <!-- ঠিকানা -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">📍 ঠিকানা</label>
                    <input type="text" name="site_address" 
                           value="<?php echo htmlspecialchars($settings['site_address'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="দোকানের ঠিকানা">
                </div>

                <!-- ইমেইল -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">📧 ইমেইল</label>
                    <input type="email" name="site_email" 
                           value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="ইমেইল ঠিকানা">
                </div>

                <!-- বিভাজক -->
                <hr class="my-6">

                <h2 class="text-xl font-bold text-gray-800 mb-4">🔐 পাসওয়ার্ড পরিবর্তন</h2>
                <p class="text-sm text-gray-500 mb-4">পাসওয়ার্ড পরিবর্তন করতে না চাইলে এই ফিল্ডগুলো খালি রাখুন।</p>

                <!-- নতুন পাসওয়ার্ড -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">🔑 নতুন পাসওয়ার্ড</label>
                    <input type="password" name="new_password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="নতুন পাসওয়ার্ড (কমপক্ষে ৬ অক্ষর)">
                </div>

                <!-- পাসওয়ার্ড নিশ্চিত -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">🔑 পাসওয়ার্ড নিশ্চিত করুন</label>
                    <input type="password" name="confirm_password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="পাসওয়ার্ড আবার লিখুন">
                </div>

                <!-- বাটন -->
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow">
                        💾 সেটিংস সংরক্ষণ করুন
                    </button>
                    <a href="dashboard.php" 
                       class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition text-center">
                        ← ফিরে যান
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>
