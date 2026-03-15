<?php
/**
 * পণ্য যোগ/সম্পাদনা পেজ (Add/Edit Product Page)
 * নতুন পণ্য যোগ বা বিদ্যমান পণ্য সম্পাদনা করা হয়
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
$product = [
    'id' => 0,
    'name' => '',
    'price' => '',
    'image' => '',
    'category_id' => '',
    'description' => ''
];

// সম্পাদনা মোড - পণ্যের তথ্য লোড
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
    if ($edit_product) {
        $editing = true;
        $product = $edit_product;
    }
}

// ফর্ম সাবমিট হলে
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);
    $existing_image = $_POST['existing_image'] ?? '';

    // ভ্যালিডেশন
    if (empty($name)) {
        $error = 'পণ্যের নাম দিন।';
    } elseif ($price < 0) {
        $error = 'সঠিক মূল্য দিন।';
    } else {
        $image = $existing_image;

        // ছবি আপলোড
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $error = 'শুধুমাত্র JPG, PNG, GIF, এবং WebP ছবি আপলোড করা যাবে।';
            } elseif ($file_size > 5 * 1024 * 1024) { // 5MB সীমা
                $error = 'ছবির সাইজ ৫MB এর বেশি হতে পারবে না।';
            } else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'product_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                $upload_path = '../uploads/' . $new_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // পুরানো ছবি মুছে ফেলা
                    if (!empty($existing_image) && file_exists('../uploads/' . $existing_image)) {
                        unlink('../uploads/' . $existing_image);
                    }
                    $image = $new_filename;
                } else {
                    $error = 'ছবি আপলোড করতে সমস্যা হয়েছে।';
                }
            }
        }

        if (empty($error)) {
            if ($product_id > 0) {
                // আপডেট করা
                $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ?, category_id = ?, description = ? WHERE id = ?");
                $stmt->bind_param("sdsssi", $name, $price, $image, $category_id, $description, $product_id);
                $stmt->execute();
                header("Location: dashboard.php?msg=product_updated");
                exit;
            } else {
                // নতুন পণ্য যোগ করা
                $stmt = $conn->prepare("INSERT INTO products (name, price, image, category_id, description) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sdsss", $name, $price, $image, $category_id, $description);
                $stmt->execute();
                header("Location: dashboard.php?msg=product_added");
                exit;
            }
        }

        // এরর হলে ফর্মে ডেটা রাখা
        $product = [
            'id' => $product_id,
            'name' => $name,
            'price' => $price,
            'image' => $existing_image,
            'category_id' => $category_id,
            'description' => $description
        ];
        $editing = ($product_id > 0);
    }
}

// সব ক্যাটাগরি লোড (ড্রপডাউনের জন্য)
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editing ? 'পণ্য সম্পাদনা' : 'নতুন পণ্য যোগ'; ?> - <?php echo htmlspecialchars($site_title); ?></title>
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
                    <a href="add-product.php" class="hover:text-green-400 transition text-sm text-green-400">➕ পণ্য যোগ</a>
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

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                <?php echo $editing ? '✏️ পণ্য সম্পাদনা করুন' : '➕ নতুন পণ্য যোগ করুন'; ?>
            </h1>

            <!-- এরর মেসেজ -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product['image'] ?? ''); ?>">

                <!-- পণ্যের নাম -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">📝 পণ্যের নাম *</label>
                    <input type="text" name="name" 
                           value="<?php echo htmlspecialchars($product['name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="পণ্যের নাম লিখুন" required>
                </div>

                <!-- মূল্য -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">💰 মূল্য (৳) *</label>
                    <input type="number" name="price" step="0.01" min="0"
                           value="<?php echo htmlspecialchars($product['price']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="মূল্য লিখুন" required>
                </div>

                <!-- ক্যাটাগরি -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">📂 ক্যাটাগরি</label>
                    <select name="category_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">-- ক্যাটাগরি নির্বাচন করুন --</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" 
                                    <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- বিবরণ -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">📄 বিবরণ</label>
                    <textarea name="description" rows="5"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="পণ্যের বিবরণ লিখুন"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <!-- ছবি আপলোড -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">🖼️ পণ্যের ছবি</label>
                    <?php if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                        <div class="mb-3">
                            <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                 class="w-32 h-32 object-cover rounded-lg border">
                            <p class="text-sm text-gray-500 mt-1">বর্তমান ছবি</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-sm text-gray-500 mt-1">সর্বোচ্চ ৫MB। JPG, PNG, GIF, WebP সমর্থিত।</p>
                </div>

                <!-- বাটন -->
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow">
                        <?php echo $editing ? '💾 আপডেট করুন' : '➕ পণ্য যোগ করুন'; ?>
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
