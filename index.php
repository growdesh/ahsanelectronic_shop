<?php
/**
 * হোমপেজ (Homepage)
 * সব ক্যাটাগরি এবং সর্বশেষ পণ্য দেখায়
 */
require_once 'db.php';

// সাইট সেটিংস লোড
$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'আহসান ইলেকট্রনিক শপ';
$whatsapp = $settings['whatsapp_number'] ?? '+8801768870308';
$site_desc = $settings['site_description'] ?? '';

// সব ক্যাটাগরি লোড
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// সর্বশেষ ১২টি পণ্য লোড
$latest_products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 12
");
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($site_desc); ?>">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* বাংলা ফন্ট সাপোর্ট */
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

    <!-- হিরো সেকশন -->
    <section class="bg-gradient-to-r from-green-600 to-green-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo htmlspecialchars($site_title); ?></h1>
            <p class="text-xl text-green-100 mb-8"><?php echo htmlspecialchars($site_desc); ?></p>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>?text=<?php echo urlencode('আসসালামু আলাইকুম, আমি একটি পণ্য অর্ডার করতে চাই।'); ?>" 
               target="_blank"
               class="inline-block bg-white text-green-600 px-8 py-3 rounded-full text-lg font-semibold hover:bg-green-100 transition shadow-lg">
                📱 WhatsApp এ অর্ডার করুন
            </a>
        </div>
    </section>

    <!-- ক্যাটাগরি সেকশন -->
    <section class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">📂 ক্যাটাগরি সমূহ</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php if ($categories && $categories->num_rows > 0): ?>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <a href="category.php?id=<?php echo (int)$cat['id']; ?>" 
                       class="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-xl hover:scale-105 transition transform">
                        <div class="text-4xl mb-3">📁</div>
                        <h3 class="text-lg font-semibold text-gray-700">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </h3>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-gray-500 py-8">
                    <p>এখনো কোনো ক্যাটাগরি যোগ করা হয়নি।</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- সর্বশেষ পণ্য সেকশন -->
    <section class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">🆕 সর্বশেষ পণ্য</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if ($latest_products && $latest_products->num_rows > 0): ?>
                <?php while ($product = $latest_products->fetch_assoc()): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                        <!-- পণ্যের ছবি -->
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
                        <!-- পণ্যের তথ্য -->
                        <div class="p-4">
                            <a href="product.php?id=<?php echo (int)$product['id']; ?>" 
                               class="text-lg font-semibold text-gray-800 hover:text-green-600 transition">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                            <?php if (!empty($product['category_name'])): ?>
                                <p class="text-sm text-gray-500 mt-1">
                                    📁 <?php echo htmlspecialchars($product['category_name']); ?>
                                </p>
                            <?php endif; ?>
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
                    <p class="text-xl">এখনো কোনো পণ্য যোগ করা হয়নি।</p>
                    <p class="mt-2">অ্যাডমিন প্যানেল থেকে পণ্য যোগ করুন।</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

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
