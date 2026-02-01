<?php
require_once '../../config.php';

// Check if logged in and is seller
if (!SessionManager::isLoggedIn() || !SessionManager::hasRole('seller')) {
    header('Location: ../customer/auth.php');
    exit;
}

// Logout
SessionManager::destroy();
header('Location: ../customer/auth.php');
exit;
?>
