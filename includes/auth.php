<?php
session_start();
require_once 'db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($username, $password, $email) {
        // Check if username or email already exists
        $existingUser = $this->db->fetch(
            "SELECT * FROM users WHERE username = :username OR email = :email",
            ['username' => $username, 'email' => $email]
        );
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Username or email already exists'
            ];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $userId = $this->db->insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email,
            'is_admin' => 0
        ]);
        
        return [
            'success' => true,
            'message' => 'Registration successful',
            'user_id' => $userId
        ];
    }
    
    public function login($username, $password) {
        // Find user by username
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE username = :username",
            ['username' => $username]
        );
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => $user['is_admin']
            ]
        ];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT id, username, email, is_admin FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        );
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }
}