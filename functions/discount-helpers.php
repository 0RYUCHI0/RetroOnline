<?php
/**
 * Discount Helper Functions
 * Quick access functions for applying discounts throughout the system
 */

require_once __DIR__ . '/../classes/Discount.php';

/**
 * Get the current discount for a product
 * This is a shorthand for checking if a product has an active discount
 */
function getProductDiscount($product_id) {
    $discount = new Discount();
    return $discount->getActiveDiscount($product_id);
}

/**
 * Get the discounted price for a product
 * Returns the final price after applying any active discounts
 */
function getDiscountedPrice($original_price, $product_id) {
    $discount = new Discount();
    $result = $discount->applyDiscountToPrice($original_price, $product_id);
    return $result['discounted_price'];
}

/**
 * Get discount information as array for displaying on product pages
 * Useful for showing discount badges and savings
 */
function getDiscountInfo($original_price, $product_id) {
    $discount = new Discount();
    return $discount->applyDiscountToPrice($original_price, $product_id);
}

/**
 * Check if a product has an active discount
 * @return bool
 */
function hasActiveDiscount($product_id) {
    $discount = new Discount();
    return $discount->getActiveDiscount($product_id) !== null;
}

/**
 * Get discount percent for a product
 * Returns discount percentage (0 if no active discount)
 */
function getDiscountPercent($product_id) {
    $discount = new Discount();
    $active = $discount->getActiveDiscount($product_id);
    return $active ? $active['discount_percent'] : 0;
}

/**
 * Get savings amount for a product
 * Returns how much money customer saves with the discount
 */
function getSavingsAmount($original_price, $product_id) {
    $discount = new Discount();
    $result = $discount->applyDiscountToPrice($original_price, $product_id);
    return $result['has_discount'] ? $result['discount_amount'] : 0;
}

/**
 * Format price with currency
 * Displays price with currency symbol
 */
function formatPrice($price, $currency = '$') {
    return $currency . number_format($price, 2, '.', ',');
}

/**
 * Format discount display
 * Displays discount in a nice format for UI
 */
function formatDiscount($discount_info) {
    if (!$discount_info['has_discount']) {
        return formatPrice($discount_info['original_price']);
    }

    $original = formatPrice($discount_info['original_price']);
    $discounted = formatPrice($discount_info['discounted_price']);
    $percent = $discount_info['discount_percent'];

    return "<span class='discount-price'>{$discounted}</span> <span class='original-price'><s>{$original}</s></span> <span class='discount-badge'>{$percent}% OFF</span>";
}

?>
