<?php
/**
 * Admin Login Page
 * Secure login with hashed password and rate limiting
 */
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

require_once '../db.php';

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } elseif (isLoginRateLimited($conn, $username)) {
        $error = 'Too many failed attempts. Please try again after 15 minutes.';
    } else {
        // Find admin user
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful
            resetLoginAttempts($conn, $username);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            recordFailedLogin($conn, $username);
            $error = 'Invalid username or password!';
        }
    }
}

// Load site settings
$settings = getSettings($conn);
$site_title = $settings['site_title'] ?? 'Ahsan Electronic Shop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo htmlspecialchars($site_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-600 to-green-800 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md mx-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Logo -->
            <div class="text-center mb-8">
                <?php if (!empty($settings['site_logo'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Logo" class="w-20 h-20 object-contain mx-auto mb-4 rounded-lg">
                <?php else: ?>
                    <div class="text-5xl mb-4">&#x1F512;</div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold text-gray-800">Admin Panel</h1>
                <p class="text-gray-500 mt-1"><?php echo htmlspecialchars($site_title); ?></p>
            </div>

            <!-- Error message -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2" for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Enter username" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2" for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Enter password" required>
                </div>

                <button type="submit" 
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow-lg">
                    Login
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="../index.php" class="text-green-600 hover:underline text-sm">
                    &larr; Back to Website
                </a>
            </div>
        </div>

        <p class="text-center text-green-100 text-sm mt-4">
            Default: admin / admin123
        </p>
    </div>

</body>
</html>
<?php $conn->close(); ?>
