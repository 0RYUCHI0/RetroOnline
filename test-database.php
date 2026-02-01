<?php
/**
 * Database Connection Test
 * This file tests the database connection and verifies all tables exist
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'RetroGameHub';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("<h1>❌ Connection Failed</h1><p>" . $conn->connect_error . "</p>");
}

echo "<h1>✅ Database Connection Successful</h1>";
echo "<p>Connected to: <strong>" . $db_name . "</strong></p>";

// Check tables
$tables_to_check = [
    'users',
    'sellers',
    'seller_applications',
    'products',
    'addresses',
    'orders',
    'order_items',
    'order_tracking',
    'reviews',
    'commissions',
    'couriers'
];

echo "<h2>Table Status:</h2>";
echo "<ul>";

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<li>✅ Table '$table' exists</li>";
    } else {
        echo "<li>❌ Table '$table' NOT found</li>";
    }
}

echo "</ul>";

// Check demo data
echo "<h2>Demo Data Count:</h2>";
echo "<ul>";

$demo_checks = [
    'users' => 'user_id',
    'sellers' => 'seller_id',
    'products' => 'product_id',
    'orders' => 'order_id',
    'reviews' => 'review_id'
];

foreach ($demo_checks as $table => $id) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<li>Table '$table': <strong>" . $row['count'] . " records</strong></li>";
    }
}

echo "</ul>";

echo "<h2>Test Credentials:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Role</th><th>Email</th><th>Password</th></tr>";

$users = $conn->query("SELECT name, email, roles FROM users");
while ($user = $users->fetch_assoc()) {
    $role = json_decode($user['roles'], true)[0];
    echo "<tr><td>" . htmlspecialchars($role) . "</td><td>" . htmlspecialchars($user['email']) . "</td><td>See setup guide</td></tr>";
}

echo "</table>";

echo "<h2>✅ All Systems Ready!</h2>";
echo "<p><a href='index.php'>Go to RetroGameHub Home</a></p>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a2e; color: white; }
h1 { color: #e94560; }
h2 { color: #3498db; }
table { border-collapse: collapse; background: #16213e; }
td, th { border: 1px solid #e94560; padding: 10px; text-align: left; }
th { background: #2c3e50; }
a { color: #e94560; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
