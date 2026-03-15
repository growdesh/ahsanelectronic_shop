<?php
/**
 * অ্যাডমিন ড্যাশবোর্ড (Admin Dashboard)
 * পণ্য ও ক্যাটাগরি পরিচালনা করা হয়
 */
session_start();

// লগইন যাচাই
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../db.php';

// সাইট সেটিংস লোড
$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'আহসান ইলেকট্রনিক শপ';

// পণ্য ডিলিট করা
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    $del_id = (int)$_GET['delete_product'];
    // পণ্যের ছবি মুছে ফেলা
    $img_stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $img_stmt->bind_param("i", $del_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    $img_row = $img_result->fetch_assoc();
    if ($img_row && !empty($img_row['image'])) {
        $img_path = '../uploads/' . $img_row['image'];
        if (file_exists($img_path)) {
            unlink($img_path);
        }
    }
    $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del_stmt->bind_param("i", $del_id);
    $del_stmt->execute();
    header("Location: dashboard.php?msg=product_deleted");
    exit;
}

// ক্যাটাগরি ডিলিট করা
if (isset($_GET['delete_category']) && is_numeric($_GET['delete_category'])) {
    $del_id = (int)$_GET['delete_category'];
    $del_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $del_stmt->bind_param("i", $del_id);
    $del_stmt->execute();
    header("Location: dashboard.php?msg=category_deleted");
    exit;
}

// সারাংশ ডেটা
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_categories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

// সব পণ্য লোড
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");

// সব ক্যাটাগরি লোড
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// সফলতার মেসেজ
$msg = $_GET['msg'] ?? '';
$success_messages = [
    'product_added' => 'পণ্য সফলভাবে যোগ করা হয়েছে!',
    'product_updated' => 'পণ্য সফলভাবে আপডেট করা হয়েছে!',
    'product_deleted' => 'পণ্য সফলভাবে মুছে ফেলা হয়েছে!',
    'category_added' => 'ক্যাটাগরি সফলভাবে যোগ করা হয়েছে!',
    'category_updated' => 'ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে!',
    'category_deleted' => 'ক্যাটাগরি সফলভাবে মুছে ফেলা হয়েছে!',
    'settings_updated' => 'সেটিংস সফলভাবে আপডেট করা হয়েছে!',
];
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড - <?php echo htmlspecialchars($site_title); ?></title>
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
                    <a href="settings.php" class="hover:text-green-400 transition text-sm">⚙️ সেটিংস</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" target="_blank" class="text-sm hover:text-green-400 transition">🌐 সাইট দেখুন</a>
                    <a href="login.php?logout=1" class="bg-red-500 px-3 py-1 rounded text-sm hover:bg-red-600 transition">🚪 লগআউট</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- সফলতার মেসেজ -->
        <?php if (!empty($msg) && isset($success_messages[$msg])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-lg mb-6">
                ✅ <?php echo $success_messages[$msg]; ?>
            </div>
        <?php endif; ?>

        <!-- সারাংশ কার্ড -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">মোট পণ্য</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_products; ?></p>
                    </div>
                    <div class="text-4xl">📦</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">মোট ক্যাটাগরি</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_categories; ?></p>
                    </div>
                    <div class="text-4xl">📂</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">WhatsApp নম্বর</p>
                        <p class="text-lg font-bold text-green-600"><?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?></p>
                    </div>
                    <div class="text-4xl">📱</div>
                </div>
            </div>
        </div>

        <!-- দ্রুত লিংক -->
        <div class="flex flex-wrap gap-4 mb-8">
            <a href="add-product.php" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition shadow">
                ➕ নতুন পণ্য যোগ করুন
            </a>
            <a href="add-category.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition shadow">
                📁 নতুন ক্যাটাগরি যোগ করুন
            </a>
            <a href="settings.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition shadow">
                ⚙️ সেটিংস পরিবর্তন করুন
            </a>
        </div>

        <!-- পণ্যের তালিকা -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h2 class="text-xl font-bold text-gray-800">📦 সব পণ্য</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ছবি</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">পণ্যের নাম</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ক্যাটাগরি</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">মূল্য</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if ($products && $products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <?php if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 class="w-12 h-12 object-cover rounded">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center text-xl">📦</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'কোনো ক্যাটাগরি নেই'); ?>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-green-600">
                                        ৳ <?php echo number_format($product['price'], 0); ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="add-product.php?edit=<?php echo (int)$product['id']; ?>" 
                                           class="inline-block bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                                            ✏️ সম্পাদনা
                                        </a>
                                        <a href="dashboard.php?delete_product=<?php echo (int)$product['id']; ?>" 
                                           onclick="return confirm('আপনি কি নিশ্চিত এই পণ্যটি মুছে ফেলতে চান?');"
                                           class="inline-block bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition ml-1">
                                            🗑️ মুছুন
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    এখনো কোনো পণ্য যোগ করা হয়নি।
                                    <a href="add-product.php" class="text-green-600 hover:underline ml-2">পণ্য যোগ করুন</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ক্যাটাগরি তালিকা -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h2 class="text-xl font-bold text-gray-800">📂 সব ক্যাটাগরি</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">আইডি</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ক্যাটাগরির নাম</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if ($categories && $categories->num_rows > 0): ?>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600"><?php echo (int)$cat['id']; ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="add-category.php?edit=<?php echo (int)$cat['id']; ?>" 
                                           class="inline-block bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                                            ✏️ সম্পাদনা
                                        </a>
                                        <a href="dashboard.php?delete_category=<?php echo (int)$cat['id']; ?>" 
                                           onclick="return confirm('আপনি কি নিশ্চিত এই ক্যাটাগরিটি মুছে ফেলতে চান?');"
                                           class="inline-block bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition ml-1">
                                            🗑️ মুছুন
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                    এখনো কোনো ক্যাটাগরি যোগ করা হয়নি।
                                    <a href="add-category.php" class="text-green-600 hover:underline ml-2">ক্যাটাগরি যোগ করুন</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>
