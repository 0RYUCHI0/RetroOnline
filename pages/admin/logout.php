<?php
require_once '../../config.php';

// Check if logged in and is admin
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('admin')) {
    header('Location: ../customer/auth.php');
    exit;
}

// Logout
SessionManager::destroy();
header('Location: ../customer/auth.php');
exit;
?>
