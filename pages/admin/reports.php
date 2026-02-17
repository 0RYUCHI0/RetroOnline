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
$order = new Order();
$product_obj = new Product();

$all_sellers = $seller_obj->getAllSellers();
$all_orders = $order->getAllOrders();

// Calculate revenue per seller
$seller_revenue = [];
foreach ($all_sellers as $seller) {
    $seller_id = $seller['seller_id'];
    $revenue = 0;
    $order_items = $order->getSellerOrderItems($seller_id);
    
    foreach ($order_items as $item) {
        $revenue += $item['price'] * $item['quantity'];
    }
    
    $seller_revenue[] = [
        'seller_id' => $seller_id,
        'store_name' => $seller['store_name'],
        'owner_name' => $seller['name'],
        'status' => $seller['status'],
        'revenue' => $revenue,
        'order_count' => count($order_items)
    ];
}

// Sort by revenue descending
usort($seller_revenue, function($a, $b) {
    return $b['revenue'] <=> $a['revenue'];
});

// Calculate totals
$total_revenue = array_sum(array_column($seller_revenue, 'revenue'));
$total_orders = count($all_orders);
$total_sellers = count($all_sellers);

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - RetroGameHub Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .report-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-weight: 600;
            color: #333;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .report-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .report-section h3 {
            margin: 0 0 20px 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .report-table thead {
            background: #f0f0f0;
        }

        .report-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
            font-size: 13px;
        }

        .report-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .report-table tbody tr:hover {
            background: #f9f9f9;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .summary-card h4 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .export-btn {
            margin: 15px 0;
        }

        @media (max-width: 768px) {
            .report-filters {
                flex-direction: column;
            }

            .summary-cards {
                grid-template-columns: 1fr;
            }

            .report-table {
                font-size: 12px;
            }

            .report-table th, .report-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body class="admin-reports-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="reports.php" class="nav-link active">Reports</a>
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
            <h2>Reports & Analytics</h2>
            <p>Detailed platform insights and revenue analytics</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h4>Total Revenue</h4>
                <div class="value">$<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="summary-card">
                <h4>Total Orders</h4>
                <div class="value"><?php echo $total_orders; ?></div>
            </div>
            <div class="summary-card">
                <h4>Average Order Value</h4>
                <div class="value">$<?php echo $total_orders > 0 ? number_format($total_revenue / $total_orders, 2) : '0.00'; ?></div>
            </div>
            <div class="summary-card">
                <h4>Active Sellers</h4>
                <div class="value"><?php echo $total_sellers; ?></div>
            </div>
        </div>

        <!-- Revenue Per Seller Report -->
        <div class="report-section">
            <h3>Revenue Per Seller</h3>
            <?php if (empty($seller_revenue)): ?>
                <p>No seller revenue data available</p>
            <?php else: ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Store Name</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Total Revenue</th>
                            <th>Order Count</th>
                            <th>Avg Order Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($seller_revenue as $seller): 
                        ?>
                            <tr>
                                <td><?php echo $rank++; ?></td>
                                <td><strong><?php echo htmlspecialchars($seller['store_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($seller['owner_name']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($seller['status']); ?>">
                                        <?php echo htmlspecialchars($seller['status']); ?>
                                    </span>
                                </td>
                                <td><strong>$<?php echo number_format($seller['revenue'], 2); ?></strong></td>
                                <td><?php echo $seller['order_count']; ?></td>
                                <td>$<?php echo $seller['order_count'] > 0 ? number_format($seller['revenue'] / $seller['order_count'], 2) : '0.00'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- All Orders Detailed Report -->
        <div class="report-section">
            <h3>All Orders Detail</h3>
            <?php if (empty($all_orders)): ?>
                <p>No orders available</p>
            <?php else: ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_orders as $ord): ?>
                            <tr>
                                <td><strong>#<?php echo $ord['order_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                                <td><strong>$<?php echo number_format($ord['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($ord['status']); ?>">
                                        <?php echo htmlspecialchars($ord['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $ord['item_count']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($ord['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Platform Commission Report -->
        <div class="report-section">
            <h3>Platform Commission Analysis</h3>
            <?php 
                $platform_commission_percent = PLATFORM_COMMISSION;
                $platform_earnings = ($total_revenue * $platform_commission_percent) / 100;
                $seller_earnings = $total_revenue - $platform_earnings;
            ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Amount</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Platform Revenue</strong></td>
                        <td><strong>$<?php echo number_format($total_revenue, 2); ?></strong></td>
                        <td>100%</td>
                    </tr>
                    <tr style="background: #e8f5e9;">
                        <td><strong>Seller Earnings</strong></td>
                        <td><strong>$<?php echo number_format($seller_earnings, 2); ?></strong></td>
                        <td><?php echo (100 - $platform_commission_percent); ?>%</td>
                    </tr>
                    <tr style="background: #fff3e0;">
                        <td><strong>Platform Commission</strong></td>
                        <td><strong>$<?php echo number_format($platform_earnings, 2); ?></strong></td>
                        <td><?php echo $platform_commission_percent; ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
