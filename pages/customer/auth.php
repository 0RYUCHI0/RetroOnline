<?php
require_once '../../config.php';
require_once '../../classes/User.php';

$error = '';
$success = '';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    SessionManager::destroy();
    header('Location: auth.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'All fields are required';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            try {
                $user = new User();
                $user_id = $user->register($name, $email, $password, $phone);
                $success = 'Registration successful! Please login.';
                // Redirect to login after 2 seconds
                header("refresh:2;url=auth.php");
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    } elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
        } else {
            try {
                $user = new User();
                $user->login($email, $password);
                // Redirect based on role
                $roles = SessionManager::getUserRoles();
                if (in_array('admin', $roles)) {
                    header('Location: ../admin/dashboard.php');
                } elseif (in_array('seller', $roles)) {
                    header('Location: ../seller/dashboard.php');
                } else {
                    header('Location: shop.php');
                }
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// If already logged in, redirect
if (SessionManager::isLoggedIn()) {
    $roles = SessionManager::getUserRoles();
    if (in_array('admin', $roles)) {
        header('Location: ../admin/dashboard.php');
    } elseif (in_array('seller', $roles)) {
        header('Location: ../seller/dashboard.php');
    } else {
        header('Location: shop.php');
    }
    exit;
}

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroGameHub - Login/Register</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h1>RetroGameHub</h1>
            <p>The Ultimate Retro Game Marketplace</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="auth-tabs">
            <a href="?tab=login" class="tab-button <?php echo $current_tab === 'login' ? 'active' : ''; ?>">Login</a>
            <a href="?tab=register" class="tab-button <?php echo $current_tab === 'register' ? 'active' : ''; ?>">Register</a>
        </div>

        <?php if ($current_tab === 'login'): ?>
            <form method="POST" class="auth-form">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="demo-credentials">
                <h3>Demo Credentials</h3>
                <p><strong>Admin:</strong> admin@retrohub.com / password123</p>
                <p><strong>Seller 1:</strong> seller1@retrohub.com / seller123</p>
                <p><strong>Seller 2:</strong> seller2@retrohub.com / seller123</p>
                <p><strong>Customer 1:</strong> gamer1@retrohub.com / gamer123</p>
                <p><strong>Customer 2:</strong> gamer2@retrohub.com / gamer123</p>
            </div>
        <?php else: ?>
            <form method="POST" class="auth-form">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number (Optional)</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
