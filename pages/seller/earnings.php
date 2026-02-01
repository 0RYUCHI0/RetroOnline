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
$user_name = SessionManager::get('user_name');

// Get overall earnings
$earnings = $order->getSellerEarnings($seller_id);

// Get database connection for custom queries
$db = Database::getInstance()->getConnection();

// Handle filter parameters
$filter_type = $_GET['filter'] ?? 'monthly';
$filter_date = $_GET['date'] ?? date('Y-m');

// Get sales trends based on filter
$sales_data = [];
$chart_labels = [];
$chart_sales = [];
$chart_commissions = [];

if ($filter_type === 'daily') {
    // Get last 30 days
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('M d', strtotime($date));
        
        $stmt = $db->prepare("
            SELECT 
                SUM(oi.price * oi.quantity) as total_sales,
                SUM(c.commission_amount) as total_commissions
            FROM order_items oi
            LEFT JOIN commissions c ON oi.order_item_id = c.order_item_id
            WHERE oi.seller_id = ? AND DATE(oi.created_at) = ?
        ");
        $stmt->bind_param("is", $seller_id, $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $sales = $result['total_sales'] ?? 0;
        $commission = $result['total_commissions'] ?? 0;
        
        $chart_sales[] = round($sales, 2);
        $chart_commissions[] = round($commission, 2);
    }
} elseif ($filter_type === 'weekly') {
    // Get last 12 weeks
    for ($i = 11; $i >= 0; $i--) {
        $end_date = date('Y-m-d', strtotime("-$i weeks"));
        $start_date = date('Y-m-d', strtotime("-" . ($i + 1) . " weeks"));
        $chart_labels[] = 'Week of ' . date('M d', strtotime($start_date));
        
        $stmt = $db->prepare("
            SELECT 
                SUM(oi.price * oi.quantity) as total_sales,
                SUM(c.commission_amount) as total_commissions
            FROM order_items oi
            LEFT JOIN commissions c ON oi.order_item_id = c.order_item_id
            WHERE oi.seller_id = ? AND DATE(oi.created_at) BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $seller_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $sales = $result['total_sales'] ?? 0;
        $commission = $result['total_commissions'] ?? 0;
        
        $chart_sales[] = round($sales, 2);
        $chart_commissions[] = round($commission, 2);
    }
} else { // monthly
    // Get last 12 months
    for ($i = 11; $i >= 0; $i--) {
        $month_date = date('Y-m', strtotime("-$i months"));
        $chart_labels[] = date('M Y', strtotime($month_date . '-01'));
        
        $stmt = $db->prepare("
            SELECT 
                SUM(oi.price * oi.quantity) as total_sales,
                SUM(c.commission_amount) as total_commissions
            FROM order_items oi
            LEFT JOIN commissions c ON oi.order_item_id = c.order_item_id
            WHERE oi.seller_id = ? AND DATE_FORMAT(oi.created_at, '%Y-%m') = ?
        ");
        $stmt->bind_param("is", $seller_id, $month_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $sales = $result['total_sales'] ?? 0;
        $commission = $result['total_commissions'] ?? 0;
        
        $chart_sales[] = round($sales, 2);
        $chart_commissions[] = round($commission, 2);
    }
}

// Get top performing products
$stmt = $db->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.console,
        COUNT(oi.order_item_id) as units_sold,
        SUM(oi.price * oi.quantity) as total_revenue
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    WHERE p.seller_id = ?
    GROUP BY p.product_id
    ORDER BY total_revenue DESC
    LIMIT 5
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate forecast (simple linear trend)
$forecast_data = [];
if (count($chart_sales) > 1) {
    $avg_daily = array_sum($chart_sales) / count($chart_sales);
    $forecast_data = array_fill(0, 7, round($avg_daily, 2));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings & Reports - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        .earnings-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-group label {
            font-weight: 500;
            color: #333;
        }

        .filter-group select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #e94560;
        }

        .earnings-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .earnings-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #2ecc71;
            transition: transform 0.3s ease;
        }

        .earnings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .earnings-card.commission {
            border-left-color: #e94560;
        }

        .earnings-card.net {
            border-left-color: #3498db;
            background: linear-gradient(135deg, #ecf0f1 0%, #ffffff 100%);
        }

        .earnings-card.orders {
            border-left-color: #f39c12;
        }

        .earnings-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .earnings-card .amount {
            font-size: 32px;
            font-weight: bold;
            color: #1a1a2e;
            margin: 0;
        }

        .earnings-card.commission .amount {
            color: #e94560;
        }

        .earnings-card.net .amount {
            color: #3498db;
        }

        .earnings-card.orders .amount {
            color: #f39c12;
        }

        .earnings-card .subtext {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }

        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .chart-container h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 18px;
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .breakdown-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e94560;
        }

        .breakdown-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .breakdown-table tr:hover {
            background: #f9f9f9;
        }

        .breakdown-table .total-row {
            background: #f0f0f0;
            font-weight: 600;
        }

        .breakdown-table .total-row td {
            border-top: 2px solid #e94560;
            border-bottom: 2px solid #e94560;
        }

        .top-products {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .top-products h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 18px;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-name {
            flex: 1;
        }

        .product-name strong {
            display: block;
            margin-bottom: 4px;
        }

        .product-name small {
            color: #999;
        }

        .product-stats {
            display: flex;
            gap: 30px;
            text-align: right;
        }

        .stat {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-weight: bold;
            color: #1a1a2e;
            font-size: 18px;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
        }

        .forecast-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 40px;
        }

        .forecast-section h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
        }

        .forecast-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .forecast-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .forecast-day {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 8px;
        }

        .forecast-amount {
            font-size: 20px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }

            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                flex-direction: column;
            }

            .product-stats {
                flex-direction: column;
                text-align: left;
                gap: 10px;
            }

            .forecast-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            }
        }
    </style>
</head>
<body class="earnings-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Seller</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="earnings.php" class="nav-link active">Earnings</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container earnings-container">
        <div class="page-header">
            <h2>📊 Earnings & Revenue Reports</h2>
            <p>Track your sales, commissions, and earnings over time</p>
        </div>

        <!-- Filter Section -->
        <form method="GET" class="filter-section">
            <div class="filter-group">
                <label for="filter">View by:</label>
                <select id="filter" name="filter" onchange="this.form.submit()">
                    <option value="daily" <?php echo $filter_type === 'daily' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="weekly" <?php echo $filter_type === 'weekly' ? 'selected' : ''; ?>>Last 12 Weeks</option>
                    <option value="monthly" <?php echo $filter_type === 'monthly' ? 'selected' : ''; ?>>Last 12 Months</option>
                </select>
            </div>
        </form>

        <!-- Summary Cards -->
        <div class="earnings-summary">
            <div class="earnings-card">
                <h3>💰 Gross Sales</h3>
                <p class="amount">$<?php echo number_format($earnings['total_sales'] ?? 0, 2); ?></p>
                <p class="subtext">From all orders</p>
            </div>
            <div class="earnings-card commission">
                <h3>📌 Platform Commission</h3>
                <p class="amount">-$<?php echo number_format($earnings['total_commissions'] ?? 0, 2); ?></p>
                <p class="subtext"><?php echo PLATFORM_COMMISSION; ?>% of sales</p>
            </div>
            <div class="earnings-card net">
                <h3>✓ Net Earnings</h3>
                <p class="amount">$<?php echo number_format($earnings['net_earnings'] ?? 0, 2); ?></p>
                <p class="subtext">Your income</p>
            </div>
            <div class="earnings-card orders">
                <h3>📦 Total Orders</h3>
                <p class="amount"><?php echo $earnings['total_orders'] ?? 0; ?></p>
                <p class="subtext">Completed sales</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <h3>Sales Trend</h3>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <h3>Commission Breakdown</h3>
                <div class="chart-wrapper">
                    <canvas id="commissionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <?php if (!empty($top_products)): ?>
            <div class="top-products">
                <h3>🏆 Top Performing Products</h3>
                <?php foreach ($top_products as $product): ?>
                    <div class="product-item">
                        <div class="product-name">
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <small><?php echo htmlspecialchars($product['console']); ?></small>
                        </div>
                        <div class="product-stats">
                            <div class="stat">
                                <span class="stat-value"><?php echo $product['units_sold'] ?? 0; ?></span>
                                <span class="stat-label">Units Sold</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">$<?php echo number_format($product['total_revenue'] ?? 0, 2); ?></span>
                                <span class="stat-label">Revenue</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Forecast Section -->
        <?php if (!empty($forecast_data)): ?>
            <div class="forecast-section">
                <h3>📈 7-Day Forecast</h3>
                <p style="margin: 0 0 20px 0; opacity: 0.9;">Based on your historical sales average</p>
                <div class="forecast-grid">
                    <?php for ($i = 1; $i <= 7; $i++): ?>
                        <div class="forecast-item">
                            <div class="forecast-day"><?php echo date('M d', strtotime("+$i days")); ?></div>
                            <div class="forecast-amount">$<?php echo number_format($forecast_data[$i-1] ?? 0, 2); ?></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Earnings Breakdown Table -->
        <div class="chart-container">
            <h3>Earnings Breakdown</h3>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Amount</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gross Revenue (from orders)</td>
                        <td>$<?php echo number_format($earnings['total_sales'] ?? 0, 2); ?></td>
                        <td><?php echo $earnings['total_orders'] ?? 0; ?> orders</td>
                    </tr>
                    <tr>
                        <td>Platform Commission Fee</td>
                        <td>-$<?php echo number_format($earnings['total_commissions'] ?? 0, 2); ?></td>
                        <td><?php echo PLATFORM_COMMISSION; ?>% per order</td>
                    </tr>
                    <tr class="total-row">
                        <td>Your Net Earnings</td>
                        <td>$<?php echo number_format($earnings['net_earnings'] ?? 0, 2); ?></td>
                        <td>Ready to withdraw</td>
                    </tr>
                </tbody>
            </table>
            <p style="color: #999; font-size: 13px; margin-top: 15px;">Commission calculations are updated in real-time as orders are placed. Net earnings represent your profit after platform fees.</p>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <script>
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Sales Revenue',
                    data: <?php echo json_encode($chart_sales); ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#2ecc71',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 13,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toFixed(2);
                            }
                        },
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 4,
                        titleFont: {
                            size: 13,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(0);
                            },
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Commission Chart
        const commissionCtx = document.getElementById('commissionChart').getContext('2d');
        const commissionChart = new Chart(commissionCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Commission Paid',
                    data: <?php echo json_encode($chart_commissions); ?>,
                    backgroundColor: 'rgba(233, 69, 96, 0.7)',
                    borderColor: '#e94560',
                    borderWidth: 1,
                    borderRadius: 4,
                    hoverBackgroundColor: '#d43552'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 13,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toFixed(2);
                            }
                        },
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 4
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(0);
                            },
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
