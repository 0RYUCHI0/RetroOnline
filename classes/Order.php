<?php
/**
 * Order Class
 * Handles order management
 */

require_once __DIR__ . '/ActivityLog.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create order from cart
     */
    public function createOrder($user_id, $address_id, $items) {
        $this->db->begin_transaction();

        try {
            $totalAmount = 0;

            // Calculate total
            foreach ($items as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, total_amount, status, shipping_address_id)
                VALUES (?, ?, 'pending', ?)
            ");
            $stmt->bind_param("idi", $user_id, $totalAmount, $address_id);
            $stmt->execute();
            $order_id = $this->db->insert_id;

            // Insert order items
            $product = new Product();
            foreach ($items as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, seller_id, quantity, price)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iiiid", $order_id, $item['product_id'], $item['seller_id'], $item['quantity'], $item['price']);
                $stmt->execute();

                $order_item_id = $this->db->insert_id;

                // Create tracking record
                $stmt = $this->db->prepare("
                    INSERT INTO order_tracking (order_item_id, status)
                    VALUES (?, 'pending')
                ");
                $stmt->bind_param("i", $order_item_id);
                $stmt->execute();

                // Calculate commission
                $commission_amount = ($item['price'] * $item['quantity']) * (PLATFORM_COMMISSION / 100);
                $commission_percent = PLATFORM_COMMISSION;
                $stmt = $this->db->prepare("
                    INSERT INTO commissions (order_item_id, seller_id, commission_percent, commission_amount)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("iddd", $order_item_id, $item['seller_id'], $commission_percent, $commission_amount);
                $stmt->execute();

                // Update product stock
                $product->updateStock($item['product_id'], $item['quantity'], true);
            }

            $this->db->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Order creation failed: " . $e->getMessage());
        }
    }

    /**
     * Get order by ID
     */
    public function getOrderById($order_id) {
        $stmt = $this->db->prepare("
            SELECT o.*, a.recipient_name, a.address_line1, a.address_line2, a.city, a.state, a.postal_code, a.country
            FROM orders o
            LEFT JOIN addresses a ON o.shipping_address_id = a.address_id
            WHERE o.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get orders by user
     */
    public function getOrdersByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT o.*, a.recipient_name, a.city, a.address_line1, a.state, a.postal_code, a.country
            FROM orders o
            LEFT JOIN addresses a ON o.shipping_address_id = a.address_id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get order items
     */
    public function getOrderItems($order_id) {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name, p.image_url, s.store_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            JOIN sellers s ON oi.seller_id = s.seller_id
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get order tracking
     */
    public function getOrderTracking($order_item_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM order_tracking
            WHERE order_item_id = ?
        ");
        $stmt->bind_param("i", $order_item_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($order_id, $status) {
        // Get order info
        $order = $this->getOrderById($order_id);
        $oldStatus = $order['status'];

        $stmt = $this->db->prepare("
            UPDATE orders SET status = ? WHERE order_id = ?
        ");
        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            // Log order status update
            $user_id = $order['user_id'];
            $activityLog = new ActivityLog();
            $activityLog->log(
                $user_id,
                'order_status_update',
                "Order status changed from {$oldStatus} to {$status}",
                'order',
                $order_id,
                ['status' => $oldStatus],
                ['status' => $status]
            );
            return true;
        } else {
            throw new Exception("Failed to update order status");
        }
    }

    /**
     * Update tracking status
     */
    public function updateTrackingStatus($order_item_id, $status, $courier_name = '', $tracking_number = '') {
        // Update order_tracking table
        $stmt = $this->db->prepare("
            UPDATE order_tracking 
            SET status = ?, courier_name = ?, tracking_number = ?
            WHERE order_item_id = ?
        ");
        $stmt->bind_param("sssi", $status, $courier_name, $tracking_number, $order_item_id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update tracking status");
        }

        // Update order status based on tracking status
        // First, get the order_id for this order_item
        $stmt = $this->db->prepare("
            SELECT oi.order_id FROM order_items oi
            WHERE oi.order_item_id = ?
        ");
        $stmt->bind_param("i", $order_item_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $order_id = $result['order_id'];

        // Map tracking status to order status
        // order_tracking has: pending, shipped, in_transit, delivered
        // orders table has: pending, processing, shipped, delivered, cancelled
        $order_status = 'processing';
        if ($status === 'pending') {
            $order_status = 'pending';
        } elseif ($status === 'shipped' || $status === 'in_transit') {
            $order_status = 'shipped';
        } elseif ($status === 'delivered') {
            $order_status = 'delivered';
        }

        // Update the order status
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE order_id = ?
        ");
        $stmt->bind_param("si", $order_status, $order_id);
        $stmt->execute();

        return true;
    }

    /**
     * Get seller orders
     */
    public function getSellerOrders($seller_id) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT o.* FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE oi.seller_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get seller's order items
     */
    public function getSellerOrderItems($seller_id) {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name, o.status as order_status, u.name as customer_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            JOIN orders o ON oi.order_id = o.order_id
            JOIN users u ON o.user_id = u.user_id
            WHERE oi.seller_id = ?
            ORDER BY oi.created_at DESC
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Calculate seller earnings
     */
    public function getSellerEarnings($seller_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT oi.order_id) as total_orders,
                SUM(oi.price * oi.quantity) as total_sales,
                SUM(c.commission_amount) as total_commissions,
                (SUM(oi.price * oi.quantity) - SUM(c.commission_amount)) as net_earnings
            FROM order_items oi
            LEFT JOIN commissions c ON oi.order_item_id = c.order_item_id
            WHERE oi.seller_id = ?
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all orders (admin)
     */
    public function getAllOrders() {
        $stmt = $this->db->query("
            SELECT o.*, u.name as customer_name, COUNT(oi.order_item_id) as item_count
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all couriers
     */
    public function getCouriers() {
        $stmt = $this->db->query("SELECT courier_id, name, phone FROM couriers ORDER BY name");
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get courier phone by name
     */
    public function getCourierPhone($courier_name) {
        $stmt = $this->db->prepare("SELECT phone FROM couriers WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $courier_name);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['phone'] : '';
    }
}
?>
