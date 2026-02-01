<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/Order.php';

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

$seller_order_items = $order->getSellerOrderItems($seller_id);
$couriers = $order->getCouriers();

$error = '';
$success = '';

// Handle tracking update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_tracking') {
        try {
            $order_item_id = (int)$_POST['order_item_id'];
            $status = $_POST['status'];
            $courier_name = isset($_POST['courier_name']) ? trim($_POST['courier_name']) : '';
            $tracking_number = isset($_POST['tracking_number']) ? trim($_POST['tracking_number']) : '';

            if (empty($courier_name) || empty($tracking_number)) {
                throw new Exception("Courier name and tracking number are required");
            }

            $order->updateTrackingStatus($order_item_id, $status, $courier_name, $tracking_number);
            
            // Refresh order items to show updated status immediately
            $seller_order_items = $order->getSellerOrderItems($seller_id);
            
            $success = 'Tracking updated successfully!';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Orders - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="orders-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Seller</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="orders.php" class="nav-link active">Orders</a>
                <a href="earnings.php" class="nav-link">Earnings</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>My Orders</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($seller_order_items)): ?>
            <p>You don't have any orders yet.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order Item ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seller_order_items as $item): ?>
                        <tr>
                            <td>#<?php echo $item['order_item_id']; ?></td>
                            <td><?php echo htmlspecialchars($item['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($item['order_status']); ?></span></td>
                            <td>
                                <button class="btn btn-small btn-info" onclick="openTracking(<?php echo $item['order_item_id']; ?>)">Update Tracking</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Tracking Modal -->
            <div id="trackingModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeTracking()">&times;</span>
                    <h3>Update Tracking</h3>
                    <form method="POST" id="trackingForm">
                        <input type="hidden" name="action" value="update_tracking">
                        <input type="hidden" id="order_item_id" name="order_item_id">
                        <div class="form-group">
                            <label>Courier Name</label>
                            <select id="courier_name" name="courier_name" required onchange="fillTrackingNumber()">
                                <option value="">-- Select Courier --</option>
                                <?php foreach ($couriers as $courier): ?>
                                    <option value="<?php echo htmlspecialchars($courier['name']); ?>" data-phone="<?php echo htmlspecialchars($courier['phone'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($courier['name']); ?> (<?php echo htmlspecialchars($courier['phone'] ?? 'N/A'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tracking Number</label>
                            <input type="text" id="tracking_number" name="tracking_number" placeholder="e.g., 1234567890" required>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select id="status" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="pending">Pending</option>
                                <option value="shipped">Shipped</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="closeTracking()">Cancel</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <script>
        function fillTrackingNumber() {
            const courierSelect = document.getElementById('courier_name');
            const selectedOption = courierSelect.options[courierSelect.selectedIndex];
            const phone = selectedOption.getAttribute('data-phone');
            
            if (phone) {
                document.getElementById('tracking_number').value = phone;
            } else {
                document.getElementById('tracking_number').value = '';
            }
        }

        function openTracking(itemId) {
            // Reset form first
            document.getElementById('trackingForm').reset();
            
            // Set the order item ID
            document.getElementById('order_item_id').value = itemId;
            
            // Set dropdowns and inputs to empty/default
            document.getElementById('courier_name').selectedIndex = 0;
            document.getElementById('tracking_number').value = '';
            document.getElementById('status').selectedIndex = 0;
            
            // Show modal
            document.getElementById('trackingModal').style.display = 'block';
            
            // Focus on courier dropdown
            document.getElementById('courier_name').focus();
        }

        function closeTracking() {
            document.getElementById('trackingModal').style.display = 'none';
            document.getElementById('trackingForm').reset();
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('trackingModal');
            if (event.target === modal) {
                closeTracking();
            }
        }
    </script>
</body>
</html>
