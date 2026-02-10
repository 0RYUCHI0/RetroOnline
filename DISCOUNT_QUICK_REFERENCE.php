<?php
/**
 * DISCOUNT SYSTEM - QUICK REFERENCE CARD
 * 
 * Keep this file handy for quick lookups while coding
 */
?>

===============================================================================
                    DISCOUNT SYSTEM - QUICK REFERENCE
===============================================================================

DATABASE TABLE: discounts
┌─────────────────┬─────────────────────┬─────────────────────────────────┐
│ Column          │ Type                │ Description                     │
├─────────────────┼─────────────────────┼─────────────────────────────────┤
│ discount_id     │ INT AUTO_INCREMENT  │ Primary key                     │
│ product_id      │ INT NOT NULL        │ Foreign key to products         │
│ discount_percent│ DECIMAL(5,2)        │ Percentage (0-100)              │
│ start_date      │ DATE NOT NULL       │ When discount begins (YYYY-MM-DD)
│ end_date        │ DATE NOT NULL       │ When discount ends (YYYY-MM-DD) │
│ created_at      │ TIMESTAMP           │ Record creation time            │
│ updated_at      │ TIMESTAMP           │ Last update time                │
└─────────────────┴─────────────────────┴─────────────────────────────────┘

===============================================================================
CORE FUNCTIONS (Most Common)
===============================================================================

1. CHECK IF PRODUCT HAS ACTIVE DISCOUNT
   ────────────────────────────────────
   $has_discount = hasActiveDiscount($product_id);
   
   Returns: Boolean (true/false)
   Use when: Determining if to show discount badge


2. GET DISCOUNTED PRICE
   ────────────────────
   $final_price = getDiscountedPrice($original_price, $product_id);
   
   Returns: Float (final price after discount)
   Use when: Calculating cart totals or displaying checkout price


3. GET FULL DISCOUNT INFO
   ─────────────────────
   $info = getDiscountInfo($price, $product_id);
   
   Returns Array:
   [
       'original_price' => 100.00,
       'discount_percent' => 15.5,
       'discount_amount' => 15.50,
       'discounted_price' => 84.50,
       'has_discount' => true,
       'discount_id' => 1,
       'start_date' => '2026-02-10',
       'end_date' => '2026-02-28'
   ]


4. GET DISCOUNT PERCENT
   ─────────────────────
   $percent = getDiscountPercent($product_id);
   
   Returns: Float (0 if no discount)
   Use when: Displaying "15% OFF" badges


5. GET SAVINGS AMOUNT
   ──────────────────
   $savings = getSavingsAmount($original_price, $product_id);
   
   Returns: Float (amount saved)
   Use when: Showing "Save $X.XX" messages

===============================================================================
DISCOUNT CLASS METHODS (For Detailed Operations)
===============================================================================

Create Instance:
    $discount = new Discount();

Add New Discount:
    $discount_id = $discount->addDiscount(
        $product_id,        // Required: int
        $discount_percent,  // Required: float (0-100)
        $start_date,        // Required: string 'YYYY-MM-DD'
        $end_date           // Required: string 'YYYY-MM-DD'
    );
    // Throws Exception on error

Remove Discount:
    $discount->removeDiscount($discount_id);
    // Throws Exception if not found

Get Active Discount:
    $active = $discount->getActiveDiscount($product_id);
    // Returns: Array or NULL

Apply Discount to Price:
    $info = $discount->applyDiscountToPrice($price, $product_id);
    // Returns: Array with all discount details

Update Discount:
    $discount->updateDiscount(
        $discount_id,
        $new_percent,
        $new_start_date,
        $new_end_date
    );

Get All Active Discounts (for seller):
    $active = $discount->getAllActiveDiscounts($seller_id);
    // Returns: Array of active discount records

Get Upcoming Discounts:
    $upcoming = $discount->getUpcomingDiscounts($seller_id, $days = 7);
    // Returns: Array of discounts starting soon

Get Discount Stats:
    $stats = $discount->getDiscountStats($seller_id);
    // Returns: Array with totals, active count, etc.

===============================================================================
VALIDATION RULES
===============================================================================

DISCOUNT PERCENT:
✓ Must be between 0 and 100
✓ Decimal places allowed (e.g., 15.5)
✗ Cannot be negative
✗ Cannot exceed 100

DATES:
✓ Format: YYYY-MM-DD (e.g., 2026-02-28)
✓ Must be valid calendar dates
✗ End date must be AFTER start date
✗ Cannot be in the past (recommendation)

PRODUCT:
✓ Must exist in products table
✗ Cannot create discount for non-existent product

OVERLAPPING:
✗ Cannot have 2 discounts on same product with overlapping dates
✓ System automatically detects and prevents overlaps

===============================================================================
ERROR HANDLING
===============================================================================

All class methods throw Exception on error.
Always use try-catch:

    try {
        $discount->addDiscount($product_id, $percent, $start, $end);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

Common Error Messages:
    "Discount percent must be between 0 and 100"
    "Invalid start date format (use YYYY-MM-DD)"
    "End date must be after start date"
    "Product not found"
    "This product already has an overlapping discount deal"
    "Discount not found"

===============================================================================
COMMON CODE PATTERNS
===============================================================================

PATTERN 1: Display price with discount on product page
─────────────────────────────────────────────────────
$info = getDiscountInfo($product['price'], $product['product_id']);
if ($info['has_discount']) {
    echo "<del>\${$info['original_price']}</del> ";
    echo "\${$info['discounted_price']} ";
    echo "{$info['discount_percent']}% OFF";
}


PATTERN 2: Calculate cart total with discounts
──────────────────────────────────────────────
$total = 0;
foreach ($cart_items as $item) {
    $price = getDiscountedPrice($item['price'], $item['product_id']);
    $total += $price * $item['quantity'];
}
echo "Total: \$" . number_format($total, 2);


PATTERN 3: Show discount stats on seller dashboard
──────────────────────────────────────────────────
$discount = new Discount();
$stats = $discount->getDiscountStats($_SESSION['user_id']);
echo "Active: " . $stats['active_discounts'];
echo "Upcoming: " . $stats['upcoming_discounts'];


PATTERN 4: Create discount from form submission
───────────────────────────────────────────────
$discount = new Discount();
try {
    $id = $discount->addDiscount(
        $_POST['product_id'],
        $_POST['discount_percent'],
        $_POST['start_date'],
        $_POST['end_date']
    );
    echo "Created: Discount #$id";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}


PATTERN 5: Get discount info via AJAX
─────────────────────────────────────
// In JavaScript:
fetch('/discount-info.php?product_id=' + id + '&price=' + price)
    .then(r => r.json())
    .then(data => {
        if (data.has_discount) {
            document.getElementById('price').innerHTML = 
                '<del>$' + data.original_price + 
                '</del> $' + data.discounted_price + 
                ' (' + data.discount_percent + '% OFF)';
        }
    });

===============================================================================
PERFORMANCE TIPS
===============================================================================

✓ Use getDiscountedPrice() for single product (optimized)
✓ Cache discount results for high-traffic pages (1 hour TTL)
✓ Use getAllActiveDiscounts() for bulk operations
✓ Database indexes handle date range queries efficiently
✓ Avoid calling getActiveDiscount() multiple times for same product

⚠ Do NOT manually query discounts table - use Discount class
⚠ Do NOT calculate discount yourself - use applyDiscountToPrice()
⚠ Do NOT check for overlaps manually - class does this automatically

===============================================================================
SECURITY
===============================================================================

✓ All queries use prepared statements
✓ Input validation on all user data
✓ Foreign key constraints prevent orphaned records
✓ Activity logging tracks all changes
✓ Session checks prevent unauthorized access (implement in UI)

⚠ Always verify seller owns product before allowing discount edit
⚠ Only show seller their own discount stats
⚠ Never trust client-side discount calculations

===============================================================================
INTEGRATION CHECKLIST
===============================================================================

Product Page:
    [ ] Import discount-helpers.php
    [ ] Add getDiscountInfo() call
    [ ] Display discounted price if has_discount
    [ ] Show discount badge/savings

Cart Page:
    [ ] Loop through items
    [ ] Call getDiscountedPrice() for each
    [ ] Sum total with discounted prices
    [ ] Show total savings
    [ ] Display "(X% OFF)" badges

Checkout Page:
    [ ] Show item prices with discounts
    [ ] Verify discounts still active (dates haven't changed)
    [ ] Store actual prices paid in order
    [ ] Show discount details in order review

Order History:
    [ ] Retrieve discount_applied from order_items
    [ ] Display original vs final price
    [ ] Show discount percentage
    [ ] Show savings amount

Seller Dashboard:
    [ ] Show discount stats widget
    [ ] List active discounts with edit/delete
    [ ] List upcoming discounts
    [ ] Provide "Create Discount" button

Admin Panel:
    [ ] View all discounts across sellers
    [ ] Filter by seller, product, status
    [ ] Edit/delete any discount
    [ ] View discount performance

===============================================================================
FILES CREATED
===============================================================================

Core Classes:
    classes/Discount.php                   - Main discount class

Helper Functions:
    functions/discount-helpers.php         - Quick access functions

Database:
    discounts table (auto-created by migration)

Documentation:
    DISCOUNT_SYSTEM_GUIDE.php             - Complete documentation
    DISCOUNT_INTEGRATION_EXAMPLES.php     - 10 detailed examples
    DISCOUNT_QUICK_REFERENCE.php          - This file

Migration:
    migrate-add-discounts.php             - Setup script

===============================================================================
USEFUL SQL QUERIES (For Reference)
===============================================================================

Get all active discounts:
    SELECT * FROM discounts
    WHERE DATE(start_date) <= CURDATE()
    AND DATE(end_date) >= CURDATE()

Get expired discounts:
    SELECT * FROM discounts
    WHERE DATE(end_date) < CURDATE()

Get upcoming discounts:
    SELECT * FROM discounts
    WHERE DATE(start_date) > CURDATE()

Get product with discount:
    SELECT p.*, d.discount_percent, d.discount_id
    FROM products p
    LEFT JOIN discounts d ON p.product_id = d.product_id
    WHERE p.product_id = ?
    AND (d.start_date IS NULL OR DATE(d.start_date) <= CURDATE())
    AND (d.end_date IS NULL OR DATE(d.end_date) >= CURDATE())

===============================================================================

Last Updated: 2026-02-10
Version: 1.0
Status: Production Ready

===============================================================================
?>
