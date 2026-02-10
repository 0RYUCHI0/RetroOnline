<?php
require_once '../../config.php';
require_once '../../classes/ActivityLog.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}

$activityLog = new ActivityLog();
$user_name = SessionManager::get('user_name');

// Pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 50;
$offset = ($page - 1) * $limit;

// Build filters
$filters = [];
if (!empty($_GET['action_type'])) {
    $filters['action_type'] = $_GET['action_type'];
}
if (!empty($_GET['entity_type'])) {
    $filters['entity_type'] = $_GET['entity_type'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Get logs and total
$logs = $activityLog->getAllLogs($limit, $offset, $filters);
$total = $activityLog->getTotalLogsCount($filters);
$pages = ceil($total / $limit);

// Get summary data
$activitySummary = $activityLog->getActivitySummary(30);
$mostActiveUsers = $activityLog->getMostActiveUsers(5, 30);

// Action types for filter dropdown
$actionTypes = [
    'login' => 'User Login',
    'product_update' => 'Product Update',
    'product_delete' => 'Product Delete',
    'stock_increase' => 'Stock Increase',
    'stock_decrease' => 'Stock Decrease',
    'order_status_update' => 'Order Status Update',
    'profile_update' => 'Profile Update',
    'password_change' => 'Password Change',
];

$entityTypes = [
    'user' => 'User',
    'product' => 'Product',
    'order' => 'Order',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - RetroGameHub Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .activity-logs-page {
            background: #f5f5f5;
            color: #333;
        }

        .page-header {
            margin: 30px 0;
            padding-bottom: 20px;
            border-bottom: 3px solid #0066cc;
        }

        .page-header h2 {
            color: #0066cc;
            margin: 0;
            font-size: 2em;
            font-weight: 600;
        }

        .page-header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 0.95em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #0066cc, #0052a3);
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #fff;
            font-size: 0.95em;
        }

        .dashboard-section {
            background: #fff;
            padding: 30px;
            margin: 30px 0;
            border-radius: 8px;
            border-left: 5px solid #0066cc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dashboard-section h3 {
            color: #0066cc;
            margin-top: 0;
            font-weight: 600;
        }

        .filter-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-row:last-child {
            margin-bottom: 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: #0066cc;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            background: #fff;
            color: #333;
            border-radius: 4px;
            font-size: 0.95em;
        }

        .form-group input::placeholder {
            color: #999;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #0066cc;
            background: #f0f7ff;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-filter {
            padding: 10px 20px;
            background: #0066cc;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-filter:hover {
            background: #0052a3;
        }

        .btn-clear {
            padding: 10px 20px;
            background: #999;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-clear:hover {
            background: #777;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .logs-table thead {
            background: #0066cc;
        }

        .logs-table th {
            padding: 15px;
            text-align: left;
            color: #fff;
            font-weight: 600;
            border-bottom: 2px solid #0052a3;
            font-size: 0.9em;
        }

        .logs-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
            font-size: 0.9em;
        }

        .logs-table tbody tr:hover {
            background: #f0f7ff;
        }

        .action-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .action-login {
            background: #d4edda;
            color: #155724;
        }

        .action-update {
            background: #cce5ff;
            color: #004085;
        }

        .action-delete {
            background: #f8d7da;
            color: #721c24;
        }

        .action-increase {
            background: #d4edda;
            color: #155724;
        }

        .action-decrease {
            background: #f8d7da;
            color: #721c24;
        }

        .action-change {
            background: #e2e3e5;
            color: #383d41;
        }

        .entity-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #0066cc;
            color: #fff;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .timestamp {
            color: #666;
            font-size: 0.85em;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #0066cc;
        }

        .user-email {
            color: #666;
            font-size: 0.85em;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #0066cc;
            color: #0066cc;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            background: #fff;
        }

        .pagination a:hover {
            background: #0066cc;
            color: #fff;
        }

        .pagination .current {
            background: #0066cc;
            color: #fff;
            border-color: #0066cc;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }

        .activity-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 3px solid #0066cc;
        }

        .activity-card h4 {
            color: #0066cc;
            margin: 0 0 10px 0;
            font-size: 0.95em;
            font-weight: 600;
        }

        .activity-card p {
            margin: 5px 0;
            color: #555;
            font-size: 0.9em;
        }

        .result-info {
            text-align: center;
            color: #999;
            padding: 40px 20px;
            font-size: 0.95em;
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }

            .two-column {
                grid-template-columns: 1fr;
            }

            .logs-table {
                font-size: 0.85em;
            }

            .logs-table th,
            .logs-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body class="activity-logs-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>RetroGameHub Admin</h1>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="activity-logs.php" class="nav-link active">Activity Logs</a>
                <a href="applications.php" class="nav-link">Applications</a>
                <a href="sellers.php" class="nav-link">Sellers</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($user_name); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Activity Logs</h2>
            <p>Monitor all user actions and system activities</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3 class="stat-value"><?php echo $total; ?></h3>
                <p class="stat-label">Total Actions</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-value"><?php echo count($mostActiveUsers); ?></h3>
                <p class="stat-label">Active Users (30 days)</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-value"><?php echo count($activitySummary); ?></h3>
                <p class="stat-label">Action Types</p>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="two-column">
            <div class="dashboard-section">
                <h3>Activity Summary (Last 30 Days)</h3>
                <?php if (empty($activitySummary)): ?>
                    <p class="result-info">No activities recorded</p>
                <?php else: ?>
                    <?php foreach ($activitySummary as $activity): ?>
                        <div class="activity-card">
                            <h4><?php echo htmlspecialchars($actionTypes[$activity['action_type']] ?? $activity['action_type']); ?></h4>
                            <p><strong><?php echo number_format($activity['count']); ?></strong> actions</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="dashboard-section">
                <h3>Most Active Users (30 Days)</h3>
                <?php if (empty($mostActiveUsers)): ?>
                    <p class="result-info">No user activities</p>
                <?php else: ?>
                    <?php foreach ($mostActiveUsers as $user): ?>
                        <div class="activity-card">
                            <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong><?php echo number_format($user['action_count']); ?></strong> actions</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="dashboard-section">
            <h3>Filter Logs</h3>
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="form-group">
                        <label>Action Type</label>
                        <select name="action_type">
                            <option value="">All Actions</option>
                            <?php foreach ($actionTypes as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($filters['action_type'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Entity Type</label>
                        <select name="entity_type">
                            <option value="">All Entities</option>
                            <?php foreach ($entityTypes as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($filters['entity_type'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                    </div>
                </div>

                <div class="filter-row">
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Search Description</label>
                        <input type="text" name="search" placeholder="Search logs..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                    </div>
                    <div class="form-group filter-buttons">
                        <button type="submit" class="btn-filter">Filter</button>
                        <a href="activity-logs.php" class="btn-clear">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Activity Logs Table -->
        <div class="dashboard-section">
            <h3>Activity Logs</h3>
            
            <?php if (empty($logs)): ?>
                <p class="result-info">No activity logs found</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="timestamp">
                                        <?php echo date('M d, Y H:i:s', strtotime($log['logged_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($log['user_name'] ?? 'N/A'); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($log['user_email'] ?? 'N/A'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $actionClass = 'action-update';
                                        if (strpos($log['action_type'], 'login') !== false) {
                                            $actionClass = 'action-login';
                                        } elseif (strpos($log['action_type'], 'delete') !== false) {
                                            $actionClass = 'action-delete';
                                        } elseif (strpos($log['action_type'], 'increase') !== false) {
                                            $actionClass = 'action-increase';
                                        } elseif (strpos($log['action_type'], 'decrease') !== false) {
                                            $actionClass = 'action-decrease';
                                        } elseif (strpos($log['action_type'], 'change') !== false) {
                                            $actionClass = 'action-change';
                                        }
                                        ?>
                                        <span class="action-badge <?php echo $actionClass; ?>">
                                            <?php echo htmlspecialchars($actionTypes[$log['action_type']] ?? $log['action_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log['entity_type']): ?>
                                            <span class="entity-badge">
                                                <?php echo htmlspecialchars($entityTypes[$log['entity_type']] ?? $log['entity_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($log['description']); ?>
                                        <?php if ($log['entity_id']): ?>
                                            <br><small style="color: #666;">(ID: <?php echo $log['entity_id']; ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1<?php echo http_build_query($filters) ? '&' . http_build_query($filters) : ''; ?>">First</a>
                            <a href="?page=<?php echo $page - 1; ?><?php echo http_build_query($filters) ? '&' . http_build_query($filters) : ''; ?>">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php echo http_build_query($filters) ? '&' . http_build_query($filters) : ''; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo http_build_query($filters) ? '&' . http_build_query($filters) : ''; ?>">Next</a>
                            <a href="?page=<?php echo $pages; ?><?php echo http_build_query($filters) ? '&' . http_build_query($filters) : ''; ?>">Last</a>
                        <?php endif; ?>
                    </div>
                    <p style="text-align: center; color: #888; margin-top: 15px;">
                        Page <?php echo $page; ?> of <?php echo $pages; ?> (<?php echo $total; ?> total records)
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 RetroGameHub. All rights reserved.</p>
    </footer>
</body>
</html>
