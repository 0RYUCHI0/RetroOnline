<?php
/**
 * Discount System - Test Script
 * Run this to verify the discount system is working correctly
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Discount.php';
require_once __DIR__ . '/functions/discount-helpers.php';

echo "====================================================================\n";
echo "DISCOUNT SYSTEM - TEST SUITE\n";
echo "====================================================================\n\n";

$discount = new Discount();
$tests_passed = 0;
$tests_failed = 0;

// Test 1: Check if discounts table exists
echo "[Test 1] Checking if discounts table exists...\n";
try {
    $result = Database::getInstance()->getConnection()->query("SELECT 1 FROM discounts LIMIT 1");
    if ($result !== false) {
        echo "✓ PASS: Discounts table exists and is accessible\n";
        $tests_passed++;
    } else {
        throw new Exception("Table not found");
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 2: Get a product to test with
echo "\n[Test 2] Finding a test product...\n";
$db = Database::getInstance()->getConnection();
$result = $db->query("SELECT product_id, name, price FROM products LIMIT 1");
if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo "✓ PASS: Found product: " . htmlspecialchars($product['name']) . " (ID: {$product['product_id']}, Price: \${$product['price']})\n";
    $tests_passed++;
    $test_product_id = $product['product_id'];
    $test_product_price = $product['price'];
} else {
    echo "✗ FAIL: No products found in database\n";
    $tests_failed++;
    echo "Cannot continue with remaining tests without a product.\n";
    exit(1);
}

// Test 3: Try adding a discount
echo "\n[Test 3] Adding a test discount...\n";
try {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+30 days'));
    $discount_id = $discount->addDiscount($test_product_id, 15.50, $start_date, $end_date);
    echo "✓ PASS: Discount created successfully (ID: $discount_id)\n";
    $tests_passed++;
    $test_discount_id = $discount_id;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $tests_failed++;
    $test_discount_id = null;
}

// Test 4: Retrieve active discount
echo "\n[Test 4] Retrieving active discount...\n";
try {
    $active = $discount->getActiveDiscount($test_product_id);
    if ($active) {
        echo "✓ PASS: Active discount found\n";
        echo "   - Discount ID: {$active['discount_id']}\n";
        echo "   - Discount %: {$active['discount_percent']}%\n";
        echo "   - Valid from: {$active['start_date']} to {$active['end_date']}\n";
        $tests_passed++;
    } else {
        echo "✗ FAIL: No active discount found\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 5: Apply discount to price
echo "\n[Test 5] Applying discount to price...\n";
try {
    $price_info = $discount->applyDiscountToPrice($test_product_price, $test_product_id);
    if ($price_info['has_discount']) {
        echo "✓ PASS: Discount applied successfully\n";
        echo "   - Original price: \${$price_info['original_price']}\n";
        echo "   - Discount: {$price_info['discount_percent']}%\n";
        echo "   - Discount amount: \${$price_info['discount_amount']}\n";
        echo "   - Final price: \${$price_info['discounted_price']}\n";
        $tests_passed++;
    } else {
        echo "✗ FAIL: No discount was applied\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 6: Test helper functions
echo "\n[Test 6] Testing helper functions...\n";
try {
    $has_disc = hasActiveDiscount($test_product_id);
    $price = getDiscountedPrice($test_product_price, $test_product_id);
    $percent = getDiscountPercent($test_product_id);
    $savings = getSavingsAmount($test_product_price, $test_product_id);
    
    echo "✓ PASS: Helper functions working\n";
    echo "   - hasActiveDiscount(): " . ($has_disc ? 'true' : 'false') . "\n";
    echo "   - getDiscountedPrice(): \$$price\n";
    echo "   - getDiscountPercent(): $percent%\n";
    echo "   - getSavingsAmount(): \$$savings\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 7: Check overlapping discount prevention
echo "\n[Test 7] Testing overlapping discount prevention...\n";
try {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+15 days'));
    
    $discount->addDiscount($test_product_id, 20, $start_date, $end_date);
    echo "✗ FAIL: Overlapping discount was allowed (should have been prevented)\n";
    $tests_failed++;
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'overlap') !== false) {
        echo "✓ PASS: Overlapping discount prevention is working\n";
        echo "   - Error message: " . $e->getMessage() . "\n";
        $tests_passed++;
    } else {
        echo "⚠ ERROR: Different exception: " . $e->getMessage() . "\n";
        $tests_failed++;
    }
}

// Test 8: Get product discounts
echo "\n[Test 8] Getting all discounts for product...\n";
try {
    $all_discounts = $discount->getProductDiscounts($test_product_id);
    echo "✓ PASS: Retrieved discount history\n";
    echo "   - Total discounts for this product: " . count($all_discounts) . "\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 9: Update discount
echo "\n[Test 9] Testing discount update...\n";
if (isset($test_discount_id)) {
    try {
        $new_start = date('Y-m-d', strtotime('+1 day'));
        $new_end = date('Y-m-d', strtotime('+45 days'));
        $discount->updateDiscount($test_discount_id, 20, $new_start, $new_end);
        echo "✓ PASS: Discount updated successfully\n";
        echo "   - New discount %: 20%\n";
        echo "   - New dates: $new_start to $new_end\n";
        $tests_passed++;
    } catch (Exception $e) {
        echo "✗ FAIL: " . $e->getMessage() . "\n";
        $tests_failed++;
    }
} else {
    echo "⊘ SKIP: No discount to update\n";
}

// Test 10: Delete discount
echo "\n[Test 10] Testing discount deletion...\n";
if (isset($test_discount_id)) {
    try {
        $discount->removeDiscount($test_discount_id);
        echo "✓ PASS: Discount deleted successfully\n";
        
        // Verify deletion
        $check = $discount->getDiscountById($test_discount_id);
        if ($check === null) {
            echo "✓ VERIFIED: Discount no longer exists in database\n";
            $tests_passed++;
        } else {
            echo "✗ FAIL: Discount still exists after deletion\n";
            $tests_failed++;
        }
    } catch (Exception $e) {
        echo "✗ FAIL: " . $e->getMessage() . "\n";
        $tests_failed++;
    }
} else {
    echo "⊘ SKIP: No discount to delete\n";
}

// Summary
echo "\n====================================================================\n";
echo "TEST SUMMARY\n";
echo "====================================================================\n";
echo "Total Tests: " . ($tests_passed + $tests_failed) . "\n";
echo "✓ PASSED: $tests_passed\n";
echo "✗ FAILED: $tests_failed\n";

if ($tests_failed === 0) {
    echo "\n🎉 ALL TESTS PASSED! The discount system is working perfectly.\n";
    echo "You can now integrate discounts into your product pages and cart.\n";
} else {
    echo "\n⚠ Some tests failed. Please review the errors above.\n";
}

echo "\n====================================================================\n";
echo "NEXT STEPS:\n";
echo "====================================================================\n";
echo "1. Review DISCOUNT_SYSTEM_GUIDE.php for complete documentation\n";
echo "2. Check DISCOUNT_INTEGRATION_EXAMPLES.php for code examples\n";
echo "3. Use DISCOUNT_QUICK_REFERENCE.php for quick lookups\n";
echo "4. Integrate discount functions into your pages:\n";
echo "   - Product pages (display discounted price)\n";
echo "   - Cart pages (calculate totals with discounts)\n";
echo "   - Checkout (show final prices)\n";
echo "   - Seller dashboard (manage discounts)\n";
echo "\n====================================================================\n";

?>
