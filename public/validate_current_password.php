<?php
session_start();
require_once '../config/database.php';

// Assuming user_id is available in the session
$user_id = $_SESSION['user_id'];

if (isset($_POST['current-password'])) {
    $currentPassword = $_POST['current-password'];

    // Fetch the user's hashed password
    $sql = "SELECT password_hash FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($currentPassword, $user['password_hash'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    }
}
?>
