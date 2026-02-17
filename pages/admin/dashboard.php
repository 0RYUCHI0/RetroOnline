<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/Order.php';
require_once '../../classes/Product.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$seller_obj = new Seller();
$order_obj = new Order();
$product_obj = new Product();

$all_sellers = $seller_obj->getAllSellers();
$all_orders = $order_obj->getAllOrders();
$all_products = $product_obj->getAllProducts();

// Calculate platform revenue
$total_revenue = 0;
foreach ($all_orders as $ord) {
    $total_revenue += $ord['total_amount'];
}

// Get top sellers by revenue
$top_sellers = $seller_obj->getTopSellersByRevenue();

// Get top selling products
$top_products = $product_obj->getTopSellingProducts();

// Get top locations
$top_locations = $order_obj->getTopLocations();


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
                <a href="reports.php" class="nav-link">Reports</a>
                <a href="activity-logs.php" class="nav-link">Activity Logs</a>
                <a href="applications.php" class="nav-link">Applications</a>
                <a href="sellers.php" class="nav-link">Sellers</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Dashboard Overview</h2>
            <p>Welcome, <?php echo htmlspecialchars($user_name); ?>! Quick overview of your platform.</p>
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
                <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                <p>Platform Revenue</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($all_products); ?></h3>
                <p>Total Products</p>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="dashboard-section">
            <h3>Top Sellers by Revenue</h3>
            <?php if (empty($top_sellers)): ?>
                <p>No seller data available</p>
            <?php else: ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Store Name</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_sellers as $seller): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($seller['store_name']); ?></td>
                                <td>$<?php echo number_format($seller['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Top Products -->
        <div class="dashboard-section">
            <h3>Top Selling Products</h3>
            <?php if (empty($top_products)): ?>
                <p>No products available</p>
            <?php else: ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Total Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['name']); ?></td>
                                <td><?php echo $prod['total_sold']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Top Locations -->
        <div class="dashboard-section">
            <h3>Top Locations by Orders</h3>
            <?php if (empty($top_locations)): ?>
                <p>No location data available</p>
            <?php else: ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>City</th>
                            <th>State/Country</th>
                            <th>Total Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_locations as $location): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($location['city']); ?></td>
                                <td><?php echo htmlspecialchars($location['state'] . ', ' . $location['country']); ?></td>
                                <td><?php echo $location['order_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-section">
            <h3>Navigate</h3>
            <div class="action-buttons">
                <a href="reports.php" class="btn btn-primary">View Detailed Reports</a>
                <a href="sellers.php" class="btn btn-primary">Manage Sellers</a>
                <a href="orders.php" class="btn btn-primary">Manage Orders</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
