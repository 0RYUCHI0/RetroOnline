<?php
require_once '../../config.php';
require_once '../../classes/Address.php';
require_once '../../classes/Order.php';
require_once '../../classes/Product.php';

// Check if logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$user_id = SessionManager::getUserId();
$address = new Address();
$order = new Order();
$product = new Product();

$error = '';
$success = '';

// Get user addresses
$addresses = $address->getUserAddresses($user_id);

// Handle address creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_address') {
        try {
            $address->createAddress(
                $user_id,
                $_POST['recipient_name'],
                $_POST['address_line1'],
                $_POST['address_line2'] ?? '',
                $_POST['city'],
                $_POST['state'],
                $_POST['postal_code'],
                $_POST['country'],
                $_POST['phone']
            );
            $success = 'Address added successfully!';
            header('refresh:2');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($_POST['action'] === 'checkout') {
        $shipping_address_id = (int)$_POST['shipping_address_id'];
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            $error = 'Your cart is empty!';
        } elseif ($shipping_address_id === 0) {
            $error = 'Please select a shipping address';
        } else {
            try {
                // Prepare cart items with seller info
                $cart_items = [];
                foreach ($cart as $product_id => $item) {
                    $prod = $product->getProductById($product_id);
                    if ($prod && $prod['stock'] >= $item['quantity']) {
                        $cart_items[] = [
                            'product_id' => $product_id,
                            'seller_id' => $prod['seller_id'],
                            'price' => $prod['price'],
                            'quantity' => $item['quantity']
                        ];
                    } else {
                        throw new Exception('Insufficient stock for ' . $prod['name']);
                    }
                }

                // Create order
                $order_id = $order->createOrder($user_id, $shipping_address_id, $cart_items);

                // Clear cart
                unset($_SESSION['cart']);

                $success = 'Order placed successfully! Redirecting...';
                header("refresh:2;url=orders.php");
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total_price = 0;

foreach ($cart as $product_id => $item) {
    $prod = $product->getProductById($product_id);
    if ($prod) {
        $item_total = $prod['price'] * $item['quantity'];
        $cart_items[] = [
            'product_id' => $product_id,
            'name' => $prod['name'],
            'price' => $prod['price'],
            'quantity' => $item['quantity'],
            'total' => $item_total
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
    <title>Checkout - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="checkout-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub</h1>
            </div>
            <div class="navbar-menu">
                <a href="shop.php" class="nav-link">Shop</a>
                <a href="cart.php" class="nav-link">Cart</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="auth.php?action=logout" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Checkout</h2>
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
            <div class="checkout-layout">
                <!-- Shipping Address -->
                <div class="checkout-section">
                    <h3>Shipping Address</h3>
                    
                    <?php if (empty($addresses)): ?>
                        <p>No saved addresses. Add one below:</p>
                    <?php else: ?>
                        <form method="POST" style="margin-bottom: 20px;">
                            <div class="form-group">
                                <label>Select Address</label>
                                <select name="shipping_address_id" required>
                                    <option value="0">-- Select Address --</option>
                                    <?php foreach ($addresses as $addr): ?>
                                        <option value="<?php echo $addr['address_id']; ?>">
                                            <?php echo htmlspecialchars($addr['recipient_name'] . ', ' . $addr['address_line1'] . ', ' . $addr['city']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" name="action" value="checkout">
                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </form>
                    <?php endif; ?>

                    <h4>Add New Address</h4>
                    <form method="POST" class="address-form">
                        <input type="hidden" name="action" value="add_address">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Recipient Name</label>
                                <input type="text" name="recipient_name" required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Address Line 1</label>
                            <input type="text" name="address_line1" required>
                        </div>
                        <div class="form-group">
                            <label>Address Line 2 (Optional)</label>
                            <input type="text" name="address_line2">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" required>
                            </div>
                            <div class="form-group">
                                <label>State</label>
                                <input type="text" name="state" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Postal Code</label>
                                <input type="text" name="postal_code" required>
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary">Save Address</button>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="checkout-section">
                    <h3>Order Summary</h3>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="summary-footer">
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
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
