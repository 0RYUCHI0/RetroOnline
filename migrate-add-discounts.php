<?php
/**
 * Database Migration: Add Discounts Table
 * Adds the discounts table to the database for handling discount deals
 */

require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check if discounts table already exists
    $tableExists = $db->query("SHOW TABLES LIKE 'discounts'")->num_rows > 0;

    if ($tableExists) {
        echo "✓ Discounts table already exists.\n";
    } else {
        // Create discounts table
        $sql = "
            CREATE TABLE discounts (
                discount_id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                discount_percent DECIMAL(5, 2) NOT NULL COMMENT 'Discount percentage (0-100)',
                start_date DATE NOT NULL COMMENT 'Discount start date',
                end_date DATE NOT NULL COMMENT 'Discount end date',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                -- Constraints
                CONSTRAINT fk_discount_product FOREIGN KEY (product_id) 
                    REFERENCES products(product_id) ON DELETE CASCADE,
                
                -- Indexes for performance
                INDEX idx_product_dates (product_id, start_date, end_date),
                INDEX idx_active_discounts (start_date, end_date),
                UNIQUE KEY uk_product_dates (product_id, start_date, end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Discount deals for products';
        ";

        if ($db->query($sql)) {
            echo "✓ Discounts table created successfully.\n";
            echo "✓ Indexes created for optimal performance.\n";
            echo "✓ Foreign key constraint added to products table.\n";
        } else {
            throw new Exception("Failed to create discounts table: " . $db->error);
        }
    }

    echo "\n✓ Database migration completed successfully!\n";
    echo "✓ The discount deals system is ready to use.\n";

} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
