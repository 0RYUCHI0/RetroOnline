<?php
/**
 * Migration: Add Activity Logs Table
 * Creates the activity_logs table for tracking user actions
 */

require_once 'config.php';

$db = Database::getInstance()->getConnection();

try {
    echo "Creating activity_logs table...\n";

    $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        entity_type VARCHAR(50),
        entity_id INT,
        old_data LONGTEXT,
        new_data LONGTEXT,
        ip_address VARCHAR(45),
        user_agent VARCHAR(500),
        logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_action_type (action_type),
        INDEX idx_entity_type_id (entity_type, entity_id),
        INDEX idx_logged_at (logged_at),
        INDEX idx_search (action_type, entity_type, logged_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($db->query($sql)) {
        echo "✅ Activity logs table created successfully!\n";
    } else {
        throw new Exception("Failed to create table: " . $db->error);
    }

    // Verify table
    $result = $db->query("SHOW TABLES LIKE 'activity_logs'");
    if ($result->num_rows > 0) {
        echo "✅ Table verified!\n";
        
        // Show table structure
        $columns = $db->query("DESCRIBE activity_logs");
        echo "\nTable Structure:\n";
        echo str_repeat("-", 60) . "\n";
        while ($col = $columns->fetch_assoc()) {
            printf("%-20s %-25s %s\n", $col['Field'], $col['Type'], $col['Null']);
        }
        echo str_repeat("-", 60) . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
