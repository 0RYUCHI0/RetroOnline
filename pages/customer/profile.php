<?php
require_once '../../config.php';
require_once '../../classes/User.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$user_id = SessionManager::getUserId();
$user_obj = new User();
$user = $user_obj->getUserById($user_id);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        try {
            $user_obj->updateProfile($user_id, $_POST['name'], $_POST['phone']);
            $success = 'Profile updated successfully!';
            $user = $user_obj->getUserById($user_id);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'change_password') {
        try {
            $user_obj->changePassword($user_id, $_POST['old_password'], $_POST['new_password']);
            $success = 'Password changed successfully!';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$user_name = SessionManager::get('user_name');
$user_email = SessionManager::get('user_email');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="profile-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub</h1>
            </div>
            <div class="navbar-menu">
                <a href="shop.php" class="nav-link">Shop</a>
                <a href="wishlist.php" class="nav-link">❤️ Wishlist</a>
                <a href="cart.php" class="nav-link">Cart</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="profile.php" class="nav-link active">Profile</a>
                <a href="auth.php?action=logout" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>My Profile</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="profile-layout">
            <!-- Profile Section -->
            <div class="profile-section">
                <h3>Account Information</h3>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small>Email cannot be changed</small>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <!-- Become a Seller Section -->
            <div class="profile-section seller-section">
                <h3>Interested in Selling?</h3>
                <p>Join RetroGameHub as a seller and start earning! Share your retro game collection with our community.</p>
                <a href="apply-seller.php" class="btn btn-primary">Apply to Become a Seller</a>
            </div>

            <!-- Password Change Section -->
            <div class="profile-section">
                <h3>Change Password</h3>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <style>
        .seller-section {
            background: linear-gradient(135deg, rgba(233, 69, 96, 0.1) 0%, rgba(52, 152, 219, 0.1) 100%);
            border-left: 4px solid #e94560;
        }

        .seller-section p {
            color: #aaa;
            margin-bottom: 20px;
        }
    </style>
</body>
</html>
