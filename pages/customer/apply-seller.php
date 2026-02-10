<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/User.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$user_id = SessionManager::getUserId();
$user_name = SessionManager::get('user_name');
$seller = new Seller();
$user = new User();

$error = '';
$success = '';
$existing_application = null;

// Check if user already has an application
try {
    $existing_application = $seller->getApplicationByUserId($user_id);
} catch (Exception $e) {
    // No application found
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($existing_application)) {
    $store_name = trim($_POST['store_name'] ?? '');
    $contact_info = trim($_POST['contact_info'] ?? '');
    $payment_info = trim($_POST['payment_info'] ?? '');

    if (empty($store_name) || empty($contact_info) || empty($payment_info)) {
        $error = 'All fields are required';
    } else {
        try {
            $seller->applyAsSeller($user_id, $store_name, $contact_info, $payment_info);
            $success = 'Application submitted successfully! The admin team will review it shortly.';
            $existing_application = [
                'store_name' => $store_name,
                'status' => 'pending',
                'applied_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$user_info = $user->getUserById($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to Become a Seller - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="seller-apply-page">
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
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="auth.php?action=logout" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Become a RetroGameHub Seller</h2>
            <p>Join our community of retro game merchants and start selling today!</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="seller-apply-container">
            <div class="benefits-section">
                <h3>Why Become a Seller?</h3>
                <ul class="benefits-list">
                    <li>✓ Reach a community of retro gaming enthusiasts</li>
                    <li>✓ Sell your products to a targeted audience</li>
                    <li>✓ Manage your inventory easily</li>
                    <li>✓ Track orders and earnings in real-time</li>
                    <li>✓ Build your store reputation with customer reviews</li>
                    <li>✓ Low commission rates (only 5%)</li>
                </ul>
            </div>

            <?php if ($existing_application): ?>
                <div class="existing-application">
                    <h3>Your Seller Application</h3>
                    <div class="application-status">
                        <div class="status-box">
                            <p><strong>Store Name:</strong> <?php echo htmlspecialchars($existing_application['store_name']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status-badge status-<?php echo htmlspecialchars($existing_application['status']); ?>">
                                    <?php 
                                    $status = $existing_application['status'];
                                    if ($status === 'pending') {
                                        echo '⏳ Pending Review';
                                    } elseif ($status === 'approved') {
                                        echo '✅ Approved';
                                    } else {
                                        echo '❌ Rejected';
                                    }
                                    ?>
                                </span>
                            </p>
                            <p><strong>Applied:</strong> <?php echo date('M d, Y', strtotime($existing_application['applied_at'])); ?></p>
                            
                            <?php if ($existing_application['status'] === 'approved'): ?>
                                <p class="success-message">🎉 Congratulations! Your application has been approved. You can now access the seller dashboard!</p>
                                <a href="../seller/dashboard.php" class="btn btn-primary">Go to Seller Dashboard</a>
                            <?php elseif ($existing_application['status'] === 'pending'): ?>
                                <p class="info-message">Your application is under review by our admin team. You'll be notified once a decision is made.</p>
                            <?php else: ?>
                                <p class="error-message">Unfortunately, your application was rejected. Please review the requirements and apply again.</p>
                                <a href="apply-seller.php" class="btn btn-secondary">Apply Again</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" class="seller-form">
                    <div class="form-section">
                        <h3>Application Details</h3>
                        
                        <div class="form-group">
                            <label>Your Name *</label>
                            <input type="text" value="<?php echo htmlspecialchars($user_info['name']); ?>" disabled class="form-control">
                            <small>Your account name will be used as your seller identity</small>
                        </div>

                        <div class="form-group">
                            <label>Your Email *</label>
                            <input type="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" disabled class="form-control">
                            <small>Contact email for your seller account</small>
                        </div>

                        <div class="form-group">
                            <label>Store Name *</label>
                            <input type="text" name="store_name" placeholder="e.g., RetroKing Gaming, Classic Games Hub" required class="form-control">
                            <small>The name customers will see for your store</small>
                        </div>

                        <div class="form-group">
                            <label>Contact Information *</label>
                            <textarea name="contact_info" placeholder="Your preferred contact information (phone, email, address)" required class="form-control" rows="3"></textarea>
                            <small>This will be used for business communication</small>
                        </div>

                        <div class="form-group">
                            <label>Payment Information *</label>
                            <textarea name="payment_info" placeholder="Bank account details, PayPal email, or preferred payment method" required class="form-control" rows="3"></textarea>
                            <small>How you'd like to receive payments from sales (kept confidential)</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Seller Requirements</h3>
                        <div class="requirements">
                            <p><strong>To become a seller, you must:</strong></p>
                            <ul>
                                <li>Be at least 18 years old</li>
                                <li>Have a valid business contact information</li>
                                <li>Agree to our seller terms and conditions</li>
                                <li>Maintain product quality standards</li>
                                <li>Respond to customer inquiries promptly</li>
                                <li>Comply with shipping and return policies</li>
                            </ul>
                            
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="agree_terms" required>
                                    I agree to the seller terms and conditions
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="shop.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h3>What Happens Next?</h3>
            <div class="info-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Application Review</h4>
                        <p>Our admin team will review your application within 24-48 hours</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Approval Decision</h4>
                        <p>You'll receive notification of approval or rejection via email</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Start Selling</h4>
                        <p>Once approved, access your seller dashboard and start listing products</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <style>
        .seller-apply-page {
            background: #1a1a2e;
            color: #fff;
        }

        .page-header {
            text-align: center;
            margin: 40px 0;
            border-bottom: 2px solid #e94560;
            padding-bottom: 20px;
        }

        .page-header h2 {
            color: #e94560;
            font-size: 2.5em;
            margin: 0;
        }

        .page-header p {
            color: #aaa;
            font-size: 1.1em;
            margin: 10px 0 0 0;
        }

        .seller-apply-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 40px 0;
        }

        .benefits-section {
            background: #16213e;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #e94560;
        }

        .benefits-section h3 {
            color: #e94560;
            margin-top: 0;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
        }

        .benefits-list li {
            padding: 12px 0;
            border-bottom: 1px solid #0f3460;
            color: #aaa;
        }

        .benefits-list li:last-child {
            border-bottom: none;
        }

        .seller-form {
            background: #16213e;
            padding: 30px;
            border-radius: 8px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .form-section h3 {
            color: #e94560;
            border-bottom: 2px solid #e94560;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #fff;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #0f3460;
            background: #0f3460;
            color: #fff;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #e94560;
            box-shadow: 0 0 5px rgba(233, 69, 96, 0.3);
        }

        .form-control:disabled {
            background: #1a1a2e;
            cursor: not-allowed;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #888;
            font-size: 0.9em;
        }

        .requirements {
            background: #0f3460;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .requirements p {
            margin: 0 0 15px 0;
            color: #aaa;
        }

        .requirements ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .requirements li {
            margin: 8px 0;
            color: #aaa;
        }

        .checkbox-group {
            margin-top: 20px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            margin: 0;
            cursor: pointer;
            color: #aaa;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .existing-application {
            grid-column: 1 / -1;
        }

        .application-status {
            background: #16213e;
            padding: 30px;
            border-radius: 8px;
            border: 2px solid #e94560;
        }

        .status-box p {
            margin: 15px 0;
            font-size: 1.1em;
        }

        .status-box strong {
            color: #e94560;
        }

        .success-message {
            color: #2ecc71 !important;
            background: rgba(46, 204, 113, 0.1);
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .info-message {
            color: #3498db !important;
            background: rgba(52, 152, 219, 0.1);
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .error-message {
            color: #e74c3c !important;
            background: rgba(231, 76, 60, 0.1);
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .info-section {
            background: #16213e;
            padding: 30px;
            border-radius: 8px;
            margin-top: 40px;
        }

        .info-section h3 {
            color: #e94560;
            margin-top: 0;
        }

        .info-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .step {
            display: flex;
            gap: 20px;
        }

        .step-number {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            background: #e94560;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5em;
            color: #fff;
        }

        .step-content h4 {
            margin: 0 0 8px 0;
            color: #fff;
        }

        .step-content p {
            margin: 0;
            color: #aaa;
            font-size: 0.95em;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid #2ecc71;
            color: #2ecc71;
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid #e74c3c;
            color: #e74c3c;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #e94560;
            color: #fff;
        }

        .btn-primary:hover {
            background: #d63447;
        }

        .btn-secondary {
            background: #0f3460;
            color: #fff;
            border: 1px solid #e94560;
        }

        .btn-secondary:hover {
            background: #1a1a2e;
        }

        @media (max-width: 768px) {
            .seller-apply-container {
                grid-template-columns: 1fr;
            }

            .page-header h2 {
                font-size: 1.8em;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .info-steps {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
