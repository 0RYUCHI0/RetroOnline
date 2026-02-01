<?php
/**
 * Migration: Add Wishlist Table and Indexes
 * Creates the wishlist table for customer product recommendations
 */

require_once 'config.php';

$db = Database::getInstance()->getConnection();

try {
    // Create wishlist table
    $sql = "
    CREATE TABLE IF NOT EXISTS wishlist (
        wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        product_id INT NOT NULL,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
        UNIQUE KEY unique_customer_product (customer_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    if ($db->query($sql)) {
        echo "✓ Wishlist table created successfully!\n";
    } else {
        echo "✗ Error creating wishlist table: " . $db->error . "\n";
    }

    // Create index for faster queries
    $index_sql = "CREATE INDEX idx_customer_id ON wishlist(customer_id);";
    if ($db->query($index_sql)) {
        echo "✓ Index created successfully!\n";
    }

    echo "\n✓ Migration completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
