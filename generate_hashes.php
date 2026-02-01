<?php
// Generate proper password hashes for database
$admin_hash = password_hash('password123', PASSWORD_BCRYPT);
$seller_hash = password_hash('seller123', PASSWORD_BCRYPT);
$customer_hash = password_hash('gamer123', PASSWORD_BCRYPT);

echo "Admin Hash: " . $admin_hash . "\n";
echo "Seller Hash: " . $seller_hash . "\n";
echo "Customer Hash: " . $customer_hash . "\n";
?>
