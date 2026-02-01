<?php
/**
 * Seller Class
 * Handles seller management and applications
 */

require_once __DIR__ . '/User.php';

class Seller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Apply as seller
     */
    public function applyAsSeller($user_id, $store_name, $contact_info, $payment_info) {
        // Check if already applied or is seller
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM seller_applications
            WHERE user_id = ? AND status IN ('pending', 'approved')
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            throw new Exception("You already have an application in progress");
        }

        $stmt = $this->db->prepare("
            INSERT INTO seller_applications (user_id, store_name, contact_info, payment_info)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $user_id, $store_name, $contact_info, $payment_info);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Application failed: " . $stmt->error);
        }
    }

    /**
     * Get seller by user ID
     */
    public function getSellerByUserId($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM sellers WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get seller by ID
     */
    public function getSellerById($seller_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name, u.email, u.status, u.phone as user_phone
            FROM sellers s
            JOIN users u ON s.user_id = u.user_id
            WHERE s.seller_id = ?
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all sellers (admin)
     */
    public function getAllSellers() {
        $stmt = $this->db->query("
            SELECT s.*, u.name, u.email, u.status,
                   COUNT(p.product_id) as product_count
            FROM sellers s
            JOIN users u ON s.user_id = u.user_id
            LEFT JOIN products p ON s.seller_id = p.seller_id
            GROUP BY s.seller_id
            ORDER BY s.created_at DESC
        ");
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get pending applications (admin)
     */
    public function getPendingApplications() {
        $stmt = $this->db->prepare("
            SELECT sa.*, u.name, u.email
            FROM seller_applications sa
            JOIN users u ON sa.user_id = u.user_id
            WHERE sa.status = 'pending'
            ORDER BY sa.applied_at DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get application by ID
     */
    public function getApplicationById($application_id) {
        $stmt = $this->db->prepare("
            SELECT sa.*, u.name, u.email
            FROM seller_applications sa
            JOIN users u ON sa.user_id = u.user_id
            WHERE sa.application_id = ?
        ");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Approve application
     */
    public function approveApplication($application_id) {
        $this->db->begin_transaction();

        try {
            $app = $this->getApplicationById($application_id);

            // Update application status
            $stmt = $this->db->prepare("
                UPDATE seller_applications SET status = 'approved' WHERE application_id = ?
            ");
            $stmt->bind_param("i", $application_id);
            $stmt->execute();

            // Create seller record
            $stmt = $this->db->prepare("
                INSERT INTO sellers (user_id, store_name, contact_info, payment_info)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isss", $app['user_id'], $app['store_name'], $app['contact_info'], $app['payment_info']);
            $stmt->execute();

            // Add seller role to user
            $user = new User();
            $user->addRole($app['user_id'], 'seller');

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Approval failed: " . $e->getMessage());
        }
    }

    /**
     * Reject application
     */
    public function rejectApplication($application_id, $reason = '') {
        $stmt = $this->db->prepare("
            UPDATE seller_applications SET status = 'rejected' WHERE application_id = ?
        ");
        $stmt->bind_param("i", $application_id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Rejection failed: " . $stmt->error);
        }
    }

    /**
     * Get seller info with ratings
     */
    public function getSellerInfo($seller_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name, u.email,
                   ROUND(AVG(r.rating), 1) as avg_rating,
                   COUNT(r.review_id) as review_count,
                   COUNT(p.product_id) as product_count
            FROM sellers s
            JOIN users u ON s.user_id = u.user_id
            LEFT JOIN reviews r ON s.seller_id = r.seller_id
            LEFT JOIN products p ON s.seller_id = p.seller_id
            WHERE s.seller_id = ?
            GROUP BY s.seller_id
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get application by user ID
     */
    public function getApplicationByUserId($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM seller_applications
            WHERE user_id = ?
            ORDER BY applied_at DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No application found");
        }
        
        return $result->fetch_assoc();
    }
}
?>
