<?php
// send_admin_message.php
session_start();
require_once '../config/database.php';

if (isset($_POST['message']) && isset($_POST['user_id'])) {
    $adminId = $_SESSION['admin_id']; // Ensure admin is logged in
    $userId = $_POST['user_id'];
    $message = $_POST['message'];

    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (user_id, message, is_admin, timestamp) 
        VALUES (?, ?, 1, NOW())
    ");
    $stmt->execute([$userId, $message]);

    // Fetch the newly inserted message to return its details (including timestamp)
    $lastMessageStmt = $pdo->prepare("
        SELECT message, is_admin, timestamp 
        FROM chat_messages 
        WHERE user_id = ? AND is_admin = 1 
        ORDER BY timestamp DESC LIMIT 1
    ");
    $lastMessageStmt->execute([$userId]);
    $lastMessage = $lastMessageStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'message' => $lastMessage['message'],
        'timestamp' => $lastMessage['timestamp']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
?>
