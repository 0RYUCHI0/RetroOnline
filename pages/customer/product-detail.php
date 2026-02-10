<?php
require_once '../../config.php';
require_once '../../classes/Product.php';
require_once '../../classes/Review.php';
require_once '../../classes/Wishlist.php';
require_once '../../classes/Recommendations.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$product_id = (int)($_GET['id'] ?? 0);
if (!$product_id) {
    header('Location: shop.php');
    exit;
}

$product = new Product();
$product_data = $product->getProductById($product_id);

if (!$product_data) {
    header('Location: shop.php');
    exit;
}

// Get all variants for this product (same name + console, different conditions)
$variants = $product->getProductVariantsForCustomer($product_data['name'], $product_data['console']);

if (empty($variants)) {
    header('Location: shop.php');
    exit;
}

// Default selected variant (first available one)
$selected_variant = $variants[0];

// Allow passing variant selection via URL (for post-add redirects)
if (isset($_GET['variant'])) {
    foreach ($variants as $v) {
        if ($v['product_id'] == (int)$_GET['variant']) {
            $selected_variant = $v;
            break;
        }
    }
}

$review = new Review();
$product_reviews = $review->getProductReviews($product_id);

// Initialize wishlist and recommendations
$customer_id = SessionManager::getUserId();
$wishlist = new Wishlist();
$recommendations = new Recommendations();

$in_wishlist = false;
try {
    $in_wishlist = $wishlist->isInWishlist($customer_id, $product_id);
} catch (Exception $e) {
    $in_wishlist = false;
}

// Get recommended products (5 items max)
$recommended_products = [];
try {
    $recommended_products = $recommendations->getRecommendedProducts($customer_id, 5);
} catch (Exception $e) {
    $recommended_products = [];
}

$error = '';
$success = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_review') {
        $user_id = SessionManager::getUserId();
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);

        if ($rating < 1 || $rating > 5) {
            $error = 'Rating must be between 1 and 5';
        } else {
            try {
                if ($review->canUserReview($user_id, $product_id)) {
                    $review->createReview($product_id, $user_id, $product_data['seller_id'], $rating, $comment);
                    $success = 'Review submitted successfully!';
                    header('refresh:2');
                } else {
                    $error = 'You can only review products you have purchased and not already reviewed.';
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'wishlist_toggle') {
        header('Content-Type: application/json');
        try {
            if ($wishlist->isInWishlist($customer_id, $product_id)) {
                $wishlist->removeFromWishlist($customer_id, $product_id);
                echo json_encode(['success' => true, 'in_wishlist' => false]);
            } else {
                $wishlist->addToWishlist($customer_id, $product_id);
                echo json_encode(['success' => true, 'in_wishlist' => true]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product_data['name']); ?> - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="product-detail-page">
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

    <div class="container">
        <div class="product-detail">
            <div class="detail-layout">
                <div class="product-showcase-card">
                    <?php 
                    $image_url = '';
                    if (!empty($product_data['image_url'])) {
                        // Check if it's an absolute URL
                        if (strpos($product_data['image_url'], 'http') === 0) {
                            $image_url = htmlspecialchars($product_data['image_url']);
                        } else {
                            // If it's a relative path, prepend SITE_URL
                            $image_url = htmlspecialchars(SITE_URL . '/' . ltrim($product_data['image_url'], '/'));
                        }
                    } else {
                        $image_url = 'https://via.placeholder.com/400?text=No+Image';
                    }
                    ?>
                    <div class="showcase-image">
                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product_data['name']); ?>">
                    </div>
                </div>

                <div class="detail-info">
                    <h1><?php echo htmlspecialchars($product_data['name']); ?></h1>
                    
                    <div class="detail-meta">
                        <span class="console"><?php echo htmlspecialchars($product_data['console']); ?></span>
                        <?php if ($product_data['avg_rating']): ?>
                            <span class="rating">
                                ★★★★★ <?php echo number_format($product_data['avg_rating'], 1); ?> (<?php echo $product_data['review_count']; ?> reviews)
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Variant Selection with Dynamic Pricing -->
                    <div class="variant-selector" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd;">
                        <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #333;">
                            Select Condition:
                        </label>
                        <div class="variant-options" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <?php foreach ($variants as $variant): ?>
                                <label style="cursor: pointer; flex: 1; min-width: 120px;">
                                    <input type="radio" name="variant" value="<?php echo $variant['product_id']; ?>" 
                                        <?php echo $selected_variant['product_id'] == $variant['product_id'] ? 'checked' : ''; ?>
                                        onchange="updateVariantDisplay(<?php echo htmlspecialchars(json_encode($variant)); ?>)"
                                        style="margin-right: 5px;">
                                    <span style="
                                        display: inline-block;
                                        padding: 10px;
                                        border-radius: 6px;
                                        background: <?php echo $selected_variant['product_id'] == $variant['product_id'] ? '#667eea' : '#e0e0e0'; ?>;
                                        color: <?php echo $selected_variant['product_id'] == $variant['product_id'] ? 'white' : '#333'; ?>;
                                        font-weight: 500;
                                        border: 2px solid <?php echo $selected_variant['product_id'] == $variant['product_id'] ? '#667eea' : '#ccc'; ?>;
                                        transition: all 0.3s ease;
                                    ">
                                        <?php echo htmlspecialchars($variant['condition']); ?>
                                        <br>
                                        <small style="font-size: 12px;">$<?php echo number_format($variant['price'], 2); ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="detail-price">
                        <h2>$<span id="variant-price"><?php echo number_format($selected_variant['price'], 2); ?></span></h2>
                        <small id="variant-condition-label" style="display: block; color: #666; margin-top: 5px;">
                            <?php echo htmlspecialchars($selected_variant['condition']); ?> condition
                        </small>
                    </div>

                    <div class="detail-seller">
                        <p>Sold by: <strong><?php echo htmlspecialchars($selected_variant['store_name']); ?></strong></p>
                    </div>

                    <div class="detail-stock">
                        <?php if ($selected_variant['stock'] > 0): ?>
                            <p id="stock-status" class="in-stock">
                                ✓ In Stock (<?php echo $selected_variant['stock']; ?> available)
                                <?php if ($selected_variant['stock'] <= 3): ?>
                                    <span style="color: #f39c12; font-weight: 600;"> - Only <?php echo $selected_variant['stock']; ?> left!</span>
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <p id="stock-status" class="out-stock">✗ Out of Stock</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($selected_variant['stock'] > 0): ?>
                        <form method="POST" action="cart.php" id="add-to-cart-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" id="hidden-product-id" value="<?php echo $selected_variant['product_id']; ?>">
                            <button type="submit" class="btn btn-success btn-block" style="margin-top: 20px; padding: 1rem;">
                                🛒 Add to Cart
                            </button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-block" style="margin-top: 20px; padding: 1rem; cursor: not-allowed;" disabled>
                            Out of Stock
                        </button>
                    <?php endif; ?>

                    <!-- Wishlist Button -->
                    <button type="button" class="btn btn-secondary btn-block wishlist-toggle-btn" 
                            onclick="toggleWishlist(<?php echo $product_id; ?>, this)"
                            style="margin-top: 10px; padding: 1rem; background: <?php echo $in_wishlist ? '#e94560' : '#6c757d'; ?>;"
                            title="<?php echo $in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                        <span id="wishlist-btn-text"><?php echo $in_wishlist ? '❤️ Remove from Wishlist' : '🤍 Add to Wishlist'; ?></span>
                    </button>

                    <div class="detail-demo" style="margin-top: 20px;">
                        <?php if (!empty($product_data['demo_url'])): ?>
                            <h3>Game Trailer</h3>
                            <?php 
                            $demo_url = htmlspecialchars($product_data['demo_url']);
                            // Check if it's a YouTube embed URL
                            if (strpos($demo_url, 'youtube.com/embed/') !== false): 
                            ?>
                                <iframe width="100%" height="300" src="<?php echo $demo_url; ?>" 
                                    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                                </iframe>
                            <?php else: ?>
                                <video width="100%" height="300" controls>
                                    <source src="<?php echo $demo_url; ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <script>
                    /**
                     * Update variant display when customer selects a different condition
                     * Updates price, stock, and hidden product_id field
                     */
                    function updateVariantDisplay(variant) {
                        // Update price
                        document.getElementById('variant-price').textContent = 
                            parseFloat(variant.price).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        
                        // Update condition label
                        document.getElementById('variant-condition-label').textContent = 
                            variant.condition + ' condition';
                        
                        // Update hidden product_id
                        document.getElementById('hidden-product-id').value = variant.product_id;
                        
                        // Update stock status
                        const stockStatus = document.getElementById('stock-status');
                        if (variant.stock > 0) {
                            let stockText = '✓ In Stock (' + variant.stock + ' available)';
                            if (variant.stock <= 3) {
                                stockText += ' - <span style="color: #f39c12; font-weight: 600;">Only ' + variant.stock + ' left!</span>';
                            }
                            stockStatus.innerHTML = stockText;
                            stockStatus.className = 'in-stock';
                            
                            // Enable add to cart button
                            document.querySelector('button[type="submit"]').disabled = false;
                            document.querySelector('button[type="submit"]').style.cursor = 'pointer';
                        } else {
                            stockStatus.innerHTML = '✗ Out of Stock';
                            stockStatus.className = 'out-stock';
                            
                            // Disable add to cart button
                            const btn = document.querySelector('button[type="submit"]');
                            if (btn) {
                                btn.disabled = true;
                                btn.style.cursor = 'not-allowed';
                            }
                        }
                    }

                    /**
                     * Toggle product wishlist status
                     */
                    function toggleWishlist(productId, button) {
                        fetch('product-detail.php?id=' + productId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=wishlist_toggle&product_id=' + productId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const textEl = document.getElementById('wishlist-btn-text');
                                if (data.in_wishlist) {
                                    button.style.background = '#e94560';
                                    textEl.textContent = '❤️ Remove from Wishlist';
                                    button.title = 'Remove from wishlist';
                                } else {
                                    button.style.background = '#6c757d';
                                    textEl.textContent = '🤍 Add to Wishlist';
                                    button.title = 'Add to wishlist';
                                }
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to update wishlist');
                        });
                    }
                    </script>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="reviews-section" style="margin-top: 3rem; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3>Customer Reviews (<?php echo count($product_reviews); ?>)</h3>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <!-- Add Review Form -->
                <div class="review-form" style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                    <h4>Leave a Review</h4>
                    <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                        <input type="hidden" name="action" value="add_review">
                        <div class="form-group">
                            <label>Rating</label>
                            <select name="rating" required style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">-- Select Rating --</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                <option value="4">⭐⭐⭐⭐ Good</option>
                                <option value="3">⭐⭐⭐ Average</option>
                                <option value="2">⭐⭐ Poor</option>
                                <option value="1">⭐ Terrible</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Review Comment (Optional)</label>
                            <textarea name="comment" rows="4" placeholder="Share your thoughts about this product..." style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-family: Arial;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>

                <!-- Display Reviews -->
                <?php if (empty($product_reviews)): ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No reviews yet. Be the first to review!</p>
                <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($product_reviews as $rev): ?>
                            <div class="review-item" style="padding: 1rem; border-bottom: 1px solid #ddd;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <strong><?php echo htmlspecialchars($rev['reviewer_name']); ?></strong>
                                    <span style="color: #f39c12;">
                                        <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?>
                                    </span>
                                </div>
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    <?php echo date('M d, Y', strtotime($rev['created_at'])); ?>
                                </p>
                                <?php if (!empty($rev['comment'])): ?>
                                    <p><?php echo htmlspecialchars($rev['comment']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recommended Products Section -->
            <?php if (!empty($recommended_products)): ?>
                <div class="recommendations-section" style="margin-top: 3rem; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h3>💡 You Might Also Like</h3>
                    <p style="color: #666; margin-bottom: 20px;">Based on your purchase history, check out these games:</p>
                    
                    <div class="recommendations-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                        <?php foreach ($recommended_products as $rec): ?>
                            <div class="recommendation-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; transition: all 0.3s ease;">
                                <div class="rec-image" style="width: 100%; height: 200px; overflow: hidden; background: #f0f0f0;">
                                    <?php 
                                    $rec_img_url = '';
                                    if (!empty($rec['image_url'])) {
                                        if (strpos($rec['image_url'], 'http') === 0) {
                                            $rec_img_url = htmlspecialchars($rec['image_url']);
                                        } else {
                                            $rec_img_url = htmlspecialchars(SITE_URL . '/' . ltrim($rec['image_url'], '/'));
                                        }
                                    } else {
                                        $rec_img_url = 'https://via.placeholder.com/250?text=No+Image';
                                    }
                                    ?>
                                    <img src="<?php echo $rec_img_url; ?>" alt="<?php echo htmlspecialchars($rec['name']); ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div style="padding: 15px;">
                                    <h4 style="margin: 0 0 10px 0; font-size: 16px;">
                                        <?php echo htmlspecialchars($rec['name']); ?>
                                    </h4>
                                    <p style="margin: 5px 0; font-size: 13px; color: #666;">
                                        <?php echo htmlspecialchars($rec['console']); ?>
                                    </p>
                                    <p style="margin: 5px 0; font-size: 13px; color: #666;">
                                        <span class="badge" style="background: #667eea; color: white; padding: 3px 6px; border-radius: 3px; font-size: 11px;">
                                            <?php echo htmlspecialchars($rec['condition']); ?>
                                        </span>
                                    </p>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0;">
                                        <span style="font-size: 18px; font-weight: bold; color: #e94560;">
                                            $<?php echo number_format($rec['price'], 2); ?>
                                        </span>
                                        <span style="color: #f39c12; font-size: 12px;">
                                            <?php 
                                            $rating = $rec['avg_rating'] ?? 0;
                                            echo str_repeat('★', floor($rating)); 
                                            echo str_repeat('☆', 5 - floor($rating)); 
                                            ?>
                                        </span>
                                    </div>
                                    <a href="product-detail.php?id=<?php echo $rec['product_id']; ?>" class="btn btn-primary btn-block" style="margin-top: 10px; padding: 8px;">
                                        View Game
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>

    <style>
        .product-showcase-card {
            background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            border: 2px solid #e94560;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 450px;
            margin-bottom: 30px;
        }

        .showcase-image {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .showcase-image img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
        }

        .showcase-image img:hover {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .product-showcase-card {
                min-height: 300px;
                padding: 15px;
            }

            .showcase-image img {
                max-height: 300px;
            }
        }
    </style>
</body>
</html>
