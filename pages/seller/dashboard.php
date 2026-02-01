<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/Order.php';
require_once '../../classes/Product.php';

// Check if logged in and is seller
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('seller')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$user_id = SessionManager::getUserId();
$seller_obj = new Seller();
$seller = $seller_obj->getSellerByUserId($user_id);

if (!$seller) {
    die('You are not an approved seller');
}

$seller_id = $seller['seller_id'];
$order = new Order();
$product = new Product();

// Get seller stats
$earnings = $order->getSellerEarnings($seller_id);
$products = $product->getProductsBySeller($seller_id);
$seller_orders = $order->getSellerOrderItems($seller_id);

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="dashboard-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Seller</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="earnings.php" class="nav-link">Earnings</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Seller Dashboard</h2>
            <p>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($products); ?></h3>
                <p>Active Products</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $earnings['total_orders'] ?? 0; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($earnings['total_sales'] ?? 0, 2); ?></h3>
                <p>Total Sales</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($earnings['net_earnings'] ?? 0, 2); ?></h3>
                <p>Net Earnings</p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-section">
            <h3>Recent Orders</h3>
            <?php if (empty($seller_orders)): ?>
                <p>No orders yet</p>
            <?php else: ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Order Item ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($seller_orders, 0, 5) as $ord): ?>
                            <tr>
                                <td>#<?php echo $ord['order_item_id']; ?></td>
                                <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($ord['name']); ?></td>
                                <td><?php echo $ord['quantity']; ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($ord['order_status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
            <?php endif; ?>
        </div>

        <!-- Products Overview -->
        <div class="dashboard-section">
            <h3>Your Products</h3>
            <?php if (empty($products)): ?>
                <p>You haven't added any products yet.</p>
                <a href="products.php?action=add" class="btn btn-primary">Add Your First Product</a>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach (array_slice($products, 0, 4) as $prod): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                $img = '';
                                if (!empty($prod['image_url'])) {
                                    if (strpos($prod['image_url'], 'http') === 0) {
                                        $img = htmlspecialchars($prod['image_url']);
                                    } else {
                                        $img = htmlspecialchars(SITE_URL . '/' . ltrim($prod['image_url'], '/'));
                                    }
                                } else {
                                    $img = 'https://via.placeholder.com/150?text=No+Image';
                                }
                                ?>
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($prod['name']); ?></h4>
                                <p><?php echo htmlspecialchars($prod['console']); ?></p>
                                <p class="price">$<?php echo number_format($prod['price'], 2); ?></p>
                                <p class="stock">Stock: <?php echo $prod['stock']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="products.php" class="btn btn-primary">Manage All Products</a>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
