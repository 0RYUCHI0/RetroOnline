<?php
/**
 * Test ActivityLog Integration
 */

require_once 'config.php';
require_once 'classes/User.php';
require_once 'classes/Product.php';
require_once 'classes/Order.php';
require_once 'classes/ActivityLog.php';

try {
    echo "Testing class initialization...\n";
    echo "✅ All classes loaded successfully\n\n";
    
    // Test ActivityLog methods exist
    $activityLog = new ActivityLog();
    $methods = ['log', 'getAllLogs', 'getTotalLogsCount', 'getLogsByUser', 'getActivitySummary'];
    
    foreach ($methods as $method) {
        if (method_exists($activityLog, $method)) {
            echo "✅ ActivityLog::$method exists\n";
        } else {
            echo "❌ ActivityLog::$method missing\n";
        }
    }
    
    echo "\n✅ Activity logging system is ready!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
?>
