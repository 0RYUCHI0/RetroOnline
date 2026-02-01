<?php
/**
 * Review Class
 * Handles product and seller reviews
 */

class Review {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create review
     */
    public function createReview($product_id, $user_id, $seller_id, $rating, $comment) {
        if ($rating < 1 || $rating > 5) {
            throw new Exception("Rating must be between 1 and 5");
        }

        $stmt = $this->db->prepare("
            INSERT INTO reviews (product_id, user_id, seller_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiis", $product_id, $user_id, $seller_id, $rating, $comment);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Failed to create review: " . $stmt->error);
        }
    }

    /**
     * Get product reviews
     */
    public function getProductReviews($product_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as reviewer_name
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get seller reviews
     */
    public function getSellerReviews($seller_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as reviewer_name, p.name as product_name
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            JOIN products p ON r.product_id = p.product_id
            WHERE r.seller_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get product average rating
     */
    public function getProductRating($product_id) {
        $stmt = $this->db->prepare("
            SELECT 
                ROUND(AVG(rating), 1) as avg_rating,
                COUNT(review_id) as review_count
            FROM reviews
            WHERE product_id = ?
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get seller average rating
     */
    public function getSellerRating($seller_id) {
        $stmt = $this->db->prepare("
            SELECT 
                ROUND(AVG(rating), 1) as avg_rating,
                COUNT(review_id) as review_count
            FROM reviews
            WHERE seller_id = ?
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Delete review
     */
    public function deleteReview($review_id) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE review_id = ?");
        $stmt->bind_param("i", $review_id);
        return $stmt->execute();
    }

    /**
     * Check if user can review product
     */
    public function canUserReview($user_id, $product_id) {
        // User must have purchased the product
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
        ");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Check if already reviewed
        if ($result['count'] > 0) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM reviews
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $reviewed = $stmt->get_result()->fetch_assoc();

            return $reviewed['count'] === 0;
        }

        return false;
    }
}
?>
