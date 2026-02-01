<?php
/**
 * Recommendations Class
 * Handles product recommendations based on customer purchase history
 */

class Recommendations {
    private $db;
    private $product;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->product = new Product();
    }

    /**
     * Get recommended products based on customer's recent purchases
     * Strategy: Recommend products in same genre as most recent purchase
     * Excludes products customer already bought
     * 
     * @param int $customer_id Customer ID
     * @param int $limit Number of recommendations (default 5)
     * @return array Array of recommended products
     */
    public function getRecommendedProducts($customer_id, $limit = 5) {
        // Step 1: Get customer's most recent purchase category
        $recent_category = $this->getMostRecentCategory($customer_id);
        
        if (!$recent_category) {
            // If no purchases, return top rated products
            return $this->getTopRatedProducts($customer_id, $limit);
        }

        // Step 2: Get products in same category, excluding already purchased
        $query = "
            SELECT p.product_id, p.name, p.console, p.category,
                   p.condition, p.price, p.stock, p.image_url,
                   s.store_name,
                   ROUND(AVG(r.rating), 1) as avg_rating,
                   COUNT(r.review_id) as review_count
            FROM products p
            JOIN sellers s ON p.seller_id = s.seller_id
            LEFT JOIN reviews r ON p.product_id = r.product_id
            WHERE p.category = ? 
            AND p.stock > 0
            AND p.product_id NOT IN (
                SELECT DISTINCT oi.product_id 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.user_id = ?
            )
            GROUP BY p.product_id
            ORDER BY avg_rating DESC, review_count DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("sii", $recent_category, $customer_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }

        $stmt->close();

        // If we don't have enough recommendations from same category, 
        // fill with top rated from other categories
        if (count($recommendations) < $limit) {
            $remaining = $limit - count($recommendations);
            $topRated = $this->getTopRatedProducts($customer_id, $remaining, $recent_category);
            $recommendations = array_merge($recommendations, $topRated);
        }

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Get the most recent purchase category for a customer
     * 
     * @param int $customer_id Customer ID
     * @return string|null Category name or null if no purchases
     */
    private function getMostRecentCategory($customer_id) {
        $query = "
            SELECT p.category
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['category'];
        }

        $stmt->close();
        return null;
    }

    /**
     * Get top-rated products, excluding those customer bought
     * Used as fallback when specific category doesn't have enough recommendations
     * 
     * @param int $customer_id Customer ID
     * @param int $limit Number of products
     * @param string|null $exclude_category Optional category to exclude
     * @return array Array of top-rated products
     */
    private function getTopRatedProducts($customer_id, $limit = 5, $exclude_category = null) {
        $query = "
            SELECT p.product_id, p.name, p.console, p.category,
                   p.condition, p.price, p.stock, p.image_url,
                   s.store_name,
                   ROUND(AVG(r.rating), 1) as avg_rating,
                   COUNT(r.review_id) as review_count
            FROM products p
            JOIN sellers s ON p.seller_id = s.seller_id
            LEFT JOIN reviews r ON p.product_id = r.product_id
            WHERE p.stock > 0
            AND p.product_id NOT IN (
                SELECT DISTINCT oi.product_id 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.user_id = ?
            )
            " . ($exclude_category ? "AND p.category != ?" : "") . "
            GROUP BY p.product_id
            ORDER BY avg_rating DESC, review_count DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if ($exclude_category) {
            $stmt->bind_param("ssi", $customer_id, $exclude_category, $limit);
        } else {
            $stmt->bind_param("ii", $customer_id, $limit);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        return $products;
    }

    /**
     * Get products recommended based on similar genres/categories
     * More sophisticated: consider customer's entire purchase history
     * 
     * @param int $customer_id Customer ID
     * @param int $limit Number of recommendations
     * @return array Array of recommended products
     */
    public function getSmartRecommendations($customer_id, $limit = 5) {
        // Get customer's most purchased categories
        $query = "
            SELECT p.category, COUNT(*) as purchase_count
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.user_id = ?
            GROUP BY p.category
            ORDER BY purchase_count DESC
            LIMIT 3
        ";

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            return $this->getRecommendedProducts($customer_id, $limit);
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        $stmt->close();

        if (empty($categories)) {
            return $this->getTopRatedProducts($customer_id, $limit);
        }

        // Get products from these categories, not previously purchased
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $query = "
            SELECT p.product_id, p.name, p.console, p.category,
                   p.condition, p.price, p.stock, p.image_url,
                   s.store_name,
                   ROUND(AVG(r.rating), 1) as avg_rating,
                   COUNT(r.review_id) as review_count
            FROM products p
            JOIN sellers s ON p.seller_id = s.seller_id
            LEFT JOIN reviews r ON p.product_id = r.product_id
            WHERE p.category IN ($placeholders)
            AND p.stock > 0
            AND p.product_id NOT IN (
                SELECT DISTINCT oi.product_id 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.user_id = ?
            )
            GROUP BY p.product_id
            ORDER BY avg_rating DESC, review_count DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            return $this->getRecommendedProducts($customer_id, $limit);
        }

        // Bind parameters dynamically
        $params = array_merge($categories, [$customer_id, $limit]);
        $types = str_repeat('s', count($categories)) . 'ii';
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }

        $stmt->close();
        return array_slice($recommendations, 0, $limit);
    }
}
?>
