<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';
require_once '../../classes/Product.php';

// Check if logged in and is seller
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('seller')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$user_id = SessionManager::getUserId();
$seller_obj = new Seller();
$seller = $seller_obj->getSellerByUserId($user_id);

if (!$seller) {
    die('You are not an approved seller');
}

$seller_id = $seller['seller_id'];
$product_obj = new Product();
$products = $product_obj->getProductsBySeller($seller_id);

$error = '';
$success = '';

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        try {
            $name = $_POST['name'];
            $console = $_POST['console'];
            $category = $_POST['category'];
            $image_url = $_POST['image_url'] ?? '';
            $demo_url = $_POST['demo_url'] ?? '';

            // Handle image file upload
            if (isset($_FILES['image_file']) && $_FILES['image_file']['size'] > 0) {
                $file = $_FILES['image_file'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!in_array($file['type'], $allowed_types)) {
                    throw new Exception("Invalid image type. Only JPEG, PNG, GIF, and WebP allowed.");
                }
                
                if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                    throw new Exception("Image file too large. Maximum 5MB allowed.");
                }

                // Create uploads directory if it doesn't exist
                $upload_dir = '../../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Generate unique filename
                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $image_url = 'uploads/products/' . $file_name;
                } else {
                    throw new Exception("Failed to upload image file.");
                }
            }

            if ($action === 'add') {
                // When adding product variants, create one for each selected condition with its own price and stock
                $conditions = $_POST['conditions'] ?? [];
                $prices = $_POST['condition_prices'] ?? [];
                $stocks = $_POST['condition_stocks'] ?? [];

                if (empty($conditions)) {
                    throw new Exception("Please select at least one condition.");
                }

                // Validate all selected conditions have valid price and stock
                foreach ($conditions as $condition) {
                    $condition = trim($condition);
                    if (!in_array($condition, ['Mint', 'Used', 'Refurbished'])) {
                        continue;
                    }

                    $price = isset($prices[$condition]) ? (float)$prices[$condition] : 0;
                    $stock = isset($stocks[$condition]) ? (int)$stocks[$condition] : 0;

                    // Validate price
                    if ($price <= 0) {
                        throw new Exception("Please enter a valid price (> 0) for " . htmlspecialchars($condition) . " condition.");
                    }

                    // Validate stock
                    if ($stock < 0) {
                        throw new Exception("Stock cannot be negative for " . htmlspecialchars($condition) . " condition.");
                    }
                }

                // Start transaction for multi-variant creation
                $product_obj->getConnection()->begin_transaction();
                
                try {
                    $created_variants = [];
                    $failed_variants = [];

                    foreach ($conditions as $condition) {
                        $condition = trim($condition);
                        if (!in_array($condition, ['Mint', 'Used', 'Refurbished'])) {
                            continue;
                        }

                        $price = isset($prices[$condition]) ? (float)$prices[$condition] : 0;
                        $stock = isset($stocks[$condition]) ? (int)$stocks[$condition] : 0;

                        try {
                            // Create variant with condition-specific price and stock
                            $product_obj->createProduct($seller_id, $name, $console, $category, $condition, $price, $stock, $image_url, $demo_url);
                            $created_variants[] = $condition . ' ($' . number_format($price, 2) . ', ' . $stock . ' units)';
                        } catch (Exception $e) {
                            $failed_variants[] = $condition . ': ' . $e->getMessage();
                        }
                    }

                    if (empty($created_variants) && !empty($failed_variants)) {
                        $product_obj->getConnection()->rollback();
                        throw new Exception("Failed to create variants: " . implode('; ', $failed_variants));
                    }

                    // Commit transaction
                    $product_obj->getConnection()->commit();

                    $success = 'Product variants created successfully: ' . implode(', ', $created_variants);
                    if (!empty($failed_variants)) {
                        $success .= ' (Some variants failed: ' . implode('; ', $failed_variants) . ')';
                    }
                } catch (Exception $e) {
                    $product_obj->getConnection()->rollback();
                    throw new Exception("Transaction failed: " . $e->getMessage());
                }
            } else {
                // When editing, only update price (not condition - that defines the variant)
                $product_id = (int)$_POST['product_id'];
                $price = (float)$_POST['price'];

                if ($price <= 0) {
                    throw new Exception("Please enter a valid price.");
                }

                $product_obj->updateProduct($product_id, $name, $console, $category, $price, $image_url, $demo_url);
                $success = 'Product variant updated successfully!';
            }

            header('refresh:2;url=products.php');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'delete') {
        try {
            $product_id = (int)$_POST['product_id'];
            $product_obj->deleteProduct($product_id);
            $success = 'Product deleted successfully!';
            header('refresh:2;url=products.php');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'add_stock') {
        // Handle stock management (AJAX)
        try {
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            // Validate product belongs to this seller
            $product = $product_obj->getProductById($product_id);
            if (!$product || $product['seller_id'] != $seller_id) {
                throw new Exception("You don't have permission to manage this product's stock.");
            }
            
            // Add stock using increment logic
            $product_obj->addStock($product_id, $quantity);
            
            // Get updated stock for response
            $new_stock = $product_obj->getStock($product_id);
            
            // Return JSON response for AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Stock updated successfully!',
                'new_stock' => $new_stock,
                'product_id' => $product_id
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}

$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_product = $product_obj->getProductById($_GET['edit']);
}

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="products-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Seller</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="products.php" class="nav-link active">Products</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="earnings.php" class="nav-link">Earnings</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2><?php echo $edit_product ? 'Edit Product' : 'My Products'; ?></h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="products-layout">
            <!-- Add/Edit Form -->
            <div class="product-form-section">
                <h3><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h3>
                <form method="POST" class="product-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" name="name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Console *</label>
                            <input type="text" name="console" list="consoles" value="<?php echo $edit_product ? htmlspecialchars($edit_product['console']) : ''; ?>" required>
                            <datalist id="consoles">
                                <option value="NES">
                                <option value="SNES">
                                <option value="Gameboy">
                                <option value="PlayStation">
                                <option value="Sega">
                                <option value="N64">
                            </datalist>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" name="category" value="<?php echo $edit_product ? htmlspecialchars($edit_product['category'] ?? '') : ''; ?>">
                        </div>
                    </div>

                    <?php if ($edit_product): ?>
                        <!-- Edit Mode: Show current condition and price only -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Condition (Cannot be changed)</label>
                                <input type="text" value="<?php echo htmlspecialchars($edit_product['condition']); ?>" disabled style="background-color: #f0f0f0; cursor: not-allowed;">
                                <small>⚠️ Condition defines the variant and cannot be changed. Create a new variant if needed.</small>
                            </div>
                            <div class="form-group">
                                <label>Price ($) *</label>
                                <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($edit_product['price']); ?>" required>
                                <small>Update price for this specific condition variant</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Add Mode: Allow selecting multiple conditions with individual prices and stock -->
                        <div class="form-group">
                            <label>Select Conditions to Create Variants *</label>
                            <small>Each selected condition creates a separate variant with its own price and initial stock</small>
                            
                            <div class="conditions-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 10px;">
                                <?php 
                                $all_conditions = ['Mint', 'Used', 'Refurbished'];
                                foreach ($all_conditions as $cond):
                                ?>
                                    <div class="condition-card" style="border: 2px solid #ddd; padding: 15px; border-radius: 6px; cursor: pointer; transition: all 0.3s ease;" onclick="toggleCondition(this, '<?php echo $cond; ?>')">
                                        <input type="checkbox" name="conditions[]" value="<?php echo $cond; ?>" class="condition-checkbox" style="display: none;" onchange="updateVariantPreview()">
                                        <div class="condition-label" style="font-weight: 600; margin-bottom: 12px; text-align: center; font-size: 16px;">
                                            <?php echo $cond; ?>
                                        </div>
                                        
                                        <div class="condition-price-wrapper" style="margin-bottom: 10px;">
                                            <label style="display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 4px;">Price ($)</label>
                                            <input type="number" 
                                                name="condition_prices[<?php echo $cond; ?>]" 
                                                class="condition-price-input" 
                                                placeholder="0.00" 
                                                step="0.01" 
                                                min="0.01"
                                                onclick="event.stopPropagation();"
                                                onchange="updateVariantPreview()"
                                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                        </div>

                                        <div class="condition-stock-wrapper">
                                            <label style="display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 4px;">Initial Stock</label>
                                            <input type="number" 
                                                name="condition_stocks[<?php echo $cond; ?>]" 
                                                class="condition-stock-input" 
                                                placeholder="0" 
                                                step="1" 
                                                min="0"
                                                onclick="event.stopPropagation();"
                                                onchange="updateVariantPreview()"
                                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Variant Preview/Summary -->
                        <div id="variant-preview-container" style="display: none; margin-top: 20px; padding: 15px; background: #f0f7ff; border: 2px solid #667eea; border-radius: 8px;">
                            <h4 style="margin: 0 0 15px 0; color: #333;">
                                📋 Variants to Create: <span id="variant-count">0</span>
                            </h4>
                            <div id="variant-preview-table" style="background: white; border-radius: 6px; overflow: hidden;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #667eea; color: white;">
                                            <th style="padding: 10px; text-align: left;">Condition</th>
                                            <th style="padding: 10px; text-align: right;">Price</th>
                                            <th style="padding: 10px; text-align: right;">Initial Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variant-preview-body">
                                    </tbody>
                                </table>
                            </div>
                            <small style="display: block; margin-top: 10px; color: #666;">
                                ℹ️ All selected variants will be created with these settings
                            </small>
                        </div>

                        <script>
                        function toggleCondition(card, condition) {
                            const checkbox = card.querySelector('.condition-checkbox');
                            checkbox.checked = !checkbox.checked;
                            
                            // Visual feedback for selection
                            if (checkbox.checked) {
                                card.style.backgroundColor = '#e3f2fd';
                                card.style.borderColor = '#667eea';
                                card.style.borderWidth = '2px';
                            } else {
                                card.style.backgroundColor = 'transparent';
                                card.style.borderColor = '#ddd';
                                card.style.borderWidth = '2px';
                            }
                            
                            updateVariantPreview();
                        }

                        /**
                         * Update the variant preview table to show what will be created
                         */
                        function updateVariantPreview() {
                            const checked = document.querySelectorAll('.condition-checkbox:checked');
                            const previewContainer = document.getElementById('variant-preview-container');
                            const previewBody = document.getElementById('variant-preview-body');
                            const variantCount = document.getElementById('variant-count');
                            
                            // Clear existing rows
                            previewBody.innerHTML = '';
                            
                            let count = 0;
                            let allValid = true;
                            
                            checked.forEach(checkbox => {
                                const condition = checkbox.value;
                                const priceInput = document.querySelector('input[name="condition_prices[' + condition + ']"]');
                                const stockInput = document.querySelector('input[name="condition_stocks[' + condition + ']"]');
                                
                                const price = parseFloat(priceInput.value);
                                const stock = parseInt(stockInput.value);
                                
                                count++;
                                
                                // Determine row styling based on validity
                                const isValid = !isNaN(price) && price > 0 && !isNaN(stock) && stock >= 0;
                                const rowStyle = isValid ? '' : 'background: #ffebee; opacity: 0.7;';
                                
                                if (!isValid) {
                                    allValid = false;
                                }
                                
                                // Create row
                                const row = document.createElement('tr');
                                row.style.cssText = rowStyle + 'border-bottom: 1px solid #ddd;';
                                row.innerHTML = `
                                    <td style="padding: 10px; color: #333;">
                                        <strong>${condition}</strong>
                                        ${!isValid ? '<br><small style="color: #c62828;">⚠️ Invalid</small>' : ''}
                                    </td>
                                    <td style="padding: 10px; text-align: right; color: #333;">
                                        ${isNaN(price) || price <= 0 ? '<span style="color: #c62828;">-</span>' : '$' + price.toFixed(2)}
                                    </td>
                                    <td style="padding: 10px; text-align: right; color: #333;">
                                        ${isNaN(stock) || stock < 0 ? '<span style="color: #c62828;">-</span>' : stock + ' units'}
                                    </td>
                                `;
                                previewBody.appendChild(row);
                            });
                            
                            // Show/hide preview container
                            if (count > 0) {
                                variantCount.textContent = count;
                                previewContainer.style.display = 'block';
                                
                                // Style container border based on validity
                                if (!allValid) {
                                    previewContainer.style.borderColor = '#ffb74d';
                                    previewContainer.style.background = '#fffbf0';
                                } else {
                                    previewContainer.style.borderColor = '#667eea';
                                    previewContainer.style.background = '#f0f7ff';
                                }
                            } else {
                                previewContainer.style.display = 'none';
                            }
                        }

                        // Validate at least one condition is selected with valid inputs before submit
                        document.querySelector('.product-form').addEventListener('submit', function(e) {
                            const checked = document.querySelectorAll('.condition-checkbox:checked');
                            if (checked.length === 0) {
                                e.preventDefault();
                                alert('Please select at least one condition for the product variant.');
                                return false;
                            }

                            // Validate price and stock for each selected condition
                            let isValid = true;
                            checked.forEach(checkbox => {
                                const condition = checkbox.value;
                                const priceInput = document.querySelector('input[name="condition_prices[' + condition + ']"]');
                                const stockInput = document.querySelector('input[name="condition_stocks[' + condition + ']"]');
                                
                                const price = parseFloat(priceInput.value);
                                const stock = parseInt(stockInput.value);

                                if (isNaN(price) || price <= 0) {
                                    alert('Price must be greater than 0 for ' + condition + ' condition.');
                                    isValid = false;
                                    return;
                                }

                                if (isNaN(stock) || stock < 0) {
                                    alert('Stock cannot be negative for ' + condition + ' condition.');
                                    isValid = false;
                                    return;
                                }
                            });

                            if (!isValid) {
                                e.preventDefault();
                                return false;
                            }
                        });
                        </script>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image_file" id="image_file" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small>📸 Upload a product image (JPEG, PNG, GIF, WebP - Max 5MB)</small>
                        <?php if ($edit_product && !empty($edit_product['image_url'])): ?>
                            <div id="image_preview" style="margin-top: 10px;">
                                <p><strong>Current Image:</strong></p>
                                <img src="<?php echo htmlspecialchars(SITE_URL . '/' . $edit_product['image_url']); ?>" alt="Current" style="max-width: 100%; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                        <div id="new_image_preview" style="margin-top: 10px; display: none;">
                            <p><strong>New Image Preview:</strong></p>
                            <img id="preview_img" src="" alt="Preview" style="max-width: 100%; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Demo Video URL</label>
                        <input type="text" name="demo_url" id="demo_url" placeholder="https://www.youtube.com/watch?v=VIDEO_ID" value="<?php echo $edit_product ? htmlspecialchars($edit_product['demo_url'] ?? '') : ''; ?>">
                        <small>🎥 Paste YouTube URL (https://www.youtube.com/watch?v=...) - we'll convert it automatically</small>
                    </div>

                    <script>
                    // Preview image when file is selected
                    document.getElementById('image_file').addEventListener('change', function() {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('preview_img').src = e.target.result;
                                document.getElementById('new_image_preview').style.display = 'block';
                            }
                            reader.readAsDataURL(file);
                        } else {
                            document.getElementById('new_image_preview').style.display = 'none';
                        }
                    });

                    // Convert YouTube URLs to embed format when user leaves the field
                    document.getElementById('demo_url').addEventListener('blur', function() {
                        const youtubeRegex = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/;
                        const match = this.value.match(youtubeRegex);
                        if (match && match[1]) {
                            this.value = 'https://www.youtube.com/embed/' + match[1];
                            console.log('✓ YouTube URL converted to embed format');
                        }
                    });
                    </script>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_product ? 'Update Product' : 'Add Product'; ?></button>
                        <?php if ($edit_product): ?>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Products List -->
            <div class="products-list-section">
                <h3>Your Products (<?php echo count($products); ?> variants)</h3>
                <?php if (empty($products)): ?>
                    <p>You haven't added any products yet.</p>
                <?php else: ?>
                    <?php
                    // Group products by name + console to show variants
                    $grouped_products = [];
                    foreach ($products as $prod) {
                        $group_key = $prod['name'] . '|' . $prod['console'];
                        if (!isset($grouped_products[$group_key])) {
                            $grouped_products[$group_key] = [
                                'name' => $prod['name'],
                                'console' => $prod['console'],
                                'variants' => []
                            ];
                        }
                        $grouped_products[$group_key]['variants'][] = $prod;
                    }
                    ?>
                    <div class="products-variants-container">
                        <?php foreach ($grouped_products as $group): ?>
                            <div class="product-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f9f9f9;">
                                <div class="group-header" style="margin-bottom: 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                    <h4 style="margin: 0; color: #333;">
                                        <?php echo htmlspecialchars($group['name']); ?> - <?php echo htmlspecialchars($group['console']); ?>
                                        <span style="background: #667eea; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">
                                            <?php echo count($group['variants']); ?> variant(s)
                                        </span>
                                    </h4>
                                </div>

                                <div class="variants-list" style="display: grid; gap: 10px;">
                                    <?php foreach ($group['variants'] as $variant): ?>
                                        <div class="variant-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: center; padding: 10px; background: white; border-radius: 4px; border-left: 4px solid #667eea;">
                                            <div>
                                                <strong>Condition:</strong><br>
                                                <span style="font-size: 14px; color: #666;">
                                                    <?php echo htmlspecialchars($variant['condition']); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <strong>Price:</strong><br>
                                                <span style="font-size: 14px; color: #666;">
                                                    $<?php echo number_format($variant['price'], 2); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <strong>Stock:</strong><br>
                                                <span style="font-size: 14px; color: #666;">
                                                    <?php echo $variant['stock']; ?>
                                                </span>
                                            </div>
                                            <div>
                                                <strong>Category:</strong><br>
                                                <span style="font-size: 14px; color: #666;">
                                                    <?php echo htmlspecialchars($variant['category'] ?? 'N/A'); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <strong>Status:</strong><br>
                                                <span style="font-size: 12px; padding: 4px 8px; border-radius: 4px; 
                                                    <?php 
                                                    if ($variant['stock'] == 0) {
                                                        echo 'background: #ffebee; color: #c62828;';
                                                    } elseif ($variant['stock'] <= 3) {
                                                        echo 'background: #fff3cd; color: #856404;';
                                                    } else {
                                                        echo 'background: #d4edda; color: #155724;';
                                                    }
                                                    ?>
                                                ">
                                                    <?php 
                                                    if ($variant['stock'] == 0) {
                                                        echo '⚠️ Out';
                                                    } elseif ($variant['stock'] <= 3) {
                                                        echo '⚡ Low';
                                                    } else {
                                                        echo '✓ OK';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end;">
                                                <a href="products.php?edit=<?php echo $variant['product_id']; ?>" class="btn btn-small btn-info" title="Edit price and details">Edit</a>
                                                <button type="button" class="btn btn-small btn-success" onclick="openStockModal(<?php echo $variant['product_id']; ?>, '<?php echo htmlspecialchars($group['name']); ?> - <?php echo htmlspecialchars($variant['condition']); ?>', <?php echo $variant['stock']; ?>)" title="Add stock for this condition">Stock</button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo $variant['product_id']; ?>">
                                                    <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Delete this variant?');" title="Remove this condition variant">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stock Management Modal -->
    <div id="stock-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Manage Stock - <span id="modal-product-name"></span></h3>
                <button type="button" class="modal-close" onclick="closeStockModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Current Stock Display -->
                <div class="stock-info">
                    <p><strong>Current Stock:</strong></p>
                    <div class="stock-display">
                        <span id="current-stock" class="stock-value">0</span>
                        <span id="low-stock-warning" class="stock-warning" style="display: none;">⚠️ Low Stock!</span>
                    </div>
                </div>

                <!-- Add Stock Form -->
                <div class="add-stock-form">
                    <label for="add-quantity">Add Stock Quantity:</label>
                    <div class="input-group">
                        <input 
                            type="number" 
                            id="add-quantity" 
                            name="quantity" 
                            min="1" 
                            value="1"
                            placeholder="Enter quantity to add"
                        >
                        <span class="help-text">Enter a positive number to add to current stock</span>
                    </div>
                </div>

                <!-- Message Display -->
                <div id="stock-message" class="stock-message" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeStockModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitStock()">Update Stock</button>
            </div>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div id="stock-modal-overlay" class="modal-overlay" style="display: none;" onclick="closeStockModal()"></div>

    <style>
    /* Stock Management Modal Styles */
    .modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        max-width: 500px;
        width: 90%;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #eee;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: white;
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover {
        opacity: 0.8;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .stock-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        border-left: 4px solid #667eea;
    }

    .stock-info p {
        margin: 0 0 10px 0;
        font-weight: 600;
        color: #333;
    }

    .stock-display {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stock-value {
        font-size: 32px;
        font-weight: bold;
        color: #667eea;
    }

    .stock-warning {
        background: #fff3cd;
        color: #856404;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
    }

    .add-stock-form {
        margin-bottom: 20px;
    }

    .add-stock-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .add-stock-form input {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .add-stock-form input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .help-text {
        font-size: 12px;
        color: #666;
    }

    .stock-message {
        padding: 12px;
        border-radius: 4px;
        margin-top: 15px;
        font-size: 14px;
    }

    .stock-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .stock-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>

    <script>
    let currentProductId = null;
    let currentStock = 0;

    /**
     * Open stock management modal
     * @param {int} productId - Product ID
     * @param {string} productName - Product name
     * @param {int} stock - Current stock quantity
     */
    function openStockModal(productId, productName, stock) {
        currentProductId = productId;
        currentStock = stock;

        // Update modal content
        document.getElementById('modal-product-name').textContent = productName;
        document.getElementById('current-stock').textContent = stock;
        document.getElementById('add-quantity').value = '1';
        document.getElementById('stock-message').style.display = 'none';

        // Show low-stock warning if stock <= 3
        const warningElement = document.getElementById('low-stock-warning');
        if (stock <= 3) {
            warningElement.style.display = 'inline';
        } else {
            warningElement.style.display = 'none';
        }

        // Show modal
        document.getElementById('stock-modal').style.display = 'block';
        document.getElementById('stock-modal-overlay').style.display = 'block';

        // Focus on quantity input
        document.getElementById('add-quantity').focus();
    }

    /**
     * Close stock management modal
     */
    function closeStockModal() {
        document.getElementById('stock-modal').style.display = 'none';
        document.getElementById('stock-modal-overlay').style.display = 'none';
        currentProductId = null;
        currentStock = 0;
    }

    /**
     * Submit stock update via AJAX
     */
    function submitStock() {
        const quantity = parseInt(document.getElementById('add-quantity').value);
        const messageEl = document.getElementById('stock-message');

        // Validate input
        if (isNaN(quantity) || quantity <= 0) {
            showMessage('Please enter a positive number', 'error');
            return;
        }

        if (!currentProductId) {
            showMessage('Error: Product not found', 'error');
            return;
        }

        // Submit via AJAX
        fetch('products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add_stock&product_id=' + currentProductId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display with new stock
                currentStock = data.new_stock;
                document.getElementById('current-stock').textContent = data.new_stock;
                
                // Show success message
                showMessage('✓ Stock updated to ' + data.new_stock + ' units', 'success');
                
                // Check for low stock warning
                const warningElement = document.getElementById('low-stock-warning');
                if (data.new_stock <= 3) {
                    warningElement.style.display = 'inline';
                } else {
                    warningElement.style.display = 'none';
                }

                // Reset form and close after 2 seconds
                setTimeout(() => {
                    document.getElementById('add-quantity').value = '1';
                    // Refresh the products list table
                    location.reload();
                }, 1500);
            } else {
                showMessage(data.message || 'Error updating stock', 'error');
            }
        })
        .catch(error => {
            showMessage('Network error: ' + error, 'error');
        });
    }

    /**
     * Show message in modal
     * @param {string} message - Message text
     * @param {string} type - 'success' or 'error'
     */
    function showMessage(message, type) {
        const messageEl = document.getElementById('stock-message');
        messageEl.textContent = message;
        messageEl.className = 'stock-message ' + type;
        messageEl.style.display = 'block';
    }

    // Allow Enter key to submit
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('add-quantity');
        if (quantityInput) {
            quantityInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    submitStock();
                }
            });
        }
    });

    // Close modal when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStockModal();
        }
    });
    </script>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
