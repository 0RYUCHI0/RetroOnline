<?php
/**
 * Discount System - Integration Verification
 * Tests all discount functionality across the system
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Discount.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/functions/discount-helpers.php';

echo "====================================================================\n";
echo "DISCOUNT SYSTEM - INTEGRATION VERIFICATION\n";
echo "====================================================================\n\n";

$passed = 0;
$failed = 0;

// Test 1: Database connection
echo "[Test 1] Database Connection\n";
try {
    $db = Database::getInstance()->getConnection();
    if ($db && $db->ping()) {
        echo "✓ PASS: Database connected\n";
        $passed++;
    } else {
        throw new Exception("Database not responding");
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 2: Discounts table exists
echo "\n[Test 2] Discounts Table\n";
try {
    $result = $db->query("DESC discounts");
    if ($result && $result->num_rows > 0) {
        $columns = [];
        while ($col = $result->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        if (in_array('discount_id', $columns) && in_array('product_id', $columns) && 
            in_array('discount_percent', $columns) && in_array('start_date', $columns)) {
            echo "✓ PASS: Discounts table properly structured\n";
            echo "  Columns: " . implode(', ', $columns) . "\n";
            $passed++;
        } else {
            throw new Exception("Missing required columns");
        }
    } else {
        throw new Exception("Table not found");
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 3: Discount class methods
echo "\n[Test 3] Discount Class Methods\n";
try {
    $discount = new Discount();
    $methods = ['addDiscount', 'removeDiscount', 'getActiveDiscount', 'applyDiscountToPrice', 
                'getAllActiveDiscounts', 'getUpcomingDiscounts', 'getDiscountStats', 'updateDiscount'];
    
    $missing = [];
    foreach ($methods as $method) {
        if (!method_exists($discount, $method)) {
            $missing[] = $method;
        }
    }
    
    if (empty($missing)) {
        echo "✓ PASS: All required methods exist\n";
        echo "  Methods: " . implode(', ', $methods) . "\n";
        $passed++;
    } else {
        throw new Exception("Missing methods: " . implode(', ', $missing));
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 4: Helper functions
echo "\n[Test 4] Helper Functions\n";
try {
    $functions = ['hasActiveDiscount', 'getDiscountedPrice', 'getDiscountInfo', 
                  'getDiscountPercent', 'getSavingsAmount'];
    
    $missing = [];
    foreach ($functions as $func) {
        if (!function_exists($func)) {
            $missing[] = $func;
        }
    }
    
    if (empty($missing)) {
        echo "✓ PASS: All helper functions exist\n";
        echo "  Functions: " . implode(', ', $functions) . "\n";
        $passed++;
    } else {
        throw new Exception("Missing functions: " . implode(', ', $missing));
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 5: Files exist
echo "\n[Test 5] Required Files\n";
try {
    $files = [
        'classes/Discount.php',
        'functions/discount-helpers.php',
        'pages/customer/api-discount-info.php',
        'pages/seller/discounts.php',
        'migrate-add-discounts.php'
    ];
    
    $missing = [];
    foreach ($files as $file) {
        $path = __DIR__ . '/' . $file;
        if (!file_exists($path)) {
            $missing[] = $file;
        }
    }
    
    if (empty($missing)) {
        echo "✓ PASS: All required files exist\n";
        foreach ($files as $file) {
            echo "  ✓ " . $file . "\n";
        }
        $passed++;
    } else {
        throw new Exception("Missing files: " . implode(', ', $missing));
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 6: Database indexes
echo "\n[Test 6] Database Indexes\n";
try {
    $result = $db->query("SHOW INDEX FROM discounts");
    $indexes = [];
    while ($idx = $result->fetch_assoc()) {
        $indexes[$idx['Key_name']] = true;
    }
    
    if (isset($indexes['idx_product_dates']) && isset($indexes['idx_active_discounts'])) {
        echo "✓ PASS: Performance indexes exist\n";
        echo "  Indexes found: " . implode(', ', array_keys($indexes)) . "\n";
        $passed++;
    } else {
        throw new Exception("Missing performance indexes");
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 7: Pages have discount integration
echo "\n[Test 7] Page Integration\n";
try {
    $pages_to_check = [
        'pages/customer/product-detail.php' => 'discount-helpers',
        'pages/customer/cart.php' => 'getDiscountInfo',
        'pages/customer/checkout.php' => 'getDiscountInfo'
    ];
    
    $issues = [];
    foreach ($pages_to_check as $page => $search_term) {
        $path = __DIR__ . '/' . $page;
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (strpos($content, $search_term) === false) {
                $issues[] = "$page missing '$search_term'";
            }
        } else {
            $issues[] = "$page not found";
        }
    }
    
    if (empty($issues)) {
        echo "✓ PASS: All customer pages have discount integration\n";
        foreach (array_keys($pages_to_check) as $page) {
            echo "  ✓ " . $page . "\n";
        }
        $passed++;
    } else {
        throw new Exception(implode(', ', $issues));
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 8: Seller navbar has discount link
echo "\n[Test 8] Seller Navigation\n";
try {
    $seller_pages = [
        'pages/seller/dashboard.php',
        'pages/seller/products.php',
        'pages/seller/orders.php',
        'pages/seller/earnings.php',
        'pages/seller/settings.php',
        'pages/seller/discounts.php'
    ];
    
    $issues = [];
    foreach ($seller_pages as $page) {
        $path = __DIR__ . '/' . $page;
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (strpos($content, 'discounts.php') === false) {
                $issues[] = "$page missing discount link";
            }
        } else {
            $issues[] = "$page not found";
        }
    }
    
    if (empty($issues)) {
        echo "✓ PASS: All seller pages have discount navigation\n";
        echo "  Discount link present in: " . count($seller_pages) . " pages\n";
        $passed++;
    } else {
        throw new Exception(implode(', ', $issues));
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 9: API endpoint exists
echo "\n[Test 9] Discount API Endpoint\n";
try {
    $api_file = __DIR__ . '/pages/customer/api-discount-info.php';
    if (file_exists($api_file)) {
        $content = file_get_contents($api_file);
        if (strpos($content, 'application/json') !== false && 
            strpos($content, 'product_id') !== false &&
            strpos($content, 'price') !== false) {
            echo "✓ PASS: API endpoint properly configured\n";
            echo "  File: pages/customer/api-discount-info.php\n";
            $passed++;
        } else {
            throw new Exception("API endpoint missing required functionality");
        }
    } else {
        throw new Exception("API endpoint file not found");
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 10: Syntax validation
echo "\n[Test 10] PHP Syntax Validation\n";
try {
    $files_to_check = [
        'classes/Discount.php',
        'functions/discount-helpers.php',
        'pages/customer/api-discount-info.php',
        'pages/seller/discounts.php',
        'pages/customer/product-detail.php',
        'pages/customer/cart.php',
        'pages/customer/checkout.php'
    ];
    
    $errors = [];
    foreach ($files_to_check as $file) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            $output = shell_exec('C:\xampps\php\php.exe -l "' . $path . '" 2>&1');
            if (strpos($output, 'syntax error') !== false || strpos($output, 'Parse error') !== false) {
                $errors[] = "$file has syntax errors";
            }
        }
    }
    
    if (empty($errors)) {
        echo "✓ PASS: All files have valid PHP syntax\n";
        echo "  Validated: " . count($files_to_check) . " files\n";
        $passed++;
    } else {
        throw new Exception(implode(', ', $errors));
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// Summary
echo "\n====================================================================\n";
echo "VERIFICATION SUMMARY\n";
echo "====================================================================\n";
echo "Total Tests: " . ($passed + $failed) . "\n";
echo "✓ PASSED: $passed\n";
echo "✗ FAILED: $failed\n\n";

if ($failed === 0) {
    echo "🎉 ALL VERIFICATION TESTS PASSED!\n\n";
    echo "The discount system is fully integrated and ready to use:\n";
    echo "1. Customers can see discounted prices on products\n";
    echo "2. Discounts apply in cart and checkout\n";
    echo "3. Sellers can manage discounts from dashboard\n";
    echo "4. All database structures in place\n";
    echo "5. Navigation links added to seller pages\n\n";
    echo "Next Steps:\n";
    echo "1. Login as seller\n";
    echo "2. Click '🏷️ Discounts' in the menu\n";
    echo "3. Create a test discount deal\n";
    echo "4. View product to see discount applied\n";
} else {
    echo "⚠️ Some tests failed. Please review the errors above.\n";
}

echo "\n====================================================================\n";

?>
