<?php
require_once '../../config.php';
require_once '../../classes/Wishlist.php';
require_once '../../classes/Product.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$customer_id = SessionManager::getUserId();
$wishlist = new Wishlist();
$product = new Product();

$error = '';
$success = '';

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_POST['action'] === 'add_to_wishlist') {
            // ✅ STRICT DUPLICATE PREVENTION: Check before insert
            $product_id = (int)$_POST['product_id'];
            
            // Database existence check using prepared statement
            if ($wishlist->isInWishlist($customer_id, $product_id)) {
                // ✅ Product already exists - do nothing, return success
                echo json_encode(['success' => true, 'in_wishlist' => true, 'message' => 'Already in wishlist']);
            } else {
                // ✅ Product doesn't exist - insert with duplicate prevention
                $wishlist->addToWishlist($customer_id, $product_id);
                echo json_encode(['success' => true, 'in_wishlist' => true, 'message' => 'Added to wishlist']);
            }
        } elseif ($_POST['action'] === 'remove_item') {
            $product_id = (int)$_POST['product_id'];
            $wishlist->removeFromWishlist($customer_id, $product_id);
            echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
        } elseif ($_POST['action'] === 'move_to_cart') {
            $product_id = (int)$_POST['product_id'];
            // Remove from wishlist (moved to cart)
            $wishlist->moveToCart($customer_id, $product_id);
            echo json_encode(['success' => true, 'message' => 'Added to cart and removed from wishlist']);
        } elseif ($_POST['action'] === 'clear_wishlist') {
            $wishlist->clearWishlist($customer_id);
            echo json_encode(['success' => true, 'message' => 'Wishlist cleared']);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get wishlist items
$wishlist_items = $wishlist->getWishlist($customer_id);

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="wishlist-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub</h1>
            </div>
            <div class="navbar-menu">
                <a href="shop.php" class="nav-link">Shop</a>
                <a href="wishlist.php" class="nav-link active">❤️ Wishlist</a>
                <a href="cart.php" class="nav-link">Cart</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="auth.php?action=logout" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>❤️ My Wishlist</h2>
            <p><?php echo count($wishlist_items); ?> item(s) saved</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div id="alert-container" style="margin-bottom: 20px;"></div>

        <?php if (empty($wishlist_items)): ?>
            <div class="empty-wishlist" style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 64px; margin-bottom: 20px;">🤍</div>
                <h3>Your wishlist is empty</h3>
                <p style="color: #666; margin: 10px 0;">Start adding games you love to your wishlist!</p>
                <a href="shop.php" class="btn btn-primary" style="margin-top: 20px;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="wishlist-controls" style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="clearWishlist()">Clear Wishlist</button>
            </div>

            <div class="wishlist-container">
                <div class="wishlist-grid">
                    <?php foreach ($wishlist_items as $item): ?>
                        <div class="wishlist-item" data-product-id="<?php echo $item['product_id']; ?>">
                            <div class="item-image">
                                <?php 
                                $img_url = '';
                                if (!empty($item['image_url'])) {
                                    if (strpos($item['image_url'], 'http') === 0) {
                                        $img_url = htmlspecialchars($item['image_url']);
                                    } else {
                                        $img_url = htmlspecialchars(SITE_URL . '/' . ltrim($item['image_url'], '/'));
                                    }
                                } else {
                                    $img_url = 'https://via.placeholder.com/200?text=No+Image';
                                }
                                ?>
                                <img src="<?php echo $img_url; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>

                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="console">📦 <?php echo htmlspecialchars($item['console']); ?></p>
                                <p class="condition">
                                    <span class="badge"><?php echo htmlspecialchars($item['condition']); ?></span>
                                </p>
                                <p class="seller">Seller: <strong><?php echo htmlspecialchars($item['store_name']); ?></strong></p>
                                
                                <div class="item-rating" style="margin: 10px 0;">
                                    <span class="stars">
                                        <?php 
                                        $rating = $item['avg_rating'] ?? 0;
                                        $count = $item['review_count'] ?? 0;
                                        echo str_repeat('★', floor($rating));
                                        echo str_repeat('☆', 5 - floor($rating));
                                        ?>
                                    </span>
                                    <small>(<?php echo $count; ?> reviews)</small>
                                </div>

                                <p class="added-date" style="font-size: 12px; color: #999;">
                                    Added: <?php echo date('M d, Y', strtotime($item['date_added'])); ?>
                                </p>

                                <div class="item-price" style="font-size: 24px; font-weight: bold; color: #e94560; margin: 15px 0;">
                                    $<?php echo number_format($item['price'], 2); ?>
                                </div>

                                <div class="item-stock" style="margin-bottom: 15px;">
                                    <?php if ($item['stock'] > 0): ?>
                                        <span style="color: #28a745; font-weight: 600;">✓ In Stock (<?php echo $item['stock']; ?> available)</span>
                                    <?php else: ?>
                                        <span style="color: #c62828; font-weight: 600;">⚠️ Out of Stock</span>
                                    <?php endif; ?>
                                </div>

                                <div class="item-actions" style="display: flex; gap: 10px; flex-direction: column;">
                                    <?php if ($item['stock'] > 0): ?>
                                        <form method="POST" action="cart.php" style="margin: 0;">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn btn-success btn-block">Add to Cart</button>
                                        </form>
                                    <?php else: ?>
                                        <button disabled class="btn btn-success btn-block" style="opacity: 0.5; cursor: not-allowed;">Out of Stock</button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="removeItem(<?php echo $item['product_id']; ?>)">Remove from Wishlist</button>
                                    <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" class="btn btn-outline btn-block">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="wishlist-summary" style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <p style="margin: 0; color: #666;">
                    You have <strong><?php echo count($wishlist_items); ?></strong> item(s) in your wishlist.
                </p>
                <p style="margin: 10px 0 0 0; color: #999; font-size: 14px;">
                    Keep an eye on these games - we'll notify you when prices drop!
                </p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <style>
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .wishlist-item {
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .wishlist-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    .item-image {
        width: 100%;
        height: 250px;
        overflow: hidden;
        background: #f0f0f0;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-details {
        padding: 15px;
    }

    .item-details h3 {
        margin: 0 0 10px 0;
        font-size: 18px;
        color: #333;
    }

    .item-details p {
        margin: 5px 0;
        font-size: 14px;
        color: #666;
    }

    .console {
        font-weight: 500;
    }

    .badge {
        display: inline-block;
        background: #667eea;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }

    .empty-wishlist {
        margin-top: 40px;
    }

    .btn-outline {
        background: white;
        border: 1px solid #667eea;
        color: #667eea;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-outline:hover {
        background: #f0f7ff;
    }

    .btn-block {
        width: 100% !important;
    }

    @media (max-width: 768px) {
        .wishlist-grid {
            grid-template-columns: 1fr;
        }

        .wishlist-controls {
            flex-direction: column;
        }

        .wishlist-controls button {
            width: 100%;
        }
    }
    </style>

    <script>
    /**
     * Remove item from wishlist
     */
    function removeItem(productId) {
        if (confirm('Remove this item from your wishlist?')) {
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=remove_item&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector('[data-product-id="' + productId + '"]');
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                        location.reload();
                    }, 300);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to remove item');
            });
        }
    }

    /**
     * Clear entire wishlist
     */
    function clearWishlist() {
        if (confirm('Are you sure you want to clear your entire wishlist? This action cannot be undone.')) {
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clear_wishlist'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Wishlist cleared!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to clear wishlist');
            });
        }
    }

    /**
     * Show alert message
     */
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = 'alert alert-' + type;
        alert.textContent = message;
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }
    </script>
</body>
</html>
