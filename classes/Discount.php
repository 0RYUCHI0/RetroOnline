<?php
/**
 * Discount Class
 * Handles discount deals for products
 */

require_once __DIR__ . '/ActivityLog.php';

class Discount {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add a new discount deal
     * @param int $product_id - Product ID
     * @param float $discount_percent - Discount percentage (0-100)
     * @param string $start_date - Start date (YYYY-MM-DD)
     * @param string $end_date - End date (YYYY-MM-DD)
     * @return int - Discount ID if successful
     * @throws Exception - If validation fails or discount overlaps
     */
    public function addDiscount($product_id, $discount_percent, $start_date, $end_date) {
        // Validate inputs
        $product_id = (int)$product_id;
        $discount_percent = (float)$discount_percent;

        if ($discount_percent < 0 || $discount_percent > 100) {
            throw new Exception("Discount percent must be between 0 and 100");
        }

        if (!$this->isValidDate($start_date)) {
            throw new Exception("Invalid start date format (use YYYY-MM-DD)");
        }

        if (!$this->isValidDate($end_date)) {
            throw new Exception("Invalid end date format (use YYYY-MM-DD)");
        }

        if ($start_date >= $end_date) {
            throw new Exception("End date must be after start date");
        }

        // Check if product exists
        $stmt = $this->db->prepare("SELECT product_id FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Product not found");
        }

        // Check for overlapping discounts
        if ($this->hasOverlappingDiscount($product_id, $start_date, $end_date)) {
            throw new Exception("This product already has an overlapping discount deal");
        }

        // Insert discount
        $stmt = $this->db->prepare("
            INSERT INTO discounts (product_id, discount_percent, start_date, end_date, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("idss", $product_id, $discount_percent, $start_date, $end_date);

        if ($stmt->execute()) {
            $discount_id = $this->db->insert_id;

            // Log the action if seller is logged in
            if (SessionManager::isLoggedIn()) {
                $user_id = SessionManager::get('user_id');
                $activityLog = new ActivityLog();
                $activityLog->log(
                    $user_id,
                    'discount_create',
                    "Created discount deal: {$discount_percent}% off on product ID {$product_id} from {$start_date} to {$end_date}",
                    'discount',
                    $discount_id,
                    null,
                    ['product_id' => $product_id, 'discount_percent' => $discount_percent, 'start_date' => $start_date, 'end_date' => $end_date]
                );
            }

            return $discount_id;
        } else {
            throw new Exception("Failed to create discount: " . $stmt->error);
        }
    }

    /**
     * Remove a discount deal
     * @param int $discount_id - Discount ID
     * @return bool - True if successful
     * @throws Exception - If discount not found
     */
    public function removeDiscount($discount_id) {
        $discount_id = (int)$discount_id;

        // Get discount info before deletion
        $discount = $this->getDiscountById($discount_id);
        if (!$discount) {
            throw new Exception("Discount not found");
        }

        $stmt = $this->db->prepare("DELETE FROM discounts WHERE discount_id = ?");
        $stmt->bind_param("i", $discount_id);

        if ($stmt->execute()) {
            // Log the action if seller is logged in
            if (SessionManager::isLoggedIn()) {
                $user_id = SessionManager::get('user_id');
                $activityLog = new ActivityLog();
                $activityLog->log(
                    $user_id,
                    'discount_delete',
                    "Deleted discount deal ID {$discount_id}",
                    'discount',
                    $discount_id,
                    $discount
                );
            }
            return true;
        } else {
            throw new Exception("Failed to delete discount: " . $stmt->error);
        }
    }

    /**
     * Get active discount for a product
     * Only returns discount if current date is within the discount period
     * @param int $product_id - Product ID
     * @return array|null - Discount data or null if no active discount
     */
    public function getActiveDiscount($product_id) {
        $product_id = (int)$product_id;
        $today = date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT discount_id, product_id, discount_percent, start_date, end_date, created_at
            FROM discounts
            WHERE product_id = ?
            AND start_date <= ?
            AND end_date >= ?
            AND discount_percent > 0
            ORDER BY discount_percent DESC
            LIMIT 1
        ");
        $stmt->bind_param("iss", $product_id, $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get all discounts for a product (including expired)
     * @param int $product_id - Product ID
     * @return array - Array of discount records
     */
    public function getProductDiscounts($product_id) {
        $product_id = (int)$product_id;

        $stmt = $this->db->prepare("
            SELECT discount_id, product_id, discount_percent, start_date, end_date, created_at
            FROM discounts
            WHERE product_id = ?
            ORDER BY start_date DESC
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get discount by ID
     * @param int $discount_id - Discount ID
     * @return array|null - Discount data or null
     */
    public function getDiscountById($discount_id) {
        $discount_id = (int)$discount_id;

        $stmt = $this->db->prepare("
            SELECT discount_id, product_id, discount_percent, start_date, end_date, created_at
            FROM discounts
            WHERE discount_id = ?
        ");
        $stmt->bind_param("i", $discount_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Apply discount to price
     * @param float $original_price - Original price
     * @param int $product_id - Product ID (to check for active discount)
     * @return array - ['original_price' => X, 'discount_percent' => X, 'discounted_price' => X]
     */
    public function applyDiscountToPrice($original_price, $product_id) {
        $original_price = (float)$original_price;
        $discount = $this->getActiveDiscount($product_id);

        if (!$discount) {
            // No active discount
            return [
                'original_price' => $original_price,
                'discount_percent' => 0,
                'discounted_price' => $original_price,
                'has_discount' => false
            ];
        }

        $discount_amount = $original_price * ($discount['discount_percent'] / 100);
        $discounted_price = $original_price - $discount_amount;

        return [
            'original_price' => $original_price,
            'discount_percent' => $discount['discount_percent'],
            'discount_amount' => $discount_amount,
            'discounted_price' => round($discounted_price, 2),
            'has_discount' => true,
            'discount_id' => $discount['discount_id'],
            'start_date' => $discount['start_date'],
            'end_date' => $discount['end_date']
        ];
    }

    /**
     * Get all active discounts (seller dashboard)
     * @param int $seller_id - Seller ID (optional, null for all)
     * @return array - Array of active discounts with product info
     */
    public function getAllActiveDiscounts($seller_id = null) {
        $today = date('Y-m-d');
        $query = "
            SELECT 
                d.discount_id,
                d.product_id,
                d.discount_percent,
                d.start_date,
                d.end_date,
                d.created_at,
                p.name as product_name,
                p.price as original_price,
                p.seller_id
            FROM discounts d
            JOIN products p ON d.product_id = p.product_id
            WHERE d.start_date <= ? AND d.end_date >= ?
        ";

        $types = "ss";
        $params = [$today, $today];

        if ($seller_id !== null) {
            $query .= " AND p.seller_id = ?";
            $types .= "i";
            $params[] = (int)$seller_id;
        }

        $query .= " ORDER BY d.end_date ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get upcoming discounts (scheduled to start soon)
     * @param int $seller_id - Seller ID (optional)
     * @param int $days - Days ahead to check (default 7)
     * @return array - Array of upcoming discounts
     */
    public function getUpcomingDiscounts($seller_id = null, $days = 7) {
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime("+$days days"));

        $query = "
            SELECT 
                d.discount_id,
                d.product_id,
                d.discount_percent,
                d.start_date,
                d.end_date,
                d.created_at,
                p.name as product_name,
                p.price as original_price,
                p.seller_id
            FROM discounts d
            JOIN products p ON d.product_id = p.product_id
            WHERE d.start_date > ? AND d.start_date <= ?
        ";

        $types = "ss";
        $params = [$today, $future];

        if ($seller_id !== null) {
            $query .= " AND p.seller_id = ?";
            $types .= "i";
            $params[] = (int)$seller_id;
        }

        $query .= " ORDER BY d.start_date ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if discount has overlapping dates
     * @param int $product_id - Product ID
     * @param string $start_date - Start date
     * @param string $end_date - End date
     * @return bool - True if overlapping discount exists
     */
    private function hasOverlappingDiscount($product_id, $start_date, $end_date) {
        $product_id = (int)$product_id;

        $stmt = $this->db->prepare("
            SELECT discount_id FROM discounts
            WHERE product_id = ?
            AND (
                (start_date <= ? AND end_date >= ?)
                OR (start_date <= ? AND end_date >= ?)
                OR (start_date >= ? AND end_date <= ?)
            )
        ");
        $stmt->bind_param("issssss", $product_id, $end_date, $start_date, $end_date, $start_date, $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Validate date format
     * @param string $date - Date string
     * @return bool - True if valid YYYY-MM-DD format
     */
    private function isValidDate($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Update discount
     * @param int $discount_id - Discount ID
     * @param float $discount_percent - New discount percent
     * @param string $start_date - New start date
     * @param string $end_date - New end date
     * @return bool - True if successful
     */
    public function updateDiscount($discount_id, $discount_percent, $start_date, $end_date) {
        $discount_id = (int)$discount_id;
        $discount_percent = (float)$discount_percent;

        if ($discount_percent < 0 || $discount_percent > 100) {
            throw new Exception("Discount percent must be between 0 and 100");
        }

        if (!$this->isValidDate($start_date) || !$this->isValidDate($end_date)) {
            throw new Exception("Invalid date format (use YYYY-MM-DD)");
        }

        if ($start_date >= $end_date) {
            throw new Exception("End date must be after start date");
        }

        // Get current discount info
        $discount = $this->getDiscountById($discount_id);
        if (!$discount) {
            throw new Exception("Discount not found");
        }

        // Check for overlapping discounts (excluding this one)
        $stmt = $this->db->prepare("
            SELECT discount_id FROM discounts
            WHERE product_id = ?
            AND discount_id != ?
            AND (
                (start_date <= ? AND end_date >= ?)
                OR (start_date <= ? AND end_date >= ?)
                OR (start_date >= ? AND end_date <= ?)
            )
        ");
        $stmt->bind_param("isssssss", $discount['product_id'], $discount_id, $end_date, $start_date, $end_date, $start_date, $start_date, $end_date);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Updated dates would create an overlapping discount");
        }

        // Update discount
        $stmt = $this->db->prepare("
            UPDATE discounts
            SET discount_percent = ?, start_date = ?, end_date = ?
            WHERE discount_id = ?
        ");
        $stmt->bind_param("dssi", $discount_percent, $start_date, $end_date, $discount_id);

        if ($stmt->execute()) {
            // Log the action
            if (SessionManager::isLoggedIn()) {
                $user_id = SessionManager::get('user_id');
                $activityLog = new ActivityLog();
                $activityLog->log(
                    $user_id,
                    'discount_update',
                    "Updated discount deal ID {$discount_id}",
                    'discount',
                    $discount_id,
                    $discount,
                    ['discount_percent' => $discount_percent, 'start_date' => $start_date, 'end_date' => $end_date]
                );
            }
            return true;
        } else {
            throw new Exception("Failed to update discount: " . $stmt->error);
        }
    }

    /**
     * Get discount summary stats
     * @param int $seller_id - Seller ID (optional)
     * @return array - Summary stats
     */
    public function getDiscountStats($seller_id = null) {
        $today = date('Y-m-d');

        $query = "
            SELECT 
                COUNT(*) as total_discounts,
                SUM(CASE WHEN start_date <= ? AND end_date >= ? THEN 1 ELSE 0 END) as active_discounts,
                SUM(CASE WHEN start_date > ? THEN 1 ELSE 0 END) as upcoming_discounts,
                SUM(CASE WHEN end_date < ? THEN 1 ELSE 0 END) as expired_discounts,
                AVG(discount_percent) as avg_discount
            FROM discounts d
            JOIN products p ON d.product_id = p.product_id
        ";

        $types = "ssss";
        $params = [$today, $today, $today, $today];

        if ($seller_id !== null) {
            $query .= " WHERE p.seller_id = ?";
            $types .= "i";
            $params[] = (int)$seller_id;
        }

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return [
            'total_discounts' => (int)($result['total_discounts'] ?? 0),
            'active_discounts' => (int)($result['active_discounts'] ?? 0),
            'upcoming_discounts' => (int)($result['upcoming_discounts'] ?? 0),
            'expired_discounts' => (int)($result['expired_discounts'] ?? 0),
            'avg_discount' => round($result['avg_discount'] ?? 0, 2)
        ];
    }
}
?>
