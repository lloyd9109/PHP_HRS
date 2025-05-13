<?php
require_once '../config/database.php';
require_once '../src/Auth.php';

class Auth {
    // Register a new user
    public static function register($firstName, $lastName, $email, $cellphone, $password) {
        global $pdo;
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email,  cellphone, password_hash ) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$firstName, $lastName, $email, $cellphone, $passwordHash]);
    }

    // Login a user
    public static function login($email, $password) {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];  // Store user ID
            $_SESSION['user_email'] = $user['email'];  // Store user email
            
            return true;
        }
        return false;
    }

    // Check if email exists
    public static function isEmailExists($email) {
        global $pdo;
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    // Check if user is logged in
    public static function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    // Log out a user
    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
    }

    public function checkLoginStatus() {
        return isset($_SESSION['user_id']);
    }
    
    public function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
}
