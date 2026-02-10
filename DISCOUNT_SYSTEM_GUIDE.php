<?php
/**
 * ============================================================================
 * DISCOUNT DEALS SYSTEM - IMPLEMENTATION GUIDE
 * ============================================================================
 * 
 * OVERVIEW:
 * The discount system allows sellers to create time-based discount deals for
 * their products. Discounts are automatically applied to product prices during
 * checkout and on product pages.
 * 
 * ============================================================================
 * DATABASE TABLE
 * ============================================================================
 * 
 * CREATE TABLE discounts (
 *     discount_id INT AUTO_INCREMENT PRIMARY KEY,
 *     product_id INT NOT NULL,
 *     discount_percent DECIMAL(5, 2) NOT NULL,
 *     start_date DATE NOT NULL,
 *     end_date DATE NOT NULL,
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 *     
 *     CONSTRAINT fk_discount_product FOREIGN KEY (product_id) 
 *         REFERENCES products(product_id) ON DELETE CASCADE,
 *     INDEX idx_product_dates (product_id, start_date, end_date),
 *     INDEX idx_active_discounts (start_date, end_date),
 *     UNIQUE KEY uk_product_dates (product_id, start_date, end_date)
 * );
 * 
 * Run migration: php migrate-add-discounts.php
 * 
 * ============================================================================
 * DISCOUNT CLASS - CORE METHODS
 * ============================================================================
 * 
 * 1. ADD A NEW DISCOUNT
 * --------------------
 * $discount = new Discount();
 * $discount_id = $discount->addDiscount(
 *     $product_id,           // int: Product ID
 *     $discount_percent,     // float: 0-100
 *     $start_date,          // string: YYYY-MM-DD
 *     $end_date             // string: YYYY-MM-DD
 * );
 * 
 * Example:
 *     $discount->addDiscount(5, 15.50, '2026-02-10', '2026-02-28');
 * 
 * Features:
 * - Validates discount percent (0-100)
 * - Validates date format (YYYY-MM-DD)
 * - Ensures end date is after start date
 * - Prevents overlapping discounts on same product
 * - Logs action if seller is logged in
 * 
 * 
 * 2. REMOVE A DISCOUNT
 * --------------------
 * $discount->removeDiscount($discount_id);
 * 
 * Example:
 *     $discount->removeDiscount(1);
 * 
 * Features:
 * - Validates discount exists
 * - Logs deletion for audit trail
 * 
 * 
 * 3. GET ACTIVE DISCOUNT (MOST IMPORTANT FOR CHECKOUT)
 * ---------------------
 * $active_discount = $discount->getActiveDiscount($product_id);
 * 
 * Example:
 *     $active_discount = $discount->getActiveDiscount(5);
 *     if ($active_discount) {
 *         echo "Product has " . $active_discount['discount_percent'] . "% off";
 *     }
 * 
 * Returns:
 * Array with keys: discount_id, product_id, discount_percent, start_date, end_date
 * Or: NULL if no active discount
 * 
 * Only returns discount if current date is between start_date and end_date.
 * 
 * 
 * 4. APPLY DISCOUNT TO PRICE (CORE CALCULATION)
 * -----------------------------------------------
 * $price_info = $discount->applyDiscountToPrice($original_price, $product_id);
 * 
 * Example:
 *     $info = $discount->applyDiscountToPrice(100.00, 5);
 *     echo "Price: " . $info['discounted_price'];     // 85.00 (if 15% off)
 *     echo "Savings: $" . $info['discount_amount'];   // 15.00
 * 
 * Returns Array:
 * [
 *     'original_price' => 100.00,
 *     'discount_percent' => 15.5,
 *     'discount_amount' => 15.50,
 *     'discounted_price' => 84.50,
 *     'has_discount' => true,
 *     'discount_id' => 1,
 *     'start_date' => '2026-02-10',
 *     'end_date' => '2026-02-28'
 * ]
 * 
 * If no active discount, has_discount = false and discounted_price = original_price
 * 
 * 
 * 5. UPDATE DISCOUNT
 * ------------------
 * $discount->updateDiscount($discount_id, $new_percent, $new_start, $new_end);
 * 
 * Example:
 *     $discount->updateDiscount(1, 20.00, '2026-02-10', '2026-03-10');
 * 
 * Features:
 * - Same validation as addDiscount
 * - Prevents overlapping with other discounts
 * - Logs update action
 * 
 * 
 * 6. GET ALL ACTIVE DISCOUNTS (FOR SELLER DASHBOARD)
 * --------------------------------------------------
 * $active_discounts = $discount->getAllActiveDiscounts($seller_id);
 * 
 * Example:
 *     $discounts = $discount->getAllActiveDiscounts(3);  // Seller 3's active deals
 *     $discounts = $discount->getAllActiveDiscounts();   // All active discounts
 * 
 * Returns: Array of discounts with product info (name, price, seller_id)
 * 
 * 
 * 7. GET UPCOMING DISCOUNTS
 * -------------------------
 * $upcoming = $discount->getUpcomingDiscounts($seller_id, $days = 7);
 * 
 * Example:
 *     $upcoming = $discount->getUpcomingDiscounts(3, 7);  // Next 7 days
 * 
 * Returns: Array of discounts scheduled to start soon
 * 
 * 
 * 8. GET DISCOUNT STATS
 * ---------------------
 * $stats = $discount->getDiscountStats($seller_id);
 * 
 * Returns:
 * [
 *     'total_discounts' => 10,
 *     'active_discounts' => 3,
 *     'upcoming_discounts' => 2,
 *     'expired_discounts' => 5,
 *     'avg_discount' => 15.75
 * ]
 * 
 * ============================================================================
 * HELPER FUNCTIONS (QUICK ACCESS)
 * ============================================================================
 * 
 * Include at top of page: require_once 'functions/discount-helpers.php';
 * 
 * 1. Check if product has discount
 *    $has_discount = hasActiveDiscount($product_id);
 * 
 * 2. Get discounted price directly
 *    $final_price = getDiscountedPrice($original_price, $product_id);
 * 
 * 3. Get discount info array
 *    $info = getDiscountInfo($price, $product_id);
 * 
 * 4. Get discount percentage
 *    $percent = getDiscountPercent($product_id);
 * 
 * 5. Get savings amount
 *    $savings = getSavingsAmount($price, $product_id);
 * 
 * ============================================================================
 * INTEGRATION EXAMPLES
 * ============================================================================
 * 
 * EXAMPLE 1: Show discounted price on product page
 * ------------------------------------------------
 * require_once 'functions/discount-helpers.php';
 * 
 * $product = [...]; // Your product from database
 * $discount_info = getDiscountInfo($product['price'], $product['product_id']);
 * 
 * if ($discount_info['has_discount']) {
 *     echo "Original: $" . number_format($discount_info['original_price'], 2);
 *     echo "Now: $" . number_format($discount_info['discounted_price'], 2);
 *     echo "Save: {$discount_info['discount_percent']}%";
 * } else {
 *     echo "Price: $" . number_format($product['price'], 2);
 * }
 * 
 * 
 * EXAMPLE 2: Apply discount in cart total calculation
 * ---------------------------------------------------
 * $total = 0;
 * foreach ($cart_items as $item) {
 *     $price = getDiscountedPrice($item['price'], $item['product_id']);
 *     $total += $price * $item['quantity'];
 * }
 * 
 * 
 * EXAMPLE 3: Create discount from seller form
 * ------------------------------------------
 * $discount = new Discount();
 * try {
 *     $discount_id = $discount->addDiscount(
 *         $_POST['product_id'],
 *         $_POST['discount_percent'],
 *         $_POST['start_date'],
 *         $_POST['end_date']
 *     );
 *     echo "Discount created successfully!";
 * } catch (Exception $e) {
 *     echo "Error: " . $e->getMessage();
 * }
 * 
 * 
 * EXAMPLE 4: Display on checkout page
 * -----------------------------------
 * $discount = new Discount();
 * foreach ($order_items as $item) {
 *     $price_info = $discount->applyDiscountToPrice(
 *         $item['price'],
 *         $item['product_id']
 *     );
 *     
 *     echo "Item: " . $item['name'];
 *     echo "Unit Price: $" . number_format($price_info['discounted_price'], 2);
 *     
 *     if ($price_info['has_discount']) {
 *         echo "Savings: $" . number_format($price_info['discount_amount'], 2);
 *     }
 * }
 * 
 * 
 * EXAMPLE 5: Seller discount management page
 * -----------------------------------------
 * $discount = new Discount();
 * $seller_id = $_SESSION['user_id'];
 * 
 * $active = $discount->getAllActiveDiscounts($seller_id);
 * $upcoming = $discount->getUpcomingDiscounts($seller_id, 7);
 * $stats = $discount->getDiscountStats($seller_id);
 * 
 * Display:
 * - "Active Discounts: {$stats['active_discounts']}"
 * - "Upcoming Discounts: {$stats['upcoming_discounts']}"
 * - List of active discounts with edit/delete options
 * - List of upcoming discounts
 * 
 * ============================================================================
 * VALIDATION & BUSINESS RULES
 * ============================================================================
 * 
 * 1. DISCOUNT PERCENT
 *    - Must be 0-100
 *    - Decimal places allowed (e.g., 15.5%)
 *    - No negative discounts
 * 
 * 2. DATES
 *    - Format: YYYY-MM-DD (e.g., 2026-02-28)
 *    - End date must be after start date
 *    - No time component (full day basis)
 * 
 * 3. OVERLAPPING PREVENTION
 *    - Same product cannot have 2 active discounts at same time
 *    - System checks all date ranges for conflicts
 *    - Clear error message if overlap detected
 * 
 * 4. PRODUCT REQUIREMENT
 *    - Product must exist in database
 *    - Foreign key constraint enforces this
 *    - Deleting product automatically removes discount
 * 
 * 5. ACTIVE DISCOUNT SELECTION
 *    - If multiple historical discounts exist, only one can be active
 *    - Active discount has highest priority (by date)
 *    - Sorting by discount_percent DESC ensures best deal shown
 * 
 * ============================================================================
 * ERROR HANDLING
 * ============================================================================
 * 
 * All methods throw Exception on error. Use try-catch:
 * 
 * try {
 *     $discount->addDiscount($product_id, $percent, $start, $end);
 * } catch (Exception $e) {
 *     echo "Error: " . $e->getMessage();
 * }
 * 
 * Common errors:
 * - "Discount percent must be between 0 and 100"
 * - "Invalid start date format (use YYYY-MM-DD)"
 * - "End date must be after start date"
 * - "Product not found"
 * - "This product already has an overlapping discount deal"
 * - "Discount not found"
 * 
 * ============================================================================
 * PERFORMANCE NOTES
 * ============================================================================
 * 
 * Indexes Created:
 * - idx_product_dates: Fast lookup of discounts by product and date range
 * - idx_active_discounts: Fast query for currently active discounts
 * - uk_product_dates: Unique constraint prevents duplicate date ranges
 * 
 * Query Performance:
 * - getActiveDiscount(): O(log n) - Highly optimized
 * - applyDiscountToPrice(): O(log n) - Single index lookup
 * - getAllActiveDiscounts(): O(log n) - Uses date range index
 * 
 * Caching Strategy (Optional):
 * - Cache active discounts for 1 hour
 * - Invalidate cache when discount added/updated/deleted
 * - Use Redis or file cache for high-traffic sites
 * 
 * ============================================================================
 * SECURITY
 * ============================================================================
 * 
 * 1. All queries use prepared statements
 * 2. All input validated before database operations
 * 3. Foreign key constraints prevent orphaned records
 * 4. Activity logging tracks all changes
 * 5. Seller can only manage their own product discounts (implement in UI)
 * 6. Admin can view/manage all discounts
 * 
 * ============================================================================
 * LOGGING & AUDIT TRAIL
 * ============================================================================
 * 
 * All discount changes are logged to activity_logs table:
 * - discount_create: When new discount is added
 * - discount_update: When discount is modified
 * - discount_delete: When discount is removed
 * 
 * Logged info includes:
 * - user_id: Who made the change
 * - timestamp: When change occurred
 * - old_data: Previous values
 * - new_data: Updated values
 * - product_id: Which product affected
 * 
 * ============================================================================
 */

// Examples end here - see code documentation above for implementation
?>
