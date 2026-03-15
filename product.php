<?php
/**
 * পণ্যের বিস্তারিত পেজ (Product Details Page)
 * একটি নির্দিষ্ট পণ্যের সম্পূর্ণ তথ্য দেখায়
 */
require_once 'db.php';

// সাইট সেটিংস লোড
$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'আহসান ইলেকট্রনিক শপ';
$whatsapp = $settings['whatsapp_number'] ?? '+8801768870308';

// পণ্য আইডি যাচাই
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

// পণ্যের তথ্য লোড
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// পণ্য পাওয়া না গেলে হোমপেজে ফেরত পাঠানো
if (!$product) {
    header("Location: index.php");
    exit;
}

// একই ক্যাটাগরির অন্যান্য পণ্য লোড
$related = null;
if (!empty($product['category_id'])) {
    $rel_stmt = $conn->prepare("
        SELECT * FROM products 
        WHERE category_id = ? AND id != ? 
        ORDER BY created_at DESC 
        LIMIT 4
    ");
    $rel_stmt->bind_param("ii", $product['category_id'], $product_id);
    $rel_stmt->execute();
    $related = $rel_stmt->get_result();
}

// WhatsApp মেসেজ তৈরি
$wa_message = urlencode(
    "আসসালামু আলাইকুম,\n" .
    "আমি এই পণ্যটি অর্ডার করতে চাই:\n\n" .
    "পণ্যের নাম: " . $product['name'] . "\n" .
    "মূল্য: ৳" . number_format($product['price'], 0) . "\n\n" .
    "অনুগ্রহ করে আমাকে ডেলিভারি সম্পর্কে জানান।"
);
$wa_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp) . "?text=" . $wa_message;
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - <?php echo htmlspecialchars($site_title); ?></title>
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
                    <a href="<?php echo $wa_link; ?>" 
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
            <?php if (!empty($product['category_name'])): ?>
                <span class="mx-2">»</span>
                <a href="category.php?id=<?php echo (int)$product['category_id']; ?>" class="hover:text-green-600">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            <?php endif; ?>
            <span class="mx-2">»</span>
            <span class="text-gray-700"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
    </div>

    <!-- পণ্যের বিস্তারিত -->
    <section class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="md:flex">
                <!-- পণ্যের ছবি -->
                <div class="md:w-1/2">
                    <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="w-full h-96 object-cover">
                    <?php else: ?>
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <span class="text-8xl">📦</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- পণ্যের তথ্য -->
                <div class="md:w-1/2 p-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-4">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>

                    <?php if (!empty($product['category_name'])): ?>
                        <p class="text-gray-500 mb-4">
                            📁 ক্যাটাগরি: 
                            <a href="category.php?id=<?php echo (int)$product['category_id']; ?>" 
                               class="text-green-600 hover:underline">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <div class="text-4xl font-bold text-green-600 mb-6">
                        ৳ <?php echo number_format($product['price'], 0); ?>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                        <div class="text-gray-600 mb-8 leading-relaxed">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">📝 বিবরণ:</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- WhatsApp অর্ডার বাটন -->
                    <a href="<?php echo $wa_link; ?>" 
                       target="_blank"
                       class="inline-block w-full text-center bg-green-500 text-white px-8 py-4 rounded-xl text-xl font-bold hover:bg-green-600 transition shadow-lg">
                        📱 WhatsApp এ অর্ডার করুন
                    </a>

                    <p class="text-gray-500 text-sm mt-4 text-center">
                        WhatsApp নম্বর: <?php echo htmlspecialchars($whatsapp); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- সম্পর্কিত পণ্য -->
    <?php if ($related && $related->num_rows > 0): ?>
    <section class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">🔗 সম্পর্কিত পণ্য</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <?php while ($rel = $related->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                    <a href="product.php?id=<?php echo (int)$rel['id']; ?>">
                        <?php if (!empty($rel['image']) && file_exists('uploads/' . $rel['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($rel['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($rel['name']); ?>"
                                 class="w-full h-40 object-cover">
                        <?php else: ?>
                            <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                                <span class="text-4xl">📦</span>
                            </div>
                        <?php endif; ?>
                    </a>
                    <div class="p-4">
                        <a href="product.php?id=<?php echo (int)$rel['id']; ?>" 
                           class="font-semibold text-gray-800 hover:text-green-600">
                            <?php echo htmlspecialchars($rel['name']); ?>
                        </a>
                        <p class="text-lg font-bold text-green-600 mt-1">
                            ৳ <?php echo number_format($rel['price'], 0); ?>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

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
