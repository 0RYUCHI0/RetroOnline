<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/Order.php';
require_once '../../classes/Address.php';

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
$address = new Address();

$seller_order_items = $order->getSellerOrderItems($seller_id);

// Group orders by order_id
$orders_grouped = [];
foreach ($seller_order_items as $item) {
    if (!isset($orders_grouped[$item['order_id']])) {
        $order_data = $order->getOrderById($item['order_id']);
        $orders_grouped[$item['order_id']] = [
            'order_id' => $item['order_id'],
            'customer_name' => $item['customer_name'],
            'shipping_address' => $order_data,
            'items' => []
        ];
    }
    $orders_grouped[$item['order_id']]['items'][] = $item;
}

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
    <style>
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .orders-table thead {
            background: #f8f9fa;
            font-weight: 600;
        }

        .orders-table th {
            padding: 15px;
            text-align: left;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .orders-table tbody tr:hover {
            background: #f8f9fa;
            cursor: pointer;
        }

        .order-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-delivered {
            background: #d4edda;
            color: #155724;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            width: 90%;
            position: relative;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        .modal-header {
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .modal-header h3 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .modal-header .order-info-line {
            font-size: 13px;
            color: #666;
        }

        .customer-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .detail-section h5 {
            margin: 0 0 12px 0;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
        }

        .detail-field {
            display: block;
            margin-bottom: 10px;
        }

        .detail-field strong {
            color: #333;
            display: block;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .detail-field span {
            color: #666;
            display: block;
        }

        .order-items-section h5 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background: #e9ecef;
        }

        .items-table th {
            padding: 10px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 13px;
        }

        .modal-footer {
            text-align: right;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .modal-footer .btn {
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
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
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders_grouped as $order_data): ?>
                        <tr onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($order_data)); ?>)" style="cursor: pointer;">
                            <td>#<?php echo $order_data['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order_data['customer_name']); ?></td>
                            <td><?php echo count($order_data['items']); ?> item<?php echo count($order_data['items']) !== 1 ? 's' : ''; ?></td>
                            <td>
                                $<?php 
                                $total = 0;
                                foreach ($order_data['items'] as $item) {
                                    $total += $item['price'] * $item['quantity'];
                                }
                                echo number_format($total, 2);
                                ?>
                            </td>
                            <td>
                                <?php 
                                $status = $order_data['items'][0]['order_status'] ?? 'pending';
                                $badge_class = 'badge-' . $status;
                                ?>
                                <span class="order-badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td>
                                <button class="btn btn-small btn-info" onclick="event.stopPropagation(); openOrderModal(<?php echo htmlspecialchars(json_encode($order_data)); ?>)">View Details</button>
                                <button class="btn btn-small btn-warning" onclick="event.stopPropagation(); openTrackingModal(<?php echo $order_data['items'][0]['order_item_id']; ?>)">Update Tracking</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Order Details Modal -->
            <div id="orderModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeOrderModal()">&times;</span>
                    
                    <div class="modal-header">
                        <h3>Order Details</h3>
                        <div class="order-info-line" id="orderInfoLine"></div>
                    </div>

                    <!-- Customer Details -->
                    <div class="customer-details">
                        <div class="details-grid">
                            <div class="detail-section">
                                <h5>Customer Information</h5>
                                <div class="detail-field">
                                    <strong>Name:</strong>
                                    <span id="modalCustomerName"></span>
                                </div>
                                <div class="detail-field">
                                    <strong>Phone:</strong>
                                    <span id="modalCustomerPhone"></span>
                                </div>
                            </div>
                            <div class="detail-section">
                                <h5>Shipping Address</h5>
                                <div class="detail-field">
                                    <strong>Recipient:</strong>
                                    <span id="modalRecipient"></span>
                                </div>
                                <div class="detail-field">
                                    <strong>Address:</strong>
                                    <span id="modalAddress"></span>
                                </div>
                                <div class="detail-field">
                                    <strong>City/State:</strong>
                                    <span id="modalCityState"></span>
                                </div>
                                <div class="detail-field">
                                    <strong>Postal Code:</strong>
                                    <span id="modalPostalCode"></span>
                                </div>
                                <div class="detail-field">
                                    <strong>Country:</strong>
                                    <span id="modalCountry"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="order-items-section">
                        <h5>Order Items</h5>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="closeOrderModal()">Close</button>
                    </div>
                </div>
            </div>

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
        let currentOrder = null;

        function openOrderModal(orderData) {
            currentOrder = orderData;
            
            // Populate header
            document.getElementById('orderInfoLine').textContent = 
                'Order #' + orderData.order_id + ' | ' + orderData.items.length + ' item(s)';
            
            // Populate customer info
            document.getElementById('modalCustomerName').textContent = orderData.customer_name || 'N/A';
            document.getElementById('modalCustomerPhone').textContent = orderData.shipping_address?.phone || 'N/A';
            
            // Populate shipping address
            document.getElementById('modalRecipient').textContent = orderData.shipping_address?.recipient_name || 'N/A';
            
            const addr = (orderData.shipping_address?.address_line1 || '') + 
                (orderData.shipping_address?.address_line2 ? ', ' + orderData.shipping_address.address_line2 : '');
            document.getElementById('modalAddress').textContent = addr || 'N/A';
            
            const cityState = (orderData.shipping_address?.city || '') + 
                (orderData.shipping_address?.state ? ', ' + orderData.shipping_address.state : '');
            document.getElementById('modalCityState').textContent = cityState || 'N/A';
            
            document.getElementById('modalPostalCode').textContent = orderData.shipping_address?.postal_code || 'N/A';
            document.getElementById('modalCountry').textContent = orderData.shipping_address?.country || 'N/A';
            
            // Populate order items
            const itemsBody = document.getElementById('orderItemsBody');
            itemsBody.innerHTML = '';
            
            orderData.items.forEach(item => {
                const row = document.createElement('tr');
                const total = item.price * item.quantity;
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                    <td>$${parseFloat(total).toFixed(2)}</td>
                    <td><span class="order-badge badge-${item.order_status}">${item.order_status}</span></td>
                `;
                itemsBody.appendChild(row);
            });
            
            // Show modal
            document.getElementById('orderModal').classList.add('show');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('show');
            currentOrder = null;
        }

        function openTrackingModal(itemId) {
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

        function closeTracking() {
            document.getElementById('trackingModal').style.display = 'none';
            document.getElementById('trackingForm').reset();
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const orderModal = document.getElementById('orderModal');
            const trackingModal = document.getElementById('trackingModal');
            
            if (event.target === orderModal) {
                closeOrderModal();
            }
            if (event.target === trackingModal) {
                closeTracking();
            }
        }
    </script>
</body>
</html>
