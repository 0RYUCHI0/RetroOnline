<?php
require_once 'config.php';
require_once 'classes/Wishlist.php';

try {
    $wishlist = new Wishlist();
    echo "✓ Wishlist class loaded successfully!\n";
    
    // Test the table exists
    $db = Database::getInstance()->getConnection();
    $result = $db->query("SHOW TABLES LIKE 'wishlist'");
    if ($result->num_rows > 0) {
        echo "✓ Wishlist table exists in database!\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
