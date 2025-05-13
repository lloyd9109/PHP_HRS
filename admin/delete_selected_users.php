<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids'])) {
    $userIds = json_decode($_POST['ids'], true);

    // Prepare SQL query to delete the selected users
    $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
    $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders)");

    if ($stmt->execute($userIds)) {
        echo "Selected users deleted successfully!";
    } else {
        echo "Failed to delete selected users.";
    }
}
?>
