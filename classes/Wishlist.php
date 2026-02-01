<?php
/**
 * Wishlist Class
 * Handles wishlist management for customers
 */

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add product to customer's wishlist
     * 
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return bool True if added, false if already in wishlist
     */
    public function addToWishlist($customer_id, $product_id) {
        // Check if already in wishlist
        if ($this->isInWishlist($customer_id, $product_id)) {
            return false;
        }

        $query = "INSERT INTO wishlist (customer_id, product_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("ii", $customer_id, $product_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            throw new Exception("Failed to add to wishlist: " . $this->db->error);
        }
    }

    /**
     * Remove product from wishlist
     * 
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return bool True if removed
     */
    public function removeFromWishlist($customer_id, $product_id) {
        $query = "DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("ii", $customer_id, $product_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            throw new Exception("Failed to remove from wishlist: " . $this->db->error);
        }
    }

    /**
     * Check if product is in customer's wishlist
     * 
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return bool True if in wishlist
     */
    public function isInWishlist($customer_id, $product_id) {
        $query = "SELECT wishlist_id FROM wishlist WHERE customer_id = ? AND product_id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result->num_rows > 0;
    }

    /**
     * Get customer's wishlist with product details
     * 
     * @param int $customer_id Customer ID
     * @return array Array of wishlist items with product details
     */
    public function getWishlist($customer_id) {
        $query = "
            SELECT w.wishlist_id, w.date_added,
                   p.product_id, p.name, p.console, p.category, 
                   p.condition, p.price, p.stock, p.image_url,
                   s.store_name,
                   ROUND(AVG(r.rating), 1) as avg_rating,
                   COUNT(r.review_id) as review_count
            FROM wishlist w
            JOIN products p ON w.product_id = p.product_id
            JOIN sellers s ON p.seller_id = s.seller_id
            LEFT JOIN reviews r ON p.product_id = r.product_id
            WHERE w.customer_id = ?
            GROUP BY w.wishlist_id, p.product_id
            ORDER BY w.date_added DESC
        ";

        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $stmt->close();
        return $items;
    }

    /**
     * Get count of items in customer's wishlist
     * 
     * @param int $customer_id Customer ID
     * @return int Number of items in wishlist
     */
    public function getWishlistCount($customer_id) {
        $query = "SELECT COUNT(*) as count FROM wishlist WHERE customer_id = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)$row['count'];
    }

    /**
     * Clear entire wishlist for a customer
     * 
     * @param int $customer_id Customer ID
     * @return bool True if cleared
     */
    public function clearWishlist($customer_id) {
        $query = "DELETE FROM wishlist WHERE customer_id = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $customer_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            throw new Exception("Failed to clear wishlist: " . $this->db->error);
        }
    }

    /**
     * Move item from wishlist to cart (for use with cart system)
     * Removes from wishlist after moving
     * 
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return bool True if moved
     */
    public function moveToCart($customer_id, $product_id) {
        return $this->removeFromWishlist($customer_id, $product_id);
    }
}
?>
