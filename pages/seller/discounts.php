<?php
/**
 * Seller Discount Management Page
 * Allows sellers to create, view, and manage discount deals for their products
 */

require_once '../../config.php';
require_once '../../classes/Discount.php';
require_once '../../classes/Product.php';

// Check if seller is logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: ../customer/auth.php');
    exit;
}

$seller_id = SessionManager::getUserId();
$user_role = SessionManager::get('user_role');

if ($user_role !== 'seller') {
    header('Location: ../customer/shop.php');
    exit;
}

$discount = new Discount();
$product = new Product();
$error = '';
$success = '';

// Handle discount creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_discount') {
        try {
            $product_id = (int)$_POST['product_id'];
            $discount_percent = (float)$_POST['discount_percent'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            
            // Verify seller owns this product
            $prod = $product->getProductById($product_id);
            if (!$prod || $prod['seller_id'] != $seller_id) {
                throw new Exception('You do not own this product');
            }
            
            $discount_id = $discount->addDiscount($product_id, $discount_percent, $start_date, $end_date);
            $success = "Discount deal created successfully! (ID: $discount_id)";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($_POST['action'] === 'update_discount') {
        try {
            $discount_id = (int)$_POST['discount_id'];
            $discount_percent = (float)$_POST['discount_percent'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            
            $discount->updateDiscount($discount_id, $discount_percent, $start_date, $end_date);
            $success = "Discount deal updated successfully!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete_discount') {
        try {
            $discount_id = (int)$_POST['discount_id'];
            $discount->removeDiscount($discount_id);
            $success = "Discount deal deleted successfully!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Get seller's products
$seller_products = $product->getProductsBySeller($seller_id);

// Get discount statistics
$stats = $discount->getDiscountStats($seller_id);

// Get active discounts
$active_discounts = $discount->getAllActiveDiscounts($seller_id);

// Get upcoming discounts
$upcoming_discounts = $discount->getUpcomingDiscounts($seller_id, 7);

// Get all discounts (including expired)
$all_discounts_query = "SELECT d.*, p.name as product_name FROM discounts d 
                        JOIN products p ON d.product_id = p.product_id 
                        WHERE p.seller_id = ? 
                        ORDER BY d.end_date DESC";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Discounts - RetroGameHub Seller</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .discount-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .discount-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .discount-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .discount-section h3 {
            margin-top: 0;
            color: #333;
        }
        .discount-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .discount-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        .discount-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .discount-table tr:hover {
            background: #fafafa;
        }
        .discount-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        .badge-upcoming {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-expired {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .btn-small {
            padding: 6px 10px;
            font-size: 12px;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin: 15px 0;
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
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        .empty-state h4 {
            margin: 10px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Seller</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="discounts.php" class="nav-link active">🏷️ Discounts</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="earnings.php" class="nav-link">Earnings</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="discount-dashboard">
        <h2>🏷️ Discount Deals Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3><?php echo $stats['total_discounts']; ?></h3>
                <p>Total Discounts</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <h3><?php echo $stats['active_discounts']; ?></h3>
                <p>Currently Active</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                <h3><?php echo $stats['upcoming_discounts']; ?></h3>
                <p>Scheduled Soon</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <h3><?php echo $stats['expired_discounts']; ?></h3>
                <p>Expired</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <h3><?php echo number_format($stats['avg_discount'], 1); ?>%</h3>
                <p>Avg. Discount</p>
            </div>
        </div>

        <!-- Create New Discount -->
        <div class="discount-form">
            <h3>➕ Create New Discount Deal</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_discount">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product *</label>
                        <select name="product_id" required>
                            <option value="">-- Select Product --</option>
                            <?php foreach ($seller_products as $prod): ?>
                                <option value="<?php echo $prod['product_id']; ?>">
                                    <?php echo htmlspecialchars($prod['name']); ?> (Condition: <?php echo $prod['condition']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Discount Percentage (%) *</label>
                        <input type="number" name="discount_percent" min="0.01" max="100" step="0.01" placeholder="15.5" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>End Date *</label>
                        <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">✅ Create Discount</button>
            </form>
        </div>

        <!-- Active Discounts -->
        <div class="discount-section">
            <h3>✅ Active Discount Deals</h3>
            <?php if (empty($active_discounts)): ?>
                <div class="empty-state">
                    <h4>No active discounts</h4>
                    <p>Create a new discount deal to get started!</p>
                </div>
            <?php else: ?>
                <table class="discount-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Discount</th>
                            <th>Original Price</th>
                            <th>Sale Price</th>
                            <th>Valid Until</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_discounts as $deal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($deal['product_name']); ?></td>
                                <td>
                                    <span class="discount-badge badge-active">
                                        <?php echo round($deal['discount_percent']); ?>% OFF
                                    </span>
                                </td>
                                <td>$<?php echo number_format($deal['original_price'], 2); ?></td>
                                <td style="color: #27ae60; font-weight: bold;">
                                    $<?php echo number_format($deal['original_price'] * (1 - $deal['discount_percent']/100), 2); ?>
                                </td>
                                <td><?php echo $deal['end_date']; ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_discount">
                                        <input type="hidden" name="discount_id" value="<?php echo $deal['discount_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Delete this discount?')">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Upcoming Discounts -->
        <div class="discount-section" style="border-left-color: #f39c12;">
            <h3>📅 Upcoming Discounts (Next 7 Days)</h3>
            <?php if (empty($upcoming_discounts)): ?>
                <div class="empty-state">
                    <h4>No upcoming discounts</h4>
                    <p>Schedule discounts in advance for future campaigns!</p>
                </div>
            <?php else: ?>
                <table class="discount-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Discount</th>
                            <th>Starts</th>
                            <th>Ends</th>
                            <th>Days Until Start</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming_discounts as $deal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($deal['product_name']); ?></td>
                                <td>
                                    <span class="discount-badge badge-upcoming">
                                        <?php echo round($deal['discount_percent']); ?>% OFF
                                    </span>
                                </td>
                                <td><?php echo $deal['start_date']; ?></td>
                                <td><?php echo $deal['end_date']; ?></td>
                                <td>
                                    <?php 
                                    $days_until = (new DateTime($deal['start_date']))->diff(new DateTime())->days;
                                    echo $days_until . ' days';
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_discount">
                                        <input type="hidden" name="discount_id" value="<?php echo $deal['discount_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Delete this discount?')">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Tips Section -->
        <div class="discount-section" style="border-left-color: #3498db; background: #ecf0f1;">
            <h3>💡 Pro Tips for Creating Effective Discounts</h3>
            <ul>
                <li><strong>Popular Discounts:</strong> 10-20% off is most attractive to customers</li>
                <li><strong>Duration:</strong> Keep sales active for at least 7-14 days for maximum impact</li>
                <li><strong>Avoid Overlap:</strong> System prevents overlapping discounts on the same product</li>
                <li><strong>Flash Sales:</strong> Create short-duration high-discount deals to drive traffic</li>
                <li><strong>Seasonal:</strong> Plan discounts around holidays and gaming events</li>
                <li><strong>Monitor Performance:</strong> Track which discounts get the most orders</li>
            </ul>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
