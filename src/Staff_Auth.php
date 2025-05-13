<?php
require_once '../config/database.php';

class Auth {
    // Register a new staff member
    public static function register($firstName, $lastName, $email, $phone, $password) {
        global $pdo;
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert staff into the database
        $stmt = $pdo->prepare('INSERT INTO staff (first_name, last_name, email, password_hash) VALUES (?, ?, ?,?)');  // Corrected column name
        return $stmt->execute([$firstName, $lastName, $email, $phone, $passwordHash]);
    }

    public static function login($email, $password) {
        global $pdo;
        
        // Get the staff member's data based on email
        $stmt = $pdo->prepare('SELECT * FROM staff WHERE email = ?');
        $stmt->execute([$email]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify the password (assuming the column name is 'password_hash')
        if ($staff && password_verify($password, $staff['password_hash'])) {  // Corrected column name
            session_start();
            $_SESSION['staff_id'] = $staff['id'];  // Store staff ID
            $_SESSION['staff_email'] = $staff['email'];  // Store staff email
    
            return true;
        }
        return false;
    }
    
    // Check if the email already exists
    public static function isEmailExists($email) {
        global $pdo;
        
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM staff WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    // Check if the staff member is logged in
    public static function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['staff_id']);
    }

    // Log out the staff member
    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
    }

    // Get the staff member's ID
    public static function getStaffId() {
        return isset($_SESSION['staff_id']) ? $_SESSION['staff_id'] : null;
    }
}
