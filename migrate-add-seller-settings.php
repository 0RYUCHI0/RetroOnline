<?php
/**
 * Migration Script: Add seller_settings table
 * 
 * Run this script once to create the seller_settings table
 * Visit: http://localhost/retroonline/migrate-add-seller-settings.php
 */

require_once 'config.php';

$db = Database::getInstance()->getConnection();

try {
    // Check if table already exists
    $result = $db->query("SHOW TABLES LIKE 'seller_settings'");
    
    if ($result->num_rows > 0) {
        echo "<div style='padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;'>";
        echo "<strong>ℹ️ Info:</strong> seller_settings table already exists. No migration needed.";
        echo "</div>";
    } else {
        // Create the table
        $sql = "
            CREATE TABLE seller_settings (
                setting_id INT AUTO_INCREMENT PRIMARY KEY,
                seller_id INT NOT NULL UNIQUE,
                notify_orders TINYINT DEFAULT 1,
                notify_reviews TINYINT DEFAULT 1,
                notify_messages TINYINT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
            )
        ";
        
        if ($db->query($sql)) {
            echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✓ Success:</strong> seller_settings table created successfully!";
            echo "</div>";
        } else {
            throw new Exception("Failed to create table: " . $db->error);
        }
    }
} catch (Exception $e) {
    echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;'>";
    echo "<strong>✗ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<p style='margin-top: 20px; text-align: center;'>";
echo "<a href='pages/seller/settings.php' style='color: #e94560; text-decoration: none; font-weight: 500;'>← Go to Seller Settings</a>";
echo "</p>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - RetroGameHub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #e94560;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background: #d43552;
        }
    </style>
</head>
<body>
    <h1>🔧 Database Migration</h1>
</body>
</html>
