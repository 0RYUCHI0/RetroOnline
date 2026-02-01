<?php
require_once '../../config.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    SessionManager::destroy();
    header('Location: auth.php');
    exit;
}

// Handle cart operations
$cart = $_SESSION['cart'] ?? [];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // Add product variant to cart
        $product_id = (int)$_POST['product_id'];
        
        // Validate that product exists and stock is available
        require_once '../../classes/Product.php';
        $product_obj = new Product();
        $product = $product_obj->getProductById($product_id);
        
        if (!$product) {
            $error = 'Product not found.';
        } elseif ($product['stock'] <= 0) {
            $error = 'This variant is out of stock.';
        } else {
            if (!isset($cart[$product_id])) {
                $cart[$product_id] = ['quantity' => 1];
            } else {
                // Check if adding more would exceed stock
                if ($cart[$product_id]['quantity'] + 1 > $product['stock']) {
                    $error = 'Cannot add more. Only ' . $product['stock'] . ' available for this variant.';
                } else {
                    $cart[$product_id]['quantity']++;
                }
            }
            
            if (!$error) {
                $_SESSION['cart'] = $cart;
                $success = 'Product variant added to cart!';
            }
        }
    } elseif ($action === 'update') {
        // Update product quantity in cart
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Validate stock for updated quantity
        require_once '../../classes/Product.php';
        $product_obj = new Product();
        $product = $product_obj->getProductById($product_id);
        
        if ($product && $quantity > $product['stock']) {
            $error = 'Only ' . $product['stock'] . ' available for this variant.';
        } elseif ($quantity <= 0) {
            unset($cart[$product_id]);
            $_SESSION['cart'] = $cart;
            $success = 'Product removed from cart!';
        } else {
            $cart[$product_id]['quantity'] = $quantity;
            $_SESSION['cart'] = $cart;
            $success = 'Cart updated!';
        }
    } elseif ($action === 'remove') {
        // Remove product from cart
        $product_id = (int)$_POST['product_id'];
        unset($cart[$product_id]);
        $_SESSION['cart'] = $cart;
        $success = 'Product removed from cart!';
    }
}

// Get cart items with product details (including variant condition)
require_once '../../classes/Product.php';
$product = new Product();
$cart_items = [];
$total_price = 0;

foreach ($cart as $product_id => $item) {
    $prod = $product->getProductById($product_id);
    if ($prod) {
        $item_total = $prod['price'] * $item['quantity'];
        $cart_items[] = [
            'product_id' => $product_id,
            'name' => $prod['name'],
            'condition' => $prod['condition'],
            'price' => $prod['price'],
            'stock' => $prod['stock'],
            'quantity' => $item['quantity'],
            'total' => $item_total,
            'console' => $prod['console'],
            'seller_id' => $prod['seller_id'],
            'store_name' => $prod['store_name']
        ];
        $total_price += $item_total;
    }
}

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="cart-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub</h1>
            </div>
            <div class="navbar-menu">
                <a href="shop.php" class="nav-link">Shop</a>
                <a href="cart.php" class="nav-link active">Cart</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="auth.php?action=logout" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Shopping Cart</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Condition</th>
                                <th>Console</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr<?php echo $item['quantity'] > $item['stock'] ? ' style="background-color: #ffebee;"' : ''; ?>>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <span style="
                                            padding: 4px 8px;
                                            border-radius: 4px;
                                            font-size: 12px;
                                            font-weight: 600;
                                            <?php 
                                            if ($item['condition'] === 'Mint') {
                                                echo 'background: #d4edda; color: #155724;';
                                            } elseif ($item['condition'] === 'Used') {
                                                echo 'background: #fff3cd; color: #856404;';
                                            } else {
                                                echo 'background: #d1ecf1; color: #0c5460;';
                                            }
                                            ?>
                                        ">
                                            <?php echo htmlspecialchars($item['condition']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['console']); ?></td>
                                    <td><?php echo htmlspecialchars($item['store_name']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" style="width: 50px;">
                                            <button type="submit" class="btn btn-small">Update</button>
                                        </form>
                                        <?php if ($item['quantity'] > $item['stock']): ?>
                                            <small style="color: #c62828; display: block; margin-top: 5px;">
                                                ⚠️ Only <?php echo $item['stock']; ?> available
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($item['total'], 2); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total_price, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>FREE</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total_price, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top: 20px;">Proceed to Checkout</a>
                    <a href="shop.php" class="btn btn-secondary btn-block">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
