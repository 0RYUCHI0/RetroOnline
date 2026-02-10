<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/Order.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$seller_obj = new Seller();
$order = new Order();

$all_sellers = $seller_obj->getAllSellers();
$all_orders = $order->getAllOrders();

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="admin-dashboard-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="activity-logs.php" class="nav-link">📊 Activity Logs</a>
                <a href="applications.php" class="nav-link">Applications</a>
                <a href="sellers.php" class="nav-link">Sellers</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Admin Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($user_name); ?>! Here's your system overview.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($all_sellers); ?></h3>
                <p>Active Sellers</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($all_orders); ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3>
                    $<?php 
                    $total = 0;
                    foreach ($all_orders as $ord) {
                        $total += $ord['total_amount'];
                    }
                    echo number_format($total, 2);
                    ?>
                </h3>
                <p>Platform Revenue</p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-section">
            <h3>Recent Orders</h3>
            <?php if (empty($all_orders)): ?>
                <p>No orders yet</p>
            <?php else: ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($all_orders, 0, 10) as $ord): ?>
                            <tr>
                                <td>#<?php echo $ord['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                                <td>$<?php echo number_format($ord['total_amount'], 2); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($ord['status']); ?></span></td>
                                <td><?php echo $ord['item_count']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
            <?php endif; ?>
        </div>

        <!-- Sellers Overview -->
        <div class="dashboard-section">
            <h3>Top Sellers</h3>
            <?php if (empty($all_sellers)): ?>
                <p>No sellers yet</p>
            <?php else: ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Store Name</th>
                            <th>Owner</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($all_sellers, 0, 5) as $seller): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($seller['store_name']); ?></td>
                                <td><?php echo htmlspecialchars($seller['name']); ?></td>
                                <td><?php echo $seller['product_count']; ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($seller['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($seller['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="sellers.php" class="btn btn-primary">View All Sellers</a>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-section">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="applications.php" class="btn btn-primary">Review Seller Applications</a>
                <a href="orders.php" class="btn btn-primary">Manage Orders</a>
                <a href="sellers.php" class="btn btn-primary">Manage Sellers</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
