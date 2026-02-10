<?php
/**
 * ActivityLog Class
 * Handles logging of user actions in the system
 */

class ActivityLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Log an action
     * @param int $user_id - User who performed the action
     * @param string $action_type - Type of action (login, product_update, stock_update, etc)
     * @param string $description - Detailed description of the action
     * @param string $entity_type - Type of entity affected (product, order, user, etc)
     * @param int $entity_id - ID of the entity affected (optional)
     * @param array $old_data - Previous data (optional)
     * @param array $new_data - New data (optional)
     */
    public function log($user_id, $action_type, $description, $entity_type, $entity_id = null, $old_data = null, $new_data = null) {
        try {
            $old_data_json = $old_data ? json_encode($old_data) : null;
            $new_data_json = $new_data ? json_encode($new_data) : null;
            $ip_address = $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, action_type, description, entity_type, entity_id, old_data, new_data, ip_address, user_agent, logged_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->bind_param(
                "isssissss",
                $user_id,
                $action_type,
                $description,
                $entity_type,
                $entity_id,
                $old_data_json,
                $new_data_json,
                $ip_address,
                $user_agent
            );

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ActivityLog Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all activity logs with pagination and filters
     */
    public function getAllLogs($limit = 50, $offset = 0, $filters = array()) {
        $query = "SELECT 
                    al.*, 
                    u.name as user_name,
                    u.email as user_email,
                    CASE WHEN u.roles LIKE '%seller%' THEN 'Seller' ELSE 'Customer' END as user_type
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.user_id
                  WHERE 1=1";

        $params = [];
        $types = "";

        // Filter by action type
        if (!empty($filters['action_type'])) {
            $query .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
            $types .= "s";
        }

        // Filter by user_id
        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        // Filter by entity_type
        if (!empty($filters['entity_type'])) {
            $query .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
            $types .= "s";
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(al.logged_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(al.logged_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        // Filter by search term (searches in description)
        if (!empty($filters['search'])) {
            $query .= " AND al.description LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }

        $query .= " ORDER BY al.logged_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get total count of activity logs
     */
    public function getTotalLogsCount($filters = array()) {
        $query = "SELECT COUNT(*) as total FROM activity_logs al WHERE 1=1";

        $params = [];
        $types = "";

        // Apply same filters as getAllLogs
        if (!empty($filters['action_type'])) {
            $query .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
            $types .= "s";
        }

        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['entity_type'])) {
            $query .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
            $types .= "s";
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(al.logged_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(al.logged_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $query .= " AND al.description LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }

        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    /**
     * Get logs by user
     */
    public function getLogsByUser($user_id, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY logged_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get logs by action type
     */
    public function getLogsByActionType($action_type, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT al.*, u.name as user_name 
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            WHERE al.action_type = ? 
            ORDER BY al.logged_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("si", $action_type, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get logs by entity
     */
    public function getLogsByEntity($entity_type, $entity_id, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT al.*, u.name as user_name 
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            WHERE al.entity_type = ? AND al.entity_id = ?
            ORDER BY al.logged_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("sii", $entity_type, $entity_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get summary statistics
     */
    public function getActivitySummary($days = 30) {
        $stmt = $this->db->prepare("
            SELECT 
                al.action_type,
                COUNT(*) as count
            FROM activity_logs al
            WHERE al.logged_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY al.action_type
            ORDER BY count DESC
        ");
        $stmt->bind_param("i", $days);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get activity by date range
     */
    public function getActivityByDateRange($date_from, $date_to) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(logged_at) as date,
                COUNT(*) as total_actions,
                COUNT(DISTINCT user_id) as unique_users
            FROM activity_logs
            WHERE DATE(logged_at) BETWEEN ? AND ?
            GROUP BY DATE(logged_at)
            ORDER BY date DESC
        ");
        $stmt->bind_param("ss", $date_from, $date_to);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get most active users
     */
    public function getMostActiveUsers($limit = 10, $days = 30) {
        $stmt = $this->db->prepare("
            SELECT 
                u.user_id,
                u.name,
                u.email,
                COUNT(al.log_id) as action_count
            FROM activity_logs al
            JOIN users u ON al.user_id = u.user_id
            WHERE al.logged_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY u.user_id, u.name, u.email
            ORDER BY action_count DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $days, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Delete old logs (older than specified days)
     */
    public function deleteOldLogs($days = 90) {
        $stmt = $this->db->prepare("DELETE FROM activity_logs WHERE logged_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->bind_param("i", $days);
        return $stmt->execute();
    }

    /**
     * Get client IP address
     */
    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
    }
}
?>
