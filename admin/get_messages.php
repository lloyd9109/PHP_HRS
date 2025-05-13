<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    $stmt = $pdo->prepare("
        SELECT message, is_admin, timestamp 
        FROM chat_messages 
        WHERE user_id = ? 
        ORDER BY timestamp ASC
    ");
    $stmt->execute([$userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($messages);
} else {
    echo json_encode([]);
}
?>
