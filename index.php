<?php
/**
 * Homepage - Category listing with icons and latest products
 * Responsive design with Tailwind CSS
 */
require_once 'db.php';

$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'Ahsan Electronic Shop';
$whatsapp = $settings['whatsapp_number'] ?? '';
$primary_color = $settings['primary_color'] ?? '#16a34a';

// Load categories with icons
$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name ASC");

// Load latest 12 products
$latest_products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 12
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['site_description'] ?? 'Best electronic products at affordable prices'); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Header with Mobile Menu -->
    <header class="bg-green-600 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <?php if (!empty($settings['site_logo']) && file_exists('uploads/' . $settings['site_logo'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Logo" class="w-10 h-10 object-contain rounded">
                    <?php endif; ?>
                    <a href="index.php" class="text-xl md:text-2xl font-bold"><?php echo htmlspecialchars($site_title); ?></a>
                </div>
                <div class="flex items-center space-x-4">
                    <nav class="hidden md:flex space-x-6">
                        <a href="index.php" class="hover:text-green-200 transition font-medium">Home</a>
                        <?php
                        $nav_cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 5");
                        while ($nc = $nav_cats->fetch_assoc()):
                        ?>
                            <a href="category.php?id=<?php echo (int)$nc['id']; ?>" class="hover:text-green-200 transition"><?php echo htmlspecialchars($nc['name']); ?></a>
                        <?php endwhile; ?>
                    </nav>
                    <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="md:hidden p-1">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden mt-4 pb-2 border-t border-green-500 pt-3 space-y-2">
                <a href="index.php" class="block py-2 hover:text-green-200 font-medium">Home</a>
                <?php
                $mob_cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
                while ($mc = $mob_cats->fetch_assoc()):
                ?>
                    <a href="category.php?id=<?php echo (int)$mc['id']; ?>" class="block py-2 hover:text-green-200"><?php echo htmlspecialchars($mc['name']); ?></a>
                <?php endwhile; ?>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-green-600 to-green-800 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h1 class="text-3xl md:text-5xl font-bold mb-4"><?php echo htmlspecialchars($site_title); ?></h1>
                <p class="text-lg md:text-xl text-green-100 mb-6"><?php echo htmlspecialchars($settings['site_description'] ?? 'Best electronic products at affordable prices'); ?></p>
                <?php if (!empty($whatsapp)): ?>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>" target="_blank"
                       class="inline-block bg-white text-green-700 px-8 py-3 rounded-full font-bold hover:bg-green-50 transition shadow-lg">
                        Contact on WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Categories Section -->
        <?php if ($categories && $categories->num_rows > 0): ?>
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8 text-center">Browse Categories</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <a href="category.php?id=<?php echo (int)$cat['id']; ?>" class="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-xl hover:-translate-y-1 transition transform">
                            <?php if (!empty($cat['icon']) && file_exists('uploads/' . $cat['icon'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($cat['icon']); ?>" alt="" class="w-16 h-16 object-cover mx-auto mb-3 rounded-lg">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-green-100 rounded-lg mx-auto mb-3 flex items-center justify-center text-green-600 text-2xl font-bold">
                                    <?php echo strtoupper(substr($cat['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <h3 class="font-semibold text-gray-800 text-sm"><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p class="text-xs text-gray-500 mt-1"><?php echo (int)$cat['product_count']; ?> products</p>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Latest Products -->
        <section class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8 text-center">Latest Products</h2>
                <?php if ($latest_products && $latest_products->num_rows > 0): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                        <?php while ($product = $latest_products->fetch_assoc()): ?>
                            <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="bg-gray-50 rounded-xl shadow-md overflow-hidden hover:shadow-xl hover:-translate-y-1 transition transform group">
                                <div class="aspect-square bg-gray-200 overflow-hidden">
                                    <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">No Image</div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <?php if (!empty($product['category_name'])): ?>
                                        <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                    <?php endif; ?>
                                    <p class="text-green-600 font-bold">&#x09F3; <?php echo number_format($product['price'], 0); ?></p>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">No products available yet.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer with Social Links -->
    <footer class="bg-gray-800 text-gray-300 py-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white font-bold text-lg mb-3"><?php echo htmlspecialchars($site_title); ?></h3>
                    <p class="text-sm"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></p>
                    <?php if (!empty($settings['address'])): ?>
                        <p class="text-sm mt-2">Address: <?php echo htmlspecialchars($settings['address']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($settings['contact_email'])): ?>
                        <p class="text-sm mt-1">Email: <?php echo htmlspecialchars($settings['contact_email']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg mb-3">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.php" class="hover:text-white transition">Home</a></li>
                        <?php
                        $footer_cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 5");
                        while ($fc = $footer_cats->fetch_assoc()):
                        ?>
                            <li><a href="category.php?id=<?php echo (int)$fc['id']; ?>" class="hover:text-white transition"><?php echo htmlspecialchars($fc['name']); ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg mb-3">Connect With Us</h3>
                    <div class="flex space-x-4 mb-4">
                        <?php if (!empty($settings['facebook_url'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['facebook_url']); ?>" target="_blank" class="bg-blue-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-blue-700 transition" title="Facebook">FB</a>
                        <?php endif; ?>
                        <?php if (!empty($settings['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['instagram_url']); ?>" target="_blank" class="bg-pink-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-pink-700 transition" title="Instagram">IG</a>
                        <?php endif; ?>
                        <?php if (!empty($settings['youtube_url'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['youtube_url']); ?>" target="_blank" class="bg-red-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-red-700 transition" title="YouTube">YT</a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($whatsapp)): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>" target="_blank"
                           class="inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                            WhatsApp Order
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_title); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
