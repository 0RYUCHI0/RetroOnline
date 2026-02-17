<?php
require_once '../../config.php';
require_once '../../functions/notification-helpers.php';
require_once '../../classes/Order.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

$order = new Order();
$order_data = $order->getOrderById($order_id);

if (!$order_data) {
    header('Location: orders.php');
    exit;
}

// Verify ownership
if ($order_data['user_id'] !== SessionManager::getUserId()) {
    die('Unauthorized access');
}

$order_items = $order->getOrderItems($order_id);

$user_name = SessionManager::get('user_name');

// Get customer notifications
$customer_id = SessionManager::getUserId();
$notifications = getCustomerNotifications($customer_id);
$unread_notifications = count($notifications);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="order-detail-page">
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

    <!-- Notification Bar (Fixed Top-Right) -->
    <?php echo renderNotificationBar($notifications); ?>

    <div class="container">
        <div class="page-header">
            <h2>Order #<?php echo $order_id; ?></h2>
        </div>

        <div class="order-detail-layout">
            <!-- Order Status -->
            <div class="order-status-section" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h3>Order Status</h3>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                    <div style="text-align: center;">
                        <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem;">Pending</span>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem;">Confirmed</p>
                    </div>
                    <span style="flex-grow: 1; height: 2px; background: #ccc; margin: 0 1rem;"></span>
                    <div style="text-align: center;">
                        <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem;">Processing</span>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem;">Being Prepared</p>
                    </div>
                    <span style="flex-grow: 1; height: 2px; background: #ccc; margin: 0 1rem;"></span>
                    <div style="text-align: center;">
                        <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem;">Shipped</span>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem;">On the Way</p>
                    </div>
                    <span style="flex-grow: 1; height: 2px; background: #ccc; margin: 0 1rem;"></span>
                    <div style="text-align: center;">
                        <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem;">Delivered</span>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem;">Completed</p>
                    </div>
                </div>
                <div style="margin-top: 1.5rem; padding: 1rem; background-color: #f8f9fa; border-radius: 4px;">
                    <p style="margin: 0.5rem 0;">
                        <strong>Current Status:</strong>
                        <span class="badge" style="margin-left: 1rem;"><?php echo ucfirst(htmlspecialchars($order_data['status'])); ?></span>
                    </p>
                </div>
            </div>

            <!-- Order Information -->
            <div class="order-info-section" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h3>Order Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;">
                    <div>
                        <h4 style="margin-bottom: 1rem;">Order Details</h4>
                        <p><strong>Order ID:</strong> #<?php echo $order_data['order_id']; ?></p>
                        <p><strong>Order Date:</strong> <?php echo date('M d, Y h:i A', strtotime($order_data['created_at'])); ?></p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($order_data['total_amount'], 2); ?></p>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 1rem;">Shipping Address</h4>
                        <p><strong><?php echo htmlspecialchars($order_data['recipient_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($order_data['address_line1']); ?></p>
                        <p><?php echo htmlspecialchars($order_data['city'] . ', ' . $order_data['state']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items-section" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h3>Items Ordered</h3>
                <table class="cart-table" style="margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Seller</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Tracking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): 
                            $tracking = new Order();
                            $tracking_data = $tracking->getOrderTracking($item['order_item_id']);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['store_name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <?php if ($tracking_data && !empty($tracking_data['tracking_number'])): ?>
                                        <small><?php echo htmlspecialchars($tracking_data['tracking_number']); ?></small><br>
                                        <small><?php echo htmlspecialchars($tracking_data['courier_name']); ?></small>
                                    <?php else: ?>
                                        <small style="color: #999;">Pending</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary -->
            <div class="order-summary-section" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3>Order Summary</h3>
                <div style="margin-top: 1rem;">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($order_data['total_amount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>FREE</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($order_data['total_amount'], 2); ?></span>
                    </div>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                    <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
