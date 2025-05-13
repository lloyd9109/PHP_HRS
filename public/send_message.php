<?php
// send_message.php
session_start();
require_once '../config/database.php';

if (isset($_POST['message'])) {
    $userId = $_SESSION['user_id']; // Get the logged-in user's ID
    $message = $_POST['message'];
    
    $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
    $stmt->execute([$userId, $message]);

    // Respond with a success message
    echo json_encode(['status' => 'success']);
}

?>