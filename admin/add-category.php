<?php
/**
 * ক্যাটাগরি যোগ/সম্পাদনা পেজ (Add/Edit Category Page)
 * নতুন ক্যাটাগরি যোগ বা বিদ্যমান ক্যাটাগরি সম্পাদনা করা হয়
 */
session_start();

// লগইন যাচাই
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../db.php';

$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'আহসান ইলেকট্রনিক শপ';

$error = '';
$editing = false;
$category = ['id' => 0, 'name' => ''];

// সম্পাদনা মোড - ক্যাটাগরির তথ্য লোড
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_cat = $result->fetch_assoc();
    if ($edit_cat) {
        $editing = true;
        $category = $edit_cat;
    }
}

// ফর্ম সাবমিট হলে
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    if (empty($name)) {
        $error = 'ক্যাটাগরির নাম দিন।';
    } else {
        if ($category_id > 0) {
            // আপডেট করা
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $category_id);
            $stmt->execute();
            header("Location: dashboard.php?msg=category_updated");
            exit;
        } else {
            // নতুন ক্যাটাগরি যোগ করা
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            header("Location: dashboard.php?msg=category_added");
            exit;
        }
    }

    // এরর হলে ফর্মে ডেটা রাখা
    $category = ['id' => $category_id, 'name' => $name];
    $editing = ($category_id > 0);
}
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editing ? 'ক্যাটাগরি সম্পাদনা' : 'নতুন ক্যাটাগরি যোগ'; ?> - <?php echo htmlspecialchars($site_title); ?></title>
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
                    <a href="add-category.php" class="hover:text-green-400 transition text-sm text-green-400">📁 ক্যাটাগরি যোগ</a>
                    <a href="settings.php" class="hover:text-green-400 transition text-sm">⚙️ সেটিংস</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" target="_blank" class="text-sm hover:text-green-400 transition">🌐 সাইট দেখুন</a>
                    <a href="login.php?logout=1" class="bg-red-500 px-3 py-1 rounded text-sm hover:bg-red-600 transition">🚪 লগআউট</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                <?php echo $editing ? '✏️ ক্যাটাগরি সম্পাদনা করুন' : '📁 নতুন ক্যাটাগরি যোগ করুন'; ?>
            </h1>

            <!-- এরর মেসেজ -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="category_id" value="<?php echo (int)$category['id']; ?>">

                <!-- ক্যাটাগরির নাম -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">📝 ক্যাটাগরির নাম *</label>
                    <input type="text" name="name" 
                           value="<?php echo htmlspecialchars($category['name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="ক্যাটাগরির নাম লিখুন" required>
                </div>

                <!-- বাটন -->
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow">
                        <?php echo $editing ? '💾 আপডেট করুন' : '➕ ক্যাটাগরি যোগ করুন'; ?>
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
