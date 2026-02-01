<?php
// RetroGameHub - Main Entry Point
require_once 'config.php';

// Check if logged in
if (SessionManager::isLoggedIn()) {
    $roles = SessionManager::getUserRoles();
    if (in_array('admin', $roles)) {
        header('Location: /retroonline/pages/admin/dashboard.php');
    } elseif (in_array('seller', $roles)) {
        header('Location: /retroonline/pages/seller/dashboard.php');
    } else {
        header('Location: /retroonline/pages/customer/shop.php');
    }
    exit;
} else {
    header('Location: /retroonline/pages/customer/auth.php');
    exit;
}
?>
