<?php
/**
 * ক্যাটাগরি পেজ (Category Page)
 * একটি নির্দিষ্ট ক্যাটাগরির সব পণ্য দেখায়
 */
require_once 'db.php';

// সাইট সেটিংস লোড
$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'আহসান ইলেকট্রনিক শপ';
$whatsapp = $settings['whatsapp_number'] ?? '+8801768870308';

// ক্যাটাগরি আইডি যাচাই
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($category_id <= 0) {
    header("Location: index.php");
    exit;
}

// ক্যাটাগরির তথ্য লোড
$cat_stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$category = $cat_result->fetch_assoc();

// ক্যাটাগরি পাওয়া না গেলে হোমপেজে ফেরত
if (!$category) {
    header("Location: index.php");
    exit;
}

// এই ক্যাটাগরির সব পণ্য লোড
$prod_stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC");
$prod_stmt->bind_param("i", $category_id);
$prod_stmt->execute();
$products = $prod_stmt->get_result();

// সব ক্যাটাগরি (সাইডবারের জন্য)
$all_categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - <?php echo htmlspecialchars($site_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Noto Sans Bengali', 'Kalpurush', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- নেভিগেশন বার -->
    <nav class="bg-green-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="index.php" class="text-2xl font-bold">
                    🏪 <?php echo htmlspecialchars($site_title); ?>
                </a>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="hover:text-green-200 transition">🏠 হোম</a>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>" 
                       target="_blank"
                       class="bg-white text-green-600 px-4 py-2 rounded-lg font-semibold hover:bg-green-100 transition">
                        📱 WhatsApp অর্ডার
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ব্রেডক্রাম্ব -->
    <div class="max-w-7xl mx-auto px-4 py-4">
        <nav class="text-sm text-gray-500">
            <a href="index.php" class="hover:text-green-600">হোম</a>
            <span class="mx-2">»</span>
            <span class="text-gray-700"><?php echo htmlspecialchars($category['name']); ?></span>
        </nav>
    </div>

    <!-- মেইন কন্টেন্ট -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="md:flex gap-8">

            <!-- সাইডবার - ক্যাটাগরি লিস্ট -->
            <aside class="md:w-1/4 mb-8 md:mb-0">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">📂 সব ক্যাটাগরি</h3>
                    <ul class="space-y-2">
                        <?php while ($cat = $all_categories->fetch_assoc()): ?>
                            <li>
                                <a href="category.php?id=<?php echo (int)$cat['id']; ?>" 
                                   class="block px-4 py-2 rounded-lg transition
                                   <?php echo ($cat['id'] == $category_id) 
                                       ? 'bg-green-600 text-white font-semibold' 
                                       : 'text-gray-700 hover:bg-green-50 hover:text-green-600'; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </aside>

            <!-- পণ্যের তালিকা -->
            <main class="md:w-3/4">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">
                    📁 <?php echo htmlspecialchars($category['name']); ?>
                </h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if ($products && $products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                                <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                                    <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="w-full h-48 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <span class="text-6xl">📦</span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div class="p-4">
                                    <a href="product.php?id=<?php echo (int)$product['id']; ?>" 
                                       class="text-lg font-semibold text-gray-800 hover:text-green-600 transition">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                    <p class="text-xl font-bold text-green-600 mt-2">
                                        ৳ <?php echo number_format($product['price'], 0); ?>
                                    </p>
                                    <div class="mt-4 flex space-x-2">
                                        <a href="product.php?id=<?php echo (int)$product['id']; ?>" 
                                           class="flex-1 bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition text-sm">
                                            বিস্তারিত দেখুন
                                        </a>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>?text=<?php echo urlencode('আমি এই পণ্যটি অর্ডার করতে চাই: ' . $product['name'] . ' - মূল্য: ৳' . number_format($product['price'], 0)); ?>" 
                                           target="_blank"
                                           class="bg-green-500 text-white px-3 py-2 rounded-lg hover:bg-green-600 transition text-sm"
                                           title="WhatsApp এ অর্ডার করুন">
                                            📱
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center text-gray-500 py-12">
                            <div class="text-6xl mb-4">📦</div>
                            <p class="text-xl">এই ক্যাটাগরিতে এখনো কোনো পণ্য নেই।</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- ফুটার -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($site_title); ?></p>
            <p class="text-gray-400 mb-4"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></p>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>" 
               target="_blank"
               class="inline-block bg-green-500 text-white px-6 py-2 rounded-full hover:bg-green-600 transition">
                📱 WhatsApp: <?php echo htmlspecialchars($whatsapp); ?>
            </a>
            <p class="text-gray-500 mt-6 text-sm">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_title); ?>। সর্বস্বত্ব সংরক্ষিত।
            </p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
