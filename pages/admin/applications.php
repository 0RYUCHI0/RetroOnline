<?php
require_once '../../config.php';
require_once '../../classes/Seller.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$seller_obj = new Seller();
$pending_apps = $seller_obj->getPendingApplications();

$error = '';
$success = '';

// Handle application actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        try {
            $app_id = (int)$_POST['application_id'];
            $seller_obj->approveApplication($app_id);
            $success = 'Application approved successfully!';
            header('refresh:2');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'reject') {
        try {
            $app_id = (int)$_POST['application_id'];
            $seller_obj->rejectApplication($app_id);
            $success = 'Application rejected!';
            header('refresh:2');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$user_name = SessionManager::get('user_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Applications - RetroGameHub</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="applications-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="activity-logs.php" class="nav-link">📊 Activity Logs</a>
                <a href="applications.php" class="nav-link active">Applications</a>
                <a href="sellers.php" class="nav-link">Sellers</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Seller Applications</h2>
            <p>Review and manage pending seller applications</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($pending_apps)): ?>
            <div class="empty-state">
                <p>No pending applications.</p>
            </div>
        <?php else: ?>
            <div class="applications-list">
                <?php foreach ($pending_apps as $app): ?>
                    <div class="application-card">
                        <div class="app-header">
                            <h3><?php echo htmlspecialchars($app['store_name']); ?></h3>
                            <span class="badge badge-pending">Pending</span>
                        </div>
                        <div class="app-details">
                            <div class="detail-row">
                                <span class="label">Applicant:</span>
                                <span><?php echo htmlspecialchars($app['name']); ?> (<?php echo htmlspecialchars($app['email']); ?>)</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Contact Info:</span>
                                <span><?php echo htmlspecialchars($app['contact_info']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Payment Info:</span>
                                <span><?php echo htmlspecialchars($app['payment_info']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Applied:</span>
                                <span><?php echo date('M d, Y h:i A', strtotime($app['applied_at'])); ?></span>
                            </div>
                        </div>
                        <div class="app-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this application?')">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
