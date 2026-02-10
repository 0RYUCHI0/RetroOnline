<?php
require_once '../../config.php';
require_once '../../classes/Order.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$order = new Order();
$all_orders = $order->getAllOrders();

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="orders-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="activity-logs.php" class="nav-link">📊 Activity Logs</a>
                <a href="applications.php" class="nav-link">Applications</a>
                <a href="sellers.php" class="nav-link">Sellers</a>
                <a href="orders.php" class="nav-link active">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>All Orders</h2>
            <p>View and manage all platform orders</p>
        </div>

        <?php if (empty($all_orders)): ?>
            <div class="empty-state">
                <p>No orders yet.</p>
            </div>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_orders as $ord): ?>
                        <tr>
                            <td>#<?php echo $ord['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                            <td>$<?php echo number_format($ord['total_amount'], 2); ?></td>
                            <td><?php echo $ord['item_count']; ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($ord['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                            <td>
                                <a href="order-detail.php?id=<?php echo $ord['order_id']; ?>" class="btn btn-small btn-info">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
