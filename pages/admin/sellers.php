<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$seller_obj = new Seller();
$all_sellers = $seller_obj->getAllSellers();

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sellers - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="sellers-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="activity-logs.php" class="nav-link">📊 Activity Logs</a>
                <a href="applications.php" class="nav-link">Applications</a>
                <a href="sellers.php" class="nav-link active">Sellers</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Manage Sellers</h2>
            <p>View and manage all active sellers</p>
        </div>

        <?php if (empty($all_sellers)): ?>
            <div class="empty-state">
                <p>No sellers yet.</p>
            </div>
        <?php else: ?>
            <table class="sellers-table">
                <thead>
                    <tr>
                        <th>Store Name</th>
                        <th>Owner</th>
                        <th>Email</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_sellers as $seller): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($seller['store_name']); ?></td>
                            <td><?php echo htmlspecialchars($seller['name']); ?></td>
                            <td><?php echo htmlspecialchars($seller['email']); ?></td>
                            <td><?php echo $seller['product_count']; ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($seller['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($seller['created_at'])); ?></td>
                            <td>
                                <a href="seller-detail.php?id=<?php echo $seller['seller_id']; ?>" class="btn btn-small btn-info">View</a>
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
