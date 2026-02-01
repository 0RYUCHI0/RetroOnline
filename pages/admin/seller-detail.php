<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/Order.php';
require_once '../../classes/Review.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: ../customer/auth.php');
    exit;
}

$seller_id = $_GET['id'] ?? 0;
if (!$seller_id) {
    header('Location: sellers.php');
    exit;
}

$seller_obj = new Seller();
$order_obj = new Order();
$review_obj = new Review();
$user_name = SessionManager::get('user_name');

try {
    $seller = $seller_obj->getSellerById($seller_id);
    if (!$seller) {
        header('Location: sellers.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: sellers.php');
    exit;
}

// Get seller orders and earnings
$seller_orders = $order_obj->getSellerOrders($seller_id);
$seller_earnings = $order_obj->getSellerEarnings($seller_id);
$seller_reviews = $review_obj->getSellerReviews($seller_id);

// Calculate statistics
$total_orders = count($seller_orders);
$total_revenue = array_sum(array_column($seller_orders, 'total_amount')) ?? 0;
$average_rating = !empty($seller_reviews) ? array_sum(array_column($seller_reviews, 'rating')) / count($seller_reviews) : 0;

// Get products
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Details - RetroGameHub Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="seller-detail-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="applications.php" class="nav-link">Applications</a>
                <a href="sellers.php" class="nav-link active">Sellers</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2><?php echo htmlspecialchars($seller['store_name']); ?></h2>
                    <p>Seller ID: <?php echo $seller['seller_id']; ?></p>
                </div>
                <a href="sellers.php" class="btn btn-secondary">← Back to Sellers</a>
            </div>
        </div>

        <!-- Seller Information Section -->
        <div class="detail-section">
            <h3>Store Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Store Name</label>
                    <p><?php echo htmlspecialchars($seller['store_name']); ?></p>
                </div>
                <div class="info-item">
                    <label>Owner Name</label>
                    <p><?php echo htmlspecialchars($seller['name'] ?? 'N/A'); ?></p>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($seller['email'] ?? 'N/A'); ?></p>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <p><span class="badge"><?php echo htmlspecialchars($seller['status'] ?? 'active'); ?></span></p>
                </div>
                <div class="info-item">
                    <label>Contact Info</label>
                    <p><?php echo htmlspecialchars($seller['contact_info']); ?></p>
                </div>
                <div class="info-item">
                    <label>Joined</label>
                    <p><?php echo date('M d, Y', strtotime($seller['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($products); ?></div>
                <div class="stat-label">Active Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($average_rating, 1); ?>/5</div>
                <div class="stat-label">Average Rating</div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="detail-section">
            <h3>Products (<?php echo count($products); ?>)</h3>
            <?php if (empty($products)): ?>
                <p class="empty-message">No products yet</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                $img = '';
                                if (!empty($product['image_url'])) {
                                    if (strpos($product['image_url'], 'http') === 0) {
                                        $img = htmlspecialchars($product['image_url']);
                                    } else {
                                        $img = htmlspecialchars(SITE_URL . '/' . ltrim($product['image_url'], '/'));
                                    }
                                } else {
                                    $img = 'https://via.placeholder.com/150?text=No+Image';
                                }
                                ?>
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p class="console"><?php echo htmlspecialchars($product['console']); ?></p>
                                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                <p class="stock">Stock: <?php echo $product['stock']; ?></p>
                                <p class="condition">
                                    <span class="badge">
                                        <?php echo htmlspecialchars($product['condition']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reviews Section -->
        <div class="detail-section">
            <h3>Customer Reviews (<?php echo count($seller_reviews); ?>)</h3>
            <?php if (empty($seller_reviews)): ?>
                <p class="empty-message">No reviews yet</p>
            <?php else: ?>
                <div class="reviews-list">
                    <?php foreach ($seller_reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="stars">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <span class="star <?php echo $i < $review['rating'] ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Orders Section -->
        <div class="detail-section">
            <h3>Recent Orders (<?php echo count($seller_orders); ?>)</h3>
            <?php if (empty($seller_orders)): ?>
                <p class="empty-message">No orders yet</p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($seller_orders, 0, 10) as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span></td>
                                <td>
                                    <a href="order-detail.php?id=<?php echo $order['order_id']; ?>" class="btn btn-small btn-info">View</a>
                                </td>
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
        .seller-detail-page {
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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            background: #0f3460;
            padding: 15px;
            border-radius: 4px;
        }

        .info-item label {
            color: #e94560;
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        .info-item p {
            margin: 0;
            color: #aaa;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #e94560, #d63447);
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95em;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: #0f3460;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #1a1a2e;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-info h4 {
            margin: 0 0 8px 0;
            color: #fff;
            font-size: 0.95em;
        }

        .product-info p {
            margin: 5px 0;
            color: #aaa;
            font-size: 0.9em;
        }

        .console {
            color: #e94560;
            font-weight: bold;
        }

        .price {
            font-size: 1.2em;
            color: #2ecc71;
            font-weight: bold;
        }

        .reviews-list {
            margin-top: 20px;
        }

        .review-item {
            background: #0f3460;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 3px solid #e94560;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .stars {
            display: flex;
            gap: 3px;
        }

        .star {
            color: #666;
            font-size: 1.2em;
        }

        .star.filled {
            color: #f39c12;
        }

        .review-date {
            color: #888;
            font-size: 0.9em;
        }

        .review-comment {
            margin: 0;
            color: #aaa;
            line-height: 1.5;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .orders-table thead {
            background: #0f3460;
        }

        .orders-table th {
            padding: 15px;
            text-align: left;
            color: #e94560;
            font-weight: bold;
            border-bottom: 2px solid #e94560;
        }

        .orders-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #0f3460;
            color: #aaa;
        }

        .orders-table tbody tr:hover {
            background: #0f3460;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            background: #0f3460;
            color: #e94560;
            border: 1px solid #e94560;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
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
            .info-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</body>
</html>
