<?php
/**
 * User Class
 * Handles user authentication and management
 */

require_once __DIR__ . '/ActivityLog.php';

class User {
    private $db;
    private $user_id;
    private $email;
    private $name;
    private $roles;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Register a new user
     */
    public function register($name, $email, $password, $phone = '') {
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Default roles for new user
        $roles = json_encode(["customer"]);

        // Insert new user
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, phone, roles, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("sssss", $name, $email, $hashedPassword, $phone, $roles);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Registration failed: " . $stmt->error);
        }
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT user_id, name, email, password, roles FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User not found or inactive");
        }

        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            throw new Exception("Invalid password");
        }

        // Set session variables
        SessionManager::set('user_id', $user['user_id']);
        SessionManager::set('user_email', $user['email']);
        SessionManager::set('user_name', $user['name']);
        SessionManager::set('user_roles', json_decode($user['roles'], true));

        // Log login activity
        $activityLog = new ActivityLog();
        $activityLog->log(
            $user['user_id'],
            'login',
            'User logged in successfully',
            'user',
            $user['user_id']
        );

        return $user;
    }

    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update user profile
     */
    public function updateProfile($user_id, $name, $phone) {
        // Get old data
        $oldUser = $this->getUserById($user_id);
        $oldData = [
            'name' => $oldUser['name'],
            'phone' => $oldUser['phone']
        ];

        $stmt = $this->db->prepare("UPDATE users SET name = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $name, $phone, $user_id);
        
        if ($stmt->execute()) {
            // Log profile update
            $activityLog = new ActivityLog();
            $activityLog->log(
                $user_id,
                'profile_update',
                'User profile updated',
                'user',
                $user_id,
                $oldData,
                ['name' => $name, 'phone' => $phone]
            );
            return true;
        } else {
            throw new Exception("Update failed: " . $stmt->error);
        }
    }

    /**
     * Change password
     */
    public function changePassword($user_id, $oldPassword, $newPassword) {
        $stmt = $this->db->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!password_verify($oldPassword, $result['password'])) {
            throw new Exception("Current password is incorrect");
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashedPassword, $user_id);

        if ($stmt->execute()) {
            // Log password change
            $activityLog = new ActivityLog();
            $activityLog->log(
                $user_id,
                'password_change',
                'User password changed',
                'user',
                $user_id
            );
            return true;
        } else {
            throw new Exception("Password change failed");
        }
    }

    /**
     * Add role to user
     */
    public function addRole($user_id, $role) {
        $user = $this->getUserById($user_id);
        $roles = json_decode($user['roles'], true);

        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $rolesJson = json_encode($roles);
            
            $stmt = $this->db->prepare("UPDATE users SET roles = ? WHERE user_id = ?");
            $stmt->bind_param("si", $rolesJson, $user_id);
            $stmt->execute();
        }

        return true;
    }

    /**
     * Check if user has role
     */
    public function hasRole($user_id, $role) {
        $user = $this->getUserById($user_id);
        $roles = json_decode($user['roles'], true);
        return in_array($role, $roles);
    }
}
?>
