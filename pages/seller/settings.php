<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/User.php';

// Check if logged in and is seller
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('seller')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$user_id = SessionManager::getUserId();
$user_name = SessionManager::get('user_name');
$seller_obj = new Seller();
$user_obj = new User();
$seller = $seller_obj->getSellerByUserId($user_id);

if (!$seller) {
    die('You are not an approved seller');
}

$seller_id = $seller['seller_id'];
$messages = [];
$errors = [];

// Handle Store Information Update
if ($_POST['action'] ?? '' === 'update_store') {
    $store_name = trim($_POST['store_name'] ?? '');
    $contact_info = trim($_POST['contact_info'] ?? '');
    $store_description = trim($_POST['store_description'] ?? '');

    if (empty($store_name)) {
        $errors['store'] = 'Store name is required';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE sellers SET store_name = ?, contact_info = ? WHERE seller_id = ?");
        $stmt->bind_param("ssi", $store_name, $contact_info, $seller_id);
        
        if ($stmt->execute()) {
            $messages['store'] = 'Store information updated successfully!';
            $seller['store_name'] = $store_name;
            $seller['contact_info'] = $contact_info;
        } else {
            $errors['store'] = 'Failed to update store information';
        }
    }
}

// Handle Password Change
if ($_POST['action'] ?? '' === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors['password'] = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $errors['password'] = 'New passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    } else {
        try {
            if ($user_obj->changePassword($user_id, $current_password, $new_password)) {
                $messages['password'] = 'Password changed successfully!';
            } else {
                $errors['password'] = 'Current password is incorrect';
            }
        } catch (Exception $e) {
            $errors['password'] = 'Error: ' . $e->getMessage();
        }
    }
}

// Handle Notification Preferences
if ($_POST['action'] ?? '' === 'update_notifications') {
    $notify_orders = isset($_POST['notify_orders']) ? 1 : 0;
    $notify_reviews = isset($_POST['notify_reviews']) ? 1 : 0;
    $notify_messages = isset($_POST['notify_messages']) ? 1 : 0;

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO seller_settings (seller_id, notify_orders, notify_reviews, notify_messages)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        notify_orders = ?, notify_reviews = ?, notify_messages = ?
    ");
    $stmt->bind_param("iiiiii", $seller_id, $notify_orders, $notify_reviews, $notify_messages, 
                      $notify_orders, $notify_reviews, $notify_messages);
    
    if ($stmt->execute()) {
        $messages['notifications'] = 'Notification preferences updated!';
    } else {
        $errors['notifications'] = 'Failed to update preferences';
    }
}

// Get current notification settings
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM seller_settings WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc() ?: [
    'notify_orders' => 1,
    'notify_reviews' => 1,
    'notify_messages' => 1
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .settings-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .settings-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e94560;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .settings-tab {
            padding: 15px 25px;
            background: #f5f5f5;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #666;
        }

        .settings-tab:hover {
            background: #efefef;
        }

        .settings-tab.active {
            background: #e94560;
            color: white;
            border-bottom: 2px solid #e94560;
        }

        .settings-content {
            display: none;
        }

        .settings-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .settings-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #e94560;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            flex: 1;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #e94560;
            color: white;
        }

        .btn-primary:hover {
            background: #d43552;
        }

        .btn-secondary {
            background: #ddd;
            color: #333;
        }

        .btn-secondary:hover {
            background: #ccc;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e94560;
        }

        @media (max-width: 768px) {
            .settings-tabs {
                flex-direction: column;
            }

            .settings-form {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="settings-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Seller</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="earnings.php" class="nav-link">Earnings</a>
                <a href="settings.php" class="nav-link active">Settings</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container settings-container">
        <div class="page-header">
            <h2>Seller Settings</h2>
            <p>Manage your store, account security, and notification preferences</p>
        </div>

        <div class="settings-tabs">
            <button class="settings-tab active" onclick="switchTab(event, 'store')">🏪 Store Info</button>
            <button class="settings-tab" onclick="switchTab(event, 'security')">🔒 Security</button>
            <button class="settings-tab" onclick="switchTab(event, 'notifications')">🔔 Notifications</button>
        </div>

        <!-- Store Information Tab -->
        <div id="store" class="settings-content active">
            <form method="POST" class="settings-form">
                <h3 class="form-section-title">Update Store Information</h3>
                
                <?php if (!empty($messages['store'])): ?>
                    <div class="alert alert-success">✓ <?php echo $messages['store']; ?></div>
                <?php endif; ?>
                <?php if (!empty($errors['store'])): ?>
                    <div class="alert alert-error">✗ <?php echo $errors['store']; ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="store_name">Store Name *</label>
                    <input type="text" id="store_name" name="store_name" value="<?php echo htmlspecialchars($seller['store_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="contact_info">Contact Information *</label>
                    <input type="text" id="contact_info" name="contact_info" value="<?php echo htmlspecialchars($seller['contact_info']); ?>" placeholder="Phone or email for customer inquiries" required>
                </div>

                <div class="form-group">
                    <label for="store_description">Store Description</label>
                    <textarea id="store_description" name="store_description" placeholder="Tell customers about your store..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    <input type="hidden" name="action" value="update_store">
                </div>
            </form>
        </div>

        <!-- Account Security Tab -->
        <div id="security" class="settings-content">
            <form method="POST" class="settings-form">
                <h3 class="form-section-title">Change Password</h3>
                
                <?php if (!empty($messages['password'])): ?>
                    <div class="alert alert-success">✓ <?php echo $messages['password']; ?></div>
                <?php endif; ?>
                <?php if (!empty($errors['password'])): ?>
                    <div class="alert alert-error">✗ <?php echo $errors['password']; ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                    <small style="color: #666; display: block; margin-top: 5px;">Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">🔑 Update Password</button>
                    <input type="hidden" name="action" value="change_password">
                </div>
            </form>

            <div style="margin-top: 40px; padding: 20px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #2196F3;">
                <h4 style="margin-top: 0;">🔐 Two-Factor Authentication (Coming Soon)</h4>
                <p>We're working on adding 2FA support to enhance your account security. This feature will allow you to add an extra layer of protection using authenticator apps or SMS codes.</p>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="notifications" class="settings-content">
            <form method="POST" class="settings-form">
                <h3 class="form-section-title">Email Notification Preferences</h3>
                
                <?php if (!empty($messages['notifications'])): ?>
                    <div class="alert alert-success">✓ <?php echo $messages['notifications']; ?></div>
                <?php endif; ?>
                <?php if (!empty($errors['notifications'])): ?>
                    <div class="alert alert-error">✗ <?php echo $errors['notifications']; ?></div>
                <?php endif; ?>

                <p style="color: #666; margin-bottom: 25px;">Choose which notifications you'd like to receive via email:</p>

                <div class="checkbox-group">
                    <input type="checkbox" id="notify_orders" name="notify_orders" <?php echo $settings['notify_orders'] ? 'checked' : ''; ?>>
                    <label for="notify_orders">
                        <strong>📦 Order Notifications</strong>
                        <br><small>Receive emails when customers place orders or when tracking status updates</small>
                    </label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="notify_reviews" name="notify_reviews" <?php echo $settings['notify_reviews'] ? 'checked' : ''; ?>>
                    <label for="notify_reviews">
                        <strong>⭐ Review Notifications</strong>
                        <br><small>Get notified when customers leave reviews or ratings on your products</small>
                    </label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="notify_messages" name="notify_messages" <?php echo $settings['notify_messages'] ? 'checked' : ''; ?>>
                    <label for="notify_messages">
                        <strong>💬 Message Notifications</strong>
                        <br><small>Receive alerts for customer inquiries and support messages</small>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Save Preferences</button>
                    <input type="hidden" name="action" value="update_notifications">
                </div>
            </form>

            <div style="margin-top: 40px; padding: 20px; background: #fffbea; border-radius: 8px; border-left: 4px solid #ffb300;">
                <h4 style="margin-top: 0;">ℹ️ Email Notification Summary</h4>
                <p>Important: Platform-critical emails (account security, payment issues) will always be sent regardless of these settings. These preferences only control promotional and update notifications.</p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <script>
        function switchTab(e, tabName) {
            // Hide all content
            const contents = document.querySelectorAll('.settings-content');
            contents.forEach(content => content.classList.remove('active'));

            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.settings-tab');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Show selected content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked tab
            e.target.classList.add('active');
        }
    </script>
</body>
</html>
