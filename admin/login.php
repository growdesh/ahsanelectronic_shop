<?php
/**
 * অ্যাডমিন লগইন পেজ (Admin Login Page)
 * পাসওয়ার্ড দিয়ে অ্যাডমিন প্যানেলে প্রবেশ
 */
session_start();

// লগআউট হ্যান্ডলিং
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// ইতিমধ্যে লগইন থাকলে ড্যাশবোর্ডে পাঠানো
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

require_once '../db.php';

$error = '';

// লগইন ফর্ম সাবমিট হলে
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'ইউজারনেম এবং পাসওয়ার্ড দিন।';
    } else {
        // ডাটাবেস থেকে অ্যাডমিন খোঁজা
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            // লগইন সফল
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'ভুল ইউজারনেম অথবা পাসওয়ার্ড!';
        }
    }
}

// সাইট সেটিংস লোড
$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'আহসান ইলেকট্রনিক শপ';
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন - <?php echo htmlspecialchars($site_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Noto Sans Bengali', 'Kalpurush', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-600 to-green-800 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md mx-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- লোগো -->
            <div class="text-center mb-8">
                <div class="text-5xl mb-4">🔐</div>
                <h1 class="text-2xl font-bold text-gray-800">অ্যাডমিন প্যানেল</h1>
                <p class="text-gray-500 mt-1"><?php echo htmlspecialchars($site_title); ?></p>
            </div>

            <!-- এরর মেসেজ -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- লগইন ফর্ম -->
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2" for="username">
                        👤 ইউজারনেম
                    </label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="ইউজারনেম লিখুন" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2" for="password">
                        🔑 পাসওয়ার্ড
                    </label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="পাসওয়ার্ড লিখুন" required>
                </div>

                <button type="submit" 
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow-lg">
                    🔓 লগইন করুন
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="../index.php" class="text-green-600 hover:underline text-sm">
                    ← ওয়েবসাইটে ফিরে যান
                </a>
            </div>
        </div>

        <p class="text-center text-green-100 text-sm mt-4">
            ডিফল্ট: admin / admin123
        </p>
    </div>

</body>
</html>
<?php $conn->close(); ?>
