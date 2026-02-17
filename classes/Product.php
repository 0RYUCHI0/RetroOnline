<?php
/**
 * Product Class
 * Handles product management
 */

require_once __DIR__ . '/ActivityLog.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get database connection for transaction management
     */
    public function getConnection() {
        return $this->db;
    }

    /**
     * Get all products with filters
     * Supports filtering by: console, condition, price range, search, platform type
     * 
     * @param array $filters Filter parameters (console, condition, min_price, max_price, search, platform)
     * @return array Array of products matching the filters
     */
    public function getAllProducts($filters = []) {
        // Base query with aggregated ratings and seller info
        $query = "SELECT p.*, s.store_name, 
                  ROUND(AVG(r.rating), 1) as avg_rating,
                  COUNT(r.review_id) as review_count
                  FROM products p
                  JOIN sellers s ON p.seller_id = s.seller_id
                  LEFT JOIN reviews r ON p.product_id = r.product_id
                  WHERE p.stock > 0";

        $params = [];
        $types = "";

        // Filter by search term (searches in name, console, category)
        if (isset($filters['search']) && !empty(trim($filters['search']))) {
            $searchTerm = "%{$filters['search']}%";
            $query .= " AND (p.name LIKE ? OR p.console LIKE ? OR p.category LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }

        // Filter by console
        if (isset($filters['console']) && !empty($filters['console'])) {
            $query .= " AND p.console = ?";
            $params[] = $filters['console'];
            $types .= "s";
        }

        // Filter by condition (Mint, Used, Refurbished)
        if (isset($filters['condition']) && !empty($filters['condition'])) {
            $query .= " AND p.`condition` = ?";
            $params[] = $filters['condition'];
            $types .= "s";
        }

        // Filter by minimum price
        if (isset($filters['min_price']) && $filters['min_price'] !== '' && $filters['min_price'] !== null) {
            $query .= " AND p.price >= ?";
            $params[] = (float)$filters['min_price'];
            $types .= "d";
        }

        // Filter by maximum price
        if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $query .= " AND p.price <= ?";
            $params[] = (float)$filters['max_price'];
            $types .= "d";
        }

        // Filter by platform type (Nintendo, PlayStation, Sega, etc.)
        if (isset($filters['platform']) && !empty($filters['platform'])) {
            $platformConsoles = $this->getConsolesByPlatform($filters['platform']);
            if (!empty($platformConsoles)) {
                $placeholders = implode(',', array_fill(0, count($platformConsoles), '?'));
                $query .= " AND p.console IN ({$placeholders})";
                foreach ($platformConsoles as $console) {
                    $params[] = $console;
                    $types .= "s";
                }
            }
        }

        // Group by product and sort by newest first
        $query .= " GROUP BY p.product_id ORDER BY p.created_at DESC";

        // Prepare and execute the query
        $stmt = $this->db->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return $result ? $result : [];
    }

    /**
     * Get product by ID
     */
    public function getProductById($product_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, s.store_name,
                   ROUND(AVG(r.rating), 1) as avg_rating,
                   COUNT(r.review_id) as review_count
            FROM products p
            JOIN sellers s ON p.seller_id = s.seller_id
            LEFT JOIN reviews r ON p.product_id = r.product_id
            WHERE p.product_id = ?
            GROUP BY p.product_id
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get products by seller
     * Returns all products/variants for the seller
     */
    public function getProductsBySeller($seller_id) {
        $stmt = $this->db->prepare("
            SELECT p.* FROM products p
            WHERE p.seller_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get product variants (all conditions for same product name + console)
     * Groups by name and console, returns all variants
     * 
     * @param string $name Product name
     * @param string $console Console name
     * @param int $seller_id Seller ID
     * @return array Array of variants with different conditions
     */
    public function getProductVariants($name, $console, $seller_id) {
        $stmt = $this->db->prepare("
            SELECT p.* FROM products p
            WHERE p.name = ? AND p.console = ? AND p.seller_id = ?
            ORDER BY p.`condition`
        ");
        $stmt->bind_param("ssi", $name, $console, $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all variants of a base product (different conditions only)
     * Used on customer product detail page to show all conditions available
     * 
     * @param string $name Product name
     * @param string $console Console name
     * @return array Array of variants with price, stock, condition
     */
    public function getProductVariantsForCustomer($name, $console) {
        $stmt = $this->db->prepare("
            SELECT p.product_id, p.`condition`, p.price, p.stock, p.seller_id, s.store_name
            FROM products p
            JOIN sellers s ON p.seller_id = s.seller_id
            WHERE p.name = ? AND p.console = ? AND p.stock > 0
            ORDER BY p.`condition`
        ");
        $stmt->bind_param("ss", $name, $console);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if a variant already exists
     * Prevent duplicate variants (same name + console + condition + seller)
     * 
     * @param int $seller_id Seller ID
     * @param string $name Product name
     * @param string $console Console name
     * @param string $condition Condition
     * @param int $exclude_product_id Optional: product_id to exclude from check (for edits)
     * @return bool True if variant exists
     */
    public function variantExists($seller_id, $name, $console, $condition, $exclude_product_id = null) {
        $query = "SELECT product_id FROM products WHERE seller_id = ? AND name = ? AND console = ? AND `condition` = ?";
        $params = [$seller_id, $name, $console, $condition];
        $types = "isss";

        if ($exclude_product_id !== null) {
            $query .= " AND product_id != ?";
            $params[] = $exclude_product_id;
            $types .= "i";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Create product with condition (creates a variant)
     * Each variant is defined by: name + console + condition
     * @return int The product_id of the newly created variant
     */
    public function createProduct($seller_id, $name, $console, $category, $condition, $price, $stock, $image_url = '', $demo_url = '') {
        // Validate condition is one of the allowed values
        $valid_conditions = ['Mint', 'Used', 'Refurbished'];
        if (!in_array($condition, $valid_conditions)) {
            throw new Exception("Invalid condition. Must be one of: " . implode(', ', $valid_conditions));
        }

        // Check for duplicate variant
        if ($this->variantExists($seller_id, $name, $console, $condition)) {
            throw new Exception("This variant (product + console + condition) already exists. Use 'Manage Stock' to add inventory.");
        }

        $stmt = $this->db->prepare("
            INSERT INTO products (seller_id, name, console, category, `condition`, price, stock, image_url, demo_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssdiss", $seller_id, $name, $console, $category, $condition, $price, $stock, $image_url, $demo_url);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Failed to create product: " . $stmt->error);
        }
    }

    /**
     * Update product
     */
    /**
     * Update product (without condition or stock - those are immutable after creation)
     * Allows updating: name, console, category, price, image_url, demo_url
     * CANNOT change: condition (defines the variant), stock (managed separately)
     */
    public function updateProduct($product_id, $name, $console, $category, $price, $image_url = '', $demo_url = '') {
        // Get old data
        $oldProduct = $this->getProductById($product_id);
        $oldData = [
            'name' => $oldProduct['name'],
            'console' => $oldProduct['console'],
            'category' => $oldProduct['category'],
            'price' => $oldProduct['price'],
            'image_url' => $oldProduct['image_url'],
            'demo_url' => $oldProduct['demo_url']
        ];

        $stmt = $this->db->prepare("
            UPDATE products 
            SET name = ?, console = ?, category = ?, price = ?, image_url = ?, demo_url = ?
            WHERE product_id = ?
        ");
        $stmt->bind_param("sssdssi", $name, $console, $category, $price, $image_url, $demo_url, $product_id);

        if ($stmt->execute()) {
            // Log product update
            $activityLog = new ActivityLog();
            $seller_id = $oldProduct['seller_id'];
            $activityLog->log(
                $seller_id,
                'product_update',
                "Product '{$name}' updated",
                'product',
                $product_id,
                $oldData,
                ['name' => $name, 'console' => $console, 'category' => $category, 'price' => $price]
            );
            return true;
        } else {
            throw new Exception("Failed to update product: " . $stmt->error);
        }
    }

    /**
     * Add stock to a product (increment only, not overwrite)
     * Uses SQL: UPDATE products SET stock = stock + ? WHERE product_id = ?
     * 
     * @param int $product_id The product ID
     * @param int $quantity The quantity to add (must be positive)
     * @return bool True if successful
     * @throws Exception If quantity is invalid or update fails
     */
    public function addStock($product_id, $quantity) {
        // Validate quantity is positive integer
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            throw new Exception("Stock quantity must be a positive integer.");
        }

        // Use SQL: stock = stock + ? (increment, not overwrite)
        $stmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Failed to add stock: " . $stmt->error);
        }
    }

    /**
     * Get current stock for a product
     * 
     * @param int $product_id The product ID
     * @return int The current stock quantity
     */
    public function getStock($product_id) {
        $stmt = $this->db->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int)$row['stock'] : 0;
    }

    /**
     * Delete product
     */
    public function deleteProduct($product_id) {
        $product = $this->getProductById($product_id);
        $seller_id = $product['seller_id'];
        $product_name = $product['name'];

        $stmt = $this->db->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            // Log product deletion
            $activityLog = new ActivityLog();
            $activityLog->log(
                $seller_id,
                'product_delete',
                "Product '{$product_name}' deleted",
                'product',
                $product_id,
                $product
            );
            return true;
        } else {
            throw new Exception("Failed to delete product: " . $stmt->error);
        }
    }

    /**
     * Update stock
     */
    public function updateStock($product_id, $quantity, $decrease = false) {
        $oldStock = $this->getStock($product_id);
        $product = $this->getProductById($product_id);
        $seller_id = $product['seller_id'];
        $product_name = $product['name'];

        if ($decrease) {
            $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?");
            $stmt->bind_param("iii", $quantity, $product_id, $quantity);
            $action_type = 'stock_decrease';
            $action_desc = "Stock decreased by {$quantity} for '{$product_name}'";
        } else {
            $stmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
            $stmt->bind_param("ii", $quantity, $product_id);
            $action_type = 'stock_increase';
            $action_desc = "Stock increased by {$quantity} for '{$product_name}'";
        }

        if ($stmt->execute()) {
            // Log stock update
            $newStock = $this->getStock($product_id);
            $activityLog = new ActivityLog();
            $activityLog->log(
                $seller_id,
                $action_type,
                $action_desc,
                'product',
                $product_id,
                ['stock' => $oldStock],
                ['stock' => $newStock]
            );
            return true;
        }
        return false;
    }

    /**
     * Get consoles list
     */
    /**
     * Get all unique consoles for filter dropdown
     */
    public function getConsoles() {
        $stmt = $this->db->query("SELECT DISTINCT console FROM products ORDER BY console");
        $results = $stmt->fetch_all(MYSQLI_ASSOC);
        return array_column($results, 'console');
    }

    /**
     * Get all unique platforms (Nintendo, PlayStation, Sega, etc.)
     */
    public function getPlatforms() {
        $consoles = $this->getConsoles();
        $platforms = [];
        
        // Map consoles to their platforms
        foreach ($consoles as $console) {
            $platform = $this->getConsoleTooltip($console);
            if (!in_array($platform, $platforms)) {
                $platforms[] = $platform;
            }
        }
        
        sort($platforms);
        return $platforms;
    }

    /**
     * Map console names to platform types
     * Returns the platform type for a given console
     */
    private function getConsoleTooltip($console) {
        $platformMap = [
            'NES' => 'Nintendo',
            'SNES' => 'Nintendo',
            'N64' => 'Nintendo',
            'Gameboy' => 'Nintendo',
            'GameCube' => 'Nintendo',
            'Wii' => 'Nintendo',
            'Switch' => 'Nintendo',
            'PlayStation' => 'PlayStation',
            'PS2' => 'PlayStation',
            'PS3' => 'PlayStation',
            'PS4' => 'PlayStation',
            'PS5' => 'PlayStation',
            'Sega Genesis' => 'Sega',
            'Sega' => 'Sega',
            'Dreamcast' => 'Sega',
            'Xbox' => 'Xbox',
            'Xbox 360' => 'Xbox',
            'Xbox One' => 'Xbox',
            'Atari 2600' => 'Atari',
            'Atari' => 'Atari',
        ];
        
        return $platformMap[$console] ?? $console;
    }

    /**
     * Get all consoles that belong to a specific platform
     */
    private function getConsolesByPlatform($platform) {
        $allConsoles = $this->getConsoles();
        $platformConsoles = [];
        
        foreach ($allConsoles as $console) {
            if ($this->getConsoleTooltip($console) === $platform) {
                $platformConsoles[] = $console;
            }
        }
        
        return $platformConsoles;
    }

    /**
     * Search products
     */
    public function searchProducts($searchTerm) {
        $searchTerm = "%{$searchTerm}%";
        $stmt = $this->db->prepare("
            SELECT p.*, s.store_name
            FROM products p
            JOIN sellers s ON p.seller_id = s.seller_id
            WHERE p.name LIKE ? OR p.console LIKE ? OR p.category LIKE ?
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT p.product_id, p.name, p.price, p.stock, SUM(oi.quantity) as total_sold
            FROM products p
            JOIN order_items oi ON p.product_id = oi.product_id
            GROUP BY p.product_id, p.name, p.price, p.stock
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
