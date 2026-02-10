<?php
/**
 * Discount Info API
 * Returns discount information for products in JSON format
 * Used by JavaScript for dynamic discount updates
 */

require_once '../../config.php';
require_once '../../functions/discount-helpers.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id']) || !isset($_GET['price'])) {
    echo json_encode(['error' => 'product_id and price parameters required']);
    exit;
}

$product_id = (int)$_GET['product_id'];
$price = (float)$_GET['price'];

if ($product_id <= 0 || $price < 0) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $discount_info = getDiscountInfo($price, $product_id);
    echo json_encode($discount_info);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
