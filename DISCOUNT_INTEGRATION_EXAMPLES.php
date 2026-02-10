<?php
/**
 * QUICK INTEGRATION EXAMPLES
 * 
 * Copy these examples into your existing pages to add discount functionality.
 * Do NOT modify the original cart, wishlist, or product display logic.
 * Just add these discount calculations where needed.
 */

// ============================================================================
// EXAMPLE 1: Display Discounted Price on Product Page
// ============================================================================

/*
 * Add this to your product-detail.php or product display page:
 * 
 * Location: After fetching product data from database
 */

/*
require_once __DIR__ . '/../../functions/discount-helpers.php';

// Assuming you already have $product from your database query
$discount_info = getDiscountInfo($product['price'], $product['product_id']);

// Display the price with discount
if ($discount_info['has_discount']) {
    ?>
    <div class="price-section">
        <div class="original-price">
            <s>$<?php echo number_format($discount_info['original_price'], 2); ?></s>
        </div>
        <div class="discount-price" style="color: green; font-size: 20px; font-weight: bold;">
            $<?php echo number_format($discount_info['discounted_price'], 2); ?>
        </div>
        <div class="discount-badge" style="color: red; font-weight: bold;">
            SAVE <?php echo $discount_info['discount_percent']; ?>%!
        </div>
        <div class="savings">
            You save: $<?php echo number_format($discount_info['discount_amount'], 2); ?>
        </div>
    </div>
    <?php
} else {
    ?>
    <div class="price-section">
        <div class="regular-price" style="font-size: 18px; font-weight: bold;">
            $<?php echo number_format($discount_info['original_price'], 2); ?>
        </div>
    </div>
    <?php
}
*/

// ============================================================================
// EXAMPLE 2: Apply Discount in Cart Total Calculation
// ============================================================================

/*
 * Add this to your cart.php or wherever you calculate cart total:
 * 
 * This calculates the final price for each item considering discounts
 */

/*
require_once __DIR__ . '/../../functions/discount-helpers.php';

$discount = new Discount();
$cart_total = 0;
$total_savings = 0;

// Assuming $cart_items is your array of items in cart
foreach ($cart_items as $item) {
    // Get the discounted price for this item
    $price_info = $discount->applyDiscountToPrice($item['price'], $item['product_id']);
    
    // Calculate line total
    $line_total = $price_info['discounted_price'] * $item['quantity'];
    $cart_total += $line_total;
    
    // Track total savings
    if ($price_info['has_discount']) {
        $total_savings += ($price_info['discount_amount'] * $item['quantity']);
    }
}

// Display cart totals
?>
<div class="cart-totals">
    <div class="subtotal">
        <label>Subtotal:</label>
        <span>$<?php echo number_format($cart_total + $total_savings, 2); ?></span>
    </div>
    <?php if ($total_savings > 0): ?>
        <div class="discount-applied" style="color: green; font-weight: bold;">
            <label>Total Discount Savings:</label>
            <span>-$<?php echo number_format($total_savings, 2); ?></span>
        </div>
    <?php endif; ?>
    <div class="total" style="font-size: 18px; font-weight: bold;">
        <label>Total:</label>
        <span>$<?php echo number_format($cart_total, 2); ?></span>
    </div>
</div>
<?php
*/

// ============================================================================
// EXAMPLE 3: Show Price on Cart Page or Checkout Review
// ============================================================================

/*
 * Add this where you display cart items before checkout:
 */

/*
require_once __DIR__ . '/../../functions/discount-helpers.php';

$discount = new Discount();

foreach ($cart_items as $item) {
    $price_info = $discount->applyDiscountToPrice($item['price'], $item['product_id']);
    ?>
    <tr>
        <td><?php echo htmlspecialchars($item['name']); ?></td>
        <td><?php echo $item['quantity']; ?></td>
        <td>
            <?php if ($price_info['has_discount']): ?>
                <span style="text-decoration: line-through;">$<?php echo number_format($price_info['original_price'], 2); ?></span>
                <span style="color: green; font-weight: bold;">$<?php echo number_format($price_info['discounted_price'], 2); ?></span>
            <?php else: ?>
                $<?php echo number_format($price_info['original_price'], 2); ?>
            <?php endif; ?>
        </td>
        <td>$<?php echo number_format($price_info['discounted_price'] * $item['quantity'], 2); ?></td>
    </tr>
    <?php
}
*/

// ============================================================================
// EXAMPLE 4: Seller Dashboard - Show Discount Management
// ============================================================================

/*
 * Add this to seller dashboard to show active discounts:
 */

/*
require_once __DIR__ . '/../../functions/discount-helpers.php';

$discount = new Discount();
$seller_id = $_SESSION['user_id'];

// Get discount statistics
$stats = $discount->getDiscountStats($seller_id);
?>
<div class="discount-stats">
    <h3>Your Discount Deals</h3>
    <div class="stat-boxes">
        <div class="stat-box">
            <h4><?php echo $stats['total_discounts']; ?></h4>
            <p>Total Discounts</p>
        </div>
        <div class="stat-box">
            <h4><?php echo $stats['active_discounts']; ?></h4>
            <p>Currently Active</p>
        </div>
        <div class="stat-box">
            <h4><?php echo $stats['upcoming_discounts']; ?></h4>
            <p>Scheduled Soon</p>
        </div>
        <div class="stat-box">
            <h4><?php echo number_format($stats['avg_discount'], 1); ?>%</h4>
            <p>Average Discount</p>
        </div>
    </div>
</div>

<?php
// Display active discounts
$active = $discount->getAllActiveDiscounts($seller_id);
if (!empty($active)): ?>
    <h4>Active Discount Deals</h4>
    <table>
        <tr>
            <th>Product</th>
            <th>Original Price</th>
            <th>Discount</th>
            <th>Sale Price</th>
            <th>Ends</th>
            <th>Action</th>
        </tr>
        <?php foreach ($active as $deal): ?>
            <tr>
                <td><?php echo htmlspecialchars($deal['product_name']); ?></td>
                <td>$<?php echo number_format($deal['original_price'], 2); ?></td>
                <td><?php echo $deal['discount_percent']; ?>%</td>
                <td>$<?php echo number_format($deal['original_price'] * (1 - $deal['discount_percent']/100), 2); ?></td>
                <td><?php echo $deal['end_date']; ?></td>
                <td>
                    <a href="edit-discount.php?id=<?php echo $deal['discount_id']; ?>">Edit</a>
                    <a href="delete-discount.php?id=<?php echo $deal['discount_id']; ?>" onclick="return confirm('Delete this discount?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php
*/

// ============================================================================
// EXAMPLE 5: Create Discount Form Processing
// ============================================================================

/*
 * Add this to handle form submission when seller creates a discount:
 */

/*
require_once __DIR__ . '/../../classes/Discount.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discount = new Discount();
    
    try {
        $discount_id = $discount->addDiscount(
            $_POST['product_id'],           // int
            $_POST['discount_percent'],     // float
            $_POST['start_date'],          // string: YYYY-MM-DD
            $_POST['end_date']             // string: YYYY-MM-DD
        );
        
        $_SESSION['success'] = "Discount deal created successfully!";
        header('Location: discounts.php');
    } catch (Exception $e) {
        $_SESSION['error'] = "Error creating discount: " . $e->getMessage();
        header('Location: create-discount.php');
    }
}
*/

// ============================================================================
// EXAMPLE 6: Update Existing Order with Discounted Prices
// ============================================================================

/*
 * When storing an order, save the actual price customer paid
 * (which includes any discount that was active)
 */

/*
require_once __DIR__ . '/../../functions/discount-helpers.php';

$discount = new Discount();

// For each item in order
foreach ($order_items as $item) {
    $price_info = $discount->applyDiscountToPrice($item['price'], $item['product_id']);
    
    // Store this in order_items table:
    // - unit_price: Original price
    // - discount_applied: Discount percentage (if any)
    // - final_price: Price customer actually paid
    
    $insert_query = "
        INSERT INTO order_items (order_id, product_id, quantity, unit_price, discount_applied, final_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $db->prepare($insert_query);
    $stmt->bind_param(
        "iiiddd",
        $order_id,
        $item['product_id'],
        $item['quantity'],
        $price_info['original_price'],
        $price_info['discount_percent'],
        $price_info['discounted_price']
    );
    $stmt->execute();
}
*/

// ============================================================================
// EXAMPLE 7: Customer - See Discount Details on Order History
// ============================================================================

/*
 * Display discount info when customer views their order history
 */

/*
$query = "
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($items as $item): ?>
    <tr>
        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
        <td><?php echo $item['quantity']; ?></td>
        <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
        <?php if ($item['discount_applied'] > 0): ?>
            <td style="color: green;">
                <?php echo $item['discount_applied']; ?>% OFF
                <br>
                <small>-$<?php echo number_format(($item['unit_price'] - $item['final_price']), 2); ?></small>
            </td>
            <td>$<?php echo number_format($item['final_price'], 2); ?></td>
        <?php else: ?>
            <td>-</td>
            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
        <?php endif; ?>
        <td>$<?php echo number_format($item['final_price'] * $item['quantity'], 2); ?></td>
    </tr>
<?php endforeach;
*/

// ============================================================================
// EXAMPLE 8: API/AJAX - Get Discount Info for Product
// ============================================================================

/*
 * Create a discount-info.php endpoint for AJAX requests
 */

/*
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/functions/discount-helpers.php';

if (!isset($_GET['product_id'])) {
    echo json_encode(['error' => 'Product ID required']);
    exit;
}

$product_id = (int)$_GET['product_id'];
$price = (float)$_GET['price'] ?? 0;

if ($price <= 0) {
    echo json_encode(['error' => 'Valid price required']);
    exit;
}

$discount_info = getDiscountInfo($price, $product_id);

echo json_encode($discount_info);
?>
*/

// ============================================================================
// EXAMPLE 9: Remove/Delete a Discount
// ============================================================================

/*
 * Add this to handle discount deletion
 */

/*
require_once __DIR__ . '/../../classes/Discount.php';

if (isset($_GET['delete_id'])) {
    $discount = new Discount();
    try {
        $discount->removeDiscount($_GET['delete_id']);
        $_SESSION['success'] = "Discount deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
*/

// ============================================================================
// EXAMPLE 10: Search for Discounts by Various Criteria
// ============================================================================

/*
 * Example queries you might want to add
 */

/*
$discount = new Discount();

// Get all discounts for a specific product
$product_discounts = $discount->getProductDiscounts($product_id);

// Get all active discounts for seller
$active = $discount->getAllActiveDiscounts($seller_id);

// Get upcoming discounts (scheduled to start soon)
$upcoming = $discount->getUpcomingDiscounts($seller_id, 7);  // Next 7 days

// Get stats for seller dashboard
$stats = $discount->getDiscountStats($seller_id);

// All functions return properly formatted data ready to display
*/

?>
