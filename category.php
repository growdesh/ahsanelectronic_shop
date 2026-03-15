<?php
/**
 * Category Page
 * Lists all products in a category with sidebar
 */
require_once 'db.php';

$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'Ahsan Electronic Shop';
$whatsapp = $settings['whatsapp_number'] ?? '';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    header("Location: index.php");
    exit;
}

// Load current category
$cat_stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$current_category = $cat_result->fetch_assoc();
$cat_stmt->close();

if (!$current_category) {
    header("Location: index.php");
    exit;
}

// Load products in this category
$prod_stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC");
$prod_stmt->bind_param("i", $category_id);
$prod_stmt->execute();
$products = $prod_stmt->get_result();
$prod_stmt->close();

// All categories for sidebar
$all_categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($current_category['name']); ?> - <?php echo htmlspecialchars($site_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Header -->
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
                    <a href="index.php" class="hidden md:inline hover:text-green-200 transition">Home</a>
                    <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="md:hidden p-1">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
            <div id="mobileMenu" class="hidden md:hidden mt-4 pb-2 border-t border-green-500 pt-3 space-y-2">
                <a href="index.php" class="block py-2 hover:text-green-200">Home</a>
                <?php
                $mob_cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
                while ($mc = $mob_cats->fetch_assoc()):
                ?>
                    <a href="category.php?id=<?php echo (int)$mc['id']; ?>" class="block py-2 hover:text-green-200 <?php echo ($mc['id'] == $category_id) ? 'text-green-200 font-semibold' : ''; ?>"><?php echo htmlspecialchars($mc['name']); ?></a>
                <?php endwhile; ?>
            </div>
        </div>
    </header>

    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-6">
                <a href="index.php" class="hover:text-green-600">Home</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700"><?php echo htmlspecialchars($current_category['name']); ?></span>
            </nav>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

                <!-- Sidebar - Categories -->
                <aside class="hidden md:block">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                        <h3 class="font-bold text-gray-800 mb-4">Categories</h3>
                        <ul class="space-y-2">
                            <?php while ($sc = $all_categories->fetch_assoc()): ?>
                                <li>
                                    <a href="category.php?id=<?php echo (int)$sc['id']; ?>" 
                                       class="flex items-center justify-between py-2 px-3 rounded-lg transition <?php echo ($sc['id'] == $category_id) ? 'bg-green-100 text-green-700 font-semibold' : 'hover:bg-gray-100 text-gray-600'; ?>">
                                        <span class="flex items-center">
                                            <?php if (!empty($sc['icon']) && file_exists('uploads/' . $sc['icon'])): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($sc['icon']); ?>" class="w-6 h-6 object-cover rounded mr-2">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($sc['name']); ?>
                                        </span>
                                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full"><?php echo (int)$sc['product_count']; ?></span>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="md:col-span-3">
                    <div class="flex items-center mb-6">
                        <?php if (!empty($current_category['icon']) && file_exists('uploads/' . $current_category['icon'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($current_category['icon']); ?>" class="w-12 h-12 object-cover rounded-lg mr-4">
                        <?php endif; ?>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($current_category['name']); ?></h1>
                    </div>

                    <?php if ($products && $products->num_rows > 0): ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                            <?php while ($product = $products->fetch_assoc()):
                                $wa_msg = urlencode("Hi! I'm interested in: " . $product['name'] . " (Price: " . number_format($product['price'], 0) . " BDT)");
                            ?>
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                                    <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                                        <div class="aspect-square bg-gray-200 overflow-hidden">
                                            <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="" class="w-full h-full object-cover hover:scale-110 transition duration-300">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">No Image</div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                    <div class="p-4">
                                        <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                                            <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-2 hover:text-green-600 transition"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        </a>
                                        <p class="text-green-600 font-bold mb-3">&#x09F3; <?php echo number_format($product['price'], 0); ?></p>
                                        <?php if (!empty($whatsapp)): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>?text=<?php echo $wa_msg; ?>" target="_blank"
                                               class="block w-full bg-green-600 text-white py-2 rounded-lg text-center text-sm hover:bg-green-700 transition">
                                                WhatsApp Order
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow-md p-12 text-center">
                            <p class="text-gray-500">No products in this category yet.</p>
                            <a href="index.php" class="text-green-600 hover:underline mt-4 inline-block">Browse all products</a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_title); ?>. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
