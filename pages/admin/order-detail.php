<?php
require_once '../../config.php';
require_once '../../classes/Order.php';
require_once '../../classes/Seller.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: ../customer/auth.php');
    exit;
}

$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

$order_obj = new Order();
$seller_obj = new Seller();
$user_name = SessionManager::get('user_name');

try {
    $order = $order_obj->getOrderById($order_id);
    if (!$order) {
        header('Location: orders.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: orders.php');
    exit;
}

// Get order items with tracking
$order_items = $order_obj->getOrderItems($order_id);
foreach ($order_items as &$item) {
    $item['tracking'] = $order_obj->getOrderTracking($item['order_item_id']);
}

// Get customer info
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $order['user_id']);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Get commission info
$stmt = $db->prepare("
    SELECT c.*, oi.price
    FROM commissions c
    JOIN order_items oi ON c.order_item_id = oi.order_item_id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$commissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_commission = array_sum(array_column($commissions, 'commission_amount')) ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - RetroGameHub Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="order-detail-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="applications.php" class="nav-link">Applications</a>
                <a href="sellers.php" class="nav-link">Sellers</a>
                <a href="orders.php" class="nav-link active">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2>Order #<?php echo $order['order_id']; ?></h2>
                    <p><?php echo date('M d, Y \a\t H:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
            </div>
        </div>

        <!-- Order Status Section -->
        <div class="detail-section">
            <h3>Order Status</h3>
            <div class="status-display">
                <div class="status-box">
                    <p><strong>Current Status:</strong> 
                        <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                        </span>
                    </p>
                    <p><strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo date('M d, Y H:i A', strtotime($order['updated_at'])); ?></p>
                </div>
            </div>
        </div>

        <div class="two-column">
            <!-- Customer Information -->
            <div class="detail-section">
                <h3>Customer Information</h3>
                <div class="info-box">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="detail-section">
                <h3>Shipping Address</h3>
                <div class="info-box">
                    <p><strong>Recipient:</strong> <?php echo htmlspecialchars($order['recipient_name'] ?? 'N/A'); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address_line1'] ?? 'N/A'); ?></p>
                    <?php if (!empty($order['address_line2'])): ?>
                        <p><?php echo htmlspecialchars($order['address_line2']); ?></p>
                    <?php endif; ?>
                    <p><strong>City:</strong> <?php echo htmlspecialchars($order['city'] ?? 'N/A'); ?>, <?php echo htmlspecialchars($order['state'] ?? 'N/A'); ?> <?php echo htmlspecialchars($order['postal_code'] ?? 'N/A'); ?></p>
                    <p><strong>Country:</strong> <?php echo htmlspecialchars($order['country'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <!-- Order Items Section -->
        <div class="detail-section">
            <h3>Order Items</h3>
            <div class="items-table">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Seller</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th>Tracking</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['store_name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <?php if ($item['tracking']): ?>
                                        <small>
                                            <?php echo htmlspecialchars($item['tracking']['tracking_number']); ?>
                                            <br>
                                            <em><?php echo htmlspecialchars($item['tracking']['courier_name']); ?></em>
                                        </small>
                                    <?php else: ?>
                                        <span class="no-tracking">No tracking</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['tracking']): ?>
                                        <span class="status-badge status-<?php echo htmlspecialchars($item['tracking']['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($item['tracking']['status']))); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary Section -->
        <div class="detail-section">
            <h3>Order Summary</h3>
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format(array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $order_items)), 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Platform Commission (5%):</span>
                    <span>$<?php echo number_format($total_commission, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Order Total:</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Commission Breakdown -->
        <div class="detail-section">
            <h3>Commission Breakdown</h3>
            <?php if (empty($commissions)): ?>
                <p class="empty-message">No commission records</p>
            <?php else: ?>
                <table class="commissions-table">
                    <thead>
                        <tr>
                            <th>Seller</th>
                            <th>Item Price</th>
                            <th>Commission %</th>
                            <th>Commission Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commissions as $commission): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['store_name'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($commission['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($commission['commission_percent']); ?>%</td>
                                <td>$<?php echo number_format($commission['commission_amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <style>
        .order-detail-page {
            background: #1a1a2e;
            color: #fff;
        }

        .page-header {
            margin: 30px 0;
            padding-bottom: 20px;
            border-bottom: 2px solid #e94560;
        }

        .page-header h2 {
            color: #e94560;
            margin: 0;
            font-size: 2em;
        }

        .page-header p {
            color: #888;
            margin: 5px 0 0 0;
        }

        .detail-section {
            background: #16213e;
            padding: 30px;
            margin: 30px 0;
            border-radius: 8px;
            border-left: 4px solid #e94560;
        }

        .detail-section h3 {
            color: #e94560;
            margin-top: 0;
        }

        .status-display {
            background: #0f3460;
            padding: 20px;
            border-radius: 4px;
        }

        .status-box p {
            margin: 12px 0;
            color: #aaa;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9em;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.2);
            color: #f39c12;
        }

        .status-processing {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }

        .status-shipped {
            background: rgba(155, 89, 182, 0.2);
            color: #9b59b6;
        }

        .status-delivered {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }

        .status-in_transit {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }

        .info-box {
            background: #0f3460;
            padding: 20px;
            border-radius: 4px;
        }

        .info-box p {
            margin: 10px 0;
            color: #aaa;
        }

        .info-box strong {
            color: #e94560;
        }

        .items-table {
            overflow-x: auto;
            margin-top: 20px;
        }

        .items-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead {
            background: #0f3460;
        }

        .items-table th {
            padding: 15px;
            text-align: left;
            color: #e94560;
            font-weight: bold;
            border-bottom: 2px solid #e94560;
        }

        .items-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #0f3460;
            color: #aaa;
        }

        .items-table tbody tr:hover {
            background: #0f3460;
        }

        .no-tracking {
            color: #888;
            font-style: italic;
        }

        .summary-box {
            background: #0f3460;
            padding: 20px;
            border-radius: 4px;
            max-width: 400px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #1a1a2e;
            color: #aaa;
        }

        .summary-row.total {
            border-bottom: none;
            padding: 15px 0;
            font-weight: bold;
            font-size: 1.2em;
            color: #e94560;
        }

        .commissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .commissions-table thead {
            background: #0f3460;
        }

        .commissions-table th {
            padding: 15px;
            text-align: left;
            color: #e94560;
            font-weight: bold;
            border-bottom: 2px solid #e94560;
        }

        .commissions-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #0f3460;
            color: #aaa;
        }

        .commissions-table tbody tr:hover {
            background: #0f3460;
        }

        .empty-message {
            color: #888;
            text-align: center;
            padding: 30px;
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
            .two-column {
                grid-template-columns: 1fr;
            }

            .summary-box {
                max-width: 100%;
            }

            .items-table {
                font-size: 0.9em;
            }

            .items-table th,
            .items-table td {
                padding: 10px;
            }
        }
    </style>
</body>
</html>
