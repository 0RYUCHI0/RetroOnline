<?php
/**
 * RetroGameHub Configuration
 * Database connection and global settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'RetroGameHub');
define('DB_PORT', 3306);

// Application Settings
define('SITE_NAME', 'RetroGameHub');
define('SITE_URL', 'http://localhost/retroonline');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('IMAGE_UPLOAD_PATH', UPLOAD_PATH . 'images/');
define('DEMO_UPLOAD_PATH', UPLOAD_PATH . 'demos/');

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('REMEMBER_ME_DAYS', 30);

// Commission Settings
define('PLATFORM_COMMISSION', 5.00); // 5%

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Database Connection
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                DB_PORT
            );

            if ($this->connection->connect_error) {
                throw new Exception('Database connection failed: ' . $this->connection->connect_error);
            }

            $this->connection->set_charset('utf8mb4');
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get database instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Close connection
     */
    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

/**
 * Session Manager
 */
class SessionManager {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function destroy() {
        $_SESSION = [];
        session_destroy();
    }

    public static function isLoggedIn() {
        return self::has('user_id') && self::has('user_email');
    }

    public static function getUserId() {
        return self::get('user_id');
    }

    public static function getUserEmail() {
        return self::get('user_email');
    }

    public static function getUserRoles() {
        return self::get('user_roles', []);
    }

    public static function hasRole($role) {
        $roles = self::getUserRoles();
        return in_array($role, $roles);
    }
}

// Initialize session
SessionManager::init();
?>
