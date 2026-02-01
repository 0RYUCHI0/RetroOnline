<?php
require_once '../../config.php';
require_once '../../classes/Product.php';
require_once '../../classes/Review.php';
require_once '../../classes/Wishlist.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$product = new Product();
$review = new Review();
$wishlist = new Wishlist();
$customer_id = SessionManager::getUserId();

// Initialize filter array - sanitize all GET parameters
$filters = [];

// Search filter - trim and sanitize
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $filters['search'] = trim($_GET['search']);
}

// Console filter - validate against available consoles
if (isset($_GET['console']) && !empty($_GET['console'])) {
    $filters['console'] = $_GET['console'];
}

// Condition filter - validate against allowed values
if (isset($_GET['condition']) && !empty($_GET['condition'])) {
    $allowed_conditions = ['Mint', 'Used', 'Refurbished'];
    if (in_array($_GET['condition'], $allowed_conditions)) {
        $filters['condition'] = $_GET['condition'];
    }
}

// Minimum price filter - validate as numeric
if (isset($_GET['min_price']) && $_GET['min_price'] !== '') {
    $min = (float)$_GET['min_price'];
    if ($min >= 0) {
        $filters['min_price'] = $min;
    }
}

// Maximum price filter - validate as numeric
if (isset($_GET['max_price']) && $_GET['max_price'] !== '') {
    $max = (float)$_GET['max_price'];
    if ($max >= 0) {
        $filters['max_price'] = $max;
    }
}

// Platform type filter - validate against available platforms
if (isset($_GET['platform']) && !empty($_GET['platform'])) {
    $filters['platform'] = $_GET['platform'];
}

// Fetch products with the applied filters
$all_products = $product->getAllProducts($filters);

// PAGINATION: 12 products per page
$products_per_page = 12;
$total_products = count($all_products);
$total_pages = ceil($total_products / $products_per_page);

// Get current page from URL (default to 1)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages) $current_page = $total_pages;

// Calculate offset for pagination
$offset = ($current_page - 1) * $products_per_page;
$products = array_slice($all_products, $offset, $products_per_page);

// Build pagination URL with current filters
$pagination_url_params = [];
foreach ($filters as $key => $value) {
    $pagination_url_params[] = urlencode($key) . '=' . urlencode($value);
}
$pagination_url = 'shop.php?' . implode('&', $pagination_url_params);
if (!empty($pagination_url_params)) {
    $pagination_url .= '&page=';
} else {
    $pagination_url = 'shop.php?page=';
}

// Get available consoles and platforms for filter dropdowns
$consoles = $product->getConsoles();
$platforms = $product->getPlatforms();

// Get wishlist count for nav display
try {
    $wishlist_count = $wishlist->getWishlistCount($customer_id);
} catch (Exception $e) {
    $wishlist_count = 0;
}

// Handle AJAX wishlist operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_POST['action'] === 'wishlist_toggle') {
            $product_id = (int)$_POST['product_id'];
            
            if ($wishlist->isInWishlist($customer_id, $product_id)) {
                $wishlist->removeFromWishlist($customer_id, $product_id);
                echo json_encode(['success' => true, 'in_wishlist' => false, 'message' => 'Removed from wishlist']);
            } else {
                $wishlist->addToWishlist($customer_id, $product_id);
                echo json_encode(['success' => true, 'in_wishlist' => true, 'message' => 'Added to wishlist']);
            }
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="shop-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub</h1>
            </div>
            <div class="navbar-menu">
                <a href="shop.php" class="nav-link active">Shop</a>
                <a href="wishlist.php" class="nav-link">
                    ❤️ Wishlist <span class="wishlist-badge" id="wishlist-count"><?php echo $wishlist_count; ?></span>
                </a>
                <a href="cart.php" class="nav-link">Cart</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="auth.php?action=logout" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Browse Retro Games</h2>
            <p>Discover amazing retro games from multiple sellers</p>
        </div>

        <div class="shop-layout">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar">
                <h3>Filters</h3>
                <form method="GET" class="filter-form">
                    <!-- Search Filter -->
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Game name..." 
                               value="<?php echo isset($filters['search']) ? htmlspecialchars($filters['search']) : ''; ?>">
                    </div>

                    <!-- Console Filter -->
                    <div class="filter-group">
                        <label for="console">Console</label>
                        <select id="console" name="console">
                            <option value="">All Consoles</option>
                            <?php foreach ($consoles as $console): ?>
                                <option value="<?php echo htmlspecialchars($console); ?>" 
                                    <?php echo (isset($filters['console']) && $filters['console'] === $console) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($console); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Platform Type Filter (NEW) -->
                    <div class="filter-group">
                        <label for="platform">Platform Type</label>
                        <select id="platform" name="platform">
                            <option value="">All Platforms</option>
                            <?php foreach ($platforms as $platform): ?>
                                <option value="<?php echo htmlspecialchars($platform); ?>" 
                                    <?php echo (isset($filters['platform']) && $filters['platform'] === $platform) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($platform); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Condition Filter -->
                    <div class="filter-group">
                        <label for="condition">Condition</label>
                        <select id="condition" name="condition">
                            <option value="">All Conditions</option>
                            <option value="Mint" <?php echo (isset($filters['condition']) && $filters['condition'] === 'Mint') ? 'selected' : ''; ?>>Mint</option>
                            <option value="Used" <?php echo (isset($filters['condition']) && $filters['condition'] === 'Used') ? 'selected' : ''; ?>>Used</option>
                            <option value="Refurbished" <?php echo (isset($filters['condition']) && $filters['condition'] === 'Refurbished') ? 'selected' : ''; ?>>Refurbished</option>
                        </select>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <label>Price Range</label>
                        <div class="price-inputs">
                            <input type="number" id="min_price" name="min_price" placeholder="Min" step="0.01" 
                                   value="<?php echo isset($filters['min_price']) ? htmlspecialchars($filters['min_price']) : ''; ?>">
                            <input type="number" id="max_price" name="max_price" placeholder="Max" step="0.01" 
                                   value="<?php echo isset($filters['max_price']) ? htmlspecialchars($filters['max_price']) : ''; ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                    <a href="shop.php" class="btn btn-secondary btn-block">Clear Filters</a>
                </form>
            </aside>

            <!-- Products Grid -->
            <main class="products-grid">
                <!-- Results info -->
                <div class="products-header" style="grid-column: 1 / -1; margin-bottom: 20px;">
                    <p style="color: #666; margin: 0;">
                        Showing <strong><?php echo $offset + 1; ?></strong> - <strong><?php echo min($offset + $products_per_page, $total_products); ?></strong> 
                        of <strong><?php echo $total_products; ?></strong> products
                    </p>
                </div>

                <?php if (empty($products)): ?>
                    <div class="no-products" style="grid-column: 1 / -1;">
                        <p>No products found matching your criteria.</p>
                        <p><a href="shop.php" style="color: #e94560;">Clear filters and try again</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                $img_url = '';
                                if (!empty($prod['image_url'])) {
                                    if (strpos($prod['image_url'], 'http') === 0) {
                                        $img_url = htmlspecialchars($prod['image_url']);
                                    } else {
                                        $img_url = htmlspecialchars(SITE_URL . '/' . ltrim($prod['image_url'], '/'));
                                    }
                                } else {
                                    $img_url = 'https://via.placeholder.com/200?text=No+Image';
                                }
                                
                                // Check if product is in wishlist
                                $in_wishlist = false;
                                try {
                                    $in_wishlist = $wishlist->isInWishlist($customer_id, $prod['product_id']);
                                } catch (Exception $e) {
                                    $in_wishlist = false;
                                }
                                ?>
                                <img src="<?php echo $img_url; ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                <div class="product-overlay">
                                    <a href="product-detail.php?id=<?php echo $prod['product_id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                                <!-- Wishlist button -->
                                <button class="wishlist-btn <?php echo $in_wishlist ? 'in-wishlist' : ''; ?>" 
                                        onclick="toggleWishlist(<?php echo $prod['product_id']; ?>, this)" 
                                        title="<?php echo $in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                    <span class="heart"><?php echo $in_wishlist ? '❤️' : '🤍'; ?></span>
                                </button>
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
                                <p class="console"><?php echo htmlspecialchars($prod['console']); ?></p>
                                <p class="condition"><span class="badge"><?php echo htmlspecialchars($prod['condition']); ?></span></p>
                                <div class="product-footer">
                                    <div class="price">$<?php echo number_format($prod['price'], 2); ?></div>
                                    <div class="rating">
                                        <?php 
                                        $rating = $prod['avg_rating'] ?? 0;
                                        $count = $prod['review_count'] ?? 0;
                                        ?>
                                        <span class="stars"><?php echo str_repeat('★', floor($rating)); ?><?php echo str_repeat('☆', 5 - floor($rating)); ?></span>
                                        <small>(<?php echo $count; ?>)</small>
                                    </div>
                                </div>
                                <p class="seller">by <strong><?php echo htmlspecialchars($prod['store_name']); ?></strong></p>
                                <form method="POST" action="cart.php" style="margin-top: 10px;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                                    <button type="submit" class="btn btn-success btn-block">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="grid-column: 1 / -1; display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <!-- Previous Button -->
                        <?php if ($current_page > 1): ?>
                            <a href="<?php echo $pagination_url . ($current_page - 1); ?>" class="btn btn-secondary">← Previous</a>
                        <?php else: ?>
                            <button disabled class="btn btn-secondary" style="opacity: 0.5; cursor: not-allowed;">← Previous</button>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <div class="page-numbers" style="display: flex; gap: 5px; flex-wrap: wrap; align-items: center;">
                            <?php 
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            if ($start_page > 1) {
                                echo '<a href="' . $pagination_url . '1" class="btn btn-outline">1</a>';
                                if ($start_page > 2) {
                                    echo '<span style="color: #999;">...</span>';
                                }
                            }

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                if ($i === $current_page) {
                                    echo '<button class="btn btn-primary" style="pointer-events: none;">' . $i . '</button>';
                                } else {
                                    echo '<a href="' . $pagination_url . $i . '" class="btn btn-outline">' . $i . '</a>';
                                }
                            }

                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<span style="color: #999;">...</span>';
                                }
                                echo '<a href="' . $pagination_url . $total_pages . '" class="btn btn-outline">' . $total_pages . '</a>';
                            }
                            ?>
                        </div>

                        <!-- Next Button -->
                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?php echo $pagination_url . ($current_page + 1); ?>" class="btn btn-secondary">Next →</a>
                        <?php else: ?>
                            <button disabled class="btn btn-secondary" style="opacity: 0.5; cursor: not-allowed;">Next →</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <style>
    /* Wishlist Button Styles */
    .product-image {
        position: relative;
    }

    .wishlist-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: white;
        border: 2px solid #ddd;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 24px;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .wishlist-btn:hover {
        transform: scale(1.1);
        border-color: #e94560;
        background: #fff5f7;
    }

    .wishlist-btn.in-wishlist {
        border-color: #e94560;
        background: #ffe0eb;
    }

    .wishlist-btn .heart {
        display: inline-block;
    }

    /* Wishlist count badge */
    .wishlist-badge {
        background: #e94560;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: bold;
        margin-left: 5px;
    }

    /* Pagination Styles */
    .btn-outline {
        background: white;
        border: 1px solid #ddd;
        color: #333;
        padding: 8px 12px;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background: #f5f5f5;
        border-color: #667eea;
    }

    .pagination {
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .wishlist-btn {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        .page-numbers {
            max-width: 100%;
        }

        .page-numbers a, .page-numbers button {
            padding: 6px 10px;
            font-size: 14px;
        }
    }
    </style>

    <script>
    /**
     * Toggle product wishlist status via AJAX
     */
    function toggleWishlist(productId, button) {
        fetch('shop.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=wishlist_toggle&product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button appearance
                if (data.in_wishlist) {
                    button.classList.add('in-wishlist');
                    button.querySelector('.heart').textContent = '❤️';
                    button.title = 'Remove from wishlist';
                } else {
                    button.classList.remove('in-wishlist');
                    button.querySelector('.heart').textContent = '🤍';
                    button.title = 'Add to wishlist';
                }
                
                // Update wishlist count in navbar
                updateWishlistCount();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update wishlist');
        });
    }

    /**
     * Update wishlist count in navbar
     */
    function updateWishlistCount() {
        const badge = document.getElementById('wishlist-count');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
        }
    }
    </script>
</body>
</html>
