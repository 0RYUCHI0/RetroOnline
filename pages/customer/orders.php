<?php
require_once '../../config.php';
require_once '../../classes/Order.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$user_id = SessionManager::getUserId();
$order = new Order();

$orders = $order->getOrdersByUser($user_id);
$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="orders-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub</h1>
            </div>
            <div class="navbar-menu">
                <a href="shop.php" class="nav-link">Shop</a>
                <a href="wishlist.php" class="nav-link">❤️ Wishlist</a>
                <a href="cart.php" class="nav-link">Cart</a>
                <a href="orders.php" class="nav-link active">Orders</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="auth.php?action=logout" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>My Orders</h2>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <p>You haven't placed any orders yet.</p>
                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $ord): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3>Order #<?php echo $ord['order_id']; ?></h3>
                            <span class="status-badge status-<?php echo htmlspecialchars($ord['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($ord['status'])); ?>
                            </span>
                        </div>
                        <div class="order-details">
                            <div class="detail-row">
                                <span class="label">Order Date:</span>
                                <span><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Total Amount:</span>
                                <span class="amount">$<?php echo number_format($ord['total_amount'], 2); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Shipping To:</span>
                                <span><?php echo htmlspecialchars($ord['recipient_name'] . ', ' . $ord['city']); ?></span>
                            </div>
                        </div>
                        <div class="order-items">
                            <h4>Items:</h4>
                            <?php
                            $items = $order->getOrderItems($ord['order_id']);
                            foreach ($items as $item):
                                $tracking = $order->getOrderTracking($item['order_item_id']);
                            ?>
                                <div class="item-row">
                                    <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="qty">x<?php echo $item['quantity']; ?></span>
                                    <span class="price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    <?php if ($tracking): ?>
                                        <span class="tracking-info">
                                            Tracking: <?php echo !empty($tracking['tracking_number']) ? htmlspecialchars($tracking['tracking_number']) : 'N/A'; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="order-actions">
                            <a href="order-detail.php?id=<?php echo $ord['order_id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
