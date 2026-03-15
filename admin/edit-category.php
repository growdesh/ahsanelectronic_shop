<?php
/**
 * Edit Category Page
 * Allows admin to edit an existing category with icon upload
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../db.php';

$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'Ahsan Electronic Shop';
$error = '';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

if (!$category) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $existing_icon = $_POST['existing_icon'] ?? '';

    if (empty($name)) {
        $error = 'Please enter category name.';
    } else {
        $icon = $existing_icon;

        if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleImageUpload($_FILES['icon'], 'cat_icon');
            if (is_array($upload_result) && isset($upload_result['error'])) {
                $error = $upload_result['error'];
            } elseif ($upload_result) {
                if (!empty($existing_icon)) {
                    deleteUploadedImage($existing_icon);
                }
                $icon = $upload_result;
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, icon = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $icon, $category_id);
            $stmt->execute();
            $stmt->close();
            header("Location: dashboard.php?msg=category_updated");
            exit;
        }
    }

    $category['name'] = $name;
    $category['icon'] = $existing_icon;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - <?php echo htmlspecialchars($site_title); ?></title>
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
                    <a href="dashboard.php" class="hover:text-green-400 transition text-sm">Dashboard</a>
                    <a href="add-product.php" class="hover:text-green-400 transition text-sm">Add Product</a>
                    <a href="add-category.php" class="hover:text-green-400 transition text-sm">Add Category</a>
                    <a href="settings.php" class="hover:text-green-400 transition text-sm">Settings</a>
                    <a href="login.php?logout=1" class="bg-red-500 px-3 py-1 rounded text-sm hover:bg-red-600 transition">Logout</a>
                </div>
            </div>
            <div id="adminMenu" class="hidden md:hidden mt-3 pb-2 border-t border-gray-700 pt-3 space-y-2">
                <a href="dashboard.php" class="block hover:text-green-400 text-sm py-1">Dashboard</a>
                <a href="add-product.php" class="block hover:text-green-400 text-sm py-1">Add Product</a>
                <a href="add-category.php" class="block hover:text-green-400 text-sm py-1">Add Category</a>
                <a href="settings.php" class="block hover:text-green-400 text-sm py-1">Settings</a>
                <a href="login.php?logout=1" class="block text-red-400 text-sm py-1">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Category</h1>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="existing_icon" value="<?php echo htmlspecialchars($category['icon'] ?? ''); ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Category Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Category Icon</label>
                    <?php if (!empty($category['icon']) && file_exists('../uploads/' . $category['icon'])): ?>
                        <div class="mb-3">
                            <img src="../uploads/<?php echo htmlspecialchars($category['icon']); ?>" class="w-20 h-20 object-cover rounded-lg border">
                            <p class="text-sm text-gray-500 mt-1">Current icon</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="icon" accept="image/*"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-sm text-gray-500 mt-1">Max 5MB. Leave empty to keep current icon.</p>
                </div>

                <div class="mb-4 bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Created: <strong class="text-gray-700"><?php echo $category['created_at']; ?></strong></p>
                    <p class="text-sm text-gray-500">Updated: <strong class="text-gray-700"><?php echo $category['updated_at']; ?></strong></p>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow">Update Category</button>
                    <a href="dashboard.php" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition text-center">&larr; Back</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>
