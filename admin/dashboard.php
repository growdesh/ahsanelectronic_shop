<?php
/**
 * Admin Dashboard
 * Shows metrics, product/category management, analytics
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../db.php';

$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'Ahsan Electronic Shop';

// Delete product
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    $del_id = (int)$_GET['delete_product'];
    $img_stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $img_stmt->bind_param("i", $del_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    $img_row = $img_result->fetch_assoc();
    $img_stmt->close();
    if ($img_row && !empty($img_row['image'])) {
        deleteUploadedImage($img_row['image']);
    }
    $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del_stmt->bind_param("i", $del_id);
    $del_stmt->execute();
    $del_stmt->close();
    header("Location: dashboard.php?msg=product_deleted");
    exit;
}

// Delete category
if (isset($_GET['delete_category']) && is_numeric($_GET['delete_category'])) {
    $del_id = (int)$_GET['delete_category'];
    $icon_stmt = $conn->prepare("SELECT icon FROM categories WHERE id = ?");
    $icon_stmt->bind_param("i", $del_id);
    $icon_stmt->execute();
    $icon_result = $icon_stmt->get_result();
    $icon_row = $icon_result->fetch_assoc();
    $icon_stmt->close();
    if ($icon_row && !empty($icon_row['icon'])) {
        deleteUploadedImage($icon_row['icon']);
    }
    $del_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $del_stmt->bind_param("i", $del_id);
    $del_stmt->execute();
    $del_stmt->close();
    header("Location: dashboard.php?msg=category_deleted");
    exit;
}

// Summary data
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_categories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
$total_views = $conn->query("SELECT COALESCE(SUM(views), 0) as total FROM products")->fetch_assoc()['total'];
$recent_views_24h = $conn->query("SELECT COUNT(*) as count FROM analytics WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['count'];

// Top viewed products
$top_products = $conn->query("SELECT id, name, views FROM products ORDER BY views DESC LIMIT 5");

// All products
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");

// All categories
$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name ASC");

// Success messages
$msg = $_GET['msg'] ?? '';
$success_messages = [
    'product_added' => 'Product added successfully!',
    'product_updated' => 'Product updated successfully!',
    'product_deleted' => 'Product deleted successfully!',
    'category_added' => 'Category added successfully!',
    'category_updated' => 'Category updated successfully!',
    'category_deleted' => 'Category deleted successfully!',
    'settings_updated' => 'Settings updated successfully!',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($site_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-gray-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <span class="text-xl font-bold">Admin Panel</span>
                    <button onclick="document.getElementById('adminMenu').classList.toggle('hidden')" class="md:hidden ml-4 p-1 rounded hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="dashboard.php" class="text-green-400 text-sm font-semibold">Dashboard</a>
                    <a href="add-product.php" class="hover:text-green-400 transition text-sm">Add Product</a>
                    <a href="add-category.php" class="hover:text-green-400 transition text-sm">Add Category</a>
                    <a href="settings.php" class="hover:text-green-400 transition text-sm">Settings</a>
                    <a href="../index.php" target="_blank" class="text-sm hover:text-green-400 transition">View Site</a>
                    <a href="login.php?logout=1" class="bg-red-500 px-3 py-1 rounded text-sm hover:bg-red-600 transition">Logout</a>
                </div>
            </div>
            <div id="adminMenu" class="hidden md:hidden mt-3 pb-2 border-t border-gray-700 pt-3 space-y-2">
                <a href="dashboard.php" class="block text-green-400 text-sm font-semibold py-1">Dashboard</a>
                <a href="add-product.php" class="block hover:text-green-400 text-sm py-1">Add Product</a>
                <a href="add-category.php" class="block hover:text-green-400 text-sm py-1">Add Category</a>
                <a href="settings.php" class="block hover:text-green-400 text-sm py-1">Settings</a>
                <a href="../index.php" target="_blank" class="block hover:text-green-400 text-sm py-1">View Site</a>
                <a href="login.php?logout=1" class="block text-red-400 text-sm py-1">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">

        <?php if (!empty($msg) && isset($success_messages[$msg])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-lg mb-6">
                <?php echo $success_messages[$msg]; ?>
            </div>
        <?php endif; ?>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-gray-500 text-sm">Total Products</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $total_products; ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-gray-500 text-sm">Total Categories</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $total_categories; ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-gray-500 text-sm">Total Page Views</p>
                <p class="text-3xl font-bold text-blue-600"><?php echo number_format($total_views); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-gray-500 text-sm">Views (24h)</p>
                <p class="text-3xl font-bold text-green-600"><?php echo number_format($recent_views_24h); ?></p>
            </div>
        </div>

        <!-- Quick Links & Top Products -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="add-product.php" class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition text-center text-sm font-semibold">Add Product</a>
                    <a href="add-category.php" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition text-center text-sm font-semibold">Add Category</a>
                    <a href="settings.php" class="bg-gray-600 text-white px-4 py-3 rounded-lg hover:bg-gray-700 transition text-center text-sm font-semibold">Settings</a>
                    <a href="../index.php" target="_blank" class="bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 transition text-center text-sm font-semibold">View Site</a>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Top Viewed Products</h3>
                <?php if ($top_products && $top_products->num_rows > 0): ?>
                    <ul class="space-y-2">
                        <?php while ($tp = $top_products->fetch_assoc()): ?>
                            <li class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                                <a href="edit-product.php?id=<?php echo (int)$tp['id']; ?>" class="text-gray-700 hover:text-green-600 truncate mr-4"><?php echo htmlspecialchars($tp['name']); ?></a>
                                <span class="text-sm bg-blue-100 text-blue-700 px-2 py-1 rounded-full whitespace-nowrap"><?php echo number_format($tp['views']); ?> views</span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500 text-sm">No products yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">All Products</h2>
                <a href="add-product.php" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">+ Add New</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Image</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Product Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 hidden md:table-cell">Category</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Price</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 hidden md:table-cell">Views</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if ($products && $products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <?php if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" class="w-12 h-12 object-cover rounded">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">No img</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td class="px-4 py-3 text-gray-600 hidden md:table-cell"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td class="px-4 py-3 font-semibold text-green-600">&#x09F3; <?php echo number_format($product['price'], 0); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-500 hidden md:table-cell"><?php echo number_format($product['views'] ?? 0); ?></td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <a href="edit-product.php?id=<?php echo (int)$product['id']; ?>" class="inline-block bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">Edit</a>
                                        <a href="dashboard.php?delete_product=<?php echo (int)$product['id']; ?>" onclick="return confirm('Delete this product?');" class="inline-block bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition ml-1">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No products yet. <a href="add-product.php" class="text-green-600 hover:underline">Add one</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">All Categories</h2>
                <a href="add-category.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">+ Add New</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Icon</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Category Name</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600">Products</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if ($categories && $categories->num_rows > 0): ?>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <?php if (!empty($cat['icon']) && file_exists('../uploads/' . $cat['icon'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($cat['icon']); ?>" class="w-10 h-10 object-cover rounded">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center text-green-600 text-xs">Cat</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-800"><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td class="px-4 py-3 text-center"><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-sm"><?php echo (int)$cat['product_count']; ?></span></td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <a href="edit-category.php?id=<?php echo (int)$cat['id']; ?>" class="inline-block bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">Edit</a>
                                        <a href="dashboard.php?delete_category=<?php echo (int)$cat['id']; ?>" onclick="return confirm('Delete this category?');" class="inline-block bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition ml-1">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No categories yet. <a href="add-category.php" class="text-green-600 hover:underline">Add one</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>
