<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $userId = $_POST['id'];

    // Prepare SQL query to delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$userId])) {
        echo "User deleted successfully!";
    } else {
        echo "Failed to delete user.";
    }
}
?>
