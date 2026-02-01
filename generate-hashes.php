<?php
/**
 * Password Hash Generator
 * Generate bcrypt hashes for demo credentials
 */

// Demo passwords
$demo_accounts = [
    'password123',  // admin
    'seller123',    // seller1
    'seller123',    // seller2
    'gamer123',     // gamer1
    'gamer123'      // gamer2
];

echo "<h1>Password Hash Generator</h1>";
echo "<p>Here are the bcrypt hashes for the demo accounts:</p>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Account</th><th>Password</th><th>Bcrypt Hash</th></tr>";

$accounts = [
    'admin@retrohub.com',
    'seller1@retrohub.com',
    'seller2@retrohub.com',
    'gamer1@retrohub.com',
    'gamer2@retrohub.com'
];

$hashes = [];
foreach ($demo_accounts as $index => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $hashes[] = $hash;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($accounts[$index]) . "</td>";
    echo "<td>" . htmlspecialchars($password) . "</td>";
    echo "<td><code>" . htmlspecialchars($hash) . "</code></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>SQL INSERT Statement (Copy and use in database.sql):</h2>";
echo "<pre>";
echo "-- Admin User login: admin@retrohub.com / password123\n";
echo "INSERT INTO users (name, email, password, phone, roles, status) VALUES\n";
echo "('Admin Retro', 'admin@retrohub.com', '" . $hashes[0] . "', '555-0001', '[\"admin\"]', 'active');\n\n";

echo "-- Seller 1 login: seller1@retrohub.com / seller123\n";
echo "INSERT INTO users (name, email, password, phone, roles, status) VALUES\n";
echo "('RetroKing', 'seller1@retrohub.com', '" . $hashes[1] . "', '555-0002', '[\"seller\"]', 'active');\n\n";

echo "-- Seller 2 login: seller2@retrohub.com / seller123\n";
echo "INSERT INTO users (name, email, password, phone, roles, status) VALUES\n";
echo "('PixelGames', 'seller2@retrohub.com', '" . $hashes[2] . "', '555-0003', '[\"seller\"]', 'active');\n\n";

echo "-- Customer 1 login: gamer1@retrohub.com / gamer123\n";
echo "INSERT INTO users (name, email, password, phone, roles, status) VALUES\n";
echo "('GamerOne', 'gamer1@retrohub.com', '" . $hashes[3] . "', '555-0004', '[\"customer\"]', 'active');\n\n";

echo "-- Customer 2 login: gamer2@retrohub.com / gamer123\n";
echo "INSERT INTO users (name, email, password, phone, roles, status) VALUES\n";
echo "('GamerTwo', 'gamer2@retrohub.com', '" . $hashes[4] . "', '555-0005', '[\"customer\"]', 'active');\n";
echo "</pre>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a2e; color: white; }
h1, h2 { color: #e94560; }
table { border-collapse: collapse; background: #16213e; margin: 20px 0; }
td, th { border: 1px solid #e94560; padding: 10px; text-align: left; }
th { background: #2c3e50; }
code { background: #0f3460; padding: 5px 10px; border-radius: 3px; display: block; margin: 10px 0; word-break: break-all; }
pre { background: #0f3460; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>
