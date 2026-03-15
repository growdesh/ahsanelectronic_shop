<?php
/**
 * Product Details Page
 * Shows product details with WhatsApp order button and view tracking
 */
require_once 'db.php';

$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'Ahsan Electronic Shop';
$whatsapp = $settings['whatsapp_number'] ?? '';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

// Track product view
trackProductView($conn, $product_id);

// Load product
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.id as cat_id FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Related products from same category
$related = null;
if (!empty($product['cat_id'])) {
    $rel_stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY created_at DESC LIMIT 4");
    $rel_stmt->bind_param("ii", $product['cat_id'], $product_id);
    $rel_stmt->execute();
    $related = $rel_stmt->get_result();
    $rel_stmt->close();
}

// WhatsApp message
$wa_message = urlencode("Hi! I'm interested in: " . $product['name'] . " (Price: " . number_format($product['price'], 0) . " BDT)");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - <?php echo htmlspecialchars($site_title); ?></title>
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
                    <a href="category.php?id=<?php echo (int)$mc['id']; ?>" class="block py-2 hover:text-green-200"><?php echo htmlspecialchars($mc['name']); ?></a>
                <?php endwhile; ?>
            </div>
        </div>
    </header>

    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-6">
                <a href="index.php" class="hover:text-green-600">Home</a>
                <?php if (!empty($product['category_name'])): ?>
                    <span class="mx-2">/</span>
                    <a href="category.php?id=<?php echo (int)$product['cat_id']; ?>" class="hover:text-green-600"><?php echo htmlspecialchars($product['category_name']); ?></a>
                <?php endif; ?>
                <span class="mx-2">/</span>
                <span class="text-gray-700"><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>

            <!-- Product Detail -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                    <!-- Product Image -->
                    <div class="bg-gray-100 flex items-center justify-center p-8">
                        <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="max-w-full max-h-96 object-contain rounded-lg">
                        <?php else: ?>
                            <div class="text-gray-400 text-center py-20">No Image Available</div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Info -->
                    <div class="p-8">
                        <?php if (!empty($product['category_name'])): ?>
                            <a href="category.php?id=<?php echo (int)$product['cat_id']; ?>" class="inline-block bg-green-100 text-green-700 text-sm px-3 py-1 rounded-full mb-4 hover:bg-green-200 transition">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        <?php endif; ?>

                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>

                        <p class="text-3xl font-bold text-green-600 mb-6">&#x09F3; <?php echo number_format($product['price'], 0); ?></p>

                        <?php if (!empty($product['description'])): ?>
                            <div class="text-gray-600 mb-6 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-sm text-gray-400 mb-6">
                            <span>Views: <?php echo number_format($product['views'] ?? 0); ?></span>
                        </div>

                        <?php if (!empty($whatsapp)): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp); ?>?text=<?php echo $wa_message; ?>" target="_blank"
                               class="block w-full bg-green-600 text-white py-4 rounded-xl font-bold text-center hover:bg-green-700 transition shadow-lg text-lg">
                                Order via WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if ($related && $related->num_rows > 0): ?>
            <section>
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Related Products</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <?php while ($rp = $related->fetch_assoc()): ?>
                        <a href="product.php?id=<?php echo (int)$rp['id']; ?>" class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl hover:-translate-y-1 transition transform">
                            <div class="aspect-square bg-gray-200 overflow-hidden">
                                <?php if (!empty($rp['image']) && file_exists('uploads/' . $rp['image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($rp['image']); ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 mb-2"><?php echo htmlspecialchars($rp['name']); ?></h3>
                                <p class="text-green-600 font-bold">&#x09F3; <?php echo number_format($rp['price'], 0); ?></p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>

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
