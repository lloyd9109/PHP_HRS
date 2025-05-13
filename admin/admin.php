<?php
class Admin {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();
    
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            return true;
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_id']);
    }

    public function logout() {
        unset($_SESSION['admin_id']);
        session_destroy();
    }

    public function getAdminDetails() {
        if ($this->isLoggedIn()) {
            $stmt = $this->pdo->prepare("SELECT firstname, lastname FROM admin WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['admin_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
}
?>
