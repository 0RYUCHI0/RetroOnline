<?php
/**
 * Notification Helper Functions
 * Provides notification-related functions for customer pages
 */

/**
 * Get customer notifications
 */
function getCustomerNotifications($customer_id) {
    require_once __DIR__ . '/../classes/Order.php';
    require_once __DIR__ . '/../classes/Product.php';
    
    $order = new Order();
    $product = new Product();
    
    $notifications = [];
    
    // Get order tracking updates
    $user_orders = $order->getOrdersByUser($customer_id);
    foreach ($user_orders as $ord) {
        $order_data = $order->getOrderById($ord['order_id']);
        if (!empty($order_data) && $order_data['status'] !== 'pending') {
            $notifications[] = [
                'type' => 'order_tracking',
                'title' => 'Order #' . $ord['order_id'] . ' Update',
                'message' => 'Status: ' . ucfirst($order_data['status']),
                'date' => $order_data['created_at'],
                'icon' => '📦'
            ];
        }
    }
    
    // Get recently added products (added in last 7 days)
    $all_products = $product->getAllProducts();
    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
    foreach ($all_products as $prod) {
        if (isset($prod['created_at']) && $prod['created_at'] > $seven_days_ago) {
            $notifications[] = [
                'type' => 'new_product',
                'title' => 'New Product Added',
                'message' => $prod['name'] . ' - $' . number_format($prod['price'], 2),
                'date' => $prod['created_at'],
                'icon' => '✨'
            ];
        }
    }
    
    // Sort notifications by date (newest first)
    usort($notifications, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // Keep only latest 10 notifications
    return array_slice($notifications, 0, 10);
}

/**
 * Get notification styles (fixed top-right positioning)
 */
function getNotificationStyles() {
    return '
        .notification-bar {
            position: fixed;
            top: 0;
            right: 0;
            z-index: 2000;
            margin: 15px 15px 0 0;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 24px;
            transition: color 0.3s;
            display: inline-block;
            background: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .notification-bell:hover {
            color: #007bff;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            border: 2px solid white;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 350px;
            max-height: 500px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-top: 10px;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-header {
            padding: 15px;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
            background: white;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f5f5f5;
            transition: background-color 0.2s;
        }

        .notification-item:hover {
            background-color: #f9f9f9;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            font-size: 18px;
            margin-right: 10px;
        }

        .notification-content {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .notification-text {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .notification-message {
            color: #666;
            font-size: 12px;
            line-height: 1.4;
        }

        .notification-time {
            color: #999;
            font-size: 11px;
            margin-top: 5px;
        }

        .notification-empty {
            padding: 30px 15px;
            text-align: center;
            color: #999;
        }

        @media (max-width: 768px) {
            .notification-bar {
                margin: 10px 10px 0 0;
            }

            .notification-bell {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }

            .notification-dropdown {
                width: 300px;
            }
        }
    ';
}

/**
 * Get notification script (with localStorage persistence)
 */
function getNotificationScript() {
    return '
        /**
         * Toggle notification dropdown
         */
        function toggleNotifications() {
            const dropdown = document.getElementById("notificationDropdown");
            dropdown.classList.toggle("show");
            
            // Save state to localStorage
            const isOpen = dropdown.classList.contains("show");
            localStorage.setItem("notificationDropdownOpen", isOpen ? "true" : "false");
        }

        /**
         * Close notification dropdown when clicking outside
         */
        document.addEventListener("click", function(event) {
            const bell = document.getElementById("notificationBell");
            const dropdown = document.getElementById("notificationDropdown");
            
            if (bell && dropdown && !bell.contains(event.target)) {
                dropdown.classList.remove("show");
                localStorage.setItem("notificationDropdownOpen", "false");
            }
        });

        /**
         * Restore notification dropdown state on page load
         */
        window.addEventListener("load", function() {
            const notificationDropdownOpen = localStorage.getItem("notificationDropdownOpen") === "true";
            const dropdown = document.getElementById("notificationDropdown");
            
            if (notificationDropdownOpen && dropdown) {
                dropdown.classList.add("show");
            }
        });
    ';
}

/**
 * Render notification bar HTML (fixed top-right)
 */
function renderNotificationBar($notifications) {
    $unread_count = count($notifications);
    $html = '<div class="notification-bar">';
    $html .= '<div class="notification-bell" id="notificationBell" onclick="toggleNotifications()">';
    $html .= '🔔';
    
    if ($unread_count > 0) {
        $html .= '<span class="notification-badge">' . min($unread_count, 9) . ($unread_count > 9 ? '+' : '') . '</span>';
    }
    
    $html .= '<div class="notification-dropdown" id="notificationDropdown">';
    $html .= '<div class="notification-header">Notifications ' . ($unread_count > 0 ? '(' . $unread_count . ')' : '') . '</div>';
    
    if (empty($notifications)) {
        $html .= '<div class="notification-empty">No notifications yet</div>';
    } else {
        foreach ($notifications as $notif) {
            $html .= '<div class="notification-item">';
            $html .= '  <div class="notification-content">';
            $html .= '    <span class="notification-icon">' . $notif['icon'] . '</span>';
            $html .= '    <div class="notification-text">';
            $html .= '      <div class="notification-title">' . htmlspecialchars($notif['title']) . '</div>';
            $html .= '      <div class="notification-message">' . htmlspecialchars($notif['message']) . '</div>';
            $html .= '      <div class="notification-time">' . date('M d, H:i', strtotime($notif['date'])) . '</div>';
            $html .= '    </div>';
            $html .= '  </div>';
            $html .= '</div>';
        }
    }
    
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
?>